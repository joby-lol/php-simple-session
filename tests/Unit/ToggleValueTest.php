<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session\Tests\Unit;

use Joby\Session\ToggleValue;
use PHPUnit\Framework\TestCase;

class ToggleValueTest extends TestCase
{
    public function test_toggle_boolean_values()
    {
        $v = new ToggleValue();
        $this->assertTrue($v->apply(false));
        $this->assertFalse($v->apply(true));
    }

    public function test_toggle_non_boolean_values()
    {
        $v = new ToggleValue();
        $this->assertTrue($v->apply(0));
        $this->assertFalse($v->apply(1));
        $this->assertTrue($v->apply(''));
        $this->assertFalse($v->apply('non-empty string'));
        $this->assertTrue($v->apply([]));
        $this->assertFalse($v->apply([1, 2, 3]));
    }

    public function test_is_not_absolute()
    {
        $v = new ToggleValue();
        $this->assertFalse($v->isAbsolute());
    }
}
