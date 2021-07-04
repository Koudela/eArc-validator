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

namespace earc\ValidatorTests\Objects;

use eArc\Validator\Attributes\Validation;

class BasicEmail
{
    public function __construct(
        #[Validation(['AllOf' => [['email' => null, 'notEmpty'], ['email', 'notEmpty' => null]]])]
        protected string $email
    ) {}
}
