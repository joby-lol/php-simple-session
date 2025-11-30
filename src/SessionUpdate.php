<?php
/*
* Context Injection
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session;

/**
 * Represents an operation to be applied to a session value during a session commit.
 */
interface SessionUpdate
{
    /**
     * Accepts the current value of the session variable, and returns the new value. Returning null is the same as
     * unsetting the session variable.
     */
    public function apply(mixed $current_value): mixed;

    /**
     * Returns true if this update is self-contained and can be applied regardless of previous values.
     */
    public function isAbsolute(): bool;
}
