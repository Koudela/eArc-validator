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

use eArc\Validator\Models\Result;
use RuntimeException;

/**
 * The assertion has failed
 */
class AssertException extends RuntimeException
{
    public function __construct(protected Result $result)
    {
        parent::__construct(var_export($result->getErrorMessages(), true));
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
