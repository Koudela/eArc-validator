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
    'not:smaller' => 'min',
    'not:greater' => 'max',
    'not:min' => 'smaller',
    'not:max' => 'greater',

    'notNull' => 'not:null',
    'not:notNull' => 'null',
    'notEmpty' => 'not:Empty',
    'not:notEmpty' => 'empty',
    'notBlank' => 'not:blank',
    'not:notBlank' => 'blank',
    'notEqual' => 'not:equal',
    'not:notEqual' => 'equal',
    'notIdentical' => 'not:identical',
    'not:notIdentical' => 'identical',
];
