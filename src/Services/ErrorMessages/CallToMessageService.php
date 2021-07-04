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

use eArc\Validator\Collections\Messages;
use eArc\Validator\Models\EvaluatedCall;
use Throwable;

abstract class CallToMessageService extends MessageEnrichmentService
{
    public function __construct(protected Messages $messages) {}

    public function getMessage(EvaluatedCall $call, $prefix): string
    {
        if (!is_null($call->with)) {
            return $call->with;
        }

        $name = is_null($call->withKey) ? $call->name : $call->withKey;

        if (!$prefix) {
            return $this->enrich($this->messages->get($name), $call->args, $call->isNot);
        }

        try {
            return $this->enrich($this->messages->get($prefix.':'.$name), $call->args, $call->isNot);
        } catch (Throwable) {
            return $this->enrich($this->messages->get($name), $call->args, $call->isNot);
        }
    }
}
