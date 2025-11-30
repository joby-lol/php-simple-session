# Simple Session

An opinionated PHP session management library designed to expose a simple API while providing smart performance optimizations and minimizing session creation and locking.

## Features

- **Lazy session creation** - Sessions are only started when data is actually written, avoiding unnecessary cookie traffic
- **Minimized locking** - Sessions are only locked once on first read, and again if necessary during commits, not for the entire response lifecycle
- **Atomic updates** - Operations like increments that apply atomically on commit are possible
- **Simple static API** - Clean, straightforward interface for common session operations

## Requirements

- PHP 8.3 or higher

## Installation

```bash
composer require joby/simple-session
```

## Usage

```php
use Joby\Session\Session;

// Set values (queued, doesn't lock the session)
Session::set('user_id', 123);
Session::set('username', 'john_doe');

// Increment a counter (queued, doesn't lock the session, will apply to actual value upon commit to avoid race conditions)
Session::increment('page_views');
Session::increment('score', 10);

// Unset values (queued, doesn't lock the session)
Session::unset('temp_data');

// Read values (applies queued updates to cached values for convenience)
$userId = Session::get('user_id');
$views = Session::get('page_views');

// Commit all changes at once atomically, does not reopen session if no changes are queued
Session::commit();
```

## How It Works

1. **Reading** - `Session::get()` reads from a cached copy of the session and applies any queued updates
2. **Writing** - `Session::set()`, `Session::increment()`, and `Session::unset()` queue changes without opening the session
3. **Committing** - `Session::commit()` opens the session, applies all updates atomically, and closes it

This approach minimizes session file locking and reduces the window where concurrent requests might conflict.

## Advanced: Custom Atomic Updates

You can create custom atomic update operations by implementing the `SessionUpdate` interface:

```php
use Joby\Session\SessionUpdate;

class AppendToArray implements SessionUpdate
{
    public function __construct(public mixed $value) {}

    public function apply(mixed $current_value): array
    {
        $array = is_array($current_value) ? $current_value : [];
        $array[] = $this->value;
        return $array;
    }

    public function isAbsolute(): bool
    {
        return false; // depends on current value
    }
}

// Use with Session::update()
Session::update('items', new AppendToArray('new_item'));
```

The `isAbsolute()` method indicates whether the update replaces the value entirely (like `SetValue`) or depends on the current value (like `IncrementValue`). Absolute updates enable performance optimizations by discarding previous queued updates for the same key.
