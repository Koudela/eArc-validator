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

namespace eArc\Validator\Collections;

use eArc\Validator\Models\Call;

class Collector
{
    protected int $lastId = -1;
    /** @var array<string, Call> */
    protected array $callStack = [];

    public function setCall(int $callId, string $callName, array $callArgs): int
    {
        $this->lastId += 2;
        $nextId = $this->lastId;

        if ($callName === 'with') {
            end($this->callStack);
            $this->callStack[key($this->callStack)]->with = $callArgs[0];
        } elseif ($callName === 'withKey') {
            end($this->callStack);
            $this->callStack[key($this->callStack)]->withKey = $callArgs[0];
        } else {
            $this->callStack[':'.$nextId] = new Call($callId, $nextId, $callName, $callArgs);
        }

        return $nextId;
    }

    /**
     * @return array<string, Call>
     */
    public function getCallStack(): array
    {
        return $this->callStack;
    }
}
