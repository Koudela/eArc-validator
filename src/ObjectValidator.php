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

use eArc\Validator\Attributes\Validation;
use eArc\Validator\Models\Results;
use ReflectionClass;

class ObjectValidator
{
    public function check(object $object, bool $throwOnNotValid = false): bool
    {
        $reflectionClass = new ReflectionClass($object);

        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes(Validation::class);
            if ($attribute = array_pop($attributes)) {
                $property->setAccessible(true);
                $value = $property->getValue($object);
                /** @var Validation $validation */
                $validation = $attribute->newInstance();
                $valid = $validation->getValidator($object)->check($value, $throwOnNotValid);

                if (!$valid) {
                    return false;
                }
            }
        }

        return true;
    }

    public function validate(object $object, bool $throwOnNotValid = false): Results
    {
        return $this->getResults($object, $throwOnNotValid, 1);
    }

    public function assert(object $object, bool $throwOnNotValid = false): Results
    {
        return $this->getResults($object, $throwOnNotValid, 2);
    }

    protected function getResults(object $object, bool $throwOnNotValid, int $verbosity): Results
    {
        $reflectionClass = new ReflectionClass($object);

        $results = [];
        $isValid = true;

        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes(Validation::class);
            if ($attribute = array_pop($attributes)) {
                $property->setAccessible(true);
                $value = $property->getValue($object);
                /** @var Validation $validation */
                $validation = $attribute->newInstance();
                $validator = $validation->getValidator($object);
                if ($verbosity === 1)  {
                    $result = $validator->validate($value, $throwOnNotValid);
                } else {
                    $result = $validator->assert($value, $throwOnNotValid);
                }

                $results[$property->getName()] = $result;
                $isValid = $isValid && $result->isValid();
            }
        }

        return new Results($results, $isValid);
    }
}
