# Drill-Down & Detailed Overview Design

**Date:** 2026-06-26
**Status:** Approved Design

## Overview

Add a personal/organizational drill-down view to the Dashboard, inspired by Hubstaff's detailed profile view. Admin clicks a user in "Team at a Glance" to see their full detailed profile, or views the "Organization Summary" using the same layout.

## 1. Backend: `GET /api/dashboard/detailed-overview`

### Route

```php
$route['api/dashboard/detailed-overview'] = 'api/dashboard/detailed_overview';
```

### Controller Method

`Dashboard::detailed_overview()` in `application/controllers/api/Dashboard.php`

### Parameters

| Param     | Type   | Default | Description |
|-----------|--------|---------|-------------|
| `user_id` | int    | null    | Specific user; null = org overview (admin only) |
| `period`  | int    | 7       | Days to look back |

### Authentication & Authorization

- Authenticated user required
- If `user_id` is null: admin/manager only (org overview)
- If `user_id` is set: must be admin/manager OR the user themselves
- Uses existing `_resolve_user_ids()` for company scoping

### Response Shape

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "profile": {
      "name": "John Doe",
      "role": "admin",
      "avatar": "http://..."
    },
    "kpi": {
      "time_logged": 60.5,
      "time_billable": 45.2,
      "trend_percent": -34.16
    },
    "daily_logged": [
      { "date": "2026-06-20", "hours": 8.0 }
    ],
    "desktop_activity": {
      "total_time": 61.5,
      "productive_time": 54.0,
      "unproductive_time": 7.5,
      "activity_score": 87.8
    },
    "daily_activity_breakdown": [
      { "date": "2026-06-20", "productive": 7.5, "unproductive": 0.5 }
    ],
    "top_apps": [
      { "name": "Chrome", "percentage": 52, "color": "#3b82f6" },
      { "name": "VS Code", "percentage": 12, "color": "#10b981" }
    ],
    "recent_screenshots": [
      {
        "id": 1, "file_url": "...", "captured_at": "...",
        "keystroke_count": 150, "mouse_click_count": 30,
        "activity_percentage": 85.0
      }
    ],
    "recent_windows": [
      {
        "app_name": "Chrome", "window_title": "Gmail — Inbox",
        "total_seconds": 3600, "recorded_at": "2026-06-26"
      }
    ]
  }
}
```

### Query Logic

**Profile:** `tbl_users` JOIN `tbl_account_details` for the target user_id

**KPIs:**
- `time_logged`: SUM total_seconds FROM tbl_desktop_time_entries WHERE type='work' AND DATE(started_at) >= period_start
- `time_billable`: Same but JOIN tbl_task ON tbl_desktop_time_entries.task_id = tbl_task.task_id WHERE tbl_task.billable = 'Yes'
- `trend_percent`: Compare current period total to previous period total; ((current - previous) / previous) * 100

**Daily logged:** GROUP BY DATE(started_at), SUM total_seconds, convert to hours.

**Desktop activity:**
- Fetch all app_usage for the user+period from `tbl_desktop_app_usage`
- Classify each app via `_categorize_app()` (productive/neutral/distracting)
- Neutral counts as 0.5 productive for the score
- `activity_score = (productive_seconds + neutral_seconds * 0.5) / total_seconds * 100`

**Daily activity breakdown:** Same as desktop activity but grouped by `recorded_at` date.

**Top apps:** Aggregate app_name from app_usage, compute percentages. Assign deterministic colors from a palette.

**Recent screenshots:** Query `tbl_screenshots` for user_id, within period, ORDER BY captured_at DESC, LIMIT 12. Return file_url, captured_at, keystroke_count, mouse_click_count, activity_percentage.

**Recent windows:** Query `tbl_desktop_app_usage` for user_id, within period, GROUP BY app_name, window_title, ORDER BY total_seconds DESC, LIMIT 20. Return app_name, window_title, total_seconds, recorded_at.

### PHP App Classification

Replicate the frontend's `categorizeApp()`:

```php
private function _categorize_app($name) {
    $productive = ['vscode', 'code', 'visual studio', 'cursor', 'windsurf',
        'terminal', 'cmd', 'powershell', 'windows terminal', 'git bash',
        'phpstorm', 'webstorm', 'intellij', 'pycharm', 'goland', 'rubymine',
        'sublime text', 'atom', 'notepad++', 'vim', 'neovim', 'nano',
        'excel', 'word', 'outlook', 'powerpoint', 'onenote',
        'chrome', 'firefox', 'edge', 'brave', 'opera', 'arc',
        'slack', 'teams', 'discord', 'zoom', 'meet', 'webex',
        'postman', 'tableplus', 'heidisql', 'dbeaver', 'datagrip',
        'git', 'github desktop', 'sourcetree', 'fork',
        'figma', 'sketch', 'adobe xd', 'photoshop', 'illustrator',
        'docker', 'k9s', 'lens', 'kubectl',
        'wsl', 'ubuntu', 'debian'];
    $neutral = ['file explorer', 'explorer', 'finder', 'settings',
        'system settings', 'calculator', 'calendar', 'clock',
        'notes', 'reminders', 'spotlight', 'search'];
    $lower = strtolower(trim($name));
    foreach ($productive as $p) { if (strpos($lower, $p) !== false) return 'productive'; }
    foreach ($neutral as $n) { if (strpos($lower, $n) !== false) return 'neutral'; }
    return 'distracting';
}

