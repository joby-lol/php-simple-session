<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session\Tests\Unit;

use Joby\Session\SetValue;
use PHPUnit\Framework\TestCase;

class SetValueTest extends TestCase
{
    public function test_sets_string_value()
    {
        $update = new SetValue('bar');
        $this->assertEquals('bar', $update->apply('foo'));
    }

    public function test_sets_integer_value()
    {
        $update = new SetValue(123);
        $this->assertEquals(123, $update->apply(456));
    }

    public function test_sets_null_value()
    {
        $update = new SetValue(null);
        $this->assertNull($update->apply('something'));
    }

    public function test_sets_array_value()
    {
        $update = new SetValue(['key' => 'value']);
        $this->assertEquals(['key' => 'value'], $update->apply([]));
    }

    public function test_sets_object_value()
    {
        $object = new \stdClass();
        $object->prop = 'value';
        $update = new SetValue($object);
        $this->assertEquals($object, $update->apply(null));
    }

    public function test_ignores_current_value()
    {
        $update = new SetValue('new');
        $this->assertEquals('new', $update->apply('old'));
        $this->assertEquals('new', $update->apply(123));
        $this->assertEquals('new', $update->apply(null));
        $this->assertEquals('new', $update->apply(['array']));
    }

    public function test_is_absolute()
    {
        $update = new SetValue('value');
        $this->assertTrue($update->isAbsolute());
    }

    public function test_sets_boolean_value()
    {
        $update = new SetValue(true);
        $this->assertTrue($update->apply(false));
    }

    public function test_sets_float_value()
    {
        $update = new SetValue(3.14);
        $this->assertEquals(3.14, $update->apply(2.71));
    }
}
