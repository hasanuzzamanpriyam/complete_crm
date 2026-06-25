<?php
use PHPUnit\Framework\TestCase;

class AuthRoleMappingTest extends TestCase
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
        // Clean up any existing test user
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

    public function testAdminRoleMapping(): void
    {
        $user = self::createTestUser('1', 'test_admin_' . uniqid());
        $result = $this->loginAs($user['username']);
        self::deleteTestUser($user['username']);
        $this->assertNotNull($result, 'Admin login should succeed');
        $this->assertEquals('admin', $result['user']['role']);
    }

    public function testManagerRoleMapping(): void
    {
        $user = self::createTestUser('3', 'test_manager_' . uniqid());
        $result = $this->loginAs($user['username']);
        self::deleteTestUser($user['username']);
        $this->assertNotNull($result, 'Manager login should succeed');
        $this->assertEquals('manager', $result['user']['role']);
    }

    public function testEmployeeRoleMapping(): void
    {
        $user = self::createTestUser('2', 'test_employee_' . uniqid());
        $result = $this->loginAs($user['username']);
        self::deleteTestUser($user['username']);
        $this->assertNotNull($result, 'Employee login should succeed');
        $this->assertEquals('employee', $result['user']['role']);
    }
}
