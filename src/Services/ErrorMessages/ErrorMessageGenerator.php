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

namespace eArc\Validator\Services\ErrorMessages;

use eArc\Validator\Models\EvaluatedCall;

class ErrorMessageGenerator extends CallToMessageService
{
    /**
     * @param array<int, EvaluatedCall> $errors
     */
    public function generateErrorMessages(array $errors, string $prefix = null): array
    {
        $messages = [];

        foreach ($errors as $call) {
            if ($call->name === 'OR') {
                $messages[] = $this->evalOR($call, $prefix);
            } else {
                $messages[] = $this->getMessage($call, $prefix);
            }
        }

        return $messages;
    }

    /**
     * @param array<int, EvaluatedCall> $errors
     */
    protected function evalOR(array $errors, $prefix): string
    {
        return implode($this->messages->get('OR'), $this->generateErrorMessages($errors, $prefix));
    }
}
