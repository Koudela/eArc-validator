<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Validator\Models;

class EvaluatedCall
{
    /**
     * @param array<int, mixed> $args
     */
    public function __construct(
        public string $name,
        public array $args,
        public string|null $with = null,
        public string|null $withKey = null,
        public bool $isNot = false,
        public array|null $localErrors = null,
    ) {}
}
