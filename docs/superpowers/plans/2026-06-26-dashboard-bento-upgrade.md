# Dashboard Bento-Grid Upgrade Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the current simple stat-card dashboard with a role-aware bento-grid layout featuring AreaCharts with gradients, project distribution charts, and a "Team at a Glance" widget.

**Architecture:** A single `GET /api/dashboard/summary` backend endpoint returns role-aware payloads (admin gets global stats + company trend + team brief; standard user gets personal stats + personal trend + project distribution). Frontend splits into AdminDashboard and UserDashboard components using a single `useDashboardSummary()` hook.

**Tech Stack:** PHP 8.3 / CI3 backend, React 19 / Tailwind / Recharts frontend, PHPUnit 12, Vitest

---

### Task 1: Backend RED — Write failing test for Dashboard/summary endpoint

**Files:**
- Create: `application/tests/controllers/DashboardSummaryTest.php`

- [ ] **Step 1: Write failing test for admin summary**

```php
<?php
use PHPUnit\Framework\TestCase;

class DashboardSummaryTest extends TestCase
{
    private static $pdo;
    private static $config;
    private $adminToken;
    private $employeeToken;
    private $managerToken;

    public static function setUpBeforeClass(): void
    {
        self::$config = require __DIR__ . '/../../config/database.php';
        $db = self::$config['default'];
        self::$pdo = new PDO(
            "mysql:host={$db['hostname']};dbname={$db['database']}",
            $db['username'],
            $db['password']
        );
    }

    protected function setUp(): void
    {
        $this->adminToken = $this->loginUser(1);
        $this->employeeToken = $this->loginUser(2);
        $this->managerToken = $this->loginUser(3);
    }

    private function loginUser($roleId)
    {
        $email = "dashboard_test_{$roleId}@" . uniqid() . ".com";
        $username = "dash_user_{$roleId}_" . uniqid();
        $password = 'testpass123';
        $hash = hash('sha256', $password . 'I6PnEPbQNLslYMj7ChKxDJ2yenuHLkXn');

        self::$pdo->prepare("INSERT INTO tbl_users (username, email, password, role_id, activated) VALUES (?, ?, ?, ?, 1)")
            ->execute([$username, $email, $hash, $roleId]);
        $userId = self::$pdo->lastInsertId();

        self::$pdo->prepare("INSERT INTO tbl_account_details (user_id, fullname) VALUES (?, ?)")
            ->execute([$userId, "User $roleId"]);

        $ch = curl_init('http://localhost/tic_crm/api/auth/login');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['username' => $username, 'password' => $password]),
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertNotNull($resp['access_token'] ?? null, 'Login failed for role ' . $roleId);
        return $resp['access_token'];
    }

    public function testAdminGetsGlobalStats()
    {
        $ch = curl_init('http://localhost/tic_crm/api/dashboard/summary');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->adminToken],
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('global_stats', $resp['data']);
        $this->assertArrayHasKey('total_company_hours_today', $resp['data']['global_stats']);
        $this->assertArrayHasKey('active_users_count', $resp['data']['global_stats']);
        $this->assertArrayHasKey('tasks_completed_today', $resp['data']['global_stats']);
        $this->assertArrayHasKey('company_weekly_trend', $resp['data']);
        $this->assertArrayHasKey('team_brief', $resp['data']);
    }

    public function testEmployeeDoesNotGetAdminFields()
    {
        $ch = curl_init('http://localhost/tic_crm/api/dashboard/summary');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->employeeToken],
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayNotHasKey('global_stats', $resp['data']);
        $this->assertArrayNotHasKey('company_weekly_trend', $resp['data']);
        $this->assertArrayNotHasKey('team_brief', $resp['data']);
    }

    public function testEmployeeGetsPersonalStats()
    {
        $ch = curl_init('http://localhost/tic_crm/api/dashboard/summary');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->employeeToken],
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->assertTrue($resp['success'] ?? false);
        $this->assertArrayHasKey('personal_stats', $resp['data']);
        $this->assertArrayHasKey('hours_today', $resp['data']['personal_stats']);
        $this->assertArrayHasKey('weekly_total_seconds', $resp['data']['personal_stats']);
        $this->assertArrayHasKey('tasks_in_progress_count', $resp['data']['personal_stats']);
        $this->assertArrayHasKey('personal_weekly_trend', $resp['data']);
        $this->assertArrayHasKey('project_distribution', $resp['data']);
    }

    protected function tearDown(): void
    {
        $tables = ['tbl_desktop_time_entries', 'tbl_desktop_app_usage', 'tbl_task', 'tbl_account_details', 'tbl_users'];
        foreach ($tables as $t) {
            self::$pdo->exec("DELETE FROM {$t} WHERE user_id IN (SELECT user_id FROM tbl_users WHERE email LIKE 'dashboard_test_%')");
        }
        self::$pdo->exec("DELETE FROM tbl_users WHERE email LIKE 'dashboard_test_%'");
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd C:\laragon\www\tic_crm && php vendor/bin/phpunit application/tests/controllers/DashboardSummaryTest.php --filter testAdminGetsGlobalStats -v`
Expected: 404 or route error — summary method doesn't exist yet

