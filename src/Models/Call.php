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

namespace eArc\Validator\Models;

class Call
{
    /**
     * @param array<int, mixed> $args
     */
    public function __construct(
        public int $id,
        public int $nextId,
        public string $name,
        public array $args,
        public string|null $with = null,
        public string|null $withKey = null,
    ) {}

    public function getEvaluatedCall(bool $isNot): EvaluatedCall
    {
        return new EvaluatedCall($this->name, $this->args, $this->with, $this->withKey, $isNot);
    }
}
