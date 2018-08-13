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

use eArc\validator\exceptions\NoCallbackException;
use eArc\validator\exceptions\NoMessageException;
use eArc\validator\exceptions\AssertException;

/**
 * Class ValidatorBase
 * @method OR(): ValidatorBase;
 * @package eArc\validator
 */
class ValidatorBase
{
    protected $messages;
    protected $callbacks;
    protected $messageKey;

    protected $errors = [];
    protected $callStack = [];

    protected $hasOr = false;
    protected $cntOr = 0;
    protected $orFailureMessage = '';

    public function __construct(Messages $messages, Callbacks $callbacks, $messageKey = null)
    {
        $this->messages = $messages;
        $this->callbacks = $callbacks;
        $this->messageKey = $messageKey ? ':' . $messageKey : '';
    }

    /**
     * @param string $fncName
     * @param array $arguments
     * @return ValidatorBase
     */
    public function __call(string $fncName , array $arguments): ValidatorBase
    {
        if ($fncName === 'OR')
        {
            $this->cntOr++;
            $this->hasOr = true;
        }

        $this->callStack[] = ['name' => $fncName, 'args' => $arguments];

        return $this;
    }

    /**
     * @param $item
     * @throws AssertException
     * @throws NoMessageException
     * @throws NoCallbackException
     */
    public function assert($item): void
    {
        if ($this->eval($item)) return;

        throw new AssertException($this->errors[\count($this->errors) - 1]);
    }

    /**
     * @param $item
     * @return bool
     * @throws NoMessageException
     * @throws NoCallbackException
     */
    public function eval($item): bool
    {
        $fastForwardOr = false;

        foreach ($this->callStack as $call)
        {
            if ($fastForwardOr)
            {
                if ($call['name'] === 'OR') $fastForwardOr = false;
                continue;
            }

            if ($call['name'] === 'OR') {
                $this->reset();
                return true;
            }

            if (!$this->callbacks->get($call['name'])($item, ...$call['args']))
            {
                if ($this->cntOr > 0)
                {
                    $this->cntOr--;
                    $fastForwardOr = true;
                    if ($this->orFailureMessage) $this->orFailureMessage .= $this->messages->get('','OR', '') . ' ';
                    $this->orFailureMessage .= ($call['message'] ?? $this->messages->get($item, $call['name'] . ($call['key'] ?? $this->messageKey), ...$call['args']));
                    continue;
                }

                $this->errors[] =
                    ($this->hasOr ? $this->orFailureMessage . $this->messages->get('','OR', '') . ' ' : '') .
                    ($call['message'] ?? $this->messages->get($item, $call['name'] . ($call['key'] ?? $this->messageKey), ...$call['args']));
                $this->reset();
                return false;
            }
        }
        if ($fastForwardOr)
        {
            $this->errors[] = $this->orFailureMessage;
            $this->reset();
            return false;
        }
        $this->reset();
        return true;
    }

    private function reset(): void
    {
        $this->hasOr = false;
        $this->cntOr = 0;
        $this->orFailureMessage = '';
        $this->callStack = [];
    }

    public function with(string $message): ValidatorBase
    {
        $cnt = count($this->callStack);

        if ($cnt > 0)
        {
            $this->callStack[$cnt-1]['message'] = $message;
        }

        return $this;
    }

    public function addKey($messageKey): ValidatorBase
    {
        $cnt = count($this->callStack);

        if ($cnt > 0)
        {
            $this->callStack[$cnt-1]['key'] = ':' . $messageKey;
        }

        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
