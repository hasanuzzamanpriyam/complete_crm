# Global Page Graphs Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add department-level charts to ActivityPage, TimerPage, and AttendancePage for admins/managers.

**Architecture:** Three new CI3 API endpoints returning department-aggregated data (reuses existing `_resolve_user_ids()` RBAC pattern in Reports.php). New React hook fetches all three in parallel. New `<PageHeaderGraph>` component renders page-appropriate chart (PieChart for activity/attendance, BarChart for timer).

**Tech Stack:** PHP (CI3), TypeScript (React), recharts

---

### Task 1: CI3 — Add team page graph API endpoints

**Files:**
- Modify: `application/controllers/api/Reports.php`
- Modify: `application/config/routes.php`

- [ ] **Step 1: Add team_app_usage() method to Reports.php**

Add after existing methods. Uses `_get_managed_user_ids()` which already exists in `Reports.php` (line ~46). If present, just use it directly:

```php
public function team_app_usage() {
    $this->load->library('api_auth');
    $logged_user = $this->api_auth->get_logged_user_id();
    if (!$logged_user) {
        return $this->_respond(false, 'Unauthorized', null, 401);
    }

    $user_ids = $this->_get_managed_user_ids($logged_user);
    if (empty($user_ids)) {
        return $this->_respond(true, 'No team members found', ['rows' => []]);
    }

    $today = date('Y-m-d');
    $sql = "SELECT a.app_name, SUM(a.total_seconds) as total_seconds
            FROM tbl_desktop_app_usage a
            WHERE a.user_id IN (" . implode(',', $user_ids) . ")
            AND a.recorded_at = ?
            GROUP BY a.app_name
            ORDER BY total_seconds DESC
            LIMIT 10";
    $rows = $this->db->query($sql, [$today])->result();

    $this->_respond(true, 'OK', ['rows' => $rows]);
}
```

- [ ] **Step 2: Add team_hours_today() method**

```php
public function team_hours_today() {
    $this->load->library('api_auth');
    $logged_user = $this->api_auth->get_logged_user_id();
    if (!$logged_user) {
        return $this->_respond(false, 'Unauthorized', null, 401);
    }

    $user_ids = $this->_get_managed_user_ids($logged_user);
    if (empty($user_ids)) {
        return $this->_respond(true, 'No team members found', ['rows' => []]);
    }

    $today = date('Y-m-d');
    $sql = "SELECT t.user_id, u.username, ad.avatar,
                   COALESCE(SUM(t.total_seconds), 0) as total_seconds
            FROM tbl_desktop_time_entries t
            JOIN tbl_users u ON u.user_id = t.user_id
            LEFT JOIN tbl_account_details ad ON ad.user_id = t.user_id
            WHERE t.user_id IN (" . implode(',', $user_ids) . ")
            AND DATE(t.started_at) = ?
            AND t.is_running = 0
            GROUP BY t.user_id
            ORDER BY total_seconds DESC";
    $rows = $this->db->query($sql, [$today])->result();

    $this->_respond(true, 'OK', ['rows' => $rows]);
}
```

- [ ] **Step 3: Add team_attendance() method**

```php
public function team_attendance() {
    $this->load->library('api_auth');
    $logged_user = $this->api_auth->get_logged_user_id();
    if (!$logged_user) {
        return $this->_respond(false, 'Unauthorized', null, 401);
    }

    $user_ids = $this->_get_managed_user_ids($logged_user);
    if (empty($user_ids)) {
        return $this->_respond(true, 'No team members found', ['rows' => []]);
    }

    $today = date('Y-m-d');
    $sql = "SELECT u.user_id, u.username, ad.avatar,
                   c.clockin_id, c.clockin_time,
                   CASE WHEN c.clockin_id IS NOT NULL THEN 1 ELSE 0 END as checked_in
            FROM tbl_users u
            LEFT JOIN tbl_account_details ad ON ad.user_id = u.user_id
            LEFT JOIN tbl_clock c ON c.user_id = u.user_id AND DATE(c.clockin_time) = ? AND c.clockout_time IS NULL
            WHERE u.user_id IN (" . implode(',', $user_ids) . ")
            ORDER BY u.username ASC";
    $rows = $this->db->query($sql, [$today])->result();

    $this->_respond(true, 'OK', ['rows' => $rows]);
}
```

- [ ] **Step 4: Add routes**

In `application/config/routes.php`, add:
```php
$route['api/reports/team/app-usage'] = 'api/reports/team_app_usage';
$route['api/reports/team/hours-today'] = 'api/reports/team_hours_today';
$route['api/reports/team/attendance'] = 'api/reports/team_attendance';
```

- [ ] **Step 5: Verify**

