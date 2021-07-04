<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * validation component
 *
 * @package earc/validator
 * @link https://github.com/Koudela/eArc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Validator\Services;

use eArc\Validator\AbstractValidator;
use eArc\Validator\Collections\Callbacks;
use eArc\Validator\Collections\Mappings;
use eArc\Validator\Exceptions\EvaluationException;
use eArc\Validator\Models\Call;
use eArc\Validator\Models\EvaluatedCall;
use eArc\Validator\Models\Result;
use eArc\Validator\Services\ErrorMessages\ErrorMessageGenerator;

class EvaluationService
{
    public function __construct(
        protected ErrorMessageGenerator $errorMessageGenerator,
        protected Callbacks $callbacks,
        protected Mappings $mappings,
    ) {}

    protected AbstractValidator $validator;
    /** @var array<string, Call> */
    protected array $callStack;
    protected int $verbosity;
    protected mixed $value;

    public function evalCallStack(
        AbstractValidator $validator,
        mixed $value,
        int $verbosity = 2,
    ): Result
    {
        $this->validator = $validator;
        $this->callStack = $validator->getCollector()->getCallStack();
        $this->value = $value;
        $this->verbosity = $verbosity;

        $errors = [];
        $rewindStack = [];

        while ($call = array_pop($this->callStack)) {
            $rewindStack = array_merge($rewindStack, $this->startRewind($errors, $call));
        }

        $result = $this->evalRewindStack($errors, $rewindStack, false);

        return new Result($this->errorMessageGenerator, $result, $errors);
    }

    private function startRewind(array &$errors, Call $call): array
    {
        if ($call->name === 'NOT' && empty($call->args)) {
            throw new EvaluationException("{697c5b90-4a01-4724-8e75-9afd2dc58766} NOT() can not be the last element of a Validation chain");
        }

        return $this->rewind($errors, $call, []);
    }

    private function rewind(array &$errors, Call $call, array $rewindStack): array
    {
        $innerRewindStack = [];

        if (array_key_exists($call->name, $this->validator::SYNTAX_METHODS)) {
            $innerRewindStack = [];
            /** @var AbstractValidator $validator */
            foreach ($call->args as $validator) {
                $key = ':'.$validator->getInitialId();
                $innerCall = $this->callStack[$key];
                unset ($this->callStack[$key]);
                $innerRewindStack[] = $this->startRewind($errors, $innerCall);
            }
        }

        $rewindStack[] = [$call, $innerRewindStack];

        $key = ':'.$call->id;

        if (!array_key_exists($key, $this->callStack)) {
            return $rewindStack;
        } else {
            $call = $this->callStack[$key];
            unset ($this->callStack[$key]);
        }

        return $this->rewind($errors, $call, $rewindStack);
    }

    private function evalRewindStack(array &$errors, array $rewindStack, bool $isNot): bool
    {
        $returnBool = true;

        while ($item = array_pop($rewindStack)) {
            $call = $item[0];
            /** @var Call $call */
            switch ($call->name) {
                case 'NOT':
                    $isNot = !$isNot;
                    if (count($call->args) === 0) {
                        $bool = true;
                    } else {
                        #$bool = $this->evalEncapsulated($call->args[0], $this->verbosity, $errors, $isNot, $call->args[0]->getInitialId());
                        $bool = $this->evalRewindStack($errors, $item[1][0], $isNot);
                    }
                    break;
                case 'WHEN':
                    #$bool = $this->evalEncapsulated($call->args[0], 0 , $errors, $isNot, $call->args[0]->getInitialId());
                    $bool = $this->evalRewindStack($errors, $item[1][0], $isNot);

                    if ($bool) {
                        #$bool = $this->evalEncapsulated($call->args[1], $this->verbosity, $errors, $isNot, $call->args[1]->getInitialId());
                        $bool = $this->evalRewindStack($errors, $item[1][1], $isNot);
                    } else if (isset($call->args[2])) {
                        #$bool = $this->evalEncapsulated($call->args[2], $this->verbosity, $errors, $isNot, $call->args[2]->getInitialId());
                        $bool = $this->evalRewindStack($errors, $item[1][2], $isNot);
                    } else {
                        $bool = true;
                    }
                    break;
                case 'NoneOf':
                    // NoneOf(...) -> XOR(...)
                    /** @noinspection PhpMissingBreakStatementInspection */
                case 'XOR':
                    // XOR(...) -> NOT(OR(...))
                    $isNot = !$isNot;
                case 'OneOf':
                    // OneOf(...) -> OR(...)
                case 'OR': $bool = $this->preEvalOR($errors, $call->args, $isNot, $item[1]);
                    break;
                case 'AllOf':
                    // AllOf(...) -> AND(...)
                case 'AND': $bool = $this->preEvalAND($errors, $call->args, $isNot, $item[1]);
                    break;
                default: $bool = $this->evalCallback($errors, $call, $isNot);
            }

            if (!$bool) {
                if ($this->verbosity < 2) {
                    return false;
                }

                $returnBool = false;
            }
        }

        return $returnBool;
    }

    private function preEvalOR(array &$errors, array $args, bool $isNot, array $rewindStack): bool
    {
        // NOT(OR(a, b,..) === AND(NOT(a), NOT(b),..)
        if ($isNot) {
            return $this->evalAND($errors, $args, $isNot, $rewindStack);
        }

        return $this->evalOR($errors, $args, $isNot, $rewindStack);
    }

    private function evalOR(array &$errors, array $args, bool $isNot, array $rewindStack): bool
    {
        $localErrors = [];
        /** @var AbstractValidator $arg */
        foreach ($args as $pos => $arg) {
            #$bool = $this->evalEncapsulated($arg, $this->verbosity, $localErrors, $isNot, $arg->getInitialId());
            $bool = $this->evalRewindStack($errors, $rewindStack[$pos], $isNot);

            if ($bool) {
                return true;
            }
        }

        $errors[] = new EvaluatedCall('OR', $args, null, null, $isNot, $localErrors);

        return false;
    }

    private function preEvalAND(array &$errors, array $args, bool $isNot, array $rewindStack): bool
    {
        // NOT(AND(a, b,..) === OR(NOT(a), NOT(b),..)
        if ($isNot) {
            return $this->evalOR($errors, $args, $isNot, $rewindStack);
        }

        return $this->evalAND($errors, $args, $isNot, $rewindStack);
    }

    private function evalAND(array &$errors, array $args, bool $isNot, array $rewindStack): bool
    {
        $returnBool = true;

        /** @var AbstractValidator $arg */
        foreach ($args as $pos => $arg) {
            #$bool = $this->evalEncapsulated($arg, $this->verbosity, $errors, $isNot, $arg->getInitialId());
            $bool = $this->evalRewindStack($errors, $rewindStack[$pos], $isNot);

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
}
