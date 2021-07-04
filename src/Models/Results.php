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

class Results
{
    /**
     * @param array<string, Result> $results
     */
    public function __construct(protected array $results, protected bool $isValid) {}

    public function getResults(): array
    {
        return $this->results;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }
}
