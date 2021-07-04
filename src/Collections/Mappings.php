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

class Mappings
{
    /** @var array<string, string> */
    protected array $mappings;

    /**
     * @param array<string, string> $mappings
     */
    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * @return array<string, string>
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }

    public function get(string $name): string
    {
        return array_key_exists($name, $this->mappings) ? $this->mappings[$name] : $name;
    }

}
