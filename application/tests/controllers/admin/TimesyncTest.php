<?php
use PHPUnit\Framework\TestCase;

class TimesyncTest extends TestCase
{
    private static $pdo;
    private $cookieFile;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
    }

    protected function setUp(): void
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'TS_');
        $this->loginAsAdmin();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }

    private function loginAsAdmin(): void
    {
        // First get the CSRF/login page
        $ch = curl_init('http://localhost/tic_crm/index.php/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        curl_exec($ch);
        curl_close($ch);

        // Submit login form
        $ch = curl_init('http://localhost/tic_crm/index.php/login');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_POSTFIELDS => http_build_query([
                'user_name' => 'admin',
                'password' => 'admin',
            ]),
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function get($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['body' => $resp, 'code' => $httpCode];
    }

    public function testDashboardReturns200()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync');
        $this->assertEquals(200, $res['code']);
    }

    public function testDashboardHasExpectedDataKeys()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('today_hours', $res['body']);
        $this->assertStringContainsString('week_hours', $res['body']);
        $this->assertStringContainsString('month_hours', $res['body']);
        $this->assertStringContainsString('active_users', $res['body']);
        $this->assertStringContainsString('daily_chart_labels', $res['body']);
        $this->assertStringContainsString('daily_chart_values', $res['body']);
        $this->assertStringContainsString('user_distribution', $res['body']);
        $this->assertStringContainsString('user_grid', $res['body']);
    }
}
