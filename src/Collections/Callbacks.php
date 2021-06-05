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

use Closure;
use eArc\Validator\Exceptions\NoCallbackException;

class Callbacks
{
    /** @var Mappings */
    protected $mappings;
    /** @var Closure[] */
    protected $callbacks;

    /**
     * @param array<int|string, Closure> $callbacks
     */
    public function __construct(array $callbacks, Mappings $mappings)
    {
        $this->callbacks = $callbacks;
        $this->mappings = $mappings;
    }

    /**
     * @return Closure[]
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    public function get(string $name): Closure
    {
        $name = $this->mappings->get($name);

        if (array_key_exists($name, $this->callbacks)) {
            return $this->callbacks[$name];
        }

        throw new NoCallbackException(sprintf(
            '{0af7acb9-a362-4e6d-8604-434e53ad5f54} %s is not a valid %s function',
            $name,
            Callbacks::class
        ));
    }
}
