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

final class Collector {

    private $lastId = 0;
    private $callStack = [];
    private $errors = [];
    private $errorMessages = [];

    public function getId(): int
    {
        return $this->lastId++;
    }

    public function setCall(int $id, int $nextId, string $name, array $args): void
    {
        if ($name === 'with' || $name === 'withKey')
        {
            end($this->callStack);
            $this->callStack[key($this->callStack)][$name] = $args[0];
        }
        else {
            $this->callStack[':' . $nextId] = ['id' => $id, 'name' => $name, 'args' => $args];
        }
    }

    public function getCallStack(): array
    {
        return $this->callStack;
    }

    public function setErrors(array $errors, string $key = null): void
    {
        if (!$key) $this->errors[] = $errors;
        else $this->errors[$key] = $errors;
    }

    public function getErrorMessages(Messages $messages): array
    {
        foreach ($this->errors as $key => $err)
        {
            if (!isset($errorMessages[$key]))
            {
                $this->errorMessages[$key] = $messages->generateErrorMessages($err);
            }
        }
        return $this->errorMessages;
    }
}
