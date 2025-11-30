<?php
/*
* Context Injection
* https://github.com/joby-lol/php-simple-session
* (c) 2025 Joby Elliott code@joby.lol
* MIT License https://opensource.org/licenses/MIT
*/

namespace Joby\Session;

/**
 * Static facade for interacting with PHP's built-in session management. Can be used for simple getting and setting, but provides so much more than that. This interface also:
 * 
 * - Lazily starts sessions only when data is written, to avoid uneccessary session data and cookie traffic.
 * - Only opens the session again for writing when commit() is called, and even then only for changes.
 * - Allows complex atomic updates to data, such as incrementing values.
 */
class Session
{
    /** 
     * @var array<string,SessionUpdate[]> $updates a list of queued updates to apply on commit
     */
    protected static array $updates = [];
    /**
     * @var array<string> $was_read a list of session keys that have been read during this request
     */
    protected static array $was_read = [];
    /**
     * @var array|null $data the session data, cached from $_SESSION on first read
     */
    protected static array|null $data = null;
    /**
     * @var string $storage_key the key in $_SESSION where managed session data is stored. If modified, the entire class will be reset and all uncommitted changes lost.
     */
    protected static string $storage_key = '_simple_session_data';

    /**
     * Get the value for a given key, applying any queued updates.
     */
    public static function get(string $key): mixed
    {
        static::loadData();
        static::markRead($key);
        $value = static::$data[$key] ?? null;
        return static::applyUpdates($key, $value);
    }

    /**
     * Set the value for a given key, queuing a SetValue update.
     */
    public static function set(string $key, mixed $value): void
    {
        static::update($key, new SetValue($value));
    }

    /**
     * Set the value for a given key if it is currently null, queuing a SetIfNullValue update.
     */
    public static function setIfNull(string $key, mixed $value): void
    {
        static::update($key, new SetIfNullValue($value));
    }

    /**
     * Unset the value for a given key, queuing an UnsetValue update
     */
    public static function unset(string $key): void
    {
        static::update($key, new UnsetValue());
    }

    /**
     * Increment the value for a given key by a given amount, queuing an IncrementValue update.
     */
    public static function increment(string $key, int|float $by = 1): void
    {
        static::update($key, new IncrementValue($by));
    }

    /**
     * Toggle the boolean value for a given key, queuing a ToggleValue update.
     */
    public static function toggle(string $key): void
    {
        static::update($key, new ToggleValue());
    }

    /**
     * Touch the value for a given key, queuing a TouchValue update. This will set the value to the current timestamp if it is not numeric, or update it to the current timestamp (or later if it's already set further out than time()).
     */
    public static function touch(string $key): void
    {
        static::update($key, new TouchValue());
    }

    /**
     * Apply any queued updates for the named key to a given value.
     */
    protected static function applyUpdates(string $key, mixed $value): mixed
    {
        if (!isset(static::$updates[$key])) {
            return $value;
        }
        foreach (static::$updates[$key] as $update) {
            $value = $update->apply($value);
        }
        return $value;
    }

    /**
     * Return whether any session values have been read, or whether a specific one has by passing a key.
     */
    public static function read(string|null $key = null): bool
    {
        if ($key === null) {
            return !empty(static::$was_read);
        }
        return in_array($key, static::$was_read, true);
    }

    /**
     * Return whether any session values are queued to be written, or whether a specific one is by passing a key.
     */
    public static function written(string|null $key = null): bool
    {
        if ($key === null) {
            return !empty(static::$updates);
        }
        return isset(static::$updates[$key]);
    }

    /**
     * Queue an update to be applied to a given session key on commit.
     */
    public static function update(string $key, SessionUpdate $update): void
    {
        if ($update->isAbsolute()) {
            // absolute updates clear any prior updates as a performance optimization
            static::$updates[$key] = [$update];
            return;
        }
        // otherwise, just append the update
        static::$updates[$key][] = $update;
    }

    /**
     * Commit any queued updates to the session storage. This will start the session if it has not already been started, and write and close it when done. If no updates are queued, this is a no-op. Can be called multiple times per request if desired, as it clears internal state after running.
     */
    public static function commit(): void
    {
        if (!static::written()) {
            return;
        }
        // we start our own session here rather than relying on session_start() having been called earlier, because we may not have needed to read any data during this request, and we want to modify the actual current values of the session, not what was cached earlier
        session_start();
        $new_values = [];
        $unset_keys = [];
        foreach (array_keys(static::$updates) as $key) {
            $value = $_SESSION[static::storageKey()][$key] ?? null;
            $value = static::applyUpdates($key, $value);
            if ($value === null) {
                $unset_keys[] = $key;
            } else {
                $new_values[$key] = $value;
            }
        }
        // apply new values after they are all built, so that if an exception occurs we don't leave the session in a half-updated state
        foreach ($new_values as $key => $value) {
            $_SESSION[static::storageKey()][$key] = $value;
        }
        // now unset any keys that need to be removed
        foreach ($unset_keys as $key) {
            unset($_SESSION[static::storageKey()][$key]);
        }
        // write and close the session
        session_write_close();
        // clear internal state so that commit can be called multiple times per request if desired
        static::$updates = [];
        static::$was_read = [];
        static::$data = null;
    }

    /**
     * Mark a key as having been read.
     */
    public static function markRead(string $key): void
    {
        if (!in_array($key, static::$was_read, true)) {
            static::$was_read[] = $key;
        }
    }

    /**
     * Change the storage key (effectively a namespace) in which managed data will actually be stored in $_SESSION. Changing this resets the entire class's state including any uncommitted changes.
     */
    public static function setStorageKey(string $key): void
    {
        if ($key === static::$storage_key) return;
        static::$storage_key = $key;
        static::$updates = [];
        static::$was_read = [];
        static::$data = null;
    }

    /**
     * Get the current storage key (effectively a namespace) in which managed data is stored in $_SESSION.
     */
    public static function storageKey(): string
    {
        return static::$storage_key;
    }

    protected static function loadData(bool $force_refresh = false): void
    {
        // if we have data, and we're not forcing a refresh, then we're done
        if (static::$data !== null && !$force_refresh) return;
        // if there is no session then we're done
        if (session_status() === PHP_SESSION_NONE && !isset($_COOKIE[session_name()])) {
            static::$data = [];
            return;
        }
        // otherwise, load the session data
        session_start();
        static::$data = $_SESSION[static::$storage_key] ?? [];
        session_abort();
    }
}
