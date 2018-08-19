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

use eArc\validator\exceptions\NoCallbackException;

/**
 * Class Callbacks
 * @package eArc\validator
 */
class Callbacks {

    protected $callbacks;
    protected $mappings;

    public function __construct(array $additionalCallbacks = [], Mappings $mappings = null)
    {
        $this->callbacks = include(__DIR__ . '/callbacks/basic.php');
        $this->mappings = $mappings ?? new Mappings();

        foreach ($additionalCallbacks as $callbacks)
        {
            $this->merge($callbacks);
        }
    }

    protected function append(array $callbacks): void
    {
        \array_replace($this->callbacks, $callbacks);
    }

    protected function load(string $callbackPath): void
    {
        \array_replace($this->callbacks, include($callbackPath));
    }

    public function merge($callbacks): void
    {
        if (\is_string($callbacks)) {
            $this->load($callbacks);
            return;
        }
        if (\is_array($callbacks)) {
            $this->append($callbacks);
            return;
        }
        if ($callbacks instanceof Callbacks) {
            $this->append($callbacks->getCallbacks());
        }
    }

    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    public function getMappings(): Mappings
    {
        return $this->mappings;
    }

    public function get(string $name): \Closure
    {
        $name = $this->mappings->get($name);

        if (!isset($this->callbacks[$name]))
        {
            throw new NoCallbackException($name . ' is not a valid ' . Callbacks::class . ' function');
        }
        return $this->callbacks[$name];
    }
}
