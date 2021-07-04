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

namespace eArc\Validator\Exceptions;

use RuntimeException;

/**
 * No message can be found, build or is available
 */
class NoMessageException extends RuntimeException {}