---

### Task 2: Backend GREEN — Implement summary() method + route

**Files:**
- Modify: `application/controllers/api/Dashboard.php` (add summary() method)
- Modify: `application/config/routes.php` (add route)

- [ ] **Step 1: Add route**

In `application/config/routes.php`, add after the existing dashboard route:
```
$route['api/dashboard/summary'] = 'api/dashboard/summary';
```

- [ ] **Step 2: Add summary() method to Dashboard.php**

Add after the `live_users()` method:

```php
public function summary()
{
    $user = $this->api_auth->authenticate();
    $period = $this->input->get('period') ?? 'weekly';
    $target_user_id = $this->input->get('user_id') ? (int)$this->input->get('user_id') : null;

    $since = $period === 'monthly' ? date('Y-m-d', strtotime('-30 days')) : date('Y-m-d', strtotime('-7 days'));

    $is_admin = $this->api_auth->is_super_admin();
    $is_manager = $user->role === 'manager';

    $data = [];

    // Admin/manager get global stats + company trend + team brief
    if ($is_admin || $is_manager) {
        // For company-wide data, resolve WITHOUT target_user_id so admin sees all users
        // even when viewing someone else's personal stats
        $company_ids = $this->_resolve_user_ids(null);

        // Global stats
        $global = $this->db
            ->select("
                (SELECT COALESCE(SUM(total_seconds), 0) FROM tbl_desktop_time_entries WHERE DATE(started_at) = CURDATE() AND type = 'work') as total_hours,
                (SELECT COUNT(DISTINCT user_id) FROM tbl_desktop_time_entries WHERE is_running = 1 AND stopped_at IS NULL) as active_users,
                (SELECT COUNT(*) FROM tbl_task WHERE task_status = 'completed' AND DATE(updated_at) = CURDATE()) as tasks_completed
            ")
            ->get()->row();

        $data['global_stats'] = [
            'total_company_hours_today' => round((float)$global->total_hours / 3600, 1),
            'active_users_count' => (int)$global->active_users,
            'tasks_completed_today' => (int)$global->tasks_completed,
        ];

        // Company weekly trend
        $trend_q = $this->db
            ->select("DATE(started_at) as date, SUM(total_seconds) as total_seconds")
            ->from('tbl_desktop_time_entries')
            ->where('DATE(started_at) >=', $since)
            ->where('type', 'work');
        if (is_array($company_ids)) {
            $trend_q->where_in('user_id', $company_ids);
        }
        $trend_rows = $trend_q->group_by('DATE(started_at)')->order_by('DATE(started_at)', 'ASC')->get()->result();

        $data['company_weekly_trend'] = array_map(function ($r) {
            return ['date' => $r->date, 'total_seconds' => (int)$r->total_seconds];
        }, $trend_rows);

        // Team brief
        $brief_q = $this->db
            ->select("u.user_id, u.online_time, ad.fullname as name, ad.avatar, COALESCE(SUM(te.total_seconds), 0) as today_seconds")
            ->from('tbl_users u')
            ->join('tbl_account_details ad', 'ad.user_id = u.user_id', 'left')
            ->join('tbl_desktop_time_entries te', 'te.user_id = u.user_id AND DATE(te.started_at) = CURDATE() AND te.type = \'work\'', 'left')
            ->where('u.activated', 1)
            ->where('u.banned', 0);
        if (is_array($company_ids)) {
            $brief_q->where_in('u.user_id', $company_ids);
        }
        $brief_rows = $brief_q->group_by('u.user_id')->get()->result();

        $active_entries = $this->db
            ->select('user_id')
            ->from('tbl_desktop_time_entries')
            ->where('is_running', 1)
            ->where('stopped_at IS NULL', null, false)
            ->get()->result();
        $active_user_ids = array_map(function ($e) { return (int)$e->user_id; }, $active_entries);

        $now_ts = time();
        $data['team_brief'] = array_map(function ($r) use ($active_user_ids, $now_ts) {
            $uid = (int)$r->user_id;
            $is_active = in_array($uid, $active_user_ids);
            $online = !empty($r->online_time) && (int)$r->online_time > ($now_ts - 300);
            $status = $is_active ? 'active' : ($online ? 'idle' : 'offline');
            return [
                'user_id' => $uid,
                'name' => $r->name ?? 'Unknown',
                'avatar' => base_url(!empty($r->avatar) ? $r->avatar : 'assets/img/user/default_avatar.jpg'),
                'hours_today' => round((float)$r->today_seconds / 3600, 1),
                'status' => $status,
            ];
        }, $brief_rows);
    }

    // Personal stats (all users)
    $personal_user_id = $target_user_id ?: (int)$user->user_id;

    // personal_stats
    $today_row = $this->db
        ->select("COALESCE(SUM(total_seconds), 0) as total")
        ->from('tbl_desktop_time_entries')
        ->where('user_id', $personal_user_id)
        ->where('DATE(started_at)', 'CURDATE()', false)
        ->where('type', 'work')
        ->get()->row();

    $weekly_row = $this->db
        ->select("COALESCE(SUM(total_seconds), 0) as total")
        ->from('tbl_desktop_time_entries')
        ->where('user_id', $personal_user_id)
        ->where('DATE(started_at) >=', $since)
        ->where('type', 'work')
        ->get()->row();

    $in_progress_row = $this->db
        ->select("COUNT(*) as cnt")
        ->from('tbl_task')
        ->where('task_status', 'in_progress')
        ->where('created_by', $personal_user_id)
        ->get()->row();

    $data['personal_stats'] = [
        'hours_today' => round((float)$today_row->total / 3600, 1),
        'weekly_total_seconds' => (int)$weekly_row->total,
        'tasks_in_progress_count' => (int)($in_progress_row->cnt ?? 0),
    ];

    // Personal weekly trend
    $personal_trend = $this->db
        ->select("DATE(started_at) as date, SUM(total_seconds) as total_seconds")
        ->from('tbl_desktop_time_entries')
        ->where('user_id', $personal_user_id)
        ->where('DATE(started_at) >=', $since)
        ->where('type', 'work')
        ->group_by('DATE(started_at)')
        ->order_by('DATE(started_at)', 'ASC')
        ->get()->result();

    $data['personal_weekly_trend'] = array_map(function ($r) {
        return ['date' => $r->date, 'total_seconds' => (int)$r->total_seconds];
    }, $personal_trend);

    // Project distribution
    $project_rows = $this->db
        ->select("p.project_id, p.project_name, SUM(te.total_seconds) as total_seconds")
        ->from('tbl_desktop_time_entries te')
        ->join('tbl_task t', 't.task_id = te.task_id')
        ->join('tbl_project p', 'p.project_id = t.project_id')
        ->where('te.user_id', $personal_user_id)
        ->where('DATE(te.started_at) >=', $since)
        ->where('te.type', 'work')
        ->group_by('p.project_id, p.project_name')
        ->order_by('total_seconds', 'DESC')
        ->get()->result();

    $grand_total = array_sum(array_map(function ($r) { return (int)$r->total_seconds; }, $project_rows));
    $data['project_distribution'] = array_map(function ($r) use ($grand_total) {
        $secs = (int)$r->total_seconds;
        return [
            'project_id' => (int)$r->project_id,
            'project_name' => $r->project_name ?? 'No Project',
            'total_seconds' => $secs,
            'percentage' => $grand_total > 0 ? round(($secs / $grand_total) * 100, 1) : 0,
        ];
    }, $project_rows);

    return $this->_respond(200, true, 'OK', ['data' => $data]);
}
```

