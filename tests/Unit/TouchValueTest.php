<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session\Tests\Unit;

use Joby\Session\TouchValue;
use PHPUnit\Framework\TestCase;

class TouchValueTest extends TestCase
{
    public function test_touch_value()
    {
        $v = new TouchValue();
        $current_time = time();
        $this->assertEquals($current_time, $v->apply(null));
        $this->assertEquals($current_time, $v->apply(1));
    }

    public function test_touch_value_with_higher_existing_value()
    {
        $v = new TouchValue();
        $future_time = time() + 3600; // 1 hour in the future
        $this->assertEquals($future_time, $v->apply($future_time));
    }

    public function test_touch_value_with_non_numeric_value()
    {
        $v = new TouchValue();
        $current_time = time();
        $this->assertEquals($current_time, $v->apply('non-numeric'));
        $this->assertEquals($current_time, $v->apply([]));
    }

    public function test_is_not_absolute()
    {
        $v = new TouchValue();
        $this->assertFalse($v->isAbsolute());
    }
}
