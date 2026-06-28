<?php
use PHPUnit\Framework\TestCase;

class TasksListResponseTest extends TestCase
{
    private static $pdo;
    private $adminToken;
    private $taskId;
    private $taskTitle;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
    }

    protected function setUp(): void
    {
        $roleId = 1;
        $this->taskTitle = 'tsk_list_test_' . uniqid();

        $email = "tsl_test_{$roleId}_" . uniqid() . "@test.com";
        $username = "tsl_user_{$roleId}_" . uniqid();
        $password = 'testpass123';
        $hash = hash('sha512', $password . 'I6PnEPbQNLslYMj7ChKxDJ2yenuHLkXn');

        self::$pdo->prepare("INSERT INTO tbl_users (username, email, password, role_id, activated, banned, created) VALUES (?, ?, ?, ?, 1, 0, NOW())")
            ->execute([$username, $email, $hash, $roleId]);
        $userId = self::$pdo->lastInsertId();

        self::$pdo->prepare("INSERT INTO tbl_account_details (user_id, fullname, avatar) VALUES (?, ?, ?)")
            ->execute([$userId, "Test Reporter", "reporter_avatar.png"]);

        $ch = curl_init('http://localhost/tic_crm/index.php/api/auth/login');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['username' => $username, 'password' => $password]),
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertNotNull($resp['access_token'] ?? null, 'Login failed HTTP: ' . $httpCode);
        $this->adminToken = $resp['access_token'];

        $permission = json_encode([$userId => ["view", "edit", "delete"]]);
        self::$pdo->prepare("INSERT INTO tbl_task (task_name, task_description, created_by, permission, task_created_date, priority, task_status) VALUES (?, ?, ?, ?, NOW(), 'medium', 'not_started')")
            ->execute([$this->taskTitle, 'Test description for list test', $userId, $permission]);
        $this->taskId = (int)self::$pdo->lastInsertId();
    }

    public function testTaskListIncludesAssigneeReporterFields()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/api/tasks?limit=100');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->adminToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('tasks', $resp);

        $found = null;
        foreach ($resp['tasks'] as $task) {
            if ($task['title'] === $this->taskTitle) {
                $found = $task;
                break;
            }
        }
        $this->assertNotNull($found, 'Created test task not found in response');

        $this->assertArrayHasKey('assignee_name', $found);
        $this->assertArrayHasKey('assignee_avatar', $found);
        $this->assertArrayHasKey('reporter_name', $found);
        $this->assertArrayHasKey('reporter_avatar', $found);

        $this->assertNotEmpty($found['assignee_name']);
        $this->assertNotEmpty($found['reporter_name']);
        $this->assertEquals('Test Reporter', $found['reporter_name']);
        $this->assertEquals('reporter_avatar.png', $found['reporter_avatar']);
    }

    protected function tearDown(): void
    {
        if ($this->taskId) {
            self::$pdo->exec("DELETE FROM tbl_task WHERE task_id = {$this->taskId}");
        }

        $stmt = self::$pdo->query("SELECT user_id FROM tbl_users WHERE email LIKE 'tsl_test_%'");
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($userIds)) {
            $ids = implode(',', $userIds);
            self::$pdo->exec("DELETE FROM tbl_account_details WHERE user_id IN ($ids)");
            self::$pdo->exec("DELETE FROM tbl_users WHERE user_id IN ($ids)");
        }
    }
}
