<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session;

/** 
 * Set a given value if it is not null. Discards the given value if an existing one is set.
 */
readonly class SetIfNullValue implements SessionUpdate
{
    public function __construct(
        public mixed $value
    ) {}

    /**
     * @inheritDoc
     */
    public function apply(mixed $current_value): mixed
    {
        if ($current_value === null) {
            return $this->value;
        }
        return $current_value;
    }

    /**
     * @inheritDoc
     */
    public function isAbsolute(): bool
    {
        return false;
    }
}
