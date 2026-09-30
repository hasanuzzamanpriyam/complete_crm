# Calendar Page Redesign — Design Spec

**Date:** 2026-09-30
**Status:** Approved by user (visual mockup + "all good implement it")
**Design source of truth:** approved mockup `.superpowers/brainstorm/108-1790752228/content/calendar-full.html` (layout C + both modals)
**Dashboard source of truth:** `_design/dashboard-modern.html` + live `admin/dashboard`

## Goal

Redesign the admin calendar page (`admin/calendar`) and its two modals to match the approved dashboard design language — same tokens, card system, toolbar/button styles — keeping all existing functionality (event sources, category filter, FullCalendar navigation, settings save).

## Approved decisions

- **Layout C:** KPI stat strip (4 cards) on top + one full-width calendar card. No right rail.
- **Calendar card toolbar** (custom HTML, replaces FullCalendar's built-in header): `‹ Today ›` nav buttons · month title · `Month | Week | Day` segmented control · category filter pill · ⚙ gear button.
- **Month grid look:** white cells, `#f1f3f5` separators, muted out-month, teal filled circle on today, solid rounded event pills in category colors, "+n more" link.
- **Two modals:**
  1. **Calendar settings** (⚙ gear) — Google Calendar section + "Show on calendar" toggle grid with color swatches.
  2. **New event** (topbar "+ New event" or clicking a day) — title, category, related-to, dates/times, all-day + notify checkboxes, notes.
- **Colors:** dashboard tokens (`--c-accent:#0d9488`, `--c-bg:#f4f6f8`, radius 16px, `--sh-1` shadow). Event chip colors = category colors from settings.
- **No fake data:** KPI numbers must be computed from real data.

## Architecture

### 1. Page restructure — `application/views/admin/calendar.php`

- Replace the Bootstrap `.panel` shell with the dashboard `.card` wrapper + custom toolbar markup (nav buttons, title span, segmented view control, filter pill, gear button).
- Hide FullCalendar's built-in toolbar (`header: {left:'', center:'', right:''}`); wire custom buttons in `my_calendar.php` JS (`fullCalendar('prev'/'next'/'today'/'changeView')`).
- **KPI strip** above the calendar card — 4 `.kpi-card` stat cards, all real:
  - **Events this month** and **Upcoming this week** — computed **client-side** after FullCalendar renders: count via `fullCalendar('clientEvents')` filtered to the current month / next 7 days, then fill the two KPI value spans. This reuses the exact event data (zero duplicated queries, guaranteed consistent with the grid).
  - **On leave** — `tbl_attendance` rows with `attendance_status = 3` scoped to the current month (existing query pattern at calendar.php:102), computed in `calendar.php` **before** the KPI markup.
  - **Holidays this month** — `tbl_holiday` rows where `[start_date, end_date]` overlaps the current month (table exists: `holiday_id, event_name, start_date, end_date, location, color`), same position.
- Keep existing category filter dropdown behavior (currently reloads with `/search/<type>`); restyle trigger as the filter pill.

### 2. Calendar grid theming — rewrite `assets/css/calendar_modern.css`

- Replace the current "Red & Gray Corporate Theme" with the dashboard token theme. Keep the proven targeting of FullCalendar v3 internals (`.fc-toolbar`, `.fc-button`, `.fc-day-number`, `.fc-event`, `.fc-button-group`, `.fc-today` — already overridden today).
- Hide the FC header entirely; style FC grid only (cells, day numbers, events, today, hover states).
- This file is loaded only on the calendar page (via `my_calendar.php:2`), so it also carries page-specific styles: KPI strip, toolbar, segmented control, filter pill, and both modal designs.
- **Add cache buster:** `calendar_modern.css?v=2` at `my_calendar.php:2` (no version param today — learned lesson from app.js/app.css caching).

### 3. Settings modal — `application/views/admin/settings/calendar_settings.php`

- Keep flow intact: gear → AJAX load into `#myModal` (`_layout_modal`), form POST → `admin/calendar/save_settings`, all current config keys and the existing colorpicker components must keep working.
- Visual redesign to match the approved modal:
  - Header: title + subtitle, round close button, no Bootstrap default styling.
  - **Google Calendar** section: info note box (teal tint) + two inputs (API key, Calendar ID).
  - **Show on calendar** section: responsive 2-col grid of `.so` rows (category dot + label + color swatch containing the existing checkbox/colorpicker, checked = swatch colored with ✓, off = white).
  - Footer: Cancel (ghost) + Save settings (teal accent).

### 4. New event modal — **new functionality** (markup is currently missing)

The existing `select` handler (`my_calendar.php:33-42`) fills `#event_modal` fields and calls `.modal('show')`, but **no `#event_modal` markup exists anywhere** — day-click is currently dead. Implement it:

- **Markup:** hidden Bootstrap modal `#event_modal` in `calendar.php`. The four legacy IDs the existing `select` handler writes to are used as-is, with `name` attributes matching the DB columns so one input serves both:

  | Element id (legacy) | `name` (POST/DB) | Filled by day-click |
  |---|---|---|
  | `#apptStartTime` | `start_date` | date ISO `yyyy-mm-dd` |
  | `#apptEndTime` | `end_date` | same date (existing handler already does this) |
  | `#when` (text) | — | "date - time" label (display only) |
  | `#apptAllDay` (hidden/checkbox) | `all_day` | `allDay` flag |

  Remaining fields are new: `title*` (required), `category`, `related_type`/`related_id` (single select: None or a related entity), `start_time`, `end_time`, `notify`, `notes`. Hidden `calendar_events_id` = 0 for create, >0 for edit.
- **Storage:** new table `tbl_calendar_events`:

  ```sql
  CREATE TABLE tbl_calendar_events (
    calendar_events_id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'project',
    start_date DATE NOT NULL,
    end_date DATE NULL,
    start_time VARCHAR(10) NULL,
    end_time VARCHAR(10) NULL,
    all_day TINYINT(1) NOT NULL DEFAULT 0,
    notify TINYINT(1) NOT NULL DEFAULT 0,
    related_type VARCHAR(30) NULL,
    related_id INT(11) NULL,
    notes TEXT NULL,
    created_by INT(11) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (calendar_events_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ```

- **Controller endpoints** (`application/controllers/admin/Calendar.php`):
  - `save_event()` — validate title/dates, insert or update (id in POST), return JSON.
  - `delete_event()` — delete by id (owner/admin check following existing controller patterns).
- **Event source:** append a `tbl_calendar_events` source in `my_calendar.php` `eventSources` with colors mapped per category (same palette used by existing sources) and `type: 'manual'`.
- **Edit/delete:** `eventClick` on `type:'manual'` events opens the same modal prefilled (id in a hidden field, modal switches to edit mode, Delete button appears).
- **Entry points:** day-click (`select` handler — already written), topbar "+ New event" button, and nothing else.

## Components & interfaces

| Piece | File(s) | Change |
|---|---|---|
| Page view | `application/views/admin/calendar.php` | panel → card + toolbar + KPI strip + `#event_modal` markup |
| Calendar JS | `application/views/admin/calendar/my_calendar.php` | hide FC header, wire custom toolbar, manual event source, eventClick edit, client-side KPI counts |
| Calendar CSS | `assets/css/calendar_modern.css` | full rewrite (dashboard theme; grid + page + modals) |
| Settings modal | `application/views/admin/settings/calendar_settings.php` | visual redesign, same fields/actions |
| Controller | `application/controllers/admin/Calendar.php` | add `save_event()`, `delete_event()` |
| Model (optional) | `application/models/Admin_model.php` | only if CRUD conventions require it; otherwise CI query builder in controller |
| DB | migration SQL | `tbl_calendar_events` |
| Cache | `my_calendar.php:2` | `calendar_modern.css?v=2` |

## Error handling

- `save_event`: reject empty title / invalid dates with JSON `{status:'error', message:...}`; modal shows inline error, stays open.
- `delete_event`: 403-style JSON if not permitted; modal stays open on failure.
- Settings save: keep existing `save_settings` behavior (page reload via existing JS).
- Google sync failures stay out of scope (existing behavior unchanged).

## Testing & verification

- Existing gates: `php plan\_tools\menu-group-test.php` (59), `smoke.php` (22), `login-test.php` (7) must stay green.
- `php -l` on every edited PHP file.
- Browser verification against the approved mockup: fresh navigate + screenshot (DPR quirks known), DOM probes for computed styles; functional pass — prev/today/next, Month/Week/Day switch, category filter, settings save persists after reload, create event → chip appears, edit, delete, KPI numbers match hand-counted real data. Wide (≈1422) and narrow (≈711) widths, no overflow.

## Out of scope

- Layouts A (side rail) and B (full-width without KPIs) — C approved.
- Google Calendar two-way sync logic (UI only; keys keep saving to `tbl_config`).
- Week/Day views beyond generic FC CSS theming (they're reachable via the segmented control).
- Client-side calendar (`project_details/calender.php`) — admin page only.

## Risks

- **FC v3 theming limits:** mitigated — `calendar_modern.css` already themes the same internals successfully.
- **New CRUD endpoints:** must follow existing auth/role patterns (`admin_head()`, session role checks) — plan will mirror `save_settings`/existing controllers.
- **Settings colorpicker:** redesign must preserve the existing plugin markup/classes it binds to (`.colorpicker-component`, fa-check span) or re-bind carefully.
- **KPI counting inside eventSources PHP:** counters must not alter event arrays; verify counts against rendered events.
