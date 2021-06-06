<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Validator\Models;

use eArc\Validator\Services\ErrorMessages\ErrorMessageGenerator;

class Result
{
    protected array|null $generatedMessages;
    protected array $generatedMessagesWithPrefix = [];

    public function __construct(
        protected ErrorMessageGenerator $errorMessageGenerator,
        protected bool $result,
        protected array $errors,
    ) {}

    public function isValid(): bool
    {
        return $this->result;
    }

    /**
     * @return array<int, <string|null>>
     */
    public function getErrorMessages(string $prefix = null): array
    {
        if (empty($this->errors)) {
            return [];
        }

        $this->generateErrorMessages();

        return is_null($prefix)
            ? $this->generatedMessages
            : $this->generatedMessagesWithPrefix[$prefix];
    }
    public function getFirstErrorMessage(string $prefix = null): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $this->generateErrorMessages($prefix);

        return is_null($prefix)
            ? reset($this->generatedMessages)
            : reset($this->generatedMessagesWithPrefix[$prefix]);
    }

    public function getLastErrorMessage(string $prefix = null): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $this->generateErrorMessages($prefix);

        return is_null($prefix)
            ? end($this->generatedMessages)
            : end($this->generatedMessagesWithPrefix[$prefix]);

    }

    protected function generateErrorMessages(string $prefix = null): void
    {
        if (empty($this->errors)) {
            return;
        }

        if (is_null($prefix)) {
            if (is_null($this->generatedMessages)) {
                $this->generatedMessages = $this->errorMessageGenerator
                    ->generateErrorMessages($this->errors);
            }
        } else {
            if (!array_key_exists($prefix, $this->generatedMessagesWithPrefix)) {
                $this->generatedMessagesWithPrefix[$prefix] = $this->errorMessageGenerator
                    ->generateErrorMessages($this->errors, $prefix);
            }
        }
    }
}
