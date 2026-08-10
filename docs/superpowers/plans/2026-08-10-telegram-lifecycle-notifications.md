# Telegram Lifecycle Notifications Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Telegram group/DM notifications when tasks and projects are created or deleted, preserving all existing functionality.

**Architecture:** All delivery logic lives in `application/helpers/telegram_helper.php` (already autoloaded). Create-hooks already exist in `Tasks.php` and `Projects.php` and are preserved. This plan adds pure, testable message/decision functions, a shared `telegram_deliver()` fan-out (group vs. per-user DM by permission), a new `telegram_notify_deleted()`, and delete-hooks in both controllers. Delivery is in-app (no DB triggers), silent-fail, and never blocks the ERP request.

**Tech Stack:** CodeIgniter 3 (PHP 8.3), cURL to `api.telegram.org`, MySQL 8 (`tic_crm`), PHPUnit 12 for helper unit tests.

**Working dir:** `C:\laragon\www\tic_crm`

---

## File Structure

| File | Responsibility |
|------|----------------|
| `application/helpers/telegram_helper.php` | All Telegram logic: HTTP send, escaping, recipient decision, message builders, fan-out, create/delete notify functions |
| `application/tests/helpers/TelegramHelperTest.php` | **New** — unit tests for the pure decision/build functions and the stub-driven `telegram_deliver`/`telegram_notify_deleted` glue |
| `application/controllers/admin/Tasks.php` | `delete_task()` gains a delete notification hook (create hook stays) |
| `application/controllers/admin/Projects.php` | `delete_project()` gains a delete notification hook (create hook stays) |

Pre-existing uncommitted Telegram work (settings UI, `tbl_users.telegram_chat_id`, migration 642, `MY_Model` unassigned visibility, create-hooks) is part of this feature and will ride along in the feature commits.

---

## Task 1: Pure decision + message-builder functions (TDD)

**Files:**
- Modify: `application/helpers/telegram_helper.php` (add 3 functions; do not change existing ones yet)
- Create: `application/tests/helpers/TelegramHelperTest.php`

- [x] **Step 1: Write the failing test**

Create `application/tests/helpers/TelegramHelperTest.php`:

```php
<?php
use PHPUnit\Framework\TestCase;

function telegram_test_site_url($uri = '')
{
    return 'http://localhost/tic_crm/index.php/' . ltrim($uri, '/');
}

function site_url($uri = '', $protocol = null)
{
    return telegram_test_site_url($uri);
}

require_once __DIR__ . '/../helpers/telegram_helper.php';

class TelegramHelperTest extends TestCase
{
    public function testShouldSendToGroupForEveryonePermissions(): void
    {
        $this->assertTrue(telegram_should_send_to_group('all'));
        $this->assertTrue(telegram_should_send_to_group(''));
        $this->assertTrue(telegram_should_send_to_group('{}'));
        $this->assertTrue(telegram_should_send_to_group(null));
    }

    public function testShouldSendToGroupForInvalidOrEmptyJson(): void
    {
        $this->assertTrue(telegram_should_send_to_group('{not json'));
        $this->assertTrue(telegram_should_send_to_group('[]'));
    }

    public function testShouldSendToGroupFalseForCustomPermissionJson(): void
    {
        $this->assertFalse(telegram_should_send_to_group('{"5":["view","edit"],"8":["view"]}'));
    }

    public function testBuildDeletedMessageForTask(): void
    {
        $this->assertSame(
            '<b>🗑 Task Deleted!</b>' . PHP_EOL . '<b>Title:</b> Fix login',
            telegram_build_deleted_message('task', 'Fix login')
        );
    }

    public function testBuildDeletedMessageForProject(): void
    {
        $this->assertSame(
            '<b>🗑 Project Deleted!</b>' . PHP_EOL . '<b>Title:</b> Website revamp',
            telegram_build_deleted_message('project', 'Website revamp')
        );
    }

    public function testBuildDeletedMessageEscapesName(): void
    {
        $this->assertSame(
            '<b>🗑 Task Deleted!</b>' . PHP_EOL . '<b>Title:</b> &lt;script&gt;alert(1)&lt;/script&gt;',
            telegram_build_deleted_message('task', '<script>alert(1)</script>')
        );
    }

    public function testBuildCreatedMessageForTaskIncludesLinkAndDueDate(): void
    {
        $msg = telegram_build_created_message('task', 42, 'Fix login', '2026-08-20');
        $this->assertStringContainsString('<b>📢 New Task Created!</b>', $msg);
        $this->assertStringContainsString('<b>Title:</b> Fix login', $msg);
        $this->assertStringContainsString('<b>Due Date:</b> 2026-08-20', $msg);
        $this->assertStringContainsString('href="http://localhost/tic_crm/index.php/admin/tasks/details/42"', $msg);
    }

    public function testBuildCreatedMessageOmitsDueDateWhenEmpty(): void
    {
        $msg = telegram_build_created_message('project', 7, 'Website', '');
        $this->assertStringContainsString('<b>📢 New Project Created!</b>', $msg);
        $this->assertStringNotContainsString('Due Date', $msg);
        $this->assertStringNotContainsString('End Date', $msg);
        $this->assertStringContainsString('href="http://localhost/tic_crm/index.php/admin/projects/project_details/7"', $msg);
    }
}
```

