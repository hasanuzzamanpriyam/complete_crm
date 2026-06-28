<?php
use PHPUnit\Framework\TestCase;

class DashboardDetailedOverviewTest extends TestCase
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
        $email = "ddo_test_{$roleId}_" . uniqid() . "@test.com";
        $username = "ddo_user_{$roleId}_" . uniqid();
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

    public function testAdminGetsCompleteResponse()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/api/dashboard/detailed-overview');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->adminToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('profile', $resp['data']);
        $this->assertArrayHasKey('kpi', $resp['data']);
        $this->assertArrayHasKey('daily_logged', $resp['data']);
        $this->assertArrayHasKey('desktop_activity', $resp['data']);
        $this->assertArrayHasKey('daily_activity_breakdown', $resp['data']);
        $this->assertArrayHasKey('top_apps', $resp['data']);
        $this->assertArrayHasKey('recent_screenshots', $resp['data']);
        $this->assertArrayHasKey('recent_windows', $resp['data']);

        $this->assertEquals('Organization Overview', $resp['data']['profile']['name']);
        $this->assertEquals('org', $resp['data']['profile']['role']);

        $this->assertArrayHasKey('time_logged', $resp['data']['kpi']);
        $this->assertArrayHasKey('time_billable', $resp['data']['kpi']);
        $this->assertArrayHasKey('trend_percent', $resp['data']['kpi']);

        $this->assertArrayHasKey('total_time', $resp['data']['desktop_activity']);
        $this->assertArrayHasKey('activity_score', $resp['data']['desktop_activity']);
    }

    public function testManagerGetsCompleteResponse()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/api/dashboard/detailed-overview');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->managerToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('profile', $resp['data']);
        $this->assertEquals('Organization Overview', $resp['data']['profile']['name']);
    }

    public function testEmployeeDeniedWithoutUserId()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/api/dashboard/detailed-overview');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->employeeToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertFalse($resp['success'] ?? true);
    }

    public function testEmployeeCanAccessOwnDataWithUserId()
    {
        $stmt = self::$pdo->query("SELECT user_id FROM tbl_users WHERE email LIKE 'ddo_test_2_%' ORDER BY user_id DESC LIMIT 1");
        $empUserId = (int)$stmt->fetchColumn();

        $ch = curl_init('http://localhost/tic_crm/index.php/api/dashboard/detailed-overview?user_id=' . $empUserId);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->employeeToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('profile', $resp['data']);
        $this->assertEquals('employee', $resp['data']['profile']['role']);
    }

    public function testAdminGetsFilteredResponseWithDateRange()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/api/dashboard/detailed-overview?start_date=2026-06-01&end_date=2026-06-28');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->adminToken],
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('kpi', $resp['data']);
        $this->assertArrayHasKey('daily_logged', $resp['data']);
        $this->assertArrayHasKey('desktop_activity', $resp['data']);
        $this->assertArrayHasKey('top_apps', $resp['data']);
        $this->assertArrayHasKey('recent_screenshots', $resp['data']);
        $this->assertArrayHasKey('recent_windows', $resp['data']);
    }

    protected function tearDown(): void
    {
        $stmt = self::$pdo->query("SELECT user_id FROM tbl_users WHERE email LIKE 'ddo_test_%'");
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
