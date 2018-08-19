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

/**
 * Class Callbacks
 * @package eArc\validator
 */
class Mappings {

    protected $mappings;

    public function __construct(array $additionalMappings = [])
    {
        $this->mappings = include(__DIR__ . '/mappings/map.php');

        foreach ($additionalMappings as $mapping)
        {
            $this->merge($mapping);
        }
    }

    protected function append(array $mappings): void
    {
        \array_replace($this->mappings, $mappings);
    }

    protected function load(string $mappingPath): void
    {
        \array_replace($this->mappings, include($mappingPath));
    }

    public function merge($mappings): void
    {
        if (\is_string($mappings)) {
            $this->load($mappings);
            return;
        }
        if (\is_array($mappings)) {
            $this->append($mappings);
            return;
        }
        if ($mappings instanceof Mappings) {
            $this->append($mappings->getMappings());
        }
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }

    public function get(string $name): string
    {
        return isset($this->mappings[$name]) ? $this->mappings[$name] : $name;
    }
}
