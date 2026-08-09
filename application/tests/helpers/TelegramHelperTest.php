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
}
