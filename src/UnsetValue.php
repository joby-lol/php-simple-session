<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session;

/** 
 * Unset a given value
 */
class UnsetValue implements SessionUpdate
{
    /**
     * @inheritDoc
     */
    public function apply(mixed $current_value): null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isAbsolute(): bool
    {
        return true;
    }
}
