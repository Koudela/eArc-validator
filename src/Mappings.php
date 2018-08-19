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

    public function __construct($additionalMappings = null)
    {
        $this->mappings = include(__DIR__ . '/mappings/map.php');

        if ($additionalMappings)
        {
            if (is_string($additionalMappings)) {
                $this->load($additionalMappings);
            } else {
                $this->append($additionalMappings);
            }
        }
    }

    public function append(array $mappings)
    {
        array_replace($this->mappings, $mappings);
    }

    public function load(string $mappingPath): void
    {
        array_replace($this->mappings, include($mappingPath));
    }

    public function get(string $name): string
    {
        return isset($this->mappings[$name]) ? $this->mappings[$name] : $name;
    }
}