- [ ] **Step 3: Run tests to verify they pass**

Run: `cd C:\laragon\www\tic_crm && php vendor/bin/phpunit application/tests/controllers/DashboardSummaryTest.php -v`
Expected: 3 tests, 12+ assertions, all pass

---

### Task 3: Add getDashboardSummary() to frontend reports.ts API service

**Files:**
- Modify: `src/services/api/reports.ts` (add types + method)
- Create: `src/services/api/reports.test.ts` (already exists — extend with new tests)

- [ ] **Step 1: Add types and method to reports.ts**

Add after the existing `DayDetailEntry` interface:

```typescript
export interface TeamBriefEntry {
  user_id: number;
  name: string;
  avatar: string;
  hours_today: number;
  status: "active" | "idle" | "offline";
}

export interface ProjectDistEntry {
  project_id: number;
  project_name: string;
  total_seconds: number;
  percentage: number;
}

export interface DashboardSummaryData {
  global_stats?: {
    total_company_hours_today: number;
    active_users_count: number;
    tasks_completed_today: number;
  };
  company_weekly_trend?: DailyHours[];
  team_brief?: TeamBriefEntry[];
  personal_stats: {
    hours_today: number;
    weekly_total_seconds: number;
    tasks_in_progress_count: number;
  };
  personal_weekly_trend: DailyHours[];
  project_distribution: ProjectDistEntry[];
}

export interface SummaryResponse {
  success: boolean;
  message: string;
  data: DashboardSummaryData;
}
```

