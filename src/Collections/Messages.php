<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */
namespace eArc\Validator\Collections;

use eArc\Validator\Exceptions\NoMessageException;
use Exception;

class Messages
{
    /** @var array<string, array<string, string>> */
    protected $messages = [];
    /** @var Mappings */
    protected $mappings;

    public function __construct(array $messages, Mappings $mappings)
    {
        $this->messages = $messages;
        $this->mappings = $mappings;
    }

    public function get(string $name): string
    {
        $name = $this->mappings->get($name);

        $languageCodes = [];

        foreach ($this->messages as $key => $messages) {
            if (array_key_exists($name, $messages)) {
                return $messages[$name];
            }

            $languageCodes[] = $key;
        }

        throw new NoMessageException(sprintf(
            'No message %s defined for languages [%s]',
            $name,
            implode(', ', $languageCodes)
        ));
    }

    public function generateErrorMessages(array $errors, string $prefix = null): array
    {
        $messages = [];

        foreach ($errors as $key => $call) {
            if ($key === 'OR') {
                $messages[] = $this->evalOR($call, $prefix);
            } else {
                $messages[] = $this->evalCall($call, $prefix);
            }
        }

        return $messages;
    }

    protected function arrayToString(array $args): string
    {
        $transformedArgs = [];

        foreach($args as $arg) {
            if (is_array($arg)) {
                $transformedArgs[] = $this->arrayToString($args);
            } elseif (!is_scalar($arg)) {
                $transformedArgs[] = (string) $arg;
            } elseif (is_bool($arg)) {
                $transformedArgs[] = $arg ? 'true' : 'false';
            } else {
                $transformedArgs[] = $arg;
            }
        }

        return '[' . implode(', ', $transformedArgs) . ']';
    }

    protected function eval(string $name, $args, bool $isNot): string
    {
        foreach ($args as $key => $arg) {
            if (is_array($arg)) {
                $args[$key] = $this->arrayToString($args);
            }
        }

        return ($isNot ? 'NOT ' : '').sprintf($this->get($name), ...$args);
    }

    protected function evalCall($call, $prefix): string
    {
        if (array_key_exists('with', $call) && $call['with']) {
            return $call['with'];
        }

        if (array_key_exists('withKey', $call) && $call['withKey']) {
            $call['name'] = $call['withKey'];
        }

        if (!$prefix) {
            return $this->eval($call['name'], $call['args'], $call['isNot']);
        }

        try {
            return $this->eval($prefix . ':' . $call['name'], $call['args'], $call['isNot']);
        } catch (Exception $e) {
            return $this->eval($call['name'], $call['args'], $call['isNot']);
        }
    }

    protected function evalOR(array $errors, $prefix): string
    {
        $messages = [];

        foreach ($errors as $key => $call) {
            if ($key === 'OR') {
                $messages[] = $this->evalOR($call, $prefix);
            } else {
                $messages[] = $this->evalCall($call, $prefix);
            }
        }

        return implode($this->get('OR'), $messages);
    }
}
