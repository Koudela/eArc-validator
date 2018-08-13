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

    public function __construct($additionalCallbacks = null)
    {
        $this->callbacks = include(__DIR__ . '/callbacks/basic.php');

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
        if (!isset($this->callbacks[$name]))
        {
            throw new NoCallbackException($name . ' is not a valid ' . Callbacks::class . ' function');
        }

        return $this->callbacks[$name];
    }
}
