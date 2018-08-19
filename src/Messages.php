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

    public function __construct(array $languageCodes = ['en'], $additionalMessages = null, Mappings $mappings = null)
    {
        $this->load(__DIR__ . '/messages');
        $this->languageCodes = $languageCodes;
        $this->mappings = $mappings ?? new Mappings();

        if ($additionalMessages)
        {
            if (is_string($additionalMessages)) {
                $this->load($additionalMessages);
            } else {
                $this->append($additionalMessages);
            }
        }
    }

    public function setLanguagePreference(array $languageCodes): void
    {
        $this->languageCodes = $languageCodes;
    }

    public function append(array $messages)
    {
        foreach ($this->languageCodes as $lang)
        {
            if (!isset($messages[$lang])) continue;

            if (!isset($this->messages[$lang]))
            {
                $this->messages[$lang] = $messages[$lang];
                continue;
            }

            array_replace($this->messages[$lang], $messages[$lang]);
        }
    }

    public function load(string $messageDir): void
    {
        $messages = [];
        foreach ($this->languageCodes as $lang)
        {
            $path = $messageDir . '/' . $lang . '.php';

            if (!is_file($path)) continue;

            $messages[$lang] = include($messageDir . '/' . $lang . '.php');
        }
        $this->append($messages);
    }

    protected function getOR(array $errors, $prefix): string
    {
        $messages = [];
        foreach ($errors as $key => $call)
        {
            if ($key === 'OR') $messages[] = $this->getOR($call, $prefix);
            else $messages[] = $this->get($call, $prefix);
        }
        return implode($this->messages[$this->languageCodes[0]]['OR'], $messages);
    }

    protected function get($call, $prefix): string
    {
        if ($call['with'])
            return $call['with'];

        if ($call['withKey'])
            $call['name'] = $call['withKey'];

        if (!$prefix) return $this->getMessage($call['name'], $call);

        try {
            return $this->getMessage($prefix . ':' . $call['name'], $call);
        }
        catch (\Exception $e)
        {
            return $this->getMessage($call['name'], $call);
        }
    }

    protected  function getMessage($name, $call) {
        $name = $this->mappings->get($name);

        foreach ($this->languageCodes as $lang)
        {
            if (isset($this->messages[$lang][$name]))
            {
                $stringArgs = [];

                foreach($call['args'] as $arg)
                {
                    if (is_object($arg))
                        $stringArgs[] = 'object';
                    else if (is_callable($arg))
                        $stringArgs[] = 'callable';
                    else if (is_array($arg))
                        $stringArgs[] = 'array';
                    else $stringArgs[] = $arg;
                }
                return $this->messages[$lang][$name] . ($stringArgs ? ' ' : '') . implode(', ', $stringArgs);
            }
        }
        throw new NoMessageException($name . ' has no messages in [' . implode(', ', $this->languageCodes) . ']');
    }

    public function generateErrorMessages(array $errors, string $prefix = null): array
    {
        $messages = [];
        foreach ($errors as $key => $call)
        {
            if ($key === 'OR') $messages[] = $this->getOR($call, $prefix);
            else $messages[] = $this->get($call, $prefix);
        }
        return $messages;
    }
}
