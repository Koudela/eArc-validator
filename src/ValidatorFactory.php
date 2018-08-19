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
        $this->mappings = $mappings ?? new Mappings();
        $this->callbacks = $callbacks ?? new Callbacks([], $mappings);
        $this->messages = $messages ?? new Messages(['en'], [], $mappings);
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
        $callbacks = $callbacks ?? $this->callbacks;
        $messages = $messages ?? $this->messages;
        if ($mappings) {
            $callbacks->getMappings()->merge($mappings);
            $messages->getMappings()->merge($mappings);
        }
        if (!$validatorClassName) $validatorClassName = $this->validatorClassName;

        return new $validatorClassName(
            $callbacks ?? $this->callbacks,
            $messages ?? $this->messages
        );
    }

    public static function make(
        Callbacks $callbacks = null,
        Messages $messages = null,
        Mappings $mappings = null,
        string $validatorClassName = '\\eArc\\validator\\Validator'
    ): Validator
    {
        if (!$mappings) $mappings = new Mappings();
        else {
            if ($callbacks) {
                $callbacks->getMappings()->merge($mappings);
            }
            if ($messages) {
                $messages->getMappings()->merge($mappings);
            }
        }
        return new $validatorClassName(
            $callbacks ?? new Callbacks([], $mappings),
            $messages ?? new Messages(['en'], [], $mappings)
        );
    }
}
