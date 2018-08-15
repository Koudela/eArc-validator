<?php

namespace eArc\validator;

use eArc\validator\exceptions\EvaluationException;
use eArc\validator\exceptions\NoCallbackException;
use eArc\validator\exceptions\AssertException;

final class Evaluator
{
    private $callbacks;
    private $callStack;
    private $value;
    private $key;
    private $result;

    private $errors;

    public function __construct(Callbacks $callbacks, Collector $collector, $value, int $key = null, $throwOnResultIsFalse = false)
    {
        $this->callbacks = $callbacks;
        $this->callStack = $collector->getCallStack();
        $this->value = $value;
        $this->key = $key;

        $this->result = $this->startRewind($this->callStack[\count($this->callStack) - 1], false);

        if ($throwOnResultIsFalse && !$this->result) throw new AssertException($this->errors[\count($this->errors) - 1]);
    }

    private function startRewind(array $call, bool $isNot): bool
    {
        if ($call['name'] === 'NOT')
        {
            throw new EvaluationException("NOT() can not be the last element on a Validation chain");
        }
        return $this->rewind($call, [], $isNot);
    }

    private function rewind(array $call, array $rewindStack, bool $isNot): bool
    {
        $rewindStack[] = $call;

        if ($call['id'] === -1) return $this->evalRewindStack($rewindStack, $isNot);

        $key = ':' .  $call['id'];
        return $this->rewind($this->callStack[$key], $rewindStack, $isNot);
    }

    private function evalRewindStack(array $rewindStack, bool $isNot): bool
    {
        while ($call = array_pop($rewindStack))
        {
            if ($call['name'] === 'NOT')
            {
                if (count($call['args']) === 0)
                {
                    $isNot = !$isNot;
                    continue;
                }
                $bool = $this->startRewind($this->callStack[$this->getKey($call['args'][0])], $isNot);

                if (!$bool) return false;
                continue;
            }

            if ($call['name'] === 'OR') return $this->evalOR($call['args'], $isNot);

            if ($call['name'] === 'AND') return $this->evalAND($call['args'], $isNot);

            return $this->evalCallback($call['name'], $call['args'], $isNot);
        }
        return true;
    }

    private function evalOR(array $args, bool $isNot): bool
    {
        foreach ($args as $arg)
        {
            $bool = $this->startRewind($this->callStack[$this->getKey($arg)], $isNot);
            // NOT(OR(a, b,..) === AND (NOT(a), NOT(b),..)
            if ($bool && !$isNot) return true;
            if (!$bool && $isNot) return false;
        }
        // if (isNot === false) => OR(all false) otherwise AND(all true)
        return $isNot;
    }

    private function evalAND(array $args, bool $isNot): bool
    {
        foreach ($args as $arg)
        {
            $bool = $this->startRewind($this->callStack[$this->getKey($arg)], $isNot);
            // NOT(AND(a, b,..)) === OR(NOT(a), NOT(b),..)
            if (!$bool && !$isNot) return false;
            if ($bool && $isNot) return true;
        }
        // if (isNot === false) => AND(all true) otherwise OR(all false)
        return !$isNot;
    }

    private function evalCallback(string $name, array $args, bool $isNot): bool
    {
        try
        {
            return $this->callbacks->get(($isNot ? 'not:' : '') . $name)($this->value, ...$args);
        }
        catch(NoCallbackException $e)
        {
            if (!$isNot) throw $e;

            return !$this->callbacks->get($name)($this->value, ...$args);
        }
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
