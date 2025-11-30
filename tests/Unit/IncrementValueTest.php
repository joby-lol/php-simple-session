<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session\Tests\Unit;

use Joby\Session\IncrementValue;
use PHPUnit\Framework\TestCase;

class IncrementValueTest extends TestCase
{
    public function test_increments_numeric_value()
    {
        $update = new IncrementValue(5);
        $this->assertEquals(15, $update->apply(10));
    }

    public function test_increments_with_default_value()
    {
        $update = new IncrementValue();
        $this->assertEquals(11, $update->apply(10));
    }

    public function test_handles_null_value()
    {
        $update = new IncrementValue(5);
        $this->assertEquals(5, $update->apply(null));
    }

    public function test_handles_non_numeric_value()
    {
        $update = new IncrementValue(5);
        $this->assertEquals(5, $update->apply('invalid'));
    }

    public function test_handles_string_numeric_value()
    {
        $update = new IncrementValue(5);
        $this->assertEquals(15, $update->apply('10'));
    }

    public function test_handles_negative_increment()
    {
        $update = new IncrementValue(-3);
        $this->assertEquals(7, $update->apply(10));
    }

    public function test_handles_float_current_value()
    {
        $update = new IncrementValue(5);
        $this->assertEquals(15, $update->apply(10.7));
    }

    public function test_is_not_absolute()
    {
        $update = new IncrementValue();
        $this->assertFalse($update->isAbsolute());
    }

    public function test_zero_increment()
    {
        $update = new IncrementValue(0);
        $this->assertEquals(10, $update->apply(10));
    }

    public function test_large_increment()
    {
        $update = new IncrementValue(1000000);
        $this->assertEquals(1000010, $update->apply(10));
    }
}
