# Dashboard Bento-Grid UX Upgrade

Date: 2026-06-26
Status: Draft

## Overview

Massive visual upgrade to the TimeSync Dashboard — replacing the simple stat card grid with a rich,
role-based bento-box layout featuring AreaCharts with gradients, Donut/Bar charts for project
distribution, and a "Team at a Glance" widget for admins.

## Approach

**Chosen: Approach A — New consolidated endpoint + Role-based components**

Previously, the dashboard mixed local SQLite queries (useTodayHours, useWeeklyHours, etc.) with
backend API calls (useAdminAnalytics, useLiveUsers). This design consolidates ALL dashboard data
through a single `GET /api/dashboard/summary` endpoint that returns different payloads based on
user role.

## Backend — `GET /api/dashboard/summary`

### Route

`$route['api/dashboard/summary'] = 'api/dashboard/summary';`

### Controller

New `summary()` method in `application/controllers/api/Dashboard.php`.

### Query Parameters

- `period` (optional, default `'weekly'`): `'weekly'` = last 7 days, `'monthly'` = last 30 days
- `user_id` (optional, admin/manager only): view dashboard for a different user

### Response Payload

**Admin/Manager response includes ALL fields. Standard user gets only personal_* + project_distribution.**

```json
{
  "success": true,
  "data": {
    "global_stats": {
      "total_company_hours_today": 342.5,
      "active_users_count": 18,
      "tasks_completed_today": 7
    },
    "company_weekly_trend": [
      { "date": "2026-06-20", "total_seconds": 14400 },
      { "date": "2026-06-21", "total_seconds": 10800 }
    ],
    "team_brief": [
      {
        "user_id": 1,
        "name": "Alice",
        "avatar": "http://...",
        "hours_today": 7.5,
        "status": "active"
      }
    ],
    "personal_stats": {
      "hours_today": 6.25,
      "weekly_total_seconds": 72000,
      "tasks_in_progress_count": 3
    },
    "personal_weekly_trend": [
      { "date": "2026-06-20", "total_seconds": 3600 }
    ],
    "project_distribution": [
      {
        "project_id": 1,
        "project_name": "Website Redesign",
        "total_seconds": 18000,
        "percentage": 45
      }
    ]
  }
}
```

### Key SQL Queries

- `total_company_hours_today`: SUM(total_seconds) WHERE DATE(started_at) = CURDATE() AND type = 'work'
- `active_users_count`: COUNT(DISTINCT user_id) WHERE is_running = 1 AND stopped_at IS NULL
- `tasks_completed_today`: COUNT(*) FROM tbl_task WHERE task_status = 'completed' AND DATE(updated_at) = CURDATE()
- `company_weekly_trend`: GROUP BY DATE(started_at) last 7 days, all allowed users, type = 'work'
- `team_brief`: For each allowed user — SUM of today's work seconds, current online status (reuse logic from live_users())
- `personal_weekly_trend`: Same as company but filtered to the authenticated user
- `project_distribution`: JOIN time_entries → task → project, GROUP BY project_id, last 7 days, compute percentage from total

### RBAC

Reuses `_resolve_allowed_user_ids()` already in Dashboard controller.
- Admin: sees all users
- Manager: sees department users
- Employee: sees only self

## Frontend — Component Architecture

### New Hook

`useDashboardSummary(period: 'weekly' | 'monthly' = 'weekly')` in
`src/features/dashboard/hooks/useDashboardSummary.ts`

Returns:
```
{
  data: DashboardSummaryData | null,
  isLoading: boolean,
  error: string | null,
  refetch: () => void
}
```

### New Types (in reports.ts or a new dashboard types file)

```typescript
interface TeamBriefEntry {
  user_id: number;
  name: string;
  avatar: string;
  hours_today: number;
  status: "active" | "idle" | "offline";
}

interface ProjectDistEntry {
  project_id: number;
  project_name: string;
  total_seconds: number;
  percentage: number;
}

interface DashboardSummaryData {
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
```

### Component Tree

