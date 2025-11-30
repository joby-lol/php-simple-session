<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session;

/** 
 * Updates a given value to the current timestamp or a higher existing value.
 */
readonly class TouchValue implements SessionUpdate
{
    /**
     * @inheritDoc
     */
    public function apply(mixed $current_value): int
    {
        if (!is_numeric($current_value)) return time();
        return max($current_value, time());
    }

    /**
     * @inheritDoc
     */
    public function isAbsolute(): bool
    {
        return false;
    }
}
