<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

return [
    'OR' => function () {},
    'equal' => function($a, $b) {return ($a == $b);},
    'notEqual' => function($a, $b) {return ($a != $b);},
    'identical' => function($a, $b) {return ($a === $b);},
    'notIdentical' => function($a, $b) {return ($a !== $b);},
    'max' => function ($a, $b) {return ($a <= $b);},
    'min' => function ($a, $b) {return ($a >= $b);},
    'maxLength' => function ($a, $b) {return (\mb_strlen($a) <= $b);},
    'minLength' => function ($a, $b) {return (\mb_strlen($a) >= $b);},
    'smaller' => function ($a, $b) {return ($a < $b);},
    'greater' => function ($a, $b) {return ($a > $b);},
    'email' => function ($a) {return (false !== \filter_var($a, FILTER_VALIDATE_EMAIL));},
    'url' => function ($a) {return (false !== \filter_var($a, FILTER_VALIDATE_URL));},
    'ipAddress' => function ($a) {return (false !== \filter_var($a, FILTER_VALIDATE_IP));},
    'macAddress' => function ($a) {return (false !== \filter_var($a, FILTER_VALIDATE_MAC));},
    'isRegex' => function ($a) {return (false !== \filter_var($a, FILTER_VALIDATE_REGEXP));},
    'regex' => function ($a, $b) {return (1 === \preg_match($b, $a));},
    'callback' => function ($a, $b) {return (true === $b($a));},
    'number' => function ($a) {return (\is_int($a) || \preg_match('/^[0-9]+$/', $a));},
    'numeric' => function ($a) {return \is_numeric($a);},
    'word' => function ($a) {return (1 === \preg_match('/^\w+$/', $a));},
    'words' => function ($a) {return (1 === \preg_match('/^[\w\ ]+$/', $a));},
    'wordNumber' => function ($a) {return (1 === \preg_match('/^[\w0-9]+$/', $a));},
    'wordsNumber' => function ($a) {return (1 === \preg_match('/^[\w0-9\ ]+$/', $a));},
    'blank' => function ($a) {return ('' === $a || null === $a);},
    'notBlank' => function ($a) {return ('' !== $a && null !== $a);},
    'null' => function ($a) {return \is_null($a);},
    'notNull' => function ($a) {return !\is_null($a);},
    'empty' => function ($a) {return empty($a);},
    'notEmpty' => function ($a) {return !empty($a);},
    'type' => function ($a, $b) {return (gettype($a) === $b);},
    'checked' => function ($a) {return ($a === 1 || $a === '1' || $a === 'on' || $a === true || $a === 'true');},
    'unchecked' => function ($a) {return (!isset($a) || $a === null || $a === 0 || $a === '0' || $a === 'off' || $a === false || $a === 'false' || $a === '');},
];
