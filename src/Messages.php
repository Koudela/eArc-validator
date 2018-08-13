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

    public function __construct(array $languageCodes = ['en'], $additionalMessages = null)
    {
        $this->languageCodes = $languageCodes;

        $this->load(__DIR__ . '/messages');

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

    /**
     * @param $evaluatedItem
     * @param $method
     * @param $predefinedItem
     * @return string
     * @throws NoMessageException
     */
    public function get($evaluatedItem, $method, $predefinedItem = ''): string
    {
        if (is_object($predefinedItem) || is_callable($predefinedItem))
        {
            $predefinedItem = '';
        }

        foreach ($this->languageCodes as $lang)
        {
            if (isset($this->messages[$lang][$method]))
            {
                return $evaluatedItem . ' ' . $this->messages[$lang][$method] . ($predefinedItem === '' ? '' : ' ') . $predefinedItem;
            }
        }
        throw new NoMessageException($method . ' has no messages in [' . implode(', ', $this->languageCodes) . ']');
    }
}
