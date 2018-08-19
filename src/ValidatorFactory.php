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

    protected $callbacks;
    protected $messages;
    protected $validatorClassName;

    public function __construct(Callbacks $callbacks = null, Messages $messages = null, string $validatorClassName = '\\eArc\\validator\\Validator')
    {
        $this->callbacks = $callbacks ? $callbacks : new Callbacks();
        $this->messages = $messages ? $messages : new Messages();
        $this->validatorClassName = $validatorClassName;
    }

    public function getCallbacks(): Callbacks
    {
        return $this->callbacks;
    }

    public function getMessages(): Messages
    {
        return $this->messages;
    }

    public function build( Callbacks $callbacks = null, Messages $messages = null, string $validatorClassName = null): Validator
    {
        return new ($validatorClassName ? $validatorClassName : $this->validatorClassName)(
            $callbacks ? $callbacks : $this->callbacks,
            $messages ? $messages : $this->messages
        );
    }

    public static function make(Callbacks $callbacks = null, Messages $messages = null, string $validatorClassName = '\\eArc\\validator\\Validator'): Validator
    {
        return new $validatorClassName(
            $callbacks ? $callbacks : new Callbacks(),
            $messages ? $messages : new Messages()
        );
    }
}
