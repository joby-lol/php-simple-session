<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session;

/** 
 * Toggle a boolean value between true and false.
 */
readonly class ToggleValue implements SessionUpdate
{
    /**
     * @inheritDoc
     */
    public function apply(mixed $current_value): bool
    {
        return !$current_value;
    }

    /**
     * @inheritDoc
     */
    public function isAbsolute(): bool
    {
        return false;
    }
}
