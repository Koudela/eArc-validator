<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * validation component
 *
 * @package earc/validator
 * @link https://github.com/Koudela/eArc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Validator;

use eArc\Validator\Collections\Callbacks;
use eArc\Validator\Collections\Collector;
use eArc\Validator\Collections\Mappings;
use eArc\Validator\Exceptions\AssertException;
use eArc\Validator\Models\Result;
use eArc\Validator\Services\EvaluationService;

abstract class AbstractValidator
{
    const SYNTAX_METHODS = [
        'NOT' => true,
        'XOR' => true,
        'OR' => true,
        'AND' => true,
        'WHEN' => true,
        'NoneOf' => true,
        'OneOf' => true,
        'AllOf' => true,
    ];

    protected int $initialId;

    public function __construct(
        protected EvaluationService $evaluationService,
        protected Collector $collector,
        protected int $id = -1,
    ) {
        $this->initialId = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getInitialId(): int
    {
        return $this->initialId;
    }

    public function getCollector(): Collector
    {
        return $this->collector;
    }

    /**
     * @param array<int, mixed> $args
     */
    public function __call(string $name, array $args): Validator
    {
        $nextId = $this->collector->setCall($this->getId(), $name, $args);

        $this->id = $nextId-1;

        return new Validator(
            $this->evaluationService,
            $this->collector,
            $nextId
        );
    }

    public function check(mixed $value, bool $throwOnNotValid = false): bool
    {
        return $this->evaluate($value, $throwOnNotValid, 0)->isValid();
    }

    public function validate(mixed $value, bool $throwOnNotValid = false): Result
    {
        return $this->evaluate($value, $throwOnNotValid, 1);
    }

    public function assert(mixed $value, bool $throwOnNotValid = false): Result
    {
        return $this->evaluate($value, $throwOnNotValid, 2);
    }

    protected function evaluate(mixed $value, bool $throwOnNotValid, int $verbosity): Result
    {
        $result = $this->evaluationService->evalCallStack($this, $value, $verbosity);

        if ($throwOnNotValid && !$result->isValid()) {
            throw new AssertException($result);
        }

        return $result;
    }

    abstract public static function getCallbacks(): Callbacks;

    abstract public static function getMappings(): Mappings;
}
