<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Validator;

use eArc\Validator\Collections\Callbacks;
use eArc\Validator\Collections\Collector;
use eArc\Validator\Collections\Mappings;
use eArc\Validator\Collections\Messages;
use eArc\Validator\Services\Evaluator;

abstract class AbstractValidator
{
    /** @var int */
    protected $id;
    /** @var Callbacks */
    protected $callbacks;
    /** @var Messages */
    protected $messages;
    /** @var Collector */
    protected $collector;

    public function __construct(Callbacks $callbacks, Messages $messages, Collector $collector, int $id = null)
    {
        $this->callbacks = $callbacks;
        $this->messages = $messages;
        $this->collector = $collector;
        $this->id = $id ?? -1;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function __call($name, $args)
    {
        $nextId = $this->collector->getId();
        $this->collector->setCall($this->id, $nextId, $name, $args);

        return new Validator($this->callbacks, $this->messages, $this->collector, $nextId);
    }

    public function check($value, string $key = null, $throwOnResultIsFalse = false): bool
    {
        return $this->evaluate($value, $key, 0, $throwOnResultIsFalse);
    }

    public function validate($value, string $key = null, $throwOnResultIsFalse = false): bool
    {
        return $this->evaluate($value, $key, 1, $throwOnResultIsFalse);
    }

    public function assert($value, string $key = null, $throwOnResultIsFalse = false): bool
    {
        return $this->evaluate($value, $key, 2, $throwOnResultIsFalse);
    }

    private function evaluate($value, string $key = null, $verbosity = 1, $throwOnResultIsFalse = false): bool
    {
        return (
            new Evaluator($this, $this->callbacks, $this->collector, $value, $key, $verbosity, $throwOnResultIsFalse)
        )->getResult();
    }

    public function getErrorMessages(string $prefix = null): array
    {
        return $this->collector->getErrorMessages($this->messages, $prefix);
    }

    abstract public static function getCallbacks(Mappings $mappings): Callbacks;

    abstract public static function getMappings(): Mappings;
}
