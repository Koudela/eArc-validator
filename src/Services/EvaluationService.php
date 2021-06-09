<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Validator\Services;

use eArc\Validator\Collections\Callbacks;
use eArc\Validator\Collections\Collector;
use eArc\Validator\Collections\Mappings;
use eArc\Validator\Exceptions\EvaluationException;
use eArc\Validator\Models\Call;
use eArc\Validator\Models\EvaluatedCall;
use eArc\Validator\Models\Result;
use eArc\Validator\Services\ErrorMessages\ErrorMessageGenerator;
use eArc\Validator\Validator;

class EvaluationService
{
    public function __construct(
        protected ErrorMessageGenerator $errorMessageGenerator,
        protected Callbacks $callbacks,
        protected Mappings $mappings,
    ) {}

    /** @var array<string, Call> */
    protected array $callStack;
    protected int $verbosity;
    protected mixed $value;

    public function evalCallStack(
        Collector $collector,
        mixed $value,
        int $verbosity = 2,
        array &$errors = [],
        bool $isNot = false,
    ): Result
    {
        $this->callStack = $collector->getCallStack();
        $this->value = $value;
        $this->verbosity = $verbosity;

        $result = $this->startRewind($errors, end($this->callStack), $isNot);

        return new Result($this->errorMessageGenerator, $result, $errors);
    }

    private function startRewind(array &$errors, Call $call, bool $isNot): bool
    {
        if ($call->name === 'NOT') {
            throw new EvaluationException("{697c5b90-4a01-4724-8e75-9afd2dc58766} NOT() can not be the last element of a Validation chain");
        }

        return $this->rewind($errors, $call, [], $isNot);
    }

    private function rewind(array &$errors, Call $call, array $rewindStack, bool $isNot): bool
    {
        $rewindStack[] = $call;

        if ($call->id === -1) {
            return $this->evalRewindStack($errors, $rewindStack, $isNot);
        }

        $key = ':'.$call->id;

        return $this->rewind($errors, $this->callStack[$key], $rewindStack, $isNot);
    }

    private function evalRewindStack(array &$errors, array $rewindStack, bool $isNot): bool
    {
        $returnBool = true;

        while ($call = array_pop($rewindStack)) {
            /** @var Call $call */
            switch ($call->name) {
                case 'NOT':
                    if (count($call->args) === 0) {
                        $isNot = !$isNot;
                        $bool = true;
                    } else {
                        $bool = $this->evalEncapsulated($call->args[0], $this->verbosity, $errors, $isNot);
                    }
                    break;
                case 'WHEN':
                    $bool = $this->evalEncapsulated($call->args[0], 0 , $errors, $isNot);

                    if ($bool) {
                        $bool = $this->evalEncapsulated($call->args[1], $this->verbosity, $errors, $isNot);
                    } else if (isset($call->args[2])) {
                        $bool = $this->evalEncapsulated($call->args[2], $this->verbosity, $errors, $isNot);
                    } else {
                        $bool = true;
                    }
                    break;
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'NoneOf':
                    // NoneOf(...) -> NOT(OR(...))
                    $isNot = !$isNot;
                case 'OneOf':
                    // OneOf() -> OR(a, b, c)
                case 'OR': $bool = $this->preEvalOR($errors, $call->args, $isNot);
                    break;
                case 'AllOf':
                    // AllOf(...) -> AND(...)
                case 'AND': $bool = $this->preEvalAND($errors, $call->args, $isNot);
                    break;
                default: $bool = $this->evalCallback($errors, $call, $isNot);
            }
            if (!$bool)
            {
                if ($this->verbosity < 2) {
                    return false;
                }

                $returnBool = false;
            }
        }

        return $returnBool;
    }

    private function preEvalOR(array &$errors, array $args, bool $isNot): bool
    {
        // NOT(OR(a, b,..) === AND(NOT(a), NOT(b),..)
        if ($isNot) {
            return $this->evalAND($errors, $args, $isNot);
        }

        return $this->evalOR($errors, $args, $isNot);
    }

    private function evalOR(array &$errors, array $args, bool $isNot): bool
    {
        $localErrors = [];

        foreach ($args as $arg) {
            $bool = $this->evalEncapsulated($arg, $this->verbosity, $localErrors, $isNot);

            if ($bool) {
                return true;
            }
        }

        $errors[] = new EvaluatedCall('OR', $args, null, null, $isNot, $localErrors);

        return false;
    }

    private function preEvalAND(array &$errors, array $args, bool $isNot): bool
    {
        // NOT(AND(a, b,..) === OR(NOT(a), NOT(b),..)
        if ($isNot) {
            return $this->evalOR($errors, $args, $isNot);
        }

        return $this->evalAND($errors, $args, $isNot);
    }

    private function evalAND(array &$errors, array $args, bool $isNot): bool
    {
        $returnBool = true;

        foreach ($args as $arg) {
            $bool = $this->evalEncapsulated($arg, $this->verbosity, $errors, $isNot);

            if (!$bool) {
                if ($this->verbosity < 2) {
                    return false;
                }

                $returnBool = false;
            }
        }
        return $returnBool;
    }

    private function evalCallback(array &$errors, Call $call, bool $isNot): bool
    {
        $callbackName = $this->mappings->get($call->name);

        while (str_starts_with($callbackName, 'not:')) {
            $isNot = !$isNot;
            $callbackName = substr($callbackName, 4);
        }

        $bool = $this->callbacks->get($callbackName)($this->value, ...$call->args);

        if ($isNot) {
            $bool = !$bool;
        }

        if (!$bool) {
            if ($isNot) {
                $call->name = 'not:'.$callbackName;
                $isNot = false;
            }

            if ($this->verbosity > 0) {
                $errors[] = $call->getEvaluatedCall($isNot);
            }
        }

        return $bool;
    }

    protected function evalEncapsulated(Validator $validator, int $verbosity, array &$errors, $isNot): bool
    {
        return (new EvaluationService($this->errorMessageGenerator, $this->callbacks, $this->mappings))
            ->evalCallStack(
                $validator->getCollector(),
                $this->value,
                $verbosity,
                $errors,
                $isNot
            )->isValid()
        ;
    }
}
