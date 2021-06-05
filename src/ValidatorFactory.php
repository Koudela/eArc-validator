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

class ValidatorFactory
{
    public static function build($languagePriority = ['en' => [], 'de' => []]): Validator
    {
        $languages = [];

        foreach ($languagePriority as $lang => $messages) {
            $file = sprintf('%s/messages/%s.php', __DIR__, $lang);
            $defaultMessages = is_file($file) ? include $file : [];
            $languages[$lang] = $messages + $defaultMessages;
        }

        $mappings = Validator::getMappings();
        $callbacks = Validator::getCallbacks($mappings);
        $messages = new Messages($languages, $mappings);

        return new Validator($callbacks, $messages, new Collector());
    }
}
