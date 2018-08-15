<?php

namespace eArc\validator;

final class Collector {

    private $lastId = 0;
    private $callStack = [];

    public function getId(): int
    {
        echo $this->lastId . '!';
        return $this->lastId++;
    }

    public function setCall(int $id, int $nextId, string $name, array $args): void
    {
        if ($name === 'with' || $name === 'withKey')
        {
            $this->callStack[count($this->callStack) - 1][$name] = $args[0];
        }
        else {
            $this->callStack[':' . $nextId] = ['id' => $id, 'name' => $name, 'args' => $args];
        }
    }

    public function getCallStack(): array
    {
        return $this->callStack;
    }
}