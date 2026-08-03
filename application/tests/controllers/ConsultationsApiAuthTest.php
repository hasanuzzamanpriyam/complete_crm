<?php
use PHPUnit\Framework\TestCase;

class ConsultationsApiAuthTest extends TestCase
{
    private const BASE_URL = 'http://localhost/tic_crm/index.php/api/v1/consultations';
    private const TEST_KEY = 'test-consultation-api-key-0123456789abcdef';

    private static $pdo;
    private $bearerToken;
    private $previousKeyValue;
    private $previousKeyExists;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
    }

    protected function setUp(): void
    {
        $row = self::$pdo->query("SELECT value FROM tbl_config WHERE config_key = 'consultation_api_key'")->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            $this->previousKeyExists = true;
            $this->previousKeyValue = $row['value'];
        } else {
            $this->previousKeyExists = false;
            $this->previousKeyValue = null;
        }

        $this->_setApiKey(self::TEST_KEY);
        $this->bearerToken = $this->_loginTestUser();
    }

    protected function tearDown(): void
    {
        if ($this->previousKeyExists) {
            $stmt = self::$pdo->prepare("UPDATE tbl_config SET value = ? WHERE config_key = 'consultation_api_key'");
            $stmt->execute([$this->previousKeyValue]);
        } else {
            self::$pdo->exec("DELETE FROM tbl_config WHERE config_key = 'consultation_api_key'");
        }

        $stmt = self::$pdo->query("SELECT user_id FROM tbl_users WHERE email LIKE 'cons_api_%'");
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($userIds)) {
            $ids = implode(',', $userIds);
            self::$pdo->exec("DELETE FROM tbl_user_api_sessions WHERE user_id IN ($ids)");
            self::$pdo->exec("DELETE FROM tbl_account_details WHERE user_id IN ($ids)");
            self::$pdo->exec("DELETE FROM tbl_users WHERE user_id IN ($ids)");
        }
    }

    public function testNoAuthReturns401()
    {
        $res = $this->_get(self::BASE_URL . '/consultants');
        $this->assertSame(401, $res['code']);
        $this->assertFalse($res['body']['success'] ?? true);
    }

    public function testWrongApiKeyReturns401()
    {
        $res = $this->_get(self::BASE_URL . '/consultants', ['X-API-Key: wrong-key']);
        $this->assertSame(401, $res['code']);
    }

    public function testCorrectApiKeyAllowsConsultants()
    {
        $res = $this->_get(self::BASE_URL . '/consultants', ['X-API-Key: ' . self::TEST_KEY]);
        $this->assertSame(200, $res['code']);
        $this->assertTrue($res['body']['success'] ?? false);
        $this->assertArrayHasKey('consultants', $res['body']);
    }

    public function testCorrectApiKeyAllowsSlotsValidation()
    {
        $res = $this->_get(self::BASE_URL . '/slots', ['X-API-Key: ' . self::TEST_KEY]);
        $this->assertSame(400, $res['code']);
        $this->assertFalse($res['body']['success'] ?? true);
    }

    public function testCorrectApiKeyAllowsBookingsList()
    {
        $res = $this->_get(self::BASE_URL . '/bookings', ['X-API-Key: ' . self::TEST_KEY]);
        $this->assertSame(200, $res['code']);
        $this->assertTrue($res['body']['success'] ?? false);
        $this->assertArrayHasKey('appointments', $res['body']);
    }

    public function testEmptySettingDisablesApiKey()
    {
        $this->_setApiKey('');
        $res = $this->_get(self::BASE_URL . '/consultants', ['X-API-Key: ' . self::TEST_KEY]);
        $this->assertSame(401, $res['code']);
    }

    public function testBearerTokenStillWorks()
    {
        $this->assertNotEmpty($this->bearerToken);
        $res = $this->_get(self::BASE_URL . '/consultants', ['Authorization: Bearer ' . $this->bearerToken]);
        $this->assertSame(200, $res['code']);
        $this->assertTrue($res['body']['success'] ?? false);
    }

    private function _get($url, array $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['code' => $code, 'body' => json_decode((string)$body, true)];
    }

    private function _setApiKey($value): void
    {
        $row = self::$pdo->query("SELECT config_key FROM tbl_config WHERE config_key = 'consultation_api_key'")->fetch();
        if ($row !== false) {
            $stmt = self::$pdo->prepare("UPDATE tbl_config SET value = ? WHERE config_key = 'consultation_api_key'");
            $stmt->execute([$value]);
        } else {
            $stmt = self::$pdo->prepare("INSERT INTO tbl_config (config_key, value) VALUES ('consultation_api_key', ?)");
            $stmt->execute([$value]);
        }
    }

    private function _loginTestUser(): string
    {
        $username = 'cons_api_' . uniqid();
        $email = $username . '@test.com';
        $password = 'testpass123';
        $hash = hash('sha512', $password . 'I6PnEPbQNLslYMj7ChKxDJ2yenuHLkXn');

        self::$pdo->prepare("INSERT INTO tbl_users (username, email, password, role_id, activated, banned, created) VALUES (?, ?, ?, 1, 1, 0, NOW())")
            ->execute([$username, $email, $hash]);
        $userId = self::$pdo->lastInsertId();
        self::$pdo->prepare("INSERT INTO tbl_account_details (user_id, fullname) VALUES (?, ?)")
            ->execute([$userId, 'Consult API Test']);

        $ch = curl_init('http://localhost/tic_crm/index.php/api/auth/login');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['username' => $username, 'password' => $password]),
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $resp['access_token'] ?? '';
    }
}
