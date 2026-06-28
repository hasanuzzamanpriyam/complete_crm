<?php
use PHPUnit\Framework\TestCase;

class DashboardSummaryTest extends TestCase
{
    private static $pdo;
    private $adminToken;
    private $managerToken;
    private $employeeToken;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
    }

    protected function setUp(): void
    {
        $this->adminToken = $this->loginUser(1);
        $this->managerToken = $this->loginUser(3);
        $this->employeeToken = $this->loginUser(2);
    }

    private function loginUser($roleId)
    {
        $email = "dashboard_test_{$roleId}_" . uniqid() . "@test.com";
        $username = "dash_user_{$roleId}_" . uniqid();
        $password = 'testpass123';
        $hash = hash('sha512', $password . 'I6PnEPbQNLslYMj7ChKxDJ2yenuHLkXn');

        self::$pdo->prepare("INSERT INTO tbl_users (username, email, password, role_id, activated, banned, created) VALUES (?, ?, ?, ?, 1, 0, NOW())")
            ->execute([$username, $email, $hash, $roleId]);
        $userId = self::$pdo->lastInsertId();

        self::$pdo->prepare("INSERT INTO tbl_account_details (user_id, fullname) VALUES (?, ?)")
            ->execute([$userId, "User $roleId"]);

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

        $this->assertNotNull($resp['access_token'] ?? null, 'Login failed for role ' . $roleId . ' HTTP: ' . $httpCode);
        return $resp['access_token'];
    }

    public function testAdminGetsGlobalStats()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/api/dashboard/summary');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->adminToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('personal', $resp['data']);
        $this->assertArrayHasKey('hours_today', $resp['data']['personal']);
        $this->assertArrayHasKey('weekly_total_seconds', $resp['data']['personal']);
        $this->assertArrayHasKey('tasks_in_progress_count', $resp['data']['personal']);
        $this->assertArrayHasKey('weekly_trend', $resp['data']['personal']);
        $this->assertArrayHasKey('project_distribution', $resp['data']['personal']);
        $this->assertArrayHasKey('team', $resp['data']);
        $this->assertArrayHasKey('total_company_hours_today', $resp['data']['team']);
        $this->assertArrayHasKey('active_users_count', $resp['data']['team']);
        $this->assertArrayHasKey('tasks_completed_today', $resp['data']['team']);
        $this->assertArrayHasKey('weekly_trend', $resp['data']['team']);
        $this->assertArrayHasKey('team_list', $resp['data']['team']);
    }

    public function testManagerGetsAdminFields()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/api/dashboard/summary');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->managerToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('personal', $resp['data']);
        $this->assertArrayHasKey('team', $resp['data']);
        $this->assertArrayHasKey('total_company_hours_today', $resp['data']['team']);
    }

    public function testEmployeeDoesNotGetAdminFields()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/api/dashboard/summary');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->employeeToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('personal', $resp['data']);
        $this->assertArrayNotHasKey('team', $resp['data']);
    }

    public function testEmployeeGetsPersonalStats()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/api/dashboard/summary');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->employeeToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('personal', $resp['data']);
        $this->assertArrayHasKey('hours_today', $resp['data']['personal']);
        $this->assertArrayHasKey('weekly_total_seconds', $resp['data']['personal']);
        $this->assertArrayHasKey('tasks_in_progress_count', $resp['data']['personal']);
        $this->assertArrayHasKey('weekly_trend', $resp['data']['personal']);
        $this->assertArrayHasKey('project_distribution', $resp['data']['personal']);
    }

    protected function tearDown(): void
    {
        // Clean up test users created in setUp
        $stmt = self::$pdo->query("SELECT user_id FROM tbl_users WHERE email LIKE 'dashboard_test_%'");
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($userIds)) {
            $ids = implode(',', $userIds);
            self::$pdo->exec("DELETE FROM tbl_desktop_time_entries WHERE user_id IN ($ids)");
            self::$pdo->exec("DELETE FROM tbl_desktop_app_usage WHERE user_id IN ($ids)");
            self::$pdo->exec("DELETE FROM tbl_task WHERE created_by IN ($ids)");
            self::$pdo->exec("DELETE FROM tbl_account_details WHERE user_id IN ($ids)");
            self::$pdo->exec("DELETE FROM tbl_users WHERE user_id IN ($ids)");
        }
    }
}
