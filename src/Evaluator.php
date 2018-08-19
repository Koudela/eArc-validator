<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\validator;

use eArc\validator\exceptions\EvaluationException;
use eArc\validator\exceptions\NoCallbackException;
use eArc\validator\exceptions\AssertException;

final class Evaluator
{
    private $callbacks;
    private $callStack;
    private $value;
    private $result;
    private $verbosity;

    public function __construct(Callbacks $callbacks, Collector $collector, $value, string $key = null, $verbosity = 0, $throwOnResultIsFalse = false)
    {
        $this->callbacks = $callbacks;
        $this->callStack = $collector->getCallStack();
        $this->value = $value;
        $this->verbosity = $verbosity;

        $errors = [];
        end($this->callStack);
        $this->result = $this->startRewind($errors, $this->callStack[key($this->callStack)], false);

        $collector->setErrors($errors, $key);

        if ($throwOnResultIsFalse && !$this->result) throw new AssertException();
   }

    private function startRewind(array &$errors, array $call, bool $isNot): bool
    {
        if ($call['name'] === 'NOT')
        {
            throw new EvaluationException("NOT() can not be the last element on a Validation chain");
        }
        return $this->rewind($errors, $call, [], $isNot);
    }

    private function rewind(array &$errors, array $call, array $rewindStack, bool $isNot): bool
    {
        $rewindStack[] = $call;

        if ($call['id'] === -1) return $this->evalRewindStack($errors, $rewindStack, $isNot);

        $key = ':' .  $call['id'];
        return $this->rewind($errors, $this->callStack[$key], $rewindStack, $isNot);
    }

    private function evalRewindStack(array &$errors, array $rewindStack, bool $isNot): bool
    {
        $returnBool = true;

        while ($call = array_pop($rewindStack))
        {
            switch ($call['name'])
            {
                case 'NOT':
                    if (count($call['args']) === 0)
                    {
                        $isNot = !$isNot;
                        $bool = true;
                    }
                    else {
                        $bool = $this->startRewind($errors, $this->callStack[$this->getKey($call['args'][0])], $isNot);
                    }
                    break;
                case 'OR': $bool = $this->preEvalOR($errors, $call['args'], $isNot);
                    break;
                case 'AND': $bool = $this->preEvalAND($errors, $call['args'], $isNot);
                    break;
                default: $bool = $this->evalCallback($errors, $call, $isNot);
            }
            if (!$bool)
            {
                if ($this->verbosity < 2) return false;
                $returnBool = false;
            }
        }
        return $returnBool;
    }

    private function preEvalOR(array &$errors, array $args, bool $isNot): bool
    {
        // NOT(OR(a, b,..) === AND(NOT(a), NOT(b),..)
        if ($isNot) return $this->evalAND($errors, $args, $isNot);
        return $this->evalOR($errors, $args, $isNot);
    }

    private function evalOR(array &$errors, array $args, bool $isNot): bool
    {
        $localErrors = [];
        foreach ($args as $arg)
        {
            $bool = $this->startRewind($localErrors, $this->callStack[$this->getKey($arg)], $isNot);
            if ($bool) return true;
        }
        $errors['OR'] = $localErrors;
        return false;
    }

    private function preEvalAND(array &$errors, array $args, bool $isNot): bool
    {
        // NOT(AND(a, b,..) === OR(NOT(a), NOT(b),..)
        if ($isNot) return $this->evalOR($errors, $args, $isNot);
        return $this->evalAND($errors, $args, $isNot);
    }

    private function evalAND(array &$errors, array $args, bool $isNot): bool
    {
        $returnBool = true;

        foreach ($args as $arg)
        {
            $bool = $this->startRewind($errors, $this->callStack[$this->getKey($arg)], $isNot);
            if (!$bool)
            {
                if ($this->verbosity < 2) return false;
                $returnBool = false;
            }
        }
        return $returnBool;
    }

    private function evalCallback(array &$errors, array $call, bool $isNot): bool
    {
        try
        {
            $bool = $this->callbacks->get(($isNot ? 'not:' : '') . $call['name'])($this->value, ...$call['args']);
            if (!$bool && $isNot) {
                $call['name'] = 'not:' . $call['name'];
                $isNot = false;
            }
        }
        catch (NoCallbackException $e)
        {
            if (!$isNot) throw $e;

            $bool = !$this->callbacks->get($call['name'])($this->value, ...$call['args']);
        }

        if (!$bool && $this->verbosity > 0)
        {
            $call['isNot'] = $isNot;
            $call['value'] = $this->value;
            $errors[] = $call;
        }

        return $bool;
    }

    private function getKey(Validator $pointer): string
    {
        return ':' . $pointer->getId();
    }

    public function getResult(): bool
    {
        return $this->result;
    }
}
