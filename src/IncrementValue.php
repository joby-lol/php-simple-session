<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session;

/** 
 * Increment a given value as an integer. Accepts floats, but the final value will be truncated to an int.
 */
readonly class IncrementValue implements SessionUpdate
{

    public function __construct(
        public int $increment_by = 1
    ) {}

    /**
     * @inheritDoc
     */
    public function apply(mixed $current_value): int
    {
        if (is_null($current_value)) return $this->increment_by;
        if (!is_numeric($current_value)) return $this->increment_by;
        return (int)$current_value + $this->increment_by;
    }

    /**
     * @inheritDoc
     */
    public function isAbsolute(): bool
    {
        return false;
    }
}
