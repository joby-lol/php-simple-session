<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session\Tests\Unit;

use Joby\Session\SetIfNullValue;
use PHPUnit\Framework\TestCase;

class SetIfNullValueTest extends TestCase
{
    public function test_sets_over_unset_value()
    {
        $update = new SetIfNullValue(1);
        $this->assertEquals(
            1,
            $update->apply(null)
        );
    }

    public function test_sets_over_set_value()
    {
        $update = new SetIfNullValue(5);
        $this->assertEquals(
            1,
            $update->apply(1)
        );
    }

    public function test_is_not_absolute()
    {
        $this->assertFalse(new SetIfNullValue(null)->isAbsolute());
    }
}