Add method inside the `reportApi` object:

```typescript
  async getDashboardSummary(period: "weekly" | "monthly" = "weekly", userId?: number): Promise<DashboardSummaryData> {
    const params: Record<string, string> = { period };
    if (userId !== undefined) params.user_id = String(userId);
    const { data } = await api.get<SummaryResponse>("/dashboard/summary", { params });
    return data.data;
  },
```

- [ ] **Step 2: Write API test**

Search existing test file `src/services/api/reports.test.ts` and add at the end:

```typescript
describe("reportApi.getDashboardSummary", () => {
  it("calls dashboard/summary endpoint with period", async () => {
    const mockResponse: SummaryResponse = {
      success: true,
      message: "OK",
      data: {
        personal_stats: { hours_today: 4.5, weekly_total_seconds: 36000, tasks_in_progress_count: 2 },
        personal_weekly_trend: [],
        project_distribution: [],
      },
    };
    mock.onGet("/dashboard/summary", { params: { period: "weekly" } }).reply(200, mockResponse);

    const result = await reportApi.getDashboardSummary("weekly");
    expect(result.personal_stats.hours_today).toBe(4.5);
  });

  it("passes user_id when provided", async () => {
    mock.onGet("/dashboard/summary", { params: { period: "weekly", user_id: "5" } }).reply(200, {
      success: true, message: "OK",
      data: {
        personal_stats: { hours_today: 0, weekly_total_seconds: 0, tasks_in_progress_count: 0 },
        personal_weekly_trend: [],
        project_distribution: [],
      },
    });

    await reportApi.getDashboardSummary("weekly", 5);
    expect(mock.history.get[1].params.user_id).toBe("5");
  });
});
```

- [ ] **Step 3: Run tests to verify**

Run: `cd C:\Users\CT\Desktop\Tracker && npx vitest run src/services/api/reports.test.ts -v`
Expected: All tests pass

---

### Task 4: Create useDashboardSummary hook

**Files:**
- Create: `src/features/dashboard/hooks/useDashboardSummary.ts`
- Create: `src/features/dashboard/hooks/useDashboardSummary.test.ts`
- Modify: `src/features/dashboard/hooks/index.ts`

- [ ] **Step 1: Write the failing test**

`src/features/dashboard/hooks/useDashboardSummary.test.ts`:

```typescript
import { describe, it, expect, vi, beforeEach } from "vitest";
import { renderHook, waitFor } from "@testing-library/react";
import { useDashboardSummary } from "./useDashboardSummary";
import { reportApi, DashboardSummaryData } from "@/services/api/reports";

vi.mock("@/services/api/reports", () => ({
  reportApi: {
    getDashboardSummary: vi.fn(),
  },
}));

const mockData: DashboardSummaryData = {
  personal_stats: { hours_today: 4.5, weekly_total_seconds: 36000, tasks_in_progress_count: 2 },
  personal_weekly_trend: [{ date: "2026-06-20", total_seconds: 3600 }],
  project_distribution: [{ project_id: 1, project_name: "Test", total_seconds: 3600, percentage: 100 }],
};

describe("useDashboardSummary", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("returns loading state initially", () => {
    vi.mocked(reportApi.getDashboardSummary).mockReturnValue(new Promise(() => {}));
    const { result } = renderHook(() => useDashboardSummary());
    expect(result.current.isLoading).toBe(true);
    expect(result.current.data).toBeNull();
    expect(result.current.error).toBeNull();
  });

  it("returns data on success", async () => {
    vi.mocked(reportApi.getDashboardSummary).mockResolvedValue(mockData);
    const { result } = renderHook(() => useDashboardSummary());
    await waitFor(() => expect(result.current.isLoading).toBe(false));
    expect(result.current.data?.personal_stats.hours_today).toBe(4.5);
    expect(result.current.data?.project_distribution).toHaveLength(1);
    expect(result.current.error).toBeNull();
  });

  it("returns error on failure", async () => {
    vi.mocked(reportApi.getDashboardSummary).mockRejectedValue(new Error("API Error"));
    const { result } = renderHook(() => useDashboardSummary());
    await waitFor(() => expect(result.current.isLoading).toBe(false));
    expect(result.current.data).toBeNull();
    expect(result.current.error).toBe("API Error");
  });

  it("accepts period and userId parameters", () => {
    renderHook(() => useDashboardSummary("monthly", 5));
    expect(reportApi.getDashboardSummary).toHaveBeenCalledWith("monthly", 5);
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd C:\Users\CT\Desktop\Tracker && npx vitest run src/features/dashboard/hooks/useDashboardSummary.test.ts -v`
Expected: FAIL — module not found

