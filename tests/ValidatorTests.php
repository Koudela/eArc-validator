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
        $validatorFactory = new ValidatorFactory(['de' => [], 'en' => []]);
        $validator = $validatorFactory->build();
//        $validator->OR($validator->NOT()->notEmpty(), $validator->email());
//        self::assertFalse($validator->assert('explicit architecture.com'));
//        dump($validator->getErrorMessages());
//
//        self::assertTrue($validator->assert('koudela@gmx.de'));
//        dump($validator->getErrorMessages());
//
//        $validator = $validatorFactory->build();
//        $validator->notEmpty()->email();
//
//        self::assertFalse($validator->assert('explicit architecture.com'));
//        dump($validator->getErrorMessages());
//
//        $result = $validator->assert('koudela@gmx.de');
//        dump($validator->getErrorMessages());
//        self::assertTrue($result);
//
//        $result = $validator->assert('');
//        dump($validator->getErrorMessages());
//        self::assertTrue($result);
//
//        $validator->email()->ipAddress();
//
//        $validator->assert('hello');
//        $validator->assert('laber@tasche.com');
//        $validator->assert('hero');
//
//        dump($validator->getLastErrorMessages());

        $validator = (new ValidatorFactory())->build();
        $validator->email();
        #$validator->regex('/@example\.com$/');

        $result = $validator->validate('validator@example.com'); // returns true
        self::assertTrue($result);
        $result = $validator->validate('no-email-address'); // returns false
        self::assertFalse($result);
//        $result = $validator->validate('example@example.de'); // returns false
//        self::assertFalse($result);
        $result = $validator->validate('valid\\tor@example.com'); // returns false
        self::assertFalse($result);


        $validator = (new ValidatorFactory())->build();
        $validator->email()->regex('/@example\.com$/');

        $result = $validator->validate('validator@example.com'); // returns true
        self::assertTrue($result);
        $result = $validator->validate('no-email-address'); // returns false
        self::assertFalse($result);
        $result = $validator->validate('example@example.de'); // returns false
        self::assertFalse($result);
        $result = $validator->validate('v*lid*tor@example.com'); // returns false
        self::assertFalse($result);
    }
}
