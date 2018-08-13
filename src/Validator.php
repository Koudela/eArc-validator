<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\validator;

/**
 * Class Validator
 * @method equal($item): Validator;
 * @method notEqual($item): Validator;
 * @method identical($item): Validator;
 * @method notIdentical($item): Validator;
 * @method max(int $max): Validator;
 * @method min(int $min): Validator;
 * @method maxLength(string $item): Validator;
 * @method minLength(string $item): Validator;
 * @method smaller(int $max): Validator;
 * @method greater(int $min): Validator;
 * @method email(): Validator;
 * @method url(): Validator;
 * @method ipAddress(): Validator;
 * @method macAddress(): Validator;
 * @method isRegex(): Validator;
 * @method regex(string $regex): Validator;
 * @method callback(callable $callback): Validator;
 * @method number(): Validator;
 * @method numeric(): Validator;
 * @method word(): Validator;
 * @method words(): Validator;
 * @method wordNumber(): Validator;
 * @method wordsNumber(): Validator;
 * @method blank(): Validator;
 * @method notBlank(): Validator;
 * @method null(): Validator;
 * @method notNull(): Validator;
 * @method empty(): Validator;
 * @method notEmpty(): Validator;
 * @method type(string $type): Validator;
 * @method checked(): Validator;
 * @method unchecked(): Validator;
 * @package eArc\validator
 */
class Validator extends ValidatorBase {}
