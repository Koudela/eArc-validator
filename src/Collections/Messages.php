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
namespace eArc\Validator\Collections;

use eArc\Validator\Exceptions\NoMessageException;

class Messages
{
    /** @var array<string, array<string, string>> */
    protected array $messages = [];
    protected Mappings $mappings;

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

        if (substr($name, 0, 4) === 'not:') {
            return 'NOT( '.$this->get(substr($name, 4)).' )';
        }

        throw new NoMessageException(sprintf(
            '{3428bf7b-3219-414a-9987-919a600342c9} No message %s defined for languages [%s]',
            $name,
            implode(', ', $languageCodes)
        ));
    }


}
