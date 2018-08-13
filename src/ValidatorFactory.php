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
 * Class ValidatorFactory
 * @package eArc\validator
 */
class ValidatorFactory {

    protected $messages;
    protected $callbacks;
    protected $validatorClassName;
    protected $messageKey;

    public function __construct(Messages $messages, Callbacks $callbacks, $messageKey = null, string $validatorClassName = '\\eArc\\validator\\Validator')
    {
        $this->messages = $messages;
        $this->callbacks = $callbacks;
        $this->validatorClassName = $validatorClassName;
        $this->messageKey = $messageKey;
    }

    public function build($messageKey = null, string $validatorClassName = null): Validator
    {
        if (!$validatorClassName) $validatorClassName = $this->validatorClassName;
        return new $validatorClassName($this->messages, $this->callbacks, $messageKey ?? $this->messageKey);
    }

    public static function make(Messages $messages = null, Callbacks $callbacks = null, $messageKey = null, string $validatorClassName = '\\eArc\\validator\\Validator'): Validator
    {
        if (!$messages) $messages = new Messages();
        if (!$callbacks) $callbacks = new Callbacks();
        return new $validatorClassName($messages, $callbacks, $messageKey);
    }
}
