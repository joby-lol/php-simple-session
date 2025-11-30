<?php
/*
* Simple Session
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session\Tests\Integration;

use Joby\Session\Session;
use Joby\Session\SetValue;
use Joby\Session\IncrementValue;
use Joby\Session\UnsetValue;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        // Clean up any existing session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // Configure session for testing
        ini_set('session.use_cookies', '0');
        ini_set('session.use_only_cookies', '0');
        ini_set('session.cache_limiter', '');

        // Clear any existing session data
        $_SESSION = [];

        // Reset Session class state
        $this->resetSessionState();
    }

    protected function tearDown(): void
    {
        // Clean up session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];

        // Reset Session class state
        $this->resetSessionState();
    }

    protected function resetSessionState(): void
    {
        $reflection = new \ReflectionClass(Session::class);

        $updates = $reflection->getProperty('updates');
        $updates->setAccessible(true);
        $updates->setValue(null, []);

        $wasRead = $reflection->getProperty('was_read');
        $wasRead->setAccessible(true);
        $wasRead->setValue(null, []);

        $data = $reflection->getProperty('data');
        $data->setAccessible(true);
        $data->setValue(null, null);
    }

    public function test_set_queues_value()
    {
        Session::set('foo', 'bar');
        $this->assertTrue(Session::written('foo'));
    }

    public function test_get_returns_queued_value()
    {
        Session::set('foo', 'bar');
        $this->assertEquals('bar', Session::get('foo'));
    }

    public function test_get_returns_null_for_nonexistent_key()
    {
        $this->assertNull(Session::get('nonexistent'));
    }

    public function test_increment_queues_update()
    {
        Session::increment('counter');
        $this->assertTrue(Session::written('counter'));
    }

    public function test_increment_returns_correct_value()
    {
        Session::increment('counter');
        $this->assertEquals(1, Session::get('counter'));
    }

    public function test_multiple_increments_apply_correctly()
    {
        Session::increment('counter', 5);
        Session::increment('counter', 3);
        Session::increment('counter', 2);

        $this->assertEquals(10, Session::get('counter'));
    }

    public function test_unset_queues_update()
    {
        Session::unset('temp');
        $this->assertTrue(Session::written('temp'));
    }

    public function test_unset_returns_null()
    {
        Session::set('temp', 'data');
        Session::unset('temp');
        $this->assertNull(Session::get('temp'));
    }

    public function test_commit_persists_unset()
    {
        Session::set('temp', 'value');
        Session::commit();

        Session::unset('temp');
        Session::commit();

        // Reset internal cache
        $this->resetSessionState();

        $this->assertNull(Session::get('temp'));
    }

    public function test_commit_without_changes_is_noop()
    {
        $sessionActive = session_status() === PHP_SESSION_ACTIVE;
        Session::commit();

        // Session should not have been started
        $this->assertEquals($sessionActive, session_status() === PHP_SESSION_ACTIVE);
    }

    public function test_written_returns_false_when_no_updates()
    {
        $this->assertFalse(Session::written());
    }

    public function test_written_returns_true_when_updates_exist()
    {
        Session::set('foo', 'bar');
        $this->assertTrue(Session::written());
    }

    public function test_written_with_key_returns_correct_value()
    {
        Session::set('foo', 'bar');
        $this->assertTrue(Session::written('foo'));
        $this->assertFalse(Session::written('baz'));
    }

    public function test_read_tracks_read_keys()
    {
        Session::get('foo');
        $this->assertTrue(Session::read('foo'));
    }

    public function test_read_returns_false_for_unread_keys()
    {
        $this->assertFalse(Session::read('foo'));
    }

    public function test_read_without_key_returns_overall_status()
    {
        $this->assertFalse(Session::read());
        Session::get('foo');
        $this->assertTrue(Session::read());
    }

    public function test_absolute_updates_discard_previous()
    {
        Session::set('foo', 'first');
        Session::set('foo', 'second');
        Session::set('foo', 'third');

        // Should only have one update queued
        $reflection = new \ReflectionClass(Session::class);
        $updates = $reflection->getProperty('updates');
        $updates->setAccessible(true);
        $updatesList = $updates->getValue();

        $this->assertCount(1, $updatesList['foo']);
        $this->assertEquals('third', Session::get('foo'));
    }

    public function test_increment_after_set_uses_set_value()
    {
        Session::set('counter', 10);
        Session::increment('counter', 5);

        $this->assertEquals(15, Session::get('counter'));
    }

    public function test_set_after_increment_discards_increment()
    {
        Session::increment('counter', 5);
        Session::set('counter', 100);

        $this->assertEquals(100, Session::get('counter'));
    }

    public function test_commit_clears_internal_state()
    {
        Session::set('foo', 'bar');
        Session::commit();

        $this->assertFalse(Session::written());
        $this->assertFalse(Session::read());
    }

    public function test_update_with_custom_implementation()
    {
        Session::update('items', new class implements \Joby\Session\SessionUpdate {
            public function apply(mixed $current_value): array
            {
                $array = is_array($current_value) ? $current_value : [];
                $array[] = 'new_item';
                return $array;
            }

            public function isAbsolute(): bool
            {
                return false;
            }
        });

        $this->assertEquals(['new_item'], Session::get('items'));
    }

    public function test_storage_key_can_be_changed()
    {
        Session::setStorageKey('custom_namespace');
        $this->assertEquals('custom_namespace', Session::storageKey());

        // Reset for other tests
        Session::setStorageKey('_simple_session_data');
    }

    public function test_changing_storage_key_resets_state()
    {
        Session::set('foo', 'bar');
        Session::setStorageKey('new_namespace');

        $this->assertFalse(Session::written());
        $this->assertFalse(Session::read());

        // Reset for other tests
        Session::setStorageKey('_simple_session_data');
    }
}
