<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\ValidatorTests;

use eArc\Validator\ValidatorFactory;
use PHPUnit\Framework\TestCase;

class ValidatorTests extends TestCase
{
    public function test() {
        $validatorFactory = new ValidatorFactory();
        $validator = $validatorFactory->build();
        $validator = $validator->email();
        self::assertFalse($validator->assert('explicit architecture', null, true));
        dump($validator->getErrorMessages());
    }
}