Run: `php -l application/controllers/api/Reports.php`
Expected: No syntax errors

Run: `php -l application/config/routes.php`
Expected: No syntax errors

---

### Task 2: React — Add API methods

**Files:**
- Modify: `src/services/api/reports.ts`
- Create: `src/hooks/useTeamGraphs.ts`

- [ ] **Step 1: Add team API methods and types to reports.ts**

```typescript
export interface TeamAppUsageRow {
  app_name: string;
  total_seconds: number;
}

export interface TeamHoursRow {
  user_id: number;
  username: string;
  avatar: string | null;
  total_seconds: number;
}

export interface TeamAttendanceRow {
  user_id: number;
  username: string;
  avatar: string | null;
  clockin_id: number | null;
  clockin_time: string | null;
  checked_in: number;
}

export const reportApi = {
  // ... existing methods (keep them) ...

  getTeamAppUsage: async (): Promise<TeamAppUsageRow[]> => {
    const res = await api.get("/reports/team/app-usage");
    return res.data.rows;
  },

  getTeamHoursToday: async (): Promise<TeamHoursRow[]> => {
    const res = await api.get("/reports/team/hours-today");
    return res.data.rows;
  },

  getTeamAttendance: async (): Promise<TeamAttendanceRow[]> => {
    const res = await api.get("/reports/team/attendance");
    return res.data.rows;
  },
};
```

- [ ] **Step 2: Create useTeamGraphs hook**

`src/hooks/useTeamGraphs.ts`:

```typescript
import { useState, useEffect } from "react";
import {
  reportApi,
  TeamAppUsageRow,
  TeamHoursRow,
  TeamAttendanceRow,
} from "@/services/api/reports";
import { useAuthStore } from "@/features/auth/store";

export function useTeamGraphs() {
  const user = useAuthStore((s) => s.user);
  const role = user?.role ?? "";
  const isManagerOrAdmin = role === "admin" || role === "manager";

  const [appUsage, setAppUsage] = useState<TeamAppUsageRow[]>([]);
  const [hoursToday, setHoursToday] = useState<TeamHoursRow[]>([]);
  const [attendance, setAttendance] = useState<TeamAttendanceRow[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!isManagerOrAdmin) return;

    let cancelled = false;
    setIsLoading(true);
    setError(null);

    Promise.all([
      reportApi.getTeamAppUsage(),
      reportApi.getTeamHoursToday(),
      reportApi.getTeamAttendance(),
    ])
      .then(([app, hours, att]) => {
        if (!cancelled) {
          setAppUsage(app);
          setHoursToday(hours);
          setAttendance(att);
        }
      })
      .catch((err) => {
        if (!cancelled) setError(err.message);
      })
      .finally(() => {
        if (!cancelled) setIsLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [isManagerOrAdmin]);

  return { appUsage, hoursToday, attendance, isLoading, error };
}
```

- [ ] **Step 3: Verify**

Run: `cd C:\Users\CT\Desktop\Tracker && npx tsc --noEmit`
Expected: No errors

---

### Task 3: React — Create PageHeaderGraph component

**Files:**
- Create: `src/components/PageHeaderGraph.tsx`

- [ ] **Step 1: Create the component**