- [x] **Step 2: Run the test to verify it fails**

Run from `C:\laragon\www\tic_crm\application\tests`:
```
php C:\laragon\www\tic_crm\vendor\bin\phpunit --filter TelegramHelperTest
```
Expected: FAIL with `Error: Call to undefined function telegram_should_send_to_group()`.

- [x] **Step 3: Add the three pure functions**

Add to `application/helpers/telegram_helper.php`, right after the `telegram_escape()` function (line 75):

```php
if (!function_exists('telegram_should_send_to_group')) {
    /**
     * Decide whether a message should go to the configured group chat.
     *
     * True for 'all', empty, null, '{}', and any permission value that is not
     * a non-empty JSON object of assigned users.
     *
     * @param mixed $permission Raw permission column value
     * @return bool
     */
    function telegram_should_send_to_group($permission)
    {
        if ($permission === 'all' || $permission === '' || $permission === null || $permission === '{}') {
            return true;
        }
        $assigned = json_decode($permission, true);
        if (!is_array($assigned) || empty($assigned)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('telegram_build_created_message')) {
    /**
     * Build the HTML message for a newly created task or project.
     *
     * @param string $type 'task' or 'project'
     * @param int $id Item id (used for the link)
     * @param string $name Item title
     * @param string $due_date Due/end date (optional)
     * @return string
     */
    function telegram_build_created_message($type, $id, $name, $due_date = '')
    {
        $is_task = ($type === 'task');
        $label = $is_task ? 'Task' : 'Project';

        $message = '<b>📢 New ' . $label . ' Created!</b>' . PHP_EOL;
        $message .= '<b>Title:</b> ' . telegram_escape($name) . PHP_EOL;
        if (!empty($due_date) && $due_date !== '0000-00-00') {
            $message .= '<b>' . ($is_task ? 'Due Date' : 'End Date') . ':</b> ' . telegram_escape($due_date) . PHP_EOL;
        }
        $link = $is_task
            ? site_url('admin/tasks/details/' . (int)$id)
            : site_url('admin/projects/project_details/' . (int)$id);
        $message .= '<b>Link:</b> <a href="' . $link . '">View ' . $label . '</a>';
        return $message;
    }
}

if (!function_exists('telegram_build_deleted_message')) {
    /**
     * Build the HTML message for a deleted task or project. No link (the
     * record no longer exists).
     *
     * @param string $type 'task' or 'project'
     * @param string $name Item title
     * @return string
     */
    function telegram_build_deleted_message($type, $name)
    {
        $label = ($type === 'task') ? 'Task' : 'Project';
        $message = '<b>🗑 ' . $label . ' Deleted!</b>' . PHP_EOL;
        $message .= '<b>Title:</b> ' . telegram_escape($name);
        return $message;
    }
}
```

