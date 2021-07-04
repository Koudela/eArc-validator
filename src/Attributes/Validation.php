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

namespace eArc\Validator\Attributes;

use Attribute;
use eArc\Validator\AbstractValidator;
use eArc\Validator\Validator;
use eArc\Validator\ValidatorFactory;

#[Attribute]
class Validation
{
    public function __construct(
        protected string|array $method,
        protected ?string $validatorFactoryFQCN = null,
    ) {}

    public function getValidator(object $object): AbstractValidator
    {
        if (is_string($this->method)) {
            $methodName = $this->method;

            if (is_null($this->validatorFactoryFQCN)) {
                return $object->$methodName();
            }

            $className = $this->validatorFactoryFQCN;

            return $className::$methodName();
        }

        /** @var ValidatorFactory $validatorFactoryClass */
        $validatorFactoryClass = is_null($this->validatorFactoryFQCN) ? ValidatorFactory::class : $this->validatorFactoryFQCN;
        $baseValidator = (new $validatorFactoryClass)->build();

        return $this->getValidatorFromArguments($baseValidator, $this->method);
    }

    protected function getValidatorFromArguments(Validator $validator, array $arguments): Validator
    {
        foreach ($arguments as $methodName => $args) {
            if (array_key_exists($methodName, $validator::SYNTAX_METHODS)) {
                $validators = $this->getSyntaxArguments($validator, $args);
                $validator = $validator->$methodName(...$validators);
            } elseif (is_int($methodName)) {
                $validator = $validator->$args();
            } else {
                $validator = $validator->$methodName($args);
            }
        }

        return $validator;
    }

    /**
     * @return array<int, Validator>
     */
    protected function getSyntaxArguments(Validator $baseValidator, array $arguments): array
    {
        $validators = [];

        foreach ($arguments as $args) {
            $validators[] = $this->getValidatorFromArguments($baseValidator, $args);
        }

        return $validators;
    }
}