```
DashboardPage — fetches useDashboardSummary() with admin user selector dropdown (passes user_id to endpoint), routes by role
├── AdminDashboard
│   ├── SummaryRow (3-column grid)
│   │   ├── StatCard("Company Hours Today", value, Clock icon, accent=green)
│   │   ├── StatCard("Active Users", value, UserCheck icon, accent=green)
│   │   └── StatCard("Tasks Completed", value, CheckCircle2 icon, accent=green)
│   ├── WeeklyHoursChart (AreaChart with green gradient, data=company_weekly_trend)
│   └── TeamAtGlance
│       └── UserRow × N (avatar, name, hours progress bar 0-8h, status dot)
└── UserDashboard
    ├── SummaryRow (3-column grid)
    │   ├── StatCard("Today's Hours", value, Clock icon, accent=blue)
    │   ├── StatCard("Weekly Total", value, CalendarDays icon, accent=blue)
    │   └── StatCard("Active Tasks", value, ListChecks icon, accent=blue)
    ├── WeeklyHoursChart (AreaChart with blue gradient, data=personal_weekly_trend)
    └── ProjectDistributionChart
        ├── Donut chart (< 5 projects, Recharts PieChart)
        └── HorizontalBar chart (5+ projects)
```

### Layout (CSS Grid with bento feel)

**AdminDashboard:**
```
1fr 1fr 1fr
2fr      2fr      (AreaChart spans full width below cards, but sits in a card)
2fr      2fr      (TeamAtGlance spans full width)
```

**UserDashboard:**
```
1fr 1fr 1fr
1fr      1fr      (AreaChart left, Donut/BarChart right)
```

### Visual Style

- StatCard: rounded corners, subtle shadow, left accent border (4px colored), large bold value
- WeeklyHoursChart: Recharts AreaChart with `areaGradientFill` — green for admin, blue for user
- TeamAtGlance: compact list rows with 8h-target progress bar (bg-muted fill, bg-primary progress), green/yellow/red status dot, user initials avatar fallback
- ProjectDistributionChart: Recharts PieChart with innerRadius (donut) when < 5 projects; Recharts BarChart layout="horizontal" when 5+

## Testing

### Backend (PHPUnit)

- `DashboardSummaryTest.php` in `application/tests/controllers/`
  - Test admin gets global_stats, company_weekly_trend, team_brief
  - Test employee does NOT get admin fields
  - Test employee gets personal_stats, personal_weekly_trend, project_distribution
  - Test RBAC isolation (user A cannot see user B's teams data)

### Frontend (Vitest)

- `useDashboardSummary.test.ts` — mock API, verify loading/success/error states
- `AdminDashboard.test.tsx` — render with mock data, assert stat cards render
- `UserDashboard.test.tsx` — render with mock data, assert charts render
- `TeamAtGlance.test.tsx` — render user rows with progress bars

## Files to Create/Modify

### Backend
- MODIFY: `application/controllers/api/Dashboard.php` — add summary() method
- MODIFY: `application/config/routes.php` — add summary route
- CREATE: `application/tests/controllers/DashboardSummaryTest.php`

### Frontend
- MODIFY: `src/services/api/reports.ts` — add getDashboardSummary() method + types
- CREATE: `src/features/dashboard/hooks/useDashboardSummary.ts`
- CREATE: `src/features/dashboard/hooks/useDashboardSummary.test.ts`
- CREATE: `src/features/dashboard/components/AdminDashboard.tsx`
- CREATE: `src/features/dashboard/components/UserDashboard.tsx`
- CREATE: `src/features/dashboard/components/TeamAtGlance.tsx`
- CREATE: `src/features/dashboard/components/ProjectDistributionChart.tsx`
- MODIFY: `src/features/dashboard/hooks/index.ts` — export new hook
- MODIFY: `src/features/dashboard/components/DashboardPage.tsx` — refactor to use summary hook + role routing
- The existing SQLite hooks (useTodayHours, useWeeklyHours, useActiveTask, useCompletedTasks, useProductivity) are kept on disk but no longer imported by dashboard components — replaced by useDashboardSummary()
- KEEP: useLiveUsers (still used for live monitoring polling)
- KEEP: useAttendanceStatus (separate concern)
- KEEP: WeeklyHoursChart (refactored to accept data prop, still works)
- KEEP: StatCard (enhance with accent color prop)

## Design Decisions

1. **Single endpoint over multiple**: Eliminates N+1 round-trips and local/remote data inconsistency.
2. **Role-split components over inline conditions**: AdminDashboard and UserDashboard can grow independently without tangled if/else chains.
3. **Donut → HorizontalBar at 5 projects**: Donuts get hard to read with many segments; bars scale better.
4. **Last 7 rolling days over current week**: Always shows complete data regardless of what day it is.
5. **AreaChart over BarChart**: More visually premium for time-series trend data. Gradient fill gives the "bento" feel.
6. **Remove local SQLite dashboard hooks**: The Tracker desktop app goes through the backend for dashboard data now. They're replaced by useDashboardSummary() which calls the API. The local DB hooks for tasks/time-entries remain for the core task tracking features.