- [ ] **Step 3: Write the hook implementation**

`src/features/dashboard/hooks/useDashboardSummary.ts`:

```typescript
import { useState, useEffect, useCallback } from "react";
import { reportApi, DashboardSummaryData } from "@/services/api/reports";

interface UseDashboardSummaryResult {
  data: DashboardSummaryData | null;
  isLoading: boolean;
  error: string | null;
  refetch: () => void;
}

export function useDashboardSummary(period: "weekly" | "monthly" = "weekly", userId?: number): UseDashboardSummaryResult {
  const [data, setData] = useState<DashboardSummaryData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetch = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    try {
      const result = await reportApi.getDashboardSummary(period, userId);
      setData(result);
    } catch (err: any) {
      setError(err?.message ?? "Failed to load dashboard data");
    } finally {
      setIsLoading(false);
    }
  }, [period, userId]);

  useEffect(() => {
    fetch();
  }, [fetch]);

  return { data, isLoading, error, refetch: fetch };
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `cd C:\Users\CT\Desktop\Tracker && npx vitest run src/features/dashboard/hooks/useDashboardSummary.test.ts -v`
Expected: 4 tests pass

- [ ] **Step 5: Export from hooks index**

In `src/features/dashboard/hooks/index.ts`, add:
```typescript
export { useDashboardSummary } from "./useDashboardSummary";
```

---

### Task 5: Create AdminDashboard component

**Files:**
- Create: `src/features/dashboard/components/AdminDashboard.tsx`

- [ ] **Step 1: Write the component**

```typescript
import { DashboardSummaryData } from "@/services/api/reports";
import { StatCard } from "@/features/dashboard/components/StatCard";
import { WeeklyHoursChart } from "@/features/dashboard/components/WeeklyHoursChart";
import { TeamAtGlance } from "@/features/dashboard/components/TeamAtGlance";
import { Clock, UserCheck, CheckCircle2 } from "lucide-react";

interface AdminDashboardProps {
  data: DashboardSummaryData;
  isLoading: boolean;
  error: string | null;
}

export function AdminDashboard({ data, isLoading, error }: AdminDashboardProps) {
  const gs = data.global_stats;

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-3 gap-4">
        <StatCard
          title="Company Hours Today"
          value={gs ? `${gs.total_company_hours_today}h` : "—"}
          icon={<Clock className="h-5 w-5" />}
          isLoading={isLoading}
          error={error}
          className="border-l-4 border-l-green-500"
        />
        <StatCard
          title="Active Users"
          value={gs?.active_users_count ?? "—"}
          icon={<UserCheck className="h-5 w-5" />}
          isLoading={isLoading}
          error={error}
          className="border-l-4 border-l-green-500"
        />
        <StatCard
          title="Tasks Completed"
          value={gs?.tasks_completed_today ?? "—"}
          icon={<CheckCircle2 className="h-5 w-5" />}
          isLoading={isLoading}
          error={error}
          className="border-l-4 border-l-green-500"
        />
      </div>

      <WeeklyHoursChart data={data.company_weekly_trend ?? []} isLoading={isLoading} />

      <TeamAtGlance entries={data.team_brief ?? []} isLoading={isLoading} />
    </div>
  );
}
```

---

### Task 6: Create UserDashboard component

**Files:**
- Create: `src/features/dashboard/components/UserDashboard.tsx`

- [ ] **Step 1: Write the component**

```typescript
import { DashboardSummaryData } from "@/services/api/reports";
import { StatCard } from "@/features/dashboard/components/StatCard";
import { WeeklyHoursChart } from "@/features/dashboard/components/WeeklyHoursChart";
import { ProjectDistributionChart } from "@/features/dashboard/components/ProjectDistributionChart";
import { Clock, CalendarDays, ListChecks } from "lucide-react";

