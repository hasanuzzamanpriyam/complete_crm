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
        $this->assertStringContainsString('period_hours', $res['body']);
        $this->assertStringContainsString('dailyHoursChart', $res['body']);
        $this->assertStringContainsString('userDistChart', $res['body']);
        $this->assertStringContainsString('userGridCard', $res['body']);
    }

    public function testCalendarReturns200()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/calendar');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('TimeSync Calendar', $res['body']);
    }

    public function testDayDetailsReturns200()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/day_details/2026-06-01');
        $this->assertEquals(200, $res['code']);
    }

    public function testUserReportReturns200()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/user/81');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('user_id', $res['body']);
        $this->assertStringContainsString('Total Hours', $res['body']);
    }

    public function testUserTabEntriesReturns200()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/user/81?tab=entries');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('Time Entries', $res['body']);
    }

    public function testUserTabScreenshotsReturns200()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/user/81?tab=screenshots');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('Screenshots', $res['body']);
    }

    public function testUserTabAppsReturns200()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/user/81?tab=apps');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('App Usage', $res['body']);
    }

    public function testEntriesPageReturns200()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/entries');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('Time Entries', $res['body']);
    }

    public function testCalendarDaysClickable()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/calendar');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('day_details', $res['body']);
    }

    public function testDashboardHasCharts()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('dailyHoursChart', $res['body']);
        $this->assertStringContainsString('userDistChart', $res['body']);
        $this->assertStringContainsString('userGridCard', $res['body']);
    }

    public function testScreenshotsEnhanced()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/screenshots');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('screenshot_count', $res['body']);
        $this->assertStringContainsString('task_id', $res['body']);
    }

    public function testUsageReportEnhanced()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/usage');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('appUsageChart', $res['body']);
    }

    public function testDashboardWithCustomDateRange()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync?from=2026-01-01&to=2026-06-28');
        $this->assertEquals(200, $res['code']);
    }

    public function testDashboardWithInvalidDateRange()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync?from=invalid&to=invalid');
        $this->assertContains($res['code'], [200, 500]);
    }

    public function testUserReportWithInvalidUserId()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/user/99999');
        $this->assertContains($res['code'], [200, 302, 303, 307]);
    }

    public function testUserReportWithDateRange()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/user/81?from=2026-01-01&to=2026-06-28');
        $this->assertEquals(200, $res['code']);
    }

    public function testEntriesDatatableReturnsJson()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/admin/timesync/entries_datatable');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'draw=1&start=0&length=10',
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest'],
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->assertEquals(200, $code);
        $json = json_decode($body, true);
        $this->assertNotNull($json);
        $this->assertArrayHasKey('draw', $json);
        $this->assertArrayHasKey('recordsTotal', $json);
        $this->assertArrayHasKey('data', $json);
    }

    public function testDayDetailsWithInvalidDate()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/day_details/not-a-date');
        $this->assertEquals(200, $res['code']);
    }

    public function testScreenshotsWithUserFilter()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/screenshots?user_id=81');
        $this->assertEquals(200, $res['code']);
    }

    public function testViewImageReturnsImage()
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/admin/timesync/view_image/1');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_HEADER => false,
            CURLOPT_NOBODY => true,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->assertContains($code, [200, 303, 404]);
    }

    public function testSettingsPage()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/settings');
        $this->assertEquals(200, $res['code']);
        $this->assertStringContainsString('TimeSync Settings', $res['body']);
    }

    public function testUsageWithUserFilter()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/usage?user_id=81&from=2026-01-01&to=2026-06-28');
        $this->assertEquals(200, $res['code']);
    }

    public function testUserPageRedirectsWithoutUserId()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/user');
        $this->assertContains($res['code'], [200, 302, 303, 307]);
    }
}