- [x] **Step 4: Run the test to verify it passes**

Run from `C:\laragon\www\tic_crm\application\tests`:
```
php C:\laragon\www\tic_crm\vendor\bin\phpunit --filter TelegramHelperTest
```
Expected: PASS (9 tests).

- [x] **Step 5: Commit**

```bash
git add application/helpers/telegram_helper.php application/tests/helpers/TelegramHelperTest.php
git commit -m "feat(telegram): add recipient decision + message builder helpers"
```

---

## Task 2: Shared `telegram_deliver()`, `telegram_notify_deleted()`, refactor `telegram_notify_created()`

**Files:**
- Modify: `application/helpers/telegram_helper.php`
- Modify: `application/tests/helpers/TelegramHelperTest.php`

- [x] **Step 1: Extend the test with stubs + deliver/notify tests**

Append the following stubs and fakes to `application/tests/helpers/TelegramHelperTest.php` **above** the `require_once` line (they must be declared before the helper is included so the helper's `function_exists` guards keep the real HTTP `send_telegram_notification` from loading):

```php
$GLOBALS['telegram_sent_to'] = [];

function send_telegram_notification($chat_id, $message)
{
    $GLOBALS['telegram_sent_to'][] = $chat_id;
    return true;
}

function config_item($key)
{
    return ($key === 'telegram_group_id') ? '-100TESTGROUP' : null;
}

function log_message($level, $msg)
{
    // no-op
}

class FakeTelegramCI
{
    public $db;

    public function __construct()
    {
        $this->db = new FakeTelegramDb();
    }
}

class FakeTelegramDb
{
    public $whereIn = [];

    public function select($cols)
    {
        return $this;
    }

    public function where_in($col, $ids)
    {
        $this->whereIn = $ids;
        return $this;
    }

    public function get($table)
    {
        return new FakeTelegramResult();
    }
}

class FakeTelegramResult
{
    public function result()
    {
        return array();
    }
}

function get_instance()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new FakeTelegramCI();
    }
    return $instance;
}
```

Then add these test methods inside `class TelegramHelperTest` (before the closing brace):

```php
    public function testDeliverToGroupWhenPermissionIsAll(): void
    {
        $GLOBALS['telegram_sent_to'] = array();
        $this->assertTrue(telegram_deliver('hello', 'all'));
        $this->assertSame(array('-100TESTGROUP'), $GLOBALS['telegram_sent_to']);
    }

    public function testDeliverSkipsUsersWithoutChatId(): void
    {
        $GLOBALS['telegram_sent_to'] = array();
        $result = telegram_deliver('hello', '{"5":["view"],"8":["view"]}');
        $this->assertFalse($result);
        $this->assertSame(array(), $GLOBALS['telegram_sent_to']);
        $this->assertSame(array(5, 8), get_instance()->db->whereIn);
    }

    public function testNotifyDeletedSendsToGroup(): void
    {
        $GLOBALS['telegram_sent_to'] = array();
        $result = telegram_notify_deleted('task', 99, 'Old task', 'all');
        $this->assertTrue($result);
        $this->assertSame(array('-100TESTGROUP'), $GLOBALS['telegram_sent_to']);
    }
```

- [x] **Step 2: Run the test to verify the new tests fail**

Run from `C:\laragon\www\tic_crm\application\tests`:
```
php C:\laragon\www\tic_crm\vendor\bin\phpunit --filter TelegramHelperTest
```
Expected: FAIL with `Error: Call to undefined function telegram_deliver()` (existing 9 tests still pass).

- [x] **Step 3: Implement `telegram_deliver`, refactor `telegram_notify_created`, add `telegram_notify_deleted`**

In `application/helpers/telegram_helper.php`:

1. Replace the entire existing `telegram_notify_created` function (currently lines 77-134) with:

```php
if (!function_exists('telegram_deliver')) {
    /**
     * Fan out a message to Telegram recipients based on the permission value.
     *
     * - 'all' / empty / null / '{}' / invalid JSON  => configured group chat
     * - permission JSON                              => DM each assigned user
     *   who has a telegram_chat_id in tbl_users
     *
     * @param string $message Message text (HTML parse mode)
     * @param string $permission Raw permission column value
     * @return bool True if at least one message was accepted by the API
     */
    function telegram_deliver($message, $permission)
    {
        if (telegram_should_send_to_group($permission)) {
            return send_telegram_notification(config_item('telegram_group_id'), $message);
        }

        $assigned = json_decode($permission, true);
        $user_ids = array_map('intval', array_keys($assigned));
        if (empty($user_ids)) {
            return send_telegram_notification(config_item('telegram_group_id'), $message);
        }

        $CI = &get_instance();
        $CI->db->select('user_id, telegram_chat_id');
        $CI->db->where_in('user_id', $user_ids);
        $rows = $CI->db->get('tbl_users')->result();

        $sent = false;
        foreach ($rows as $row) {
            if (!empty($row->telegram_chat_id)) {
                if (send_telegram_notification($row->telegram_chat_id, $message)) {
                    $sent = true;
                }
            }
        }
        return $sent;
    }
}

if (!function_exists('telegram_notify_created')) {
    /**
     * Notify Telegram about a newly created task or project.
     *
     * @param string $type 'task' or 'project'
     * @param int $id Item id
     * @param string $name Item title
     * @param string $due_date Due/end date (optional)
     * @param string $permission Raw permission column value
     * @return bool
     */
    function telegram_notify_created($type, $id, $name, $due_date = '', $permission = '')
    {
        return telegram_deliver(telegram_build_created_message($type, $id, $name, $due_date), $permission);
    }
}

if (!function_exists('telegram_notify_deleted')) {
    /**
     * Notify Telegram about a deleted task or project.
     *
     * @param string $type 'task' or 'project'
     * @param int $id Item id (unused today, kept for API symmetry)
     * @param string $name Item title
     * @param string $permission Raw permission column value
     * @return bool
     */
    function telegram_notify_deleted($type, $id, $name, $permission = '')
    {
        return telegram_deliver(telegram_build_deleted_message($type, $name), $permission);
    }
}
```

- [x] **Step 4: Run the test to verify it passes**

Run from `C:\laragon\www\tic_crm\application\tests`:
```
php C:\laragon\www\tic_crm\vendor\bin\phpunit --filter TelegramHelperTest
```
Expected: PASS (12 tests).

- [x] **Step 5: Lint the helper**

```
php -l C:\laragon\www\tic_crm\application\helpers\telegram_helper.php
php -l C:\laragon\www\tic_crm\application\tests\helpers\TelegramHelperTest.php
```
Expected: both report `No syntax errors detected`.

- [x] **Step 6: Commit**

```bash
git add application/helpers/telegram_helper.php application/tests/helpers/TelegramHelperTest.php
git commit -m "feat(telegram): shared deliver fan-out + delete notifications"
```

---

## Task 3: Hook delete notification into `Tasks.php::delete_task()`

**Files:**
- Modify: `application/controllers/admin/Tasks.php:2166`

- [x] **Step 1: Add the hook**

In `application/controllers/admin/Tasks.php`, the real delete happens at line 2166:

```php
                $this->tasks_model->_table_name = "tbl_task"; // table name
                $this->tasks_model->_primary_key = "task_id"; // $id
                $this->tasks_model->delete($id);
```

Insert immediately after that `delete($id);` line (16-space indent, same block — this block only runs for top-level tasks, so subtask deletes never notify):

```php

                if (function_exists('telegram_notify_deleted')) {
                    telegram_notify_deleted('task', $id, $task_info->task_name, $task_info->permission);
                }
```

- [x] **Step 2: Lint**

```
php -l C:\laragon\www\tic_crm\application\controllers\admin\Tasks.php
```
Expected: `No syntax errors detected`.

- [x] **Step 3: Commit**

```bash
git add application/controllers/admin/Tasks.php
git commit -m "feat(telegram): notify on task delete"
```

---

## Task 4: Hook delete notification into `Projects.php::delete_project()`

**Files:**
- Modify: `application/controllers/admin/Projects.php:2657`

- [x] **Step 1: Add the hook**

In `application/controllers/admin/Projects.php`, the real delete happens at line 2657:

```php
                $this->items_model->_table_name = 'tbl_project';
                $this->items_model->_primary_key = 'project_id';
                $this->items_model->delete($id);
```

Insert immediately after that `delete($id);` line (16-space indent):

```php

                if (function_exists('telegram_notify_deleted')) {
                    telegram_notify_deleted('project', $id, $project_info->project_name, $project_info->permission);
                }
```

- [x] **Step 2: Lint**

```
php -l C:\laragon\www\tic_crm\application\controllers\admin\Projects.php
```
Expected: `No syntax errors detected`.

- [x] **Step 3: Commit**

```bash
git add application/controllers/admin/Projects.php
git commit -m "feat(telegram): notify on project delete"
```

---

## Task 5: End-to-end verification

**Files:** none (verification only)

- [x] **Step 1: Lint every changed file**

```
php -l C:\laragon\www\tic_crm\application\helpers\telegram_helper.php
php -l C:\laragon\www\tic_crm\application\tests\helpers\TelegramHelperTest.php
php -l C:\laragon\www\tic_crm\application\controllers\admin\Tasks.php
php -l C:\laragon\www\tic_crm\application\controllers\admin\Projects.php
```
Expected: all report `No syntax errors detected`.

- [x] **Step 2: Run the helper unit tests**

From `C:\laragon\www\tic_crm\application\tests`:
```
php C:\laragon\www\tic_crm\vendor\bin\phpunit --filter TelegramHelperTest
```
Expected: PASS (12 tests).

- [x] **Step 3: Smoke-test create + delete via the browser**

Logged in as admin at `http://localhost/tic_crm`:
1. Create a task (`admin/tasks/new`) → save. Page must redirect normally with no 500.
2. Delete that task (`admin/tasks/delete_task/<id>`) → success message.
3. Create a project (`admin/projects/create`, required: Project Name, Client, Start/End Date, Description, Assigned To = Everyone) → save. Page must load `project_details/<id>` normally.
4. Delete that project → success message.

Expected: no PHP errors in any page load. The Telegram calls fire silently (token is a placeholder).

- [x] **Step 4: Confirm the send attempts are logged**

Read the most recent error log:
```
Get-ChildItem C:\laragon\www\tic_crm\application\logs -Filter "log-*.php" | Sort-Object LastWriteTime -Descending | Select-Object -First 1
```
Expected: the newest log contains `send_telegram_notification cURL error` or `send_telegram_notification API error` entries with chat ids `-1009999999999` (group) — one per create and one per delete from the smoke test. This proves the code paths run.

- [x] **Step 5: Confirm no regression in existing tests (optional baseline)**

From `C:\laragon\www\tic_crm\application\tests`:
```
php C:\laragon\www\tic_crm\vendor\bin\phpunit
```
Note: this suite is integration-style (hits the live server + DB). Pre-existing tests may be environment-dependent; the required assertion for this feature is that `TelegramHelperTest` passes (Step 2) and the app is smoke-clean (Steps 3-4).

---

## Rollback

To remove the feature: revert the four commits from Tasks 1-4 (`git revert` or `git reset`) and, optionally, clear the placeholder config values:
```sql
DELETE FROM tic_crm.tbl_config WHERE config_key IN ('telegram_bot_token', 'telegram_group_id');
```
The `tbl_users.telegram_chat_id` column is inert when no token is configured.
