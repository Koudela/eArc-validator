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

namespace eArc\ValidatorTests;

use eArc\Validator\ObjectValidator;
use eArc\Validator\Exceptions\AssertException;
use eArc\Validator\ValidatorFactory;
use earc\ValidatorTests\Objects\BasicEmail;
use PHPUnit\Framework\TestCase;

class ValidatorTests extends TestCase
{
    public function testBasicUsageCheck(): void
    {
        $validator = (new ValidatorFactory())->build();
        $validator->email();

        self::assertTrue($validator->check('validator@example.com'));
        self::assertFalse($validator->check('no-email-address'));

        $objectValidator = new ObjectValidator();
        self::assertTrue($objectValidator->check(new BasicEmail('validator@example.com')));
        self::assertFalse($objectValidator->check(new BasicEmail('no-email-address')));

        $check = null;
        try {
            $check = $validator->check('validator@example.com', true);
        } catch (AssertException) {}
        self::assertTrue($check);

        $check = null;
        try {
            $check = $objectValidator->check(new BasicEmail('validator@example.com'), true);
        } catch (AssertException) {}
        self::assertTrue($check);

        $e = null;
        try {
            $validator->check('no-email-address', true);
        } catch (AssertException $e) {}
        self::assertInstanceOf(AssertException::class, $e);
        self::assertFalse($e->getResult()->isValid());

        $e = null;
        try {
            $objectValidator->check(new BasicEmail('no-email-address'), true);
        } catch (AssertException $e) {}
        self::assertInstanceOf(AssertException::class, $e);
        self::assertFalse($e->getResult()->isValid());
    }

    public function testBasicUsageValidate(): void
    {
        $validator = (new ValidatorFactory())->build();
        $validator->email();


        $result = $validator->validate('no-email-address');
        self::assertFalse($result->isValid());
        self::assertSame($result->getFirstErrorMessage(), 'has to be a valid email address');

        $objectValidator = new ObjectValidator();
        $results = $objectValidator->validate(new BasicEmail('no-email-address'));
        self::assertFalse($results->isValid());
        self::assertSame($results->getResults()['email']->getFirstErrorMessage(), 'has to be a valid email address');

        $e = null;
        try {
            $validator->validate('no-email-address', true);
        } catch (AssertException $e) {}
        self::assertSame($e->getResult()->getFirstErrorMessage(), 'has to be a valid email address');

        $e = null;
        try {
            $objectValidator->validate(new BasicEmail('no-email-address'), true);
        } catch (AssertException $e) {}
        self::assertSame($e->getResult()->getFirstErrorMessage(), 'has to be a valid email address');
    }

    public function testBasicUsageChainingValidationLogicConjunctionAnd(): void
    {
        $validator = (new ValidatorFactory())->build();
        $validator->email();
        $validator->regex('/@example\.com$/');

        self::assertTrue($validator->check('validator@example.com'));
        self::assertFalse($validator->check('validator@example.de'));
        self::assertFalse($validator->check('valid\\tor@example.com'));

        // 2. Method chaining of atomic validator methods:

        $validator = (new ValidatorFactory())
            ->build()
            ->email()
            ->regex('/@example\.com$/');

        self::assertTrue($validator->check('validator@example.com'));
        self::assertFalse($validator->check('validator@example.de'));
        self::assertFalse($validator->check('valid\\tor@example.com'));

        // 3.1 Use of the `AND()` method.

        $validator = (new ValidatorFactory())->build();
        $validator = $validator->AND(
            $validator->email(),
            $validator->regex('/@example\.com$/')
        );

        self::assertTrue($validator->check('validator@example.com'));
        self::assertFalse($validator->check('validator@example.de'));
        self::assertFalse($validator->check('valid\\tor@example.com'));

        // 3.1 Use of the `AllOf()` method.

        $validator = (new ValidatorFactory())->build();
        $validator = $validator->AllOf(
            $validator->email(),
            $validator->regex('/@example\.com$/')
        );

        self::assertTrue($validator->check('validator@example.com'));
        self::assertFalse($validator->check('validator@example.de'));
        self::assertFalse($validator->check('valid\\tor@example.com'));
    }

    public function testBasicUsageChainingValidationLogicConjunctionOr(): void
    {
        $validator = (new ValidatorFactory())->build();
        $validator = $validator->email()->OR(
            $validator->regex('/@example\.com$/'),
            $validator->regex('/@coding-crimes\.com$/'),
        );

        self::assertTrue($validator->check('validator@example.com'));
        self::assertTrue($validator->check('validator@coding-crimes.com'));
        self::assertFalse($validator->check('validator@example.de'));

        // The above validation logic is equivalent to:

        $validator = (new ValidatorFactory())->build();
        $validator = $validator->email()->OneOf(
            $validator->regex('/@example\.com$/'),
            $validator->regex('/@coding-crimes\.com$/')
        );

        self::assertTrue($validator->check('validator@example.com'));
        self::assertTrue($validator->check('validator@coding-crimes.com'));
        self::assertFalse($validator->check('validator@example.de'));
    }

    public function testBasicUsageChainingValidationLogicConjunctionXor(): void
    {
        $validator = (new ValidatorFactory())->build();
        $validator = $validator->email()->XOR(
            $validator->regex('/@blacklisted\.com$/'),
            $validator->regex('/@coding-crimes\.com$/'),
        );

        self::assertTrue($validator->check('validator@example.com'));
        self::assertFalse($validator->check('validator@blacklisted.com'));
        self::assertFalse($validator->check('validator@coding-crimes.com'));

        // The above validation logic is equivalent to:

        $validator = (new ValidatorFactory())->build();
        $validator = $validator->email()->NoneOf(
            $validator->regex('/@blacklisted\.com$/'),
            $validator->regex('/@coding-crimes\.com$/')
        );

        self::assertTrue($validator->check('validator@example.com'));
        self::assertFalse($validator->check('validator@blacklisted.com'));
        self::assertFalse($validator->check('validator@coding-crimes.com'));
    }

    public function testBasicUsageChainingValidationLogicLogicalInversionNot()
    {
    }

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
        self::assertTrue($result->isValid());
        $result = $validator->validate('no-email-address'); // returns false
        self::assertFalse($result->isValid());
//        $result = $validator->validate('example@example.de'); // returns false
//        self::assertFalse($result->isValid());
        $result = $validator->validate('valid\\tor@example.com'); // returns false
        self::assertFalse($result->isValid());


        $validator = (new ValidatorFactory())->build();
        $validator->email()->regex('/@example\.com$/');

        $result = $validator->validate('validator@example.com'); // returns true
        self::assertTrue($result->isValid());
        $result = $validator->validate('no-email-address'); // returns false
        self::assertFalse($result->isValid());
        $result = $validator->validate('example@example.de'); // returns false
        self::assertFalse($result->isValid());
        $result = $validator->validate('valid\\tor@example.com'); // returns false
        self::assertFalse($result->isValid());
    }
}
