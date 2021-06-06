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
use eArc\Validator\Collections\Mappings;

/**
 * syntax methods:
 * @method Validator NOT(Validator $validator = null)
 * @method Validator OR(Validator ...$validators)
 * @method Validator AND(Validator ...$validators)
 * @method Validator WHEN(Validator $validator, Validator $validator, ?Validator $validator)
 * @method Validator NoneOf(Validator ...$validators)
 * @method Validator AllOf(Validator ...$validators)
 * @method Validator OneOf(Validator ...$validators)
 *
 * extended syntax methods:
 * @method Validator with(string $message)
 * @method Validator withKey(string $messageKey)
 *
 * validation methods:
 * @method Validator equal($item)
 * @method Validator notEqual($item)
 * @method Validator identical($item)
 * @method Validator notIdentical($item)
 * @method Validator max(int $max)
 * @method Validator min(int $min)
 * @method Validator maxLength(string $item)
 * @method Validator minLength(string $item)
 * @method Validator smaller(int $max)
 * @method Validator greater(int $min)
 * @method Validator email()
 * @method Validator url()
 * @method Validator ipAddress()
 * @method Validator macAddress()
 * @method Validator isRegex()
 * @method Validator number()
 * @method Validator numeric()
 * @method Validator word()
 * @method Validator words()
 * @method Validator wordNumber()
 * @method Validator wordsNumber()
 * @method Validator blank()
 * @method Validator notBlank()
 * @method Validator null()
 * @method Validator notNull()
 * @method Validator empty()
 * @method Validator notEmpty()
 * @method Validator type(string $type)
 * @method Validator checked()
 * @method Validator unchecked()
 *
 * extended validation methods:
 * @method Validator regex(string $regex)
 * @method Validator callback(callable $callback)
 */
class Validator extends AbstractValidator
{
    public static function getCallbacks(): Callbacks
    {
        $methods = [
            'OR' => function () {},
            'equal' => function($a, $b) {return ($a == $b);},
            'identical' => function($a, $b) {return ($a === $b);},
            'max' => function ($a, $b) {return ($a <= $b);},
            'min' => function ($a, $b) {return ($a >= $b);},
            'smaller' => function ($a, $b) {return ($a < $b);},
            'greater' => function ($a, $b) {return ($a > $b);},
            'email' => function ($a) {return (false !== filter_var($a, FILTER_VALIDATE_EMAIL));},
            'url' => function ($a) {return (false !== filter_var($a, FILTER_VALIDATE_URL));},
            'ipAddress' => function ($a) {return (false !== filter_var($a, FILTER_VALIDATE_IP));},
            'macAddress' => function ($a) {return (false !== filter_var($a, FILTER_VALIDATE_MAC));},
            'isRegex' => function ($a) {return (false !== filter_var($a, FILTER_VALIDATE_REGEXP));},
            'number' => function ($a) {return (is_int($a) || preg_match('/^[0-9]+$/', $a));},
            'numeric' => function ($a) {return is_numeric($a);},
            'word' => function ($a) {return (1 === preg_match('/^\w+$/', $a));},
            'words' => function ($a) {return (1 === preg_match('/^\w[\w ]*$/', $a));},
            'wordNumber' => function ($a) {return (1 === preg_match('/^[\w0-9]+$/', $a));},
            'wordsNumber' => function ($a) {return (1 === preg_match('/^[\w0-9][\w0-9 ]*$/', $a));},
            'blank' => function ($a) {return ('' === $a || null === $a);},
            'null' => function ($a) {return is_null($a);},
            'empty' => function ($a) {return empty($a);},
            'type' => function ($a, $b) {return (gettype($a) === $b);},
            'checked' => function ($a) {return ($a === 1 || $a === '1' || $a === 'on' || $a === true || $a === 'true');},
            'unchecked' => function ($a) {return (!isset($a) || $a === 0 || $a === '0' || $a === 'off' || $a === false || $a === 'false' || $a === '');},

            'regex' => function ($a, $b) {return (1 === preg_match($b, $a));},
            'callback' => function ($a, $b) {return (true === $b($a));},
        ];

        return new Callbacks($methods);
    }

    public static function getMappings(): Mappings
    {
        $rawMapping = [
            'not:smaller' => 'min',
            'not:greater' => 'max',
            'not:min' => 'smaller',
            'not:max' => 'greater',

            'notNull' => 'not:null',
            'not:notNull' => 'null',
            'notEmpty' => 'not:empty',
            'not:notEmpty' => 'empty',
            'notBlank' => 'not:blank',
            'not:notBlank' => 'blank',
            'notEqual' => 'not:equal',
            'not:notEqual' => 'equal',
            'notIdentical' => 'not:identical',
            'not:notIdentical' => 'identical',
        ];

        return new Mappings($rawMapping);
    }
}
