<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Validator\Collections;

use eArc\Validator\Models\Call;

class Collector
{
    protected int $lastId = 0;
    /** @var array<string, Call> */
    protected array $callStack = [];

    public function getNextId(): int
    {
        return $this->lastId++;
    }

    public function setCall(Call $call): void
    {
        if ($call->name === 'with') {
            end($this->callStack);
            $this->callStack[key($this->callStack)]->with = $call->args[0];
        } elseif ($call->name === 'withKey') {
            end($this->callStack);
            $this->callStack[key($this->callStack)]->withKey = $call->args[0];
        } else {
            $this->callStack[':'.$call->nextId] = $call;
        }
    }

    /**
     * @return array<string, Call>
     */
    public function getCallStack(): array
    {
        return $this->callStack;
    }
}
