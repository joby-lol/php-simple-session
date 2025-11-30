<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session;

/** 
 * Set a given value to a specific value, discarding any existing value.
 */
readonly class SetValue implements SessionUpdate
{
    public function __construct(
        public mixed $value
    ) {}

    /**
     * @inheritDoc
     */
    public function apply(mixed $current_value): mixed
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function isAbsolute(): bool
    {
        return true;
    }
}
