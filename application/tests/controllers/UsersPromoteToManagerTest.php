<?php
use PHPUnit\Framework\TestCase;

class UsersPromoteToManagerTest extends TestCase
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
        $pdo->exec("DELETE FROM tbl_account_details WHERE fullname = 'Test User'");

        $hash = self::hashPassword('test123');
        $pdo->exec("INSERT INTO tbl_users (username, email, password, role_id, activated, banned, created)
            VALUES (" . $pdo->quote($username) . ", " . $pdo->quote($username . '@test.com') . ", " . $pdo->quote($hash) . ", $role_id, 1, 0, NOW())");
        $userId = $pdo->lastInsertId();
        $pdo->exec("INSERT INTO tbl_account_details (user_id, fullname) VALUES ($userId, 'Test User')");

        return ['id' => $userId, 'username' => $username];
    }

    private static function deleteTestUser(string $username): void
    {
        $pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
        $pdo->exec("DELETE u FROM tbl_users u WHERE u.username = " . $pdo->quote($username));
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

    private function promoteToManager(string $token, int $userId): array
    {
        $ch = curl_init(self::$baseUrl . '/index.php/api/users/promote-to-manager');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => json_encode(['user_id' => $userId]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['http_code' => $httpCode, 'response' => json_decode($response, true)];
    }

    public function testAdminCanPromoteEmployeeToManager(): void
    {
        $admin = self::createTestUser('1', 'test_promo_admin_' . uniqid());
        $emp = self::createTestUser('2', 'test_promo_emp_' . uniqid());

        $loginResult = $this->loginAs($admin['username']);
        $this->assertNotNull($loginResult, 'Admin login should succeed');
        $token = $loginResult['access_token'] ?? '';

        $result = $this->promoteToManager($token, $emp['id']);

        $this->assertEquals(200, $result['http_code'], 'Admin should get 200 when promoting employee');
        $this->assertTrue($result['response']['success'] ?? false, 'Response should have success=true');

        $pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
        $stmt = $pdo->prepare("SELECT role_id FROM tbl_users WHERE user_id = ?");
        $stmt->execute([$emp['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($row, 'User should still exist in DB');
        $this->assertEquals(3, (int)$row['role_id'], 'Employee role_id should be changed to 3 (manager)');

        self::deleteTestUser($admin['username']);
        self::deleteTestUser($emp['username']);
    }

    public function testEmployeeCannotPromoteSelf(): void
    {
        $emp = self::createTestUser('2', 'test_promo_self_' . uniqid());

        $loginResult = $this->loginAs($emp['username']);
        $this->assertNotNull($loginResult, 'Employee login should succeed');
        $token = $loginResult['access_token'] ?? '';

        $result = $this->promoteToManager($token, $emp['id']);

        self::deleteTestUser($emp['username']);

        $this->assertEquals(403, $result['http_code'], 'Employee should get 403 when trying to promote');
        $this->assertFalse($result['response']['success'] ?? true, 'Response should have success=false');
    }
}
