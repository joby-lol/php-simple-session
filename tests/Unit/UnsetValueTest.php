<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session\Tests\Unit;

use Joby\Session\UnsetValue;
use PHPUnit\Framework\TestCase;

class UnsetValueTest extends TestCase
{
    public function test_returns_null_for_string_value()
    {
        $update = new UnsetValue();
        $this->assertNull($update->apply('value'));
    }

    public function test_returns_null_for_integer_value()
    {
        $update = new UnsetValue();
        $this->assertNull($update->apply(123));
    }

    public function test_returns_null_for_null_value()
    {
        $update = new UnsetValue();
        $this->assertNull($update->apply(null));
    }

    public function test_returns_null_for_array_value()
    {
        $update = new UnsetValue();
        $this->assertNull($update->apply(['key' => 'value']));
    }

    public function test_returns_null_for_object_value()
    {
        $update = new UnsetValue();
        $object = new \stdClass();
        $this->assertNull($update->apply($object));
    }

    public function test_is_absolute()
    {
        $update = new UnsetValue();
        $this->assertTrue($update->isAbsolute());
    }

    public function test_always_returns_null_regardless_of_input()
    {
        $update = new UnsetValue();
        $this->assertNull($update->apply('string'));
        $this->assertNull($update->apply(42));
        $this->assertNull($update->apply(3.14));
        $this->assertNull($update->apply(true));
        $this->assertNull($update->apply(false));
        $this->assertNull($update->apply([]));
    }
}
