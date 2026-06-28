# TimeSync Admin Panel Integration Design

## Overview

Upgrade the existing CI3 admin TimeSync module (`/admin/timesync/`) from basic KPIs/tables to a full-featured dashboard matching the Tracker React UI patterns, using PHP views + Chart.js + jQuery AJAX — matching the existing ERP UI conventions.

## Architecture

- **No build step** — all views are PHP templates in `views/admin/timesync/`
- **Chart.js** loaded via CDN (or local asset) for all charts
- **AJAX endpoints** in `controllers/admin/Timesync.php` for dynamic chart data
- **jQuery** for interactivity (matching existing ERP patterns)
- **Bootstrap 3 panels** for layout consistency
- **DataTables** plugin (already available in ERP) for sortable tables

## File Changes

### Backend (Timesync.php controller)

| Method | Type | Purpose |
|--------|------|---------|
| `index()` | Rewrite | Dashboard — KPI queries + chart data + user grid |
| `user($id)` | Rewrite | 3-tab drill-down (Timeline/Apps/Tasks) |
| `screenshots()` | Enhance | Enhanced gallery with lightbox |
| `usage()` | Enhance | App usage with Chart.js pie/bar |
| `calendar()` | **New** | Month grid data |
| `day_details($date)` | **New** | Day breakdown for calendar clicks |
| `entries_datatable()` | **New** | AJAX DataTables endpoint for time entries |
| `settings()` | Keep | Existing settings page |
| `view_image()` | Keep | Existing image proxy |

### Views

| File | Action |
|------|--------|
| `dashboard.php` | Rewrite — full dashboard layout |
| `user_report.php` | Rewrite — 3-tab layout |
| `screenshots.php` | Enhance — add lightbox |
| `usage_report.php` | Enhance — add Chart.js |
| `calendar.php` | **New** — month grid |
| `entries.php` | **New** — time entries datatable |
| `settings.php` | Keep as-is |

## Dashboard Layout

1. **Filter row** — preset buttons (Today / This Week / This Month / Custom) + from/to date inputs
2. **KPI row** — 6 Bootstrap info panels in a row:
   - Today's Hours, This Week, This Month, Active Users, Total Entries, Total Screenshots
3. **Chart row** — two columns:
   - Left: Chart.js bar chart (daily hours for date range)
   - Right: Chart.js doughnut (per-user distribution)
4. **User grid** — 3-column cards showing: avatar (or initials), fullname, total hours, screenshots count, last active time, link to user drill-down
5. **Bottom row** — Team summary: total hours this period, top apps (from usage), focus score avg

## User Drill-Down (3 tabs)

1. **Timeline** — DataTable of time entries (date, start/stop, duration, task link), filtered by date range
2. **App Usage** — Chart.js pie chart of app breakdown + table below
3. **Tasks** — Task list with status badges, assignee/reporter, progress

## Calendar

- Month grid rendered server-side (no JS calendar lib)
- Days with entries get a colored dot
- Click day → AJAX modal with day's entries/screenshots

## Screenshots Gallery

- Filter by user/task/date
- Lightbox on click (Bootstrap modal or simple overlay)
- Shows: thumbnail, user name, timestamp, task name

## Data Flow

- `index()` loads KPI data + user grid data + chart data in a single request
- Chart data loaded via inline `<script>` with JSON from PHP
- Date range changes → form submit → full page reload (simple, matches ERP patterns)
- Calendar day click → AJAX → modal populates
- DataTable uses server-side processing via `entries_datatable()` AJAX endpoint

## Approach

- **TDD**: RED → GREEN → REFACTOR for every change
- **PHPUnit 12** for backend tests (TimesyncTest.php)
- **No frontend test framework** — PHP views tested via functional assertions
- Start with backend: TimesyncTest.php → rewrite controller methods → rewrite views
- Backend tests: 3-5 per endpoint, 15-20 total

## Testing

- `application/tests/controllers/admin/TimesyncTest.php` — 15-20 tests
  - Dashboard KPI data shape (assert keys, types)
  - User report data shape
  - Calendar returns proper month grid
  - Day details returns entries + screenshots
  - DataTable endpoint returns JSON with expected structure
- Run: `vendor/bin/phpunit --testsuite admin`

## Implementation Order

1. **Backend** — Rewrite `index()` + add `calendar()` + `day_details()` + `entries_datatable()` + rewrite `user()` + enhance `screenshots()`/`usage()`
2. **Tests** — Write TimesyncTest.php covering all endpoints
3. **Views** — Rewrite `dashboard.php`, `user_report.php`, create `calendar.php`, `entries.php`, enhance `screenshots.php`, `usage_report.php`
4. **Verify** — Full test suite green, manual check in browser