interface UserDashboardProps {
  data: DashboardSummaryData;
  isLoading: boolean;
  error: string | null;
}

export function UserDashboard({ data, isLoading, error }: UserDashboardProps) {
  const ps = data.personal_stats;
  const weeklyHours = Math.round((ps.weekly_total_seconds / 3600) * 100) / 100;

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-3 gap-4">
        <StatCard
          title="Today's Hours"
          value={`${ps.hours_today}h`}
          icon={<Clock className="h-5 w-5" />}
          isLoading={isLoading}
          error={error}
          className="border-l-4 border-l-blue-500"
        />
        <StatCard
          title="Weekly Total"
          value={`${weeklyHours}h`}
          icon={<CalendarDays className="h-5 w-5" />}
          isLoading={isLoading}
          error={error}
          className="border-l-4 border-l-blue-500"
        />
        <StatCard
          title="Active Tasks"
          value={ps.tasks_in_progress_count}
          icon={<ListChecks className="h-5 w-5" />}
          isLoading={isLoading}
          error={error}
          className="border-l-4 border-l-blue-500"
        />
      </div>

      <div className="grid grid-cols-2 gap-6">
        <WeeklyHoursChart data={data.personal_weekly_trend} isLoading={isLoading} />
        <ProjectDistributionChart data={data.project_distribution} isLoading={isLoading} />
      </div>
    </div>
  );
}
```

---

### Task 7: Create TeamAtGlance component

**Files:**
- Create: `src/features/dashboard/components/TeamAtGlance.tsx`

- [ ] **Step 1: Write the component**

```typescript
import { TeamBriefEntry } from "@/services/api/reports";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

function initials(name: string): string {
  return name.split(" ").map((n) => n[0]).join("").toUpperCase().slice(0, 2);
}

function statusColor(status: string): string {
  switch (status) {
    case "active": return "bg-green-500";
    case "idle": return "bg-yellow-500";
    default: return "bg-gray-400";
  }
}

interface TeamAtGlanceProps {
  entries: TeamBriefEntry[];
  isLoading: boolean;
}

function Skeleton({ className }: { className?: string }) {
  return <div className={`bg-muted animate-pulse rounded ${className ?? ""}`} />;
}

