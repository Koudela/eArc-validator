<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/validator
 * @link https://github.com/Koudela/earc-validator/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Validator;

use eArc\Validator\Collections\Collector;
use eArc\Validator\Collections\Messages;
use eArc\Validator\Services\ErrorMessages\ErrorMessageGenerator;
use eArc\Validator\Services\EvaluationService;

class ValidatorFactory
{
    /** @var array<string, array<string, string>> */
    protected array $languagePriority;

    /**
     * @param array<string, array<string, string>> $languagePriority
     */
    public function __construct(array $languagePriority = ['en' => [], 'de' => []])
    {
        $this->languagePriority = $languagePriority;
    }

    public function build(): Validator
    {
        $languages = [];

        foreach ($this->languagePriority as $lang => $messages) {
            $file = sprintf('%s/messages/%s.php', __DIR__, $lang);
            $defaultMessages = is_file($file) ? include $file : [];
            $languages[$lang] = $messages + $defaultMessages;
        }

        $mappings = Validator::getMappings();
        $callbacks = Validator::getCallbacks();
        $messages = new Messages($languages, $mappings);
        $evaluationService = new EvaluationService(
            new ErrorMessageGenerator($messages),
            $callbacks,
            $mappings,
        );

        return new Validator($evaluationService, new Collector());
    }
}
