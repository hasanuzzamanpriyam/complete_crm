<?php
use PHPUnit\Framework\TestCase;

class ReportsDayDetailsTest extends TestCase
{
    private static string $baseUrl = 'http://localhost/tic_crm';

    private static function getEncryptionKey(): string
    {
        return 'I6PnEPbQNLslYMj7ChKxDJ2yenuHLkXn';
    }

    private static function hashPassword(string $password): string
    {
        return hash('sha512', $password . self::getEncryptionKey());
    }

    private static function createTestUser(string $role_id, string $username): array
    {
        $pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
        $pdo->exec("DELETE FROM tbl_users WHERE username = " . $pdo->quote($username));
        $pdo->exec("DELETE FROM tbl_account_details WHERE fullname = 'Test DayDetails User'");

        $hash = self::hashPassword('test123');
        $pdo->exec("INSERT INTO tbl_users (username, email, password, role_id, activated, banned, created)
            VALUES (" . $pdo->quote($username) . ", " . $pdo->quote($username . '@test.com') . ", " . $pdo->quote($hash) . ", $role_id, 1, 0, NOW())");
        $userId = $pdo->lastInsertId();
        $pdo->exec("INSERT INTO tbl_account_details (user_id, fullname) VALUES ($userId, 'Test DayDetails User')");

        return ['id' => $userId, 'username' => $username];
    }

    private static function deleteTestUser(string $username): void
    {
        $pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
        $pdo->exec("DELETE FROM tbl_users WHERE username = " . $pdo->quote($username));
    }

    private function loginAs(string $username, string $password = 'test123'): ?array
    {
        $ch = curl_init(self::$baseUrl . '/index.php/api/auth/login');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['username' => $username, 'password' => $password]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        return null;
    }

    private function callDayDetails(string $token, string $year, string $month, string $day, ?string $userId = null): array
    {
        $url = self::$baseUrl . '/index.php/api/reports/day-details?year=' . $year . '&month=' . $month . '&day=' . $day;
        if ($userId !== null) {
            $url .= '&user_id=' . $userId;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['http_code' => $httpCode, 'body' => json_decode($response, true)];
    }

    public function testDayDetailsReturnsCorrectStructure(): void
    {
        $user = self::createTestUser('2', 'test_dd_emp_' . uniqid());
        $loginResult = $this->loginAs($user['username']);
        $this->assertNotNull($loginResult, 'Login should succeed');
        $token = $loginResult['access_token'];

        // Use today's date
        $result = $this->callDayDetails($token, date('Y'), date('m'), date('d'));

        self::deleteTestUser($user['username']);

        $this->assertEquals(200, $result['http_code'], 'Should return 200');
        $this->assertTrue($result['body']['success'] ?? false, 'Should be successful');
        $this->assertArrayHasKey('day_details', $result['body'], 'Should have day_details key');
        $this->assertIsArray($result['body']['day_details'], 'day_details should be an array');

        foreach ($result['body']['day_details'] as $entry) {
            $this->assertArrayHasKey('task_id', $entry);
            $this->assertArrayHasKey('task_title', $entry);
            $this->assertArrayHasKey('project_name', $entry);
            $this->assertArrayHasKey('total_seconds', $entry);
        }
    }

    public function testDayDetailsRequiresAuth(): void
    {
        $result = $this->callDayDetails('invalid-token', date('Y'), date('m'), date('d'));
        $this->assertEquals(401, $result['http_code'], 'Should return 401 without valid auth');
    }

    public function testDayDetailsRequiresAllDateParams(): void
    {
        $user = self::createTestUser('2', 'test_dd_miss_' . uniqid());
        $loginResult = $this->loginAs($user['username']);
        $this->assertNotNull($loginResult);
        $token = $loginResult['access_token'];

        // Missing day param
        $url = self::$baseUrl . '/index.php/api/reports/day-details?year=' . date('Y') . '&month=' . date('m');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        self::deleteTestUser($user['username']);

        $this->assertEquals(400, $httpCode, 'Should return 400 when day is missing');
    }
}
