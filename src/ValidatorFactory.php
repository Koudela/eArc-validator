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
    protected $mappings;
    protected $validatorClassName;

    public function __construct(
        Callbacks $callbacks = null,
        Messages $messages = null,
        Mappings $mappings = null,
        string $validatorClassName = '\\eArc\\validator\\Validator'
    )
    {
        $this->callbacks = $callbacks ?? new Callbacks();
        $this->messages = $messages ?? new Messages();
        $this->mappings = $mappings ?? new Mappings();
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

    public function getMappings(): Mappings
    {
        return $this->mappings;
    }

    public function build(
        Callbacks $callbacks = null,
        Messages $messages = null,
        Mappings $mappings = null,
        string $validatorClassName = null
    ): Validator
    {
        return new ($validatorClassName ?? $this->validatorClassName)(
            $callbacks ?? $this->callbacks,
            $messages ?? $this->messages,
            $mappings ?? $this->mappings
        );
    }

    public static function make(
        Callbacks $callbacks = null,
        Messages $messages = null,
        Mappings $mappings = null,
        string $validatorClassName = '\\eArc\\validator\\Validator'
    ): Validator
    {
        return new $validatorClassName(
            $callbacks ?? new Callbacks(),
            $messages ?? new Messages(),
            $mappings ?? new Mappings()
        );
    }
}
