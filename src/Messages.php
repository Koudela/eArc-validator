<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\validator;

use eArc\validator\exceptions\NoMessageException;

/**
 * Class Messages
 * @package eArc\validator
 */
class Messages {

    protected $languageCodes = [];
    protected $messages = [];
    protected $mappings;

    public function __construct(array $languageCodes = ['en'], array $additionalMessages = [], Mappings $mappings = null)
    {
        $this->load(__DIR__ . '/messages');
        $this->languageCodes = $languageCodes;
        $this->mappings = $mappings ?? new Mappings();

        foreach ($additionalMessages as $messages)
        {
            $this->merge($messages);
        }
    }

    public function setLanguagePreference(array $languageCodes): void
    {
        $this->languageCodes = $languageCodes;
    }

    protected function append(array $messages): void
    {
        foreach ($this->languageCodes as $lang)
        {
            if (!isset($messages[$lang])) continue;

            if (!isset($this->messages[$lang]))
            {
                $this->messages[$lang] = $messages[$lang];
                continue;
            }
            \array_replace($this->messages[$lang], $messages[$lang]);
        }
    }

    protected function load(string $messageDir): void
    {
        $messages = [];
        foreach ($this->languageCodes as $lang)
        {
            $path = $messageDir . '/' . $lang . '.php';

            if (!is_file($path)) continue;

            $messages[$lang] = include($path);
        }
        $this->append($messages);
    }

    public function merge($messages): void
    {
        if (is_string($messages)) {
            $this->load($messages);
            return;
        }
        if (is_array($messages)) {
            $this->append($messages);
            return;
        }
        if ($messages instanceof Messages) {
            $this->append($messages->getMessages());
        }
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getMappings(): Mappings
    {
        return $this->mappings;
    }

    public function get(string $name): string
    {
        $name = $this->mappings->get($name);

        foreach ($this->languageCodes as $lang)
        {
            if (isset($this->messages[$lang][$name]))
            {
                return $this->messages[$lang][$name];
            }
        }
        throw new NoMessageException(
            $name . ' has no messages in [' . \implode(', ', $this->languageCodes) . ']'
        );
    }

    protected function arrayToString(array $args): string
    {
        $stringifiedArgs = [];

        foreach($args as $arg)
        {
            if (\is_array($arg)) {
                $stringifiedArgs[] = $this->arrayToString($args);
                continue;
            }
            if (!\is_scalar($arg)) {
                $stringifiedArgs[] = (string) $arg;
                continue;
            }
            if (\is_bool($arg)) {
                $stringifiedArgs[] = $arg ? 'true' : 'false';
                continue;
            }
            $stringifiedArgs[] = $arg;
        }
        return '[' . \implode(', ', $stringifiedArgs) . ']';
    }

    protected function eval(string $name, $value, $args, bool $isNot): string
    {
        if (\is_array($value)) $value = $this->arrayToString($value);

        foreach ($args as $key => $arg)
        {
            if (\is_array($arg)) $args[$key] = $this->arrayToString($args);
        }
        return ($isNot ? 'NOT ' : '') . sprintf($this->get($name), $value, ...$args);
    }

    protected function evalCall($call, $prefix): string
    {
        if ($call['with']) {
            return $call['with'];
        }
        if ($call['withKey']) {
            $call['name'] = $call['withKey'];
        }
        if (!$prefix) return $this->eval($call['name'], $call['value'], $call['args'], $call['isNot']);

        try {
            return $this->eval($prefix . ':' . $call['name'], $call['value'], $call['args'], $call['isNot']);
        }
        catch (\Exception $e)
        {
            return $this->eval($call['name'], $call['value'], $call['args'], $call['isNot']);
        }
    }

    protected function evalOR(array $errors, $prefix): string
    {
        $messages = [];
        foreach ($errors as $key => $call)
        {
            if ($key === 'OR') $messages[] = $this->evalOR($call, $prefix);
            else $messages[] = $this->evalCall($call, $prefix);
        }
        return \implode($this->messages[$this->languageCodes[0]]['OR'], $messages);
    }

    public function generateErrorMessages(array $errors, string $prefix = null): array
    {
        $messages = [];
        foreach ($errors as $key => $call)
        {
            if ($key === 'OR') $messages[] = $this->evalOR($call, $prefix);
            else $messages[] = $this->evalCall($call, $prefix);
        }
        return $messages;
    }
}
