# eArc-validator

Lightweight dependency free validator component of the
[earc framework](https://github.com/Koudela/eArc-core) for an
[SOLID](https://en.wikipedia.org/wiki/SOLID) validation approach.

## table of Contents

- [Install](#install)
- [bootstrap](#bootstrap)
- [configure](#configure)
- [basic usage](#basic-usage)
    - [check](#check)
    - [validate](#validate)
    - [chaining validation logic](#chaining-validation-logic)
        - [conjunction AND (AllOf)](#conjunction-and-allof)
        - [conjunction OR (OneOf)](#conjunction-or-oneof)
        - [conjunction XOR (NoneOf)](#conjunction-xor-noneof)
        - [logical inversion NOT](#logical-inversion-not)
        - [conditional validation WHEN](#conditional-validation-when)
    - [mixing validators](#mixing-validators)
    - [complete validation (assert)](#complete-validation-assert)
    - [locally individualized messages](#locally-individualized-messages)
        - [with key](#with-key)
        - [with](#with)
    - [object property validation (using attributes)](#object-property-validation-using-attributes)
- [advanced usage](#advanced-usage)
    - [globally individualized messages (localization)](#globally-individualized-messages-localization)
    - [extending the atomic validation logic](#extending-the-atomic-validation-logic)
- [releases](#releases)
    - [release 0.0](#release-00)

## install

```shell script
$ composer require earc/validator
```

## bootstrap

The earc/validator does not require any bootstrapping.

## configure

The earc/validator does not require any configuration.

## basic usage

### check

Validation will be done in two steps.
1. Define the validation logic to validate against.
2. Check if the validation logic holds true against a target.

After setting the validation logic, checking can be repeated against different 
targets unlimited times.

```php
use eArc\Validator\ValidatorFactory;

$validator = (new ValidatorFactory())->build();
$validator->email();

$validator->check('validator@example.com'); // returns true
$validator->check('no-email-address'); // returns false
```

By passing a `true` value as second parameter instead of returning true or false,
an `AssertException` is thrown.

```php
use eArc\Validator\Exceptions\AssertException;
use eArc\Validator\ValidatorFactory;

$validator = (new ValidatorFactory())->build();
$validator->email();

$throwOnNotValid = true;
try {
    $validator->check('no-email-address', $throwOnNotValid);
} catch (AssertException) {
    // email not valid
}
```

### validate 

To receive a message explaining why the value is not valid use `validate()` instead
of `check()`. It returns a result object instead of a bool.

```php
use eArc\Validator\Exceptions\AssertException;
use eArc\Validator\ValidatorFactory;

$validator = (new ValidatorFactory())->build();
$validator->email();

$result = $validator->validate('no-email-address');
if (!$result->isValid()) {
    echo $result->getFirstErrorMessage(); // echos 'has to be a valid email address'
}

$throwOnError = true;
try {
    $validator->validate('no-email-address', $throwOnError);
} catch (AssertException $e) {
    echo $e->getResult()->getFirstErrorMessage(); // echos 'has to be a valid email address'
}
```

You can [use your own messages](#locally-individualized-messages).

### chaining validation logic

#### conjunction AND (AllOf)

The simplest chain is build by the `AND` conjunction. You can do this in three 
different ways:
1. Subsequent calls to atomic validator methods.
2. Method chaining of atomic validator methods.
3. Use of the `AND()` or the `AllOf()` method.

The validation holds true, if all arguments validate to true.

```php
use eArc\Validator\ValidatorFactory;

// 1. Subsequent calls to atomic validator methods:

$validator = (new ValidatorFactory())->build();
$validator->email();
$validator->regex('/@example\.com$/');

$validator->check('validator@example.com'); // returns true
$validator->check('validator@example.de'); // returns false
$validator->check('valid\\tor@example.com'); // returns false

// 2. Method chaining of atomic validator methods:

$validator = (new ValidatorFactory())
    ->build()
    ->email()
    ->regex('/@example\.com$/');

// 3.1 Use of the `AND()` method.

$validator = (new ValidatorFactory())->build();
$validator = $validator->AND(
    $validator->email(),
    $validator->regex('/@example\.com$/')    
);

// 3.1 Use of the `AllOf()` method.

$validator = (new ValidatorFactory())->build();
$validator = $validator->AllOf(
    $validator->email(),
    $validator->regex('/@example\.com$/')    
);
```

Choosing between or mixing them is just a case of syntactic preference.

#### conjunction OR (OneOf)

The logical `OR` is represented by two methods `OR()` and `OneOf()`. Both take
`Validators` as arguments.

The validation holds true, if one argument validates to true.

```php
use eArc\Validator\ValidatorFactory;

$validator = (new ValidatorFactory())->build();
$validator = $validator->email()->OR(
    $validator->regex('/@example\.com$/'),
    $validator->regex('/@coding-crimes\.com$/'),    
);

$validator->check('validator@example.com'); // returns true
$validator->check('validator@coding-crimes.com'); // returns true
$validator->check('validtor@example.de'); // returns false

// The above validation logic is equivalent to:

$validator = (new ValidatorFactory())->build();
$validator = $validator->email()->OneOf(
    $validator->regex('/@example\.com$/'),
    $validator->regex('/@coding-crimes\.com$/')
);
```

#### conjunction XOR (NoneOf)

The logical `XOR` is represented by two methods `XOR()` and `NoneOf()`. Both take
`Validators` as arguments.

The validation holds true, if no argument validates to true.

```php
use eArc\Validator\ValidatorFactory;

$validator = (new ValidatorFactory())->build();
$validator = $validator->email()->XOR(
    $validator->regex('/@blacklisted\.com$/'),
    $validator->regex('/@coding-crimes\.com$/'),    
);

$validator->check('validator@example.com'); // returns true
$validator->check('validator@blacklisted.com'); // returns false
$validator->check('validator@coding-crimes.com'); // returns false

// The above validation logic is equivalent to:

$validator = (new ValidatorFactory())->build();
$validator = $validator->email()->NoneOf(
    $validator->regex('/@blacklisted\.com$/'),
    $validator->regex('/@coding-crimes\.com$/')
);
```


#### logical inversion NOT

Logical inversion can be realized via the `NOT()` method.

`NOT()` holds true, if all arguments and the validation chain on the right-hand 
side validates to false.

#### conditional validation WHEN

To realize conditional validation there exists the `WHEN()` method. 

If the first argument is true, the second argument will be validated. Otherwise,
the third argument will be used for validation, which defaults to true. 

### mixing validators

### complete validation (assert)

### locally individualized messages

There are two methods that influence the validation error messages. `withKey()`
sets the message key for the validation error message. `with()` sets the validation
error message itself.

Messages can be [individualized on a global scope](#globally-individualized-messages-localization),
too.

#### with key

#### with

### object property validation (using attributes)

## advanced usage

### globally individualized messages (localization)

### extending the atomic validation logic

## releases

### release 0.0
- first official release
- PHP ^8.0
- is coming soon (code base <= release candidate status)
- TODO:
  - Documentation
  - Tests