private function _app_color($name) {
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6',
               '#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
    return $colors[crc32($name) % count($colors)];
}
```

## 2. Frontend Types (`reports.ts`)

```typescript
export interface DetailedProfile {
  name: string;
  role: string;
  avatar: string;
}

export interface DetailedKpi {
  time_logged: number;
  time_billable: number;
  trend_percent: number;
}

export interface DesktopActivity {
  total_time: number;
  productive_time: number;
  unproductive_time: number;
  activity_score: number;
}

export interface DailyActivityBreakdown {
  date: string;
  productive: number;
  unproductive: number;
}

export interface TopAppEntry {
  name: string;
  percentage: number;
  color: string;
}

export interface RecentScreenshot {
  id: number;
  file_url: string;
  captured_at: string;
  keystroke_count: number;
  mouse_click_count: number;
  activity_percentage: number;
}

export interface RecentWindow {
  app_name: string;
  window_title: string;
  total_seconds: number;
  recorded_at: string;
}

export interface DetailedOverviewData {
  profile: DetailedProfile;
  kpi: DetailedKpi;
  daily_logged: DailyHours[];
  desktop_activity: DesktopActivity;
  daily_activity_breakdown: DailyActivityBreakdown[];
  top_apps: TopAppEntry[];
  recent_screenshots: RecentScreenshot[];
  recent_windows: RecentWindow[];
}
```

### API Method

```typescript
async getDetailedOverview(userId?: number, period?: number): Promise<DetailedOverviewData>
```

- URL: `GET /dashboard/detailed-overview`
- Params: `{ user_id?: userId, period?: period }`

## 3. Navigation State (`DashboardPage.tsx`)

```typescript
type EntityView = { type: "overview" } | { type: "user"; userId: number };
```

- Default: `{ type: "overview" }` — shows existing Team Overview + LiveUsersSection
- Admin clicks user in TeamAtGlance: navigate to `{ type: "user"; userId: X }`
- "← Back to Organization Overview" button in header when viewing a user
- TeamAtGlance gets optional `onUserClick?: (userId: number) => void` prop

## 4. DetailedOverviewComponent Layout

```
┌─────────────────────────────────────────────────┐
│ ← Back to Organization             [Profile]     │
├─────────────────────────────────────────────────┤
│  [Avatar] John Doe                admin          │
├─────────────────────────────────────────────────┤
│ ┌──────────┐ ┌──────────┐ ┌─────────────────┐  │
│ │Time loggd│ │ Billable │ │   Mini BarChart  │  │
│ │ 60.5h    │ │ 45.2h    │ │  Hours by day    │  │
│ │▼ -34.16% │ │          │ │  ▄▄▄▄▄▄▄▄▄▄     │  │
│ └──────────┘ └──────────┘ └─────────────────┘  │
├─────────────────────────────────────────────────┤
│ [Desktop Activity] [Screenshots] [Windows]       │
├─────────────────────────────────────────────────┤
│  (Tab content below)                             │
└─────────────────────────────────────────────────┘
```

### Tab 1 — Desktop Activity

- Total desktop time (large number) + **Activity Score** as a custom styled progress bar:
  - Green (>80%), Yellow (50-80%), Red (<50%)
- Stacked Bar Chart (Recharts): `<Bar dataKey="productive" fill="#22c55e" stackId="a"/>` + `<Bar dataKey="unproductive" fill="#eab308" stackId="a"/>`
- X-axis: dates, Y-axis: hours
- Top apps list with color dots + percentage bars

### Tab 2 — Screenshots

- 2-column CSS grid of screenshot cards
- Each card: thumbnail image (file_url), captured_at timestamp, keystroke count, mouse click count, activity_percentage badge
- Empty state message if none found

### Tab 3 — Active Windows & URLs

- Styled list/table with:
  - App name (with color dot from palette)
  - Window title (truncated)
  - Duration (formatted as h/m/s)
  - Percentage bar of total time
- Sorted by total_seconds DESC

### Reused Components

- `StatCard` — for KPI cards (time_logged, time_billable)
- `Avatar` — for profile picture
- `Card`, `CardHeader`, `CardTitle`, `CardContent` — layout shells
- `cn()` utility — for conditional class merging

### New Components

- `DetailedOverviewComponent` — orchestrator, passes data to tabs
- `ActivityScoreBar` — custom progress bar with color zones
- `TopAppsList` — app list with color dots + percentage bars
- `ScreenshotsGrid` — the screenshot thumbnail grid
- `ActiveWindowsList` — the windows/URLs table

## 5. Testing

### Backend

- `DashboardDetailedOverviewTest.php` — integration test using curl against live DB
  - Admin gets full response for org overview
  - Admin gets full response for specific user
  - Manager gets full response for scoped user
  - Employee gets own data
  - Verify KPIs, desktop_activity, screenshots, windows exist in response

### Frontend

- `reports.test.ts` — add `getDetailedOverview` test case
- `useDetailedOverview.test.ts` — loading, success, error, refetch
- `DetailedOverviewComponent.test.tsx` — renders profile, KPIs, tabs, tab switching
- `ScreenshotsGrid.test.tsx` — renders thumbnails, empty state
- `ActiveWindowsList.test.tsx` — renders rows, empty state
- `ActivityScoreBar.test.tsx` — renders correct color zone
- `TopAppsList.test.tsx` — renders app entries with percentages

## 6. Route

```php
$route['api/dashboard/detailed-overview'] = 'api/dashboard/detailed_overview';
```

## Files Changed

### Backend (CI3)
- `application/controllers/api/Dashboard.php` — new `detailed_overview()` method + `_categorize_app()` + `_app_color()`
- `application/config/routes.php` — add route
- `application/tests/controllers/DashboardDetailedOverviewTest.php` — new test file

### Frontend (Tracker)
- `src/services/api/reports.ts` — new types + `getDetailedOverview()` method
- `src/services/api/reports.test.ts` — new test
- `src/features/dashboard/hooks/useDetailedOverview.ts` — new hook
- `src/features/dashboard/hooks/useDetailedOverview.test.ts` — new test
- `src/features/dashboard/hooks/index.ts` — barrel export
- `src/features/dashboard/components/DetailedOverviewComponent.tsx` — main component
- `src/features/dashboard/components/DetailedOverviewComponent.test.tsx` — new test
- `src/features/dashboard/components/ActivityScoreBar.tsx` — new component
- `src/features/dashboard/components/ActivityScoreBar.test.tsx` — new test
- `src/features/dashboard/components/TopAppsList.tsx` — new component
- `src/features/dashboard/components/TopAppsList.test.tsx` — new test
- `src/features/dashboard/components/ScreenshotsGrid.tsx` — new component
- `src/features/dashboard/components/ScreenshotsGrid.test.tsx` — new test
- `src/features/dashboard/components/ActiveWindowsList.tsx` — new component
- `src/features/dashboard/components/ActiveWindowsList.test.tsx` — new test
- `src/features/dashboard/components/DashboardPage.tsx` — add `EntityView` state, navigation
- `src/features/dashboard/components/DashboardPage.test.tsx` — new tests for entity nav
- `src/features/dashboard/components/TeamAtGlance.tsx` — add `onUserClick` prop
- `src/features/dashboard/components/TeamAtGlance.test.tsx` — update for click handler
