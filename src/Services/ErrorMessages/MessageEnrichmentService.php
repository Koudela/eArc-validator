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

abstract class MessageEnrichmentService
{
    /**
     * @param array<int, mixed> $args
     */
    public function enrich(string $message, array $args, bool $isNot): string
    {
        foreach ($args as $key => $arg) {
            $args[$key] = $this->argumentToString($args);
        }

        $enrichedMessage = sprintf($message, ...$args);

        return $isNot ? 'NOT ('.$enrichedMessage.')' : $enrichedMessage;
    }

    protected function argumentToString(mixed $arg): string
    {
        if (is_array($arg)) {
            return $this->argumentsToString($arg);
        } elseif (is_object($arg)) {
            if (method_exists($arg, '__toString')) {
                return (string) $arg;
            }

            return get_class($arg);
        } elseif (is_bool($arg)) {
            return $arg ? 'true' : 'false';
        } elseif (is_scalar($arg)) {
            return (string) $arg;
        }

        return var_export($arg, true);
    }

    protected function argumentsToString(array $args): string
    {
        $transformedArgs = [];

        foreach($args as $arg) {
            $transformedArgs[] = $this->argumentToString($arg);
        }

        return '[' . implode(', ', $transformedArgs) . ']';
    }
}