```tsx
import { useTeamGraphs } from "@/hooks/useTeamGraphs";
import { useAuthStore } from "@/features/auth/store";
import {
  PieChart,
  Pie,
  Cell,
  Tooltip,
  Legend,
  ResponsiveContainer,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
} from "recharts";
import { Skeleton } from "@/components/ui/skeleton";

const COLORS = [
  "#6366f1", "#f59e0b", "#10b981", "#ef4444", "#8b5cf6",
  "#ec4899", "#14b8a6", "#f97316", "#06b6d4", "#84cc16",
];

interface Props {
  page: "activity" | "timer" | "attendance";
}

export function PageHeaderGraph({ page }: Props) {
  const { appUsage, hoursToday, attendance, isLoading } = useTeamGraphs();
  const role = useAuthStore((s) => s.user?.role ?? "");

  if (role === "employee" || role === "") return null;

  if (isLoading) {
    return (
      <div className="mb-6">
        <Skeleton className="h-48 w-full rounded-lg" />
      </div>
    );
  }

  if (page === "activity") {
    if (appUsage.length === 0) return null;
    const data = appUsage.slice(0, 5).map((r) => ({
      name: r.app_name,
      value: Math.round(r.total_seconds / 60),
    }));
    return (
      <div className="mb-6 rounded-lg border p-4">
        <h3 className="mb-2 text-sm font-medium text-muted-foreground">
          Team App Usage Today
        </h3>
        <ResponsiveContainer width="100%" height={200}>
          <PieChart>
            <Pie
              data={data}
              cx="50%"
              cy="50%"
              outerRadius={70}
              innerRadius={45}
              dataKey="value"
              label={({ name }) => name}
            >
              {data.map((_, i) => (
                <Cell key={i} fill={COLORS[i % COLORS.length]} />
              ))}
            </Pie>
            <Tooltip formatter={(v: number) => `${v} min`} />
          </PieChart>
        </ResponsiveContainer>
      </div>
    );
  }

  if (page === "timer") {
    if (hoursToday.length === 0) return null;
    const data = hoursToday.map((r) => ({
      name: r.username,
      hours: Math.round((r.total_seconds / 3600) * 100) / 100,
    }));
    return (
      <div className="mb-6 rounded-lg border p-4">
        <h3 className="mb-2 text-sm font-medium text-muted-foreground">
          Team Hours Today
        </h3>
        <ResponsiveContainer width="100%" height={200}>
          <BarChart data={data}>
            <CartesianGrid strokeDasharray="3 3" vertical={false} />
            <XAxis dataKey="name" tick={{ fontSize: 12 }} />
            <YAxis tick={{ fontSize: 12 }} />
            <Tooltip formatter={(v: number) => `${v}h`} />
            <Bar dataKey="hours" fill="#6366f1" radius={[4, 4, 0, 0]} />
          </BarChart>
        </ResponsiveContainer>
      </div>
    );
  }

  if (page === "attendance") {
    if (attendance.length === 0) return null;
    const checkedIn = attendance.filter((r) => r.checked_in).length;
    const absent = attendance.length - checkedIn;
    const pieData = [
      { name: "Checked In", value: checkedIn },
      { name: "Absent", value: absent },
    ];
    return (
      <div className="mb-6 rounded-lg border p-4">
        <h3 className="mb-2 text-sm font-medium text-muted-foreground">
          Team Attendance Today
        </h3>
        {attendance.length <= 6 ? (
          <div className="flex flex-wrap gap-3">
            {attendance.map((r) => (
              <div
                key={r.user_id}
                className="flex items-center gap-2 rounded-md border px-3 py-1.5 text-sm"
              >
                <span
                  className={`h-2 w-2 rounded-full ${r.checked_in ? "bg-green-500" : "bg-red-400"}`}
                />
                {r.username}
              </div>
            ))}
          </div>
        ) : (
          <ResponsiveContainer width="100%" height={160}>
            <PieChart>
              <Pie
                data={pieData}
                cx="50%"
                cy="50%"
                outerRadius={60}
                innerRadius={35}
                dataKey="value"
              >
                {pieData.map((_, i) => (
                  <Cell key={i} fill={i === 0 ? "#10b981" : "#ef4444"} />
                ))}
              </Pie>
              <Tooltip />
              <Legend />
            </PieChart>
          </ResponsiveContainer>
        )}
      </div>
    );
  }

  return null;
}
```

- [ ] **Step 2: Verify**

Run: `cd C:\Users\CT\Desktop\Tracker && npx tsc --noEmit`
Expected: No errors

---

### Task 4: React — Integrate into pages

**Files:**
- Modify: `src/features/activity/components/ActivityPage.tsx`
- Modify: `src/features/timer/components/TimerPage.tsx`
- Modify: `src/features/attendance/components/AttendancePage.tsx`

- [ ] **Step 1: Add to ActivityPage.tsx**

Import at top:
```tsx
import { PageHeaderGraph } from "@/components/PageHeaderGraph";
```

Find the main page wrapper. After the page title heading (before the tabs or main content area), add:
```tsx
<PageHeaderGraph page="activity" />
```

- [ ] **Step 2: Add to TimerPage.tsx**

Same import, add below page title:
```tsx
<PageHeaderGraph page="timer" />
```

- [ ] **Step 3: Add to AttendancePage.tsx**

Same import, add below page title:
```tsx
<PageHeaderGraph page="attendance" />
```

- [ ] **Step 4: Verify**

Run: `cd C:\Users\CT\Desktop\Tracker && npx tsc --noEmit`
Expected: No errors

---

### Verification

- [ ] Run `cd C:\Users\CT\Desktop\Tracker && npx tsc --noEmit` — passes
- [ ] Run `php -l application/controllers/api/Reports.php` — passes
- [ ] Run `php -l application/config/routes.php` — passes
- [ ] Manual: Login as admin → navigate to ActivityPage → see "Team App Usage Today" pie chart
- [ ] Manual: Login as manager → navigate to TimerPage → see "Team Hours Today" bar chart
- [ ] Manual: Login as manager → navigate to AttendancePage → see attendance status cards/pie chart
- [ ] Manual: Login as regular employee → no team chart visible on any page
