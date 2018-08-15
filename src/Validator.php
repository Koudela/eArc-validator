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
 * Class Validator
 * @method NOT(|Validator $validator)
 * @method OR(Validator ...$validators): Validator;
 * @method AND(Validator ...$validators): Validator;
 * @method with(string $message): Validator;
 * @method withKey(string $messageKey): Validator;
 * @method equal($item): Validator;
 * @method notEqual($item): Validator;
 * @method identical($item): Validator;
 * @method notIdentical($item): Validator;
 * @method max(int $max): Validator;
 * @method min(int $min): Validator;
 * @method maxLength(string $item): Validator;
 * @method minLength(string $item): Validator;
 * @method smaller(int $max): Validator;
 * @method greater(int $min): Validator;
 * @method email(): Validator;
 * @method url(): Validator;
 * @method ipAddress(): Validator;
 * @method macAddress(): Validator;
 * @method isRegex(): Validator;
 * @method regex(string $regex): Validator;
 * @method callback(callable $callback): Validator;
 * @method number(): Validator;
 * @method numeric(): Validator;
 * @method word(): Validator;
 * @method words(): Validator;
 * @method wordNumber(): Validator;
 * @method wordsNumber(): Validator;
 * @method blank(): Validator;
 * @method notBlank(): Validator;
 * @method null(): Validator;
 * @method notNull(): Validator;
 * @method empty(): Validator;
 * @method notEmpty(): Validator;
 * @method type(string $type): Validator;
 * @method checked(): Validator;
 * @method unchecked(): Validator;
 * @package eArc\validator
 */
class Validator {

    protected $id;
    protected $callbacks;
    protected $collector;

    public function __construct(Callbacks $callbacks, Collector $collector = null, $id = null)
    {
        $this->callbacks = $callbacks;
        $this->collector = $collector ?? new Collector();
        $this->id = $id ?? -1;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function __call($name, $args): Validator
    {
        $nextId = $this->collector->getId();
        $this->collector->setCall($this->id, $nextId, $name, $args);
        return new Validator($this->callbacks, $this->collector, $nextId);
    }

    public function validate($value, string $key = null): bool
    {
        return (new Evaluator($this->callbacks, $this->collector, $value, $key, false))->getResult();
    }

    public function assert($value, string $key = null): bool
    {
        return (new Evaluator($this->callbacks, $this->collector, $value, $key, true))->getResult();
    }
}
