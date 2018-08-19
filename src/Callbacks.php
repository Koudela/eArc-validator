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

    public function __construct($additionalCallbacks = null, Mappings $mappings = null)
    {
        $this->callbacks = include(__DIR__ . '/callbacks/basic.php');
        $this->mappings = $mappings ?? new Mappings();

        if ($additionalCallbacks)
        {
            if (is_string($additionalCallbacks)) {
                $this->load($additionalCallbacks);
            } else {
                $this->append($additionalCallbacks);
            }
        }
    }

    public function append(array $callbacks)
    {
        array_replace($this->callbacks, $callbacks);
    }

    public function load(string $callbackPath): void
    {
        array_replace($this->callbacks, include($callbackPath));
    }

    /**
     * @param string $name
     * @return \Closure
     */
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
