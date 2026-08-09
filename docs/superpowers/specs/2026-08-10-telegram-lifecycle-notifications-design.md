# Telegram Lifecycle Notifications for Tasks & Projects

## Overview

Add Telegram notifications for the task and project lifecycle in the ERP. When a task or project is **created** or **deleted**, a message is sent to a configured Telegram group chat and/or to the direct chats of assigned users. Unassigned items remain visible to all staff.

Create-notifications, settings UI, per-user `telegram_chat_id`, and unassigned-visibility are already implemented (uncommitted) and are preserved as-is. This spec adds the missing delete-notification path and refactors the helper for shared fan-out logic.

## Architecture

- All delivery logic lives in `application/helpers/telegram_helper.php` (already autoloaded).
- Delivery is **in-app** (controller hooks), not a DB trigger — DB triggers cannot reliably make HTTP calls.
- HTTP delivery via cURL to `https://api.telegram.org/bot<token>/sendMessage` with `parse_mode=HTML`.
- **Silent-fail contract:** any delivery failure is logged (`application/logs`) and never breaks the ERP request.
- Config keys `telegram_bot_token` and `telegram_group_id` are read from `tbl_config` (loaded into config items by `MY_Controller::__construct`). Per-user recipient chat ids come from `tbl_users.telegram_chat_id`.

## File Changes

### 1. `application/helpers/telegram_helper.php` (refactor + extend)

| Function | Action |
|----------|--------|
| `send_telegram_notification($chat_id, $message)` | Keep as-is |
| `telegram_escape($value)` | Keep as-is |
| `telegram_deliver($message, $permission)` | **New** — shared fan-out: `all`/`NULL`/`''`/`'{}'` → group chat; permission JSON → DM each assigned user who has a `telegram_chat_id` |
| `telegram_notify_created($type, $id, $name, $due_date, $permission)` | Refactor to use `telegram_deliver()`; behavior unchanged |
| `telegram_notify_deleted($type, $id, $name, $permission)` | **New** — sends a "Deleted" message (title only, no dead link) via `telegram_deliver()` |

Delete message format:
```
🗑 Task Deleted!
Title: <name>
```
(`Project Deleted!` for projects; no link because the record no longer exists.)

### 2. `application/controllers/admin/Tasks.php`

- `save_task()` — keep existing `telegram_notify_created()` call (line ~750).
- `delete_task($id, $bulk)` — after a successful top-level delete, call `telegram_notify_deleted('task', $id, $task_info->task_name, $task_info->permission)`. Fires only for top-level tasks (not subtasks), matching the existing `if (empty($sub_task_info) || !empty($bulk))` guard.

### 3. `application/controllers/admin/Projects.php`

- `save_project()` — keep existing `telegram_notify_created()` call (line ~474).
- `delete_project($id, $bulk)` — after a successful delete, call `telegram_notify_deleted('project', $id, $project_info->project_name, $project_info->permission)`.

### 4. Already done (no changes)

- `MY_Model.php::staff_query()` — unassigned visibility (`permission` `NULL`/`''`/`'{}'` match for role-3 staff)
- Migration `642_add_telegram_integration.php` — config keys + `tbl_users.telegram_chat_id`
- `Settings.php` + `views/admin/settings/general.php` — bot token / group ID fields
- `User.php` + `views/admin/user/update_contact.php` — `telegram_chat_id` field
- `application/language/english/utilities_lang.php` — Telegram language strings

## Behavior / Recipient Resolution

- Permission `all` (or `NULL`/`''`/`'{}'`): one message to `telegram_group_id`.
- Permission JSON (customized assignment, e.g. `{"5":"5","8":"8"}`): one message to each assigned user whose `tbl_users.telegram_chat_id` is set. Users without a chat id are skipped.
- If no recipients resolve (e.g., no group id configured and no user chat ids), delivery is a no-op.

## Error Handling

- Empty chat id / empty token → log and return `false`.
- cURL failure / non-`ok` API response → log and return `false`.
- Delivery is synchronous but fast (10s timeout); callers ignore the return value so ERP flow is never interrupted.

## Explicit Non-Goals

- No DB triggers.
- No new group-chat table (the configured Telegram group is the group chat target).
- No edit/update notifications.
- No in-app "test connection" button.
- No removal or behavioral change to existing create-notification, visibility, settings, or profile-code.

## Configuration

- `telegram_bot_token` — bot token from BotFather.
- `telegram_group_id` — numeric group chat id (negative for supergroups, e.g. `-100xxxxxxxxxx`).
- `tbl_users.telegram_chat_id` — per-user DM chat id (set on the user profile page).

Current DB values are placeholders (`TESTTOKEN:12345` / `-1009999999999`) and must be replaced with real values for live delivery.

## Verification

1. `php -l` on every changed file.
2. Browser smoke test (admin login, `http://localhost/tic_crm`):
   - Create a task → ERP page loads normally; a send attempt is logged.
   - Delete the task → same.
   - Create + delete a project → same.
   - Subtask delete → no delete notification (top-level only).
3. Confirm no PHP errors/warnings in `application/logs` other than the expected silent-fail Telegram entries (while token is a placeholder).
4. Live end-to-end: set a real bot token + group id (via Settings), create/delete a task, confirm the message appears in the Telegram group, and that assigned users with a `telegram_chat_id` receive a DM.

## Rollback

Revert the git working-tree changes for `telegram_helper.php`, `Tasks.php`, and `Projects.php`. Config keys in `tbl_config` and the `tbl_users.telegram_chat_id` column are inert when no token is configured (delivery no-ops).
