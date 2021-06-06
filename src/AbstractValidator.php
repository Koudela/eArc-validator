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
use eArc\Validator\Exceptions\AssertException;
use eArc\Validator\Models\Call;
use eArc\Validator\Models\Result;
use eArc\Validator\Services\EvaluationService;

abstract class AbstractValidator
{
    public function __construct(
        protected EvaluationService $evaluationService,
        protected Collector $collector,
        protected int $id = -1,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param array<int, mixed> $args
     */
    public function __call(string $name, array $args): Validator
    {
        $nextId = $this->collector->getNextId();
        $this->collector->setCall(new Call($this->id, $nextId, $name, $args));

        return new Validator(
            $this->evaluationService,
            $this->collector,
            $nextId
        );
    }

    public function check($value, $throwOnNotValid = false): bool
    {
        return $this->evaluate($value, $throwOnNotValid, 0)->isValid();
    }

    public function validate($value,$throwOnNotValid = false): Result
    {
        return $this->evaluate($value, $throwOnNotValid, 1);
    }

    public function assert($value, $throwOnNotValid = false): Result
    {
        return $this->evaluate($value, $throwOnNotValid, 2);
    }

    protected function evaluate($value, $throwOnNotValid = false, int $verbosity = 1): Result
    {
        $result = $this->evaluationService->evalCallStack($this->collector, $value, $verbosity);

        if ($throwOnNotValid && !$result->isValid()) {
            throw new AssertException(var_export($result->getErrorMessages()));
        }

        return $result;
    }

    abstract public static function getCallbacks(): Callbacks;

    abstract public static function getMappings(): Mappings;
}
