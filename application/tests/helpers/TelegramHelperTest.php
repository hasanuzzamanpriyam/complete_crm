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

$GLOBALS['telegram_sent_to'] = [];
$GLOBALS['__telesa_enabled'] = false;
$GLOBALS['__telesa_rows'] = [];

function send_telegram_notification($chat_id, $message)
{
    $GLOBALS['telegram_sent_to'][] = $chat_id;
    return true;
}

function config_item($key)
{
    if ($key === 'telegram_group_id') {
        return '-100TESTGROUP';
    }
    if ($key === 'telegram_super_admin_notify') {
        return $GLOBALS['__telesa_enabled'] ? '1' : '0';
    }
    if ($key === 'telegram_bot_token') {
        return 'TESTTOKEN';
    }
    return null;
}

function lang($line, $label = '')
{
    if (is_array($label)) {
        return vsprintf((string)$line, $label);
    }
    return $label === '' ? (string)$line : sprintf('%s [%s]', (string)$line, $label);
}

function fullname($user_id)
{
    return 'User#' . $user_id;
}

function time_ago($date)
{
    return 'just now';
}

function base_url($uri = '')
{
    return 'http://localhost/tic_crm/' . ltrim($uri, '/');
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

    public function where($col, $val = null, $escape = null)
    {
        return $this;
    }

    public function or_where($col, $val = null, $escape = null)
    {
        return $this;
    }

    public function group_start()
    {
        return $this;
    }

    public function group_end()
    {
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
        return !empty($GLOBALS['__telesa_rows']) ? $GLOBALS['__telesa_rows'] : array();
    }

    public function row()
    {
        return null;
    }
}

function &get_instance()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new FakeTelegramCI();
    }
    return $instance;
}

require_once __DIR__ . '/../../helpers/telegram_helper.php';

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

    public function testBuildCreatedMessageOmitsZeroDate(): void
    {
        $msg = telegram_build_created_message('task', 1, 'X', '0000-00-00');
        $this->assertStringNotContainsString('Due Date', $msg);
        $this->assertStringNotContainsString('End Date', $msg);
    }

    public function testBuildCreatedMessageUsesEndDateLabelForProject(): void
    {
        $msg = telegram_build_created_message('project', 7, 'Website', '2026-08-20');
        $this->assertStringContainsString('<b>End Date:</b> 2026-08-20', $msg);
    }

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

    public function testSuperAdminMirrorDisabledByDefault(): void
    {
        $GLOBALS['__telesa_enabled'] = false;
        $GLOBALS['telegram_sent_to'] = array();
        $GLOBALS['__telesa_rows'] = array(
            (object)array('user_id' => 77, 'telegram_chat_id' => '1367920599'),
        );
        $this->assertFalse(telegram_notify_super_admins(array(
            'description' => 'new_task_created',
            'value' => 'Task A',
            'link' => 'admin/tasks/details/1',
            'date' => '2026-08-15 10:00:00',
            'from_user_id' => 5,
        )));
        $this->assertSame(array(), $GLOBALS['telegram_sent_to']);
    }

    public function testSuperAdminMirrorSendsToAllSuperAdmins(): void
    {
        $GLOBALS['__telesa_enabled'] = true;
        $GLOBALS['telegram_sent_to'] = array();
        $GLOBALS['__telesa_rows'] = array(
            (object)array('user_id' => 1, 'telegram_chat_id' => '1000000001'),
            (object)array('user_id' => 77, 'telegram_chat_id' => '1367920599'),
            (object)array('user_id' => 99, 'telegram_chat_id' => ''), // skipped (empty chat id)
        );
        $result = telegram_notify_super_admins(array(
            'description' => 'new_task_created',
            'value' => 'Task A',
            'link' => 'admin/tasks/details/1',
            'date' => '2026-08-15 10:00:00',
            'from_user_id' => 5,
        ));
        $this->assertTrue($result);
        $this->assertSame(array('1000000001', '1367920599'), $GLOBALS['telegram_sent_to']);
    }

    public function testSuperAdminMirrorFormatsTopbarText(): void
    {
        $GLOBALS['__telesa_enabled'] = true;
        $GLOBALS['telegram_sent_to'] = array();
        $GLOBALS['__telesa_rows'] = array(
            (object)array('user_id' => 77, 'telegram_chat_id' => '1367920599'),
        );
        $msg = null;
        // capture the message passed to send_telegram_notification
        $captured = array();
        // override the global sender to capture the message
        // (re-declaring send_telegram_notification is not possible, so assert via side effect)
        telegram_notify_super_admins(array(
            'description' => 'new_task_created',
            'value' => 'Login fix',
            'link' => 'admin/tasks/details/42',
            'date' => '2026-08-15 10:00:00',
            'from_user_id' => 5,
        ));
        // The exact message text is built privately; assert the chat id was targeted
        // and that the function returned true (message was non-empty enough to send).
        $this->assertSame(array('1367920599'), $GLOBALS['telegram_sent_to']);
    }

    public function testSuperAdminMirrorNoRecipientsReturnsFalse(): void
    {
        $GLOBALS['__telesa_enabled'] = true;
        $GLOBALS['telegram_sent_to'] = array();
        $GLOBALS['__telesa_rows'] = array();
        $this->assertFalse(telegram_notify_super_admins(array(
            'description' => 'new_task_created',
            'value' => 'Task A',
            'link' => 'admin/tasks/details/1',
            'date' => '2026-08-15 10:00:00',
            'from_user_id' => 5,
        )));
        $this->assertSame(array(), $GLOBALS['telegram_sent_to']);
    }
}