export function TeamAtGlance({ entries, isLoading }: TeamAtGlanceProps) {
  return (
    <Card>
      <CardHeader className="pb-3">
        <CardTitle className="text-lg">Team at a Glance</CardTitle>
      </CardHeader>
      <CardContent>
        {isLoading ? (
          <div className="space-y-3">
            {Array.from({ length: 5 }).map((_, i) => (
              <div key={i} className="flex items-center gap-4">
                <Skeleton className="h-9 w-9 rounded-full" />
                <div className="flex-1 space-y-1">
                  <Skeleton className="h-4 w-32" />
                  <Skeleton className="h-2 w-full" />
                </div>
              </div>
            ))}
          </div>
        ) : entries.length === 0 ? (
          <p className="text-sm text-muted-foreground py-4 text-center">No team data available</p>
        ) : (
          <div className="space-y-3">
            {entries.map((entry) => (
              <div key={entry.user_id} className="flex items-center gap-4">
                <div className="relative shrink-0">
                  <Avatar className="h-9 w-9">
                    <AvatarImage src={entry.avatar} alt={entry.name} />
                    <AvatarFallback>{initials(entry.name)}</AvatarFallback>
                  </Avatar>
                  <span className={`absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-background ${statusColor(entry.status)}`} />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between">
                    <p className="text-sm font-medium truncate">{entry.name}</p>
                    <span className="text-sm text-muted-foreground shrink-0 ml-2">{entry.hours_today}h / 8h</span>
                  </div>
                  <div className="mt-1 h-2 w-full rounded-full bg-muted">
                    <div
                      className="h-2 rounded-full bg-primary transition-all"
                      style={{ width: `${Math.min(100, (entry.hours_today / 8) * 100)}%` }}
                    />
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
```

---

### Task 8: Create ProjectDistributionChart component

**Files:**
- Create: `src/features/dashboard/components/ProjectDistributionChart.tsx`

- [ ] **Step 1: Write the component**

```typescript
import {
  PieChart, Pie, Cell, Tooltip, ResponsiveContainer,
  BarChart, Bar, XAxis, YAxis, CartesianGrid,
} from "recharts";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ProjectDistEntry } from "@/services/api/reports";

const COLORS = ["#10b981", "#3b82f6", "#f59e0b", "#ef4444", "#8b5cf6", "#ec4899", "#14b8a6", "#f97316"];

function formatHours(seconds: number): string {
  const h = seconds / 3600;
  return `${h.toFixed(1)}h`;
}

interface ProjectDistributionChartProps {
  data: ProjectDistEntry[];
  isLoading?: boolean;
}

export function ProjectDistributionChart({ data, isLoading }: ProjectDistributionChartProps) {
  const hasMany = data.length >= 5;

  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-sm font-medium">Project Distribution</CardTitle>
      </CardHeader>
      <CardContent>
        {isLoading ? (
          <div className="h-48 bg-muted animate-pulse rounded" />
        ) : data.length === 0 ? (
          <p className="text-sm text-muted-foreground py-8 text-center">No project data this period</p>
        ) : hasMany ? (
          <div className="h-48 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={data} layout="vertical" margin={{ top: 5, right: 20, left: 0, bottom: 5 }}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                <XAxis type="number" tick={{ fontSize: 11 }} tickFormatter={(v) => `${v}%`} />
                <YAxis type="category" dataKey="project_name" tick={{ fontSize: 11 }} width={90} />
                <Tooltip
                  formatter={(value: number) => [`${value}%`, "Percentage"]}
                  contentStyle={{ fontSize: 13 }}
                />
                <Bar dataKey="percentage" fill="#3b82f6" radius={[0, 4, 4, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        ) : (
          <div className="h-48 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={data}
                  cx="50%"
                  cy="50%"
                  innerRadius={55}
                  outerRadius={85}
                  dataKey="percentage"
                  nameKey="project_name"
                  label={({ project_name, percent }: any) =>
                    `${(percent * 100).toFixed(0)}%`
                  }
                  labelLine={false}
                >
                  {data.map((_, i) => (
                    <Cell key={i} fill={COLORS[i % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip
                  formatter={(value: number, name: string) => [`${value}%`, name]}
                />
              </PieChart>
            </ResponsiveContainer>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
```

---

### Task 9: Refactor DashboardPage

**Files:**
- Modify: `src/features/dashboard/components/DashboardPage.tsx`

- [ ] **Step 1: Rewrite DashboardPage to use new hook + role routing**

```typescript
import { useState, useEffect } from "react";
import { useAuthStore } from "@/features/auth/store";
import { userApi } from "@/services/api/users";
import { useDashboardSummary } from "@/features/dashboard/hooks";
import { AdminDashboard } from "@/features/dashboard/components/AdminDashboard";
import { UserDashboard } from "@/features/dashboard/components/UserDashboard";
import { LiveUsersSection } from "@/features/dashboard/components/LiveUsersSection";
import { User } from "@/types";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

export function DashboardPage() {
  const user = useAuthStore((s) => s.user);
  const isAdmin = user?.role === "admin" || user?.role === "manager";
  const [selectedUserId, setSelectedUserId] = useState<number | "self">("self");
  const [users, setUsers] = useState<User[]>([]);

  useEffect(() => {
    if (isAdmin) {
      userApi.fetchUsers().then(setUsers).catch(() => {});
    }
  }, [isAdmin]);

  const otherUserId = isAdmin && selectedUserId !== "self" ? (selectedUserId as number) : undefined;
  const { data, isLoading, error } = useDashboardSummary("weekly", otherUserId);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">Dashboard</h1>
        {isAdmin && (
          <Select
            value={selectedUserId === "self" ? "self" : String(selectedUserId)}
            onValueChange={(val) => setSelectedUserId(val === "self" ? "self" : parseInt(val))}
          >
            <SelectTrigger className="w-56">
              <SelectValue placeholder="Select user..." />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="self">My Stats</SelectItem>
              {users.map((u) => (
                <SelectItem key={u.id} value={String(u.id)}>{u.full_name}</SelectItem>
              ))}
            </SelectContent>
          </Select>
        )}
      </div>

      {error && (
        <div className="rounded-lg bg-destructive/10 p-3 text-sm text-destructive">
          {error}
        </div>
      )}

      {data && isAdmin && (
        <AdminDashboard data={data} isLoading={isLoading} error={error} />
      )}

      {data && !isAdmin && (
        <UserDashboard data={data} isLoading={isLoading} error={error} />
      )}

      {!data && isLoading && (
        <div className="space-y-6">
          <div className="grid grid-cols-3 gap-4">
            {Array.from({ length: 3 }).map((_, i) => (
              <div key={i} className="h-24 rounded-lg bg-muted animate-pulse" />
            ))}
          </div>
          <div className="h-48 rounded-lg bg-muted animate-pulse" />
        </div>
      )}

      {isAdmin && <LiveUsersSection />}
    </div>
  );
}
```

- [ ] **Step 2: Run frontend tests to verify nothing broke**

Run: `cd C:\Users\CT\Desktop\Tracker && npx vitest run -v`
Expected: All existing tests pass (may need to update snapshots if any)

---

### Task 10: Refactor WeeklyHoursChart to use AreaChart with gradient

**Files:**
- Modify: `src/features/dashboard/components/WeeklyHoursChart.tsx`

- [ ] **Step 1: Replace BarChart with AreaChart**

```typescript
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from "recharts";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { DailyHours } from "@/services/api/reports";

const DAY_NAMES: Record<string, string> = {
  "Monday": "Mon", "Tuesday": "Tue", "Wednesday": "Wed",
  "Thursday": "Thu", "Friday": "Fri", "Saturday": "Sat", "Sunday": "Sun",
};

function formatDay(dateStr: string): string {
  const d = new Date(dateStr + "T00:00:00");
  return DAY_NAMES[d.toLocaleDateString("en-US", { weekday: "long" })] ?? dateStr.slice(5);
}

function formatHours(value: number | string): string {
  return `${(Number(value) / 3600).toFixed(1)}h`;
}

interface WeeklyHoursChartProps {
  data: DailyHours[];
  isLoading?: boolean;
}

export function WeeklyHoursChart({ data, isLoading }: WeeklyHoursChartProps) {
  const chartData = data.map((d) => ({
    day: formatDay(d.date),
    hours: Math.round(d.total_seconds / 36) / 100,
  }));

  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-sm font-medium">Weekly Hours Trend</CardTitle>
      </CardHeader>
      <CardContent>
        {isLoading ? (
          <div className="h-48 bg-muted animate-pulse rounded" />
        ) : chartData.length === 0 ? (
          <p className="text-sm text-muted-foreground py-8 text-center">No data for this period.</p>
        ) : (
          <div className="h-48 w-full">
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={chartData} margin={{ top: 5, right: 5, left: -20, bottom: 5 }}>
              <defs>
                <linearGradient id="hoursGradient" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="hsl(142.1 76.2% 36.3%)" stopOpacity={0.3} />
                  <stop offset="95%" stopColor="hsl(142.1 76.2% 36.3%)" stopOpacity={0} />
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
              <XAxis dataKey="day" tick={{ fontSize: 12 }} />
              <YAxis tick={{ fontSize: 12 }} tickFormatter={formatHours} />
              <Tooltip
                formatter={(value) => [`${Number(value).toFixed(1)}h`, "Hours"]}
                contentStyle={{ fontSize: 13 }}
              />
              <Area
                type="monotone"
                dataKey="hours"
                stroke="hsl(142.1 76.2% 36.3%)"
                fill="url(#hoursGradient)"
                strokeWidth={2}
              />
            </AreaChart>
          </ResponsiveContainer>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
```

- [ ] **Step 2: Add accent color prop to StatCard**

In `StatCard.tsx`, the `className` prop is already accepted — the AdminDashboard passes `border-l-4 border-l-green-500` and UserDashboard passes `border-l-4 border-l-blue-500` through it. No change needed to StatCard internals.

---

### Task 11: Run all tests — backend + frontend

- [ ] **Step 1: Run all backend tests**

Run: `cd C:\laragon\www\tic_crm && php vendor/bin/phpunit -c application/tests/phpunit.xml -v`
Expected: All 9+ tests pass (3 existing auth + 3 existing reports + 3 new dashboard)

- [ ] **Step 2: Run all frontend tests**

Run: `cd C:\Users\CT\Desktop\Tracker && npx vitest run -v`
Expected: All 70+ tests pass

- [ ] **Step 3: TypeScript check**

Run: `cd C:\Users\CT\Desktop\Tracker && npx tsc --noEmit`
Expected: No type errors
