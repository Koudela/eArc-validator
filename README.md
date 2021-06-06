# eArc-validator

Lightweight dependency free validator component of the
[earc framework](https://github.com/Koudela/eArc-core) for an
[SOLID](https://en.wikipedia.org/wiki/SOLID) validation approach.

## table of Contents

- [Install](#install)
- [bootstrap](#bootstrap)
- [configure](#configure)
- [basic usage](#basic-usage)
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

```php
use eArc\Validator\ValidatorFactory;

$validator = (new ValidatorFactory())->build();
$validator->email();

$validator->validate('validator@example.com'); // returns true
$validator->validate('no-email-address'); // returns false

echo implode(', ', $validator->getLastErrorMessages()); // echos 'has to be a valid email address'
```

```php
use eArc\Validator\ValidatorFactory;

$validator = (new ValidatorFactory())->build();
$validator->email();
$validator->regex('/@example\.com$/');

$validator->validate('validator@example.com'); // returns true
$validator->validate('validator@example.de'); // returns false
$validator->validate('valid\\tor@example.de'); // returns false

echo implode(', ', $validator->getLastErrorMessages()); // echos 'has to be a valid email address'
```

## releases

### release 0.0
- first official release
