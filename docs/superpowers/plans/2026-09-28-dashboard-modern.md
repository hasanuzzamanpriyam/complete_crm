# Dashboard Colour-Forward Redesign (A2-15) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle the live `admin/dashboard` to match the approved static demo `_design/dashboard-modern.html`: three dark hero cards, white cards everywhere else, family colours — with the page's structure, widgets, order and Block A KPI cards exactly as they are today.

**Architecture:** Scope all new CSS under a single `.dash-page` class added to the two widget rows in `main_content.php` (the only view the dashboard controller loads, verified). Replace markup only where structure changes (the two row-table report blocks, the overdue footer, and four AJAX partials whose charts are rebuilt as server-rendered div bars). All other widgets are restyled by scoped CSS over their existing `.panel.panel-custom` markup; every `tbl_dashboard` query, guard, AJAX endpoint and JS id stays untouched.

**Tech Stack:** CodeIgniter 3 PHP views, custom CSS in `assets/css/app.css` (A2-15 block appended at EOF), Bootstrap 3 grid/nav/table primitives restyled in scope, existing jQuery AJAX (`ins_data`) and DataTables unchanged.

**Spec:** `docs/superpowers/specs/2026-09-28-dashboard-modern-design.md` (approved).
**Demo (visual target):** `_design/dashboard-modern.html` (approved by user: "good, implement the design").

---

## File Structure

| File | Change |
|---|---|
| `assets/css/app.css` | Append `A2-15 dashboard` block at EOF (line 9272) |
| `application/views/admin/main_content.php` | Add `dash-page` class (2 divs); rewrite Block B/C report tiles as heroes; rewrite overdue footer as chips |
| `application/views/admin/components/htmlheader.php:47` | `app.css?v=8` → `v=9` |
| `application/views/login/lock_screen.php:17` | `app.css?v=8` → `v=9` |
| `application/views/admin/dashboard/finance_overview.php` | White panel + Chart.js line → hero `g1` + div bars |
| `application/views/admin/dashboard/recently_paid_invoices.php` | `.list-group` rows → table + method tags |
| `application/views/admin/dashboard/income_report_old.php` | Canvas/Chart.js → `.bars` divs (form kept) |
| `application/views/admin/dashboard/expense_report_old.php` | Canvas/Chart.js → red `.bars` divs (form kept) |
| `application/views/admin/partials/payments_report_old.php` | Canvas/Chart.js → `.bars` divs (form kept) |
| `application/views/admin/dashboard/income_expense_old.php` | Morris donut → stat pair + `.bars pair` (form kept) |
| `application/views/admin/partials/goal_report.php` | Morris.Bar → `.goal`/`.track` rows (form kept) |
| `application/views/admin/dashboard/announcements.php` | Strip fixed height, add heading `.sub` |
| `application/views/admin/dashboard/active_timers.php` | Strip fixed height, add heading `.sub` |
| `application/views/admin/dashboard/recent_activities.php` | Strip fixed height, add heading `.sub` |
| `application/views/admin/dashboard/my_projects_data.php` | Strip fixed height, add heading `.sub` |
| `application/views/admin/partials/my_task.php` | Strip fixed height, add heading `.sub` |
| `application/views/admin/dashboard/to_do_list_data.php` | Strip fixed height, add heading `.sub` |

**Not touched:** Block A markup (`main_content.php:55-115`) and its CSS (`app.css` kpi-stat block ~4696-4826); `tbl_dashboard` queries/guards/order loops; all `ins_data` endpoints and their JS wiring (`main_content.php:626-804`); sortable initializers; `tokens.css`; overdue DataTables tables and tab anchors; `.dragger` inline style block.

**Environment constants** (used verbatim in steps):

- PHP: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`
- MySQL: `C:\laragon\bin\mysql\mariadb-12.2.2-winx64\bin\mysql.exe -u root tic_crm`
- Login: `forid` / `Admin@123`, base `http://localhost/complete_crm`
- Bash: `C:\laragon\bin\git\bin\bash.exe`
- Playwright DPR quirk: set viewport width = CSS px × 0.9 (1920→1728, 768→691, 360→324)
- Branch `sakib`; commits prefixed `feat:` / `fix:` with `A2-15` label

---

### Task 1: Scope anchor + CSS cache bump

**Files:**
- Modify: `application/views/admin/main_content.php:51`
- Modify: `application/views/admin/main_content.php:291`
- Modify: `application/views/admin/components/htmlheader.php:47`
- Modify: `application/views/login/lock_screen.php:17`

- [ ] **Step 1: Add the `dash-page` class to both widget rows**

In `main_content.php`, change line 51 from:

```html
        <div id="report_menu" class="row">
```

to:

```html
        <div id="report_menu" class="row dash-page">
```

Change line 291 from:

```html
    <div id="menu" class="row">
```

to:

```html
    <div id="menu" class="row dash-page">
```

- [ ] **Step 2: Bump the CSS cache buster**

In `htmlheader.php:47` and `lock_screen.php:17`, replace `app.css?v=8` with `app.css?v=9`:

```powershell
Select-String -Path "application\views\admin\components\htmlheader.php","application\views\login\lock_screen.php" -Pattern "app\.css\?v=" | ForEach-Object { "$($_.Filename):$($_.LineNumber): $($_.Line.Trim())" }
```

Expected: two hits, both showing `?v=9` after the edit.

- [ ] **Step 3: Syntax-check and commit**

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\main_content.php"
```

Expected: `No syntax errors detected`.

```bash
git add application/views/admin/main_content.php application/views/admin/components/htmlheader.php application/views/login/lock_screen.php
git commit -m "feat: A2-15 dashboard - dash-page scope class and app.css cache bump"
```

---

### Task 2: A2-15 CSS block in app.css

**Files:**
- Modify: `assets/css/app.css` (append at EOF, current last line 9272)

- [ ] **Step 1: Append the block**

Append the following block at the very end of `assets/css/app.css` (do not modify any existing rule):

```css
/* ============================================================
   A2-15 · dashboard colour-forward skin
   Approved target: _design/dashboard-modern.html (2026-09-28)
   Scope: .dash-page (report row + widget grid of admin/dashboard only).
   Block A (.kpi-*) is intentionally NOT styled here - it stays A2-14.
   ============================================================ */

.dash-page {
  --dc-bg: #f4f6f8;
  --dc-surface: #fff;
  --dc-border-2: #e3e6ea;
  --dc-text: #1a1d21;
  --dc-text-2: #475569;
  --dc-slate: #94a3b8;
  --dc-accent: #0d9488;
  --dc-mint: #10b981;
  --dc-cyan: #06b6d4;
  --dc-amber: #f59e0b;
  --dc-purple: #8b5cf6;
  --dc-danger: #dc2626;
  --dc-r-xl: 16px;
  --dc-r-md: 10px;
  --dc-sh-1: 0 1px 2px rgba(15, 23, 42, .06);
  --dc-hero-1: linear-gradient(135deg, #0f172a, #134e4a);
  --dc-hero-2: linear-gradient(135deg, #111827, #164e63);
  --dc-hero-3: linear-gradient(135deg, #1c1917, #3b2f52);
}

/* ---------------- grid rhythm (16px gutters) ---------------- */
.dash-page .row {
  margin-left: -8px;
  margin-right: -8px;
}
.dash-page .row > [class*="col-"] {
  padding-left: 8px;
  padding-right: 8px;
}
.dash-page .mt-lg { margin-top: 16px; }
.dash-page .report,
.dash-page #menu > div { position: relative; }

/* ---------------- white card chrome (existing .panel markup) ---------------- */
.dash-page .panel,
.dash-page .panel.panel-custom {
  background: var(--dc-surface);
  border: 1px solid var(--dc-border-2);
  border-left: 0;
  border-radius: var(--dc-r-xl);
  box-shadow: var(--dc-sh-1);
  margin-bottom: 0;
}
.dash-page .panel > .panel-heading,
.dash-page .panel-custom .panel-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 16px 0;
  margin-bottom: 0;
  background: transparent;
  border-bottom: 0;
  font-weight: 400;
  font-size: inherit;
  color: inherit;
}
.dash-page .panel-heading .panel-title {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 4px 10px;
  width: 100%;
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  letter-spacing: -.01em;
  color: #0f172a;
}
.dash-page .panel-title .sub {
  flex: 0 0 100%;
  order: 9;
  font-size: 11px;
  font-weight: 400;
  color: var(--dc-slate);
  margin-top: -2px;
}
.dash-page .panel-heading .pull-right {
  float: none;
  margin-left: auto;
}
.dash-page .panel > .panel-body { padding: 14px 16px 16px; }
.dash-page .panel > .panel-footer {
  padding: 12px 16px 16px;
  border-top: 1px solid #f1f3f5;
  background: #fff;
  border-radius: 0 0 var(--dc-r-xl) var(--dc-r-xl);
}

/* close button (wrapper-level .close-btn) */
.dash-page .close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 4;
  width: 24px;
  height: 24px;
  display: grid;
  place-items: center;
  margin: 0;
  padding: 0;
  font-size: 14px;
  color: #c3c9d0;
  background: transparent;
  border: 0;
  opacity: 0;
  text-shadow: none;
  filter: none;
  transition: .15s;
}
.dash-page .report:hover .close-btn,
.dash-page #menu > div:hover .close-btn { opacity: 1; }

/* ---------------- heroes ---------------- */
.dash-page .hero {
  border-radius: var(--dc-r-xl);
  color: #fff;
  padding: 18px;
  position: relative;
  min-width: 0;
  overflow: hidden;
  margin-bottom: 0;
}
.dash-page .hero.g1 { background: var(--dc-hero-1); border: 0; }
.dash-page .hero.g2 { background: var(--dc-hero-2); border: 0; }
.dash-page .hero.g3 { background: var(--dc-hero-3); border: 0; }
.dash-page .hero .h-title { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
.dash-page .hero .lbl {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .03em;
  opacity: .7;
}
.dash-page .hero .val {
  font-size: 26px;
  font-weight: 800;
  letter-spacing: -.02em;
  margin-top: 3px;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}
.dash-page .hero .h-sub { font-size: 11px; opacity: .72; margin-top: 6px; }
.dash-page .hero .more { font-size: 11px; color: #fff; opacity: .75; margin-top: 8px; display: inline-block; }
.dash-page .hero .more:hover { opacity: 1; color: #fff; text-decoration: none; }
.dash-page .hero .h-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px 18px;
  margin-top: 12px;
}
.dash-page .hero .h-div { border-top: 1px solid rgba(255, 255, 255, .14); padding-top: 12px; }
.dash-page .hero .h-bars {
  display: flex;
  align-items: flex-end;
  gap: 5px;
  height: 44px;
  margin-top: 12px;
}
.dash-page .hero .h-bars i { flex: 1; background: rgba(255, 255, 255, .18); border-radius: 3px; min-height: 4px; }
.dash-page .hero .h-bars i.hi { background: #2dd4bf; }
.dash-page .hero .h-chip {
  display: inline-block;
  font-size: 11px;
  font-weight: 700;
  border-radius: 999px;
  padding: 3px 9px;
  background: rgba(255, 255, 255, .14);
  margin-right: 6px;
  margin-top: 10px;
}
.dash-page .hero .h-chip.mint { background: rgba(52, 211, 153, .18); color: #34d399; margin-top: 0; }
.dash-page .hero .hero-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}
.dash-page .hero .hero-foot {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px;
}
.dash-page .hero .h-chip + .h-chip { margin-top: 10px; }

/* forms on dark hero */
.dash-page .hero .form-control {
  height: 28px;
  width: 92px;
  background: rgba(255, 255, 255, .12);
  border: 1px solid rgba(255, 255, 255, .22);
  color: #fff;
  border-radius: 8px;
  box-shadow: none;
  font-size: 12px;
}
.dash-page .hero .form-control:focus {
  background: rgba(255, 255, 255, .18);
  border-color: rgba(255, 255, 255, .4);
  color: #fff;
}
.dash-page .hero .input-group-addon {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, .22);
  border-left: 0;
  color: rgba(255, 255, 255, .7);
}
.dash-page .hero .input-group-addon a { color: rgba(255, 255, 255, .7); }
.dash-page .hero .btn-custom {
  background: rgba(255, 255, 255, .18);
  border: 0;
  color: #fff;
  border-radius: 8px;
  padding: 4px 9px;
}
.dash-page .hero .btn-custom:hover { background: rgba(255, 255, 255, .3); color: #fff; }
.dash-page .hero .control-label { color: rgba(255, 255, 255, .65); }
.dash-page .hero .years,
.dash-page .hero .datepicker { color: #fff; }

/* ---------------- overdue: pill tabs ---------------- */
.dash-page .nav-tabs-custom .nav-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  padding: 14px 16px 0;
  margin: 0;
  border: 0;
}
.dash-page .nav-tabs-custom .nav-tabs > li {
  float: none;
  margin: 0;
}
.dash-page .nav-tabs-custom .nav-tabs > li > a {
  margin: 0;
  border: 0;
  border-radius: 999px;
  background: #f1f3f5;
  color: var(--dc-text-2);
  font-size: 12px;
  font-weight: 700;
  line-height: 1.3;
  padding: 6px 12px;
}
.dash-page .nav-tabs-custom .nav-tabs > li > a strong { font-weight: 800; }
.dash-page .nav-tabs-custom .nav-tabs > li.active > a,
.dash-page .nav-tabs-custom .nav-tabs > li.active > a:hover,
.dash-page .nav-tabs-custom .nav-tabs > li.active > a:focus {
  background: var(--dc-accent);
  color: #fff;
  border: 0;
  border-top: 0;
}
.dash-page .nav-tabs-custom .nav-tabs > li.active > a strong { color: #fff; }
.dash-page .nav-tabs-custom .tab-content { border-top: 1px solid #f1f3f5; margin-top: 12px; }
.dash-page aside.nav-tabs-custom { border: 0; background: transparent; }

/* overdue footer chips */
.dash-page .foot-totals {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.dash-page .ft {
  background: #f8fafb;
  border: 1px solid var(--dc-border-2);
  border-radius: 10px;
  padding: 7px 11px;
  font-size: 12px;
  color: var(--dc-text-2);
  white-space: nowrap;
}
.dash-page .ft b { color: var(--dc-text); font-size: 13px; margin-left: 4px; }

/* ---------------- tables ---------------- */
.dash-page .table { color: var(--dc-text); font-size: 13px; margin-bottom: 0; }
.dash-page .table > thead > tr > th {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: var(--dc-slate);
  font-weight: 700;
  text-align: left;
  padding: 10px 16px;
  background: #fafbfc;
  border-top: 1px solid #f1f3f5;
  border-bottom: 1px solid #f1f3f5;
}
.dash-page .table > tbody > tr > td {
  padding: 10px 16px;
  border-top: 0;
  border-bottom: 1px solid #f1f3f5;
  vertical-align: middle;
  color: var(--dc-text);
}
.dash-page .table > tbody > tr:last-child > td { border-bottom: 0; }
.dash-page .table-striped > tbody > tr:nth-of-type(odd) > td { background-color: transparent; }
.dash-page .table .num { font-variant-numeric: tabular-nums; font-weight: 700; color: var(--dc-danger); }

/* ---------------- bars / axis / legend (server-rendered charts) ---------------- */
.dash-page .bars {
  display: flex;
  align-items: flex-end;
  gap: 6px;
  height: 120px;
  padding-top: 6px;
}
.dash-page .bars i { flex: 1; border-radius: 4px 4px 0 0; background: var(--dc-accent); min-height: 6px; }
.dash-page .bars i.red { background: var(--dc-danger); opacity: .85; }
.dash-page .bars.pair { justify-content: center; gap: 24px; }
.dash-page .bars.pair i { flex: 0 0 56px; }
.dash-page .xaxis { display: flex; gap: 6px; margin-top: 6px; }
.dash-page .xaxis span { flex: 1; text-align: center; font-size: 9px; color: var(--dc-slate); }
.dash-page .legend { display: flex; gap: 14px; font-size: 11px; color: #7a828c; margin-top: 8px; }
.dash-page .legend i { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 5px; }

/* stat pair (income vs expense) */
.dash-page .two { display: flex; gap: 16px; margin-bottom: 14px; }
.dash-page .two > div { flex: 1; min-width: 0; }
.dash-page .stat-big {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -.02em;
  font-variant-numeric: tabular-nums;
}
.dash-page .stat-lbl {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .03em;
  color: var(--dc-slate);
  margin-bottom: 3px;
}

/* goal tracks */
.dash-page .goal { padding: 10px 0; border-bottom: 1px solid #f1f3f5; }
.dash-page .goal:last-child { border-bottom: 0; }
.dash-page .goal .gr {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-bottom: 6px;
}
.dash-page .goal .gr b { font-variant-numeric: tabular-nums; }
.dash-page .track { height: 8px; border-radius: 999px; background: #eef1f4; overflow: hidden; }
.dash-page .track > span { display: block; height: 100%; border-radius: 999px; }

/* ---------------- tags / labels ---------------- */
.dash-page .label {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  border-radius: 999px;
  padding: 3px 8px;
  line-height: 1.4;
}
.dash-page .label-success { background: #ecfdf5; color: #047857; }
.dash-page .label-danger { background: #fef2f2; color: #b91c1c; }
.dash-page .label-warning { background: #fffbeb; color: #b45309; }
.dash-page .label-default { background: #f1f3f5; color: var(--dc-text-2); }
.dash-page .label-primary { background: #ecfeff; color: #0e7490; }
.dash-page .label-dark { background: #f1f3f5; color: var(--dc-text-2); }

/* mini buttons (timer/todo actions) */
.dash-page .btn-xs {
  border-radius: 7px;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 9px;
  border: 0;
  text-shadow: none;
}
.dash-page .btn-success { background: #ecfdf5; color: #047857; }
.dash-page .btn-success:hover { background: #d1fae5; color: #047857; }
.dash-page .btn-warning { background: #fffbeb; color: #b45309; }
.dash-page .btn-warning:hover { background: #fef3c7; color: #b45309; }
.dash-page .btn-danger { background: #fef2f2; color: #b91c1c; }
.dash-page .btn-danger:hover { background: #fee2e2; color: #b91c1c; }
.dash-page .btn-custom {
  background: var(--dc-accent);
  border: 0;
  color: #fff;
  border-radius: 8px;
  text-shadow: none;
}
.dash-page .btn-custom:hover { background: #0b7c72; color: #fff; }

/* ---------------- progress (my tasks / my projects) ---------------- */
.dash-page .progress {
  height: 8px;
  border-radius: 999px;
  background: #eef1f4;
  box-shadow: none;
  margin: 6px 0 4px;
}
.dash-page .progress-bar {
  border-radius: 999px;
  box-shadow: none;
  background-image: none;
}
.dash-page .progress-bar-primary { background-color: var(--dc-amber); }
.dash-page .progress-bar-success { background-color: var(--dc-mint); }

/* ---------------- forms inside white cards ---------------- */
.dash-page .panel-body .form-groups-bordered .form-group {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 0 12px;
}
.dash-page .panel-body .form-groups-bordered .control-label {
  width: auto;
  margin: 0;
  padding: 0;
  text-align: left;
  font-size: 11px;
  color: var(--dc-slate);
}
.dash-page .panel-body .form-groups-bordered .col-sm-5 {
  width: auto;
  flex: 0 0 150px;
  padding: 0;
}
.dash-page .panel-body .form-control {
  height: 32px;
  border: 1px solid var(--dc-border-2);
  border-radius: 8px;
  box-shadow: none;
  font-size: 12.5px;
  color: var(--dc-text);
  background: #fff;
}
.dash-page .panel-body .input-group-addon {
  background: #f8fafb;
  border: 1px solid var(--dc-border-2);
  border-left: 0;
  border-radius: 0 8px 8px 0;
  color: var(--dc-slate);
}
.dash-page .panel-body .input-group-addon a { color: var(--dc-slate); }

/* ---------------- feeds / announcements ---------------- */
.dash-page .comment-item {
  padding: 10px 0;
  border-bottom: 1px solid #f1f3f5;
}
.dash-page .comment-item:last-child { border-bottom: 0; }
.dash-page .comment-item hr { display: none; }
.dash-page .notice-calendar-list {
  padding-top: 10px;
  border-bottom: 1px solid #f1f3f5;
}
.dash-page .notice-calendar-list:last-child { border-bottom: 0; }

/* ---------------- misc ---------------- */
.dash-page .list-group-item {
  border-color: #f1f3f5;
  font-size: 13px;
}
.dash-page .list-group-item:first-child { border-top-right-radius: 8px; border-top-left-radius: 8px; }
.dash-page a { color: var(--dc-accent); }
.dash-page .text-info { color: var(--dc-accent) !important; }
.dash-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dash-page .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
  background: var(--dc-accent) !important;
  border-color: var(--dc-accent) !important;
  color: #fff !important;
}

@media (max-width: 768px) {
  .dash-page .hero .h-grid { grid-template-columns: 1fr; }
  .dash-page .hero .h-div { border-top: 0; padding-top: 0; }
}
```

- [ ] **Step 2: Verify the CSS loads and the page is intact**

Open `http://localhost/complete_crm/admin/dashboard` (login `forid`/`Admin@123` first if needed) and evaluate:

```js
() => {
  const cs = getComputedStyle(document.querySelector('#report_menu'));
  const p  = document.querySelector('#menu .panel');
  return JSON.stringify({
    scope: cs.display,                    // must be 'block' (.dash-page applied)
    cardRadius: p ? getComputedStyle(p).borderRadius : null,   // '16px'
    cardBorder: p ? getComputedStyle(p).borderTopColor  : null // rgb(227, 230, 234)
  });
}
```

Expected: `display` is block, radius `16px`, border `rgb(227, 230, 234)`.

```powershell
Select-String -Path "assets\css\app.css" -Pattern "A2-15 dashboard colour-forward" | ForEach-Object { $_.LineNumber }
```

Expected: one hit near the end of the file.

- [ ] **Step 3: Commit**

```bash
git add assets/css/app.css
git commit -m "feat: A2-15 dashboard - colour-forward CSS block scoped to .dash-page"
```

---

### Task 3: Block B/C — row-table tiles become heroes

**Files:**
- Modify: `application/views/admin/main_content.php:118-196` (invoice)
- Modify: `application/views/admin/main_content.php:197-284` (tickets)

Block A (lines 55-115) is untouched. The AJAX endpoints fill the same element ids; keep every id and every link.

- [ ] **Step 1: Replace the invoice report block**

Replace the entire `invoice_payment_report` conditional body (starting at `if ($v_report->name == 'invoice_payment_report' ...` and its inner markup through the closing `</div>` before `if ($v_report->name == 'ticket_tasks_report'`) with:

```php
                    if ($v_report->name == 'invoice_payment_report' && $v_report->status == 1) {
                        ?>
                        <!--        ******** Sales ************** -->
                        <div class="<?= $v_report->col ?> report" id="<?= $v_report->id ?>">
                            <?php echo ajax_anchor(base_url("admin/settings/save_dashboard/$v_report->id" . '/0'), "<i class='fa fa-times-circle'></i>", array("class" => "close-btn", "title" => lang('inactive'), "data-fade-out-on-success" => "#" . $v_report->id)); ?>
                            <div class="hero g2 report_menu">
                                <div class="h-title"><?= lang('invoice') ?> &amp; <?= lang('payments') ?></div>
                                <div class="h-grid">
                                    <div>
                                        <div class="lbl"><?= lang('invoice_today') ?></div>
                                        <div class="val" id="today_invo_amount"></div>
                                        <a class="more" href="<?= base_url() ?>admin/invoice/manage_invoice"><?= lang('more_info') ?> <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                    <div>
                                        <div class="lbl"><?= lang('payment_today') ?></div>
                                        <div class="val" id="payment_today" style="color:#34d399"></div>
                                        <a class="more" href="<?= base_url() ?>admin/invoice/all_payments"><?= lang('more_info') ?> <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                    <div class="h-div">
                                        <div class="lbl"><?= lang('paid_amount') ?></div>
                                        <div class="val" id="paid_invo_amount"></div>
                                        <a class="more" href="<?= base_url() ?>admin/invoice/all_payments"><?= lang('more_info') ?> <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                    <div class="h-div">
                                        <div class="lbl"><?= lang('due_amount') ?></div>
                                        <div class="val" id="client_outstanding" style="color:#fbbf24"></div>
                                        <a class="more" href="<?= base_url() ?>admin/invoice/manage_invoice"><?= lang('more_info') ?> <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }
```

- [ ] **Step 2: Replace the tickets report block**

Replace the entire `ticket_tasks_report` conditional body the same way with:

```php
                    if ($v_report->name == 'ticket_tasks_report' && $v_report->status == 1) { ?>
                        <!--        ******** Ticket ************** -->
                        <div class="<?= $v_report->col ?> report" id="<?= $v_report->id ?>">
                            <?php echo ajax_anchor(base_url("admin/settings/save_dashboard/$v_report->id" . '/0'), "<i class='fa fa-times-circle'></i>", array("class" => "close-btn", "title" => lang('inactive'), "data-fade-out-on-success" => "#" . $v_report->id)); ?>
                            <div class="hero g3 report_menu">
                                <div class="h-title"><?= lang('task') ?> &amp; <?= lang('tickets') ?></div>
                                <div class="h-grid">
                                    <div>
                                        <div class="lbl"><?= lang('in_progress') ?> <?= lang('task') ?></div>
                                        <div class="val" id="tasks_in_progress"></div>
                                        <a class="more" href="<?= base_url() ?>admin/tasks/all_task"><?= lang('more_info') ?> <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                    <div>
                                        <div class="lbl"><?= lang('open') ?> <?= lang('tickets') ?></div>
                                        <div class="val" id="tickets_open"></div>
                                        <a class="more" href="<?= base_url() ?>admin/tickets"><?= lang('more_info') ?> <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                    <div class="h-div">
                                        <div class="lbl"><?= lang('in_progress') ?> <?= lang('bugs') ?></div>
                                        <div class="val" id="bugs_in_progress"></div>
                                        <a class="more" href="<?= base_url() ?>admin/bugs"><?= lang('more_info') ?> <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                    <div class="h-div">
                                        <div class="lbl"><?= lang('in_progress') ?> <?= lang('project') ?></div>
                                        <div class="val" id="project_in_progress"></div>
                                        <a class="more" href="<?= base_url() ?>admin/projects"><?= lang('more_info') ?> <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }
```

The demo's status-chip row on the tickets hero is deliberately omitted: those three chips were fake numbers with no live source (data-honesty rule in the spec).

- [ ] **Step 3: Verify AJAX still fills the hero values**

Load `/admin/dashboard`, wait for XHR, evaluate:

```js
() => JSON.stringify(['today_invo_amount','payment_today','paid_invo_amount','client_outstanding',
  'tasks_in_progress','tickets_open','bugs_in_progress','project_in_progress']
  .map(id => [id, document.getElementById(id) ? document.getElementById(id).textContent.trim() : 'MISSING']))
```

Expected: 8 entries, none `MISSING`, values non-empty (numeric/currency strings).

Also confirm heroes sit side by side:

```js
() => {
  const a = document.querySelector('#report_menu .hero.g2').getBoundingClientRect();
  const b = document.querySelector('#report_menu .hero.g3').getBoundingClientRect();
  return JSON.stringify({ sameRow: Math.abs(a.y - b.y) < 2 && b.x > a.x, aW: Math.round(a.width), bW: Math.round(b.width) });
}
```

Expected: `sameRow: true`, widths roughly equal (each ~half of row).

- [ ] **Step 4: Lint + commit**

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\main_content.php"
```

```bash
git add application/views/admin/main_content.php
git commit -m "feat: A2-15 dashboard - report row tiles become colour heroes"
```

---

### Task 4: Overdue footer becomes chips

**Files:**
- Modify: `application/views/admin/main_content.php:419-475` (footer inside overdue block)

- [ ] **Step 1: Replace the footer markup**

Replace the `<?php if ($this->session->userdata('user_type') == '1') { ?> <footer ...> ... </footer> <?php } ?>` block (lines 419-475) with:

- Quality review found the first pass duplicated the divider: `.dash-page .panel > .panel-footer` already sets `padding: 12px 16px 16px` + `border-top: 1px solid #f1f3f5`, so those two declarations were removed from `.foot-totals` and `white-space: nowrap` was added to `.ft` (see Task 2 Step 1) — single hairline above the chips now.

```php
                        <?php
                        if ($this->session->userdata('user_type') == '1') { ?>
                            <footer class="panel-footer">
                                <div class="foot-totals">
                                    <span class="ft"><?= lang('complete_projects') ?><b id="completed_projects"></b></span>
                                    <span class="ft"><?= lang('complete_tasks') ?><b id="completed_tasks"></b></span>
                                    <span class="ft"><?= lang('total') ?> <?= lang('invoice_amount') ?><b id="total_invoice_amount"></b></span>
                                    <span class="ft"><?= lang('total') ?> <?= lang('estimate') ?><b id="total_estimate_amount"></b></span>
                                    <span class="ft"><?= lang('resolved') ?> <?= lang('bugs') ?><b id="total_resolved_bugs"></b></span>
                                    <span class="ft"><?= lang('won') ?> <?= lang('opportunities') ?><b id="total_won_opportunities"></b></span>
                                </div>
                            </footer>
                        <?php } ?>
```

- [ ] **Step 2: Verify totals still land in the chips**

Load dashboard, click the *Overdue invoices* tab, then evaluate:

```js
() => ['completed_projects','completed_tasks','total_invoice_amount','total_estimate_amount','total_resolved_bugs','total_won_opportunities']
  .map(id => [id, (document.getElementById(id)||{}).textContent || 'EMPTY'])
```

Expected: ids present; values populated after the `different_completed_total` / `different_overdue_total` XHRs (may be `0`/currency strings, not blank).

Also check pills render: `getComputedStyle(document.querySelector('.nav-tabs > li > a')).borderRadius` → `999px`, and active tab background `rgb(13, 148, 136)` after clicking a tab.

- [ ] **Step 3: Lint + commit**

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\main_content.php"
```

```bash
git add application/views/admin/main_content.php
git commit -m "feat: A2-15 dashboard - overdue footer totals as chips"
```

---

### Task 5: finance_overview becomes hero g1

**Files:**
- Modify: `application/views/admin/dashboard/finance_overview.php` (full rewrite)

Data available: `$total_annual['total_income'|'total_expense'|'total_profit']`, `$finance_overview_by_year` (month => `total_income`/`total_expense`), `$finance_year`. The year form ids (`finance_overview_year`, `finance_overview_btn`) are wired to JS in `main_content.php:750-757` — keep them.

- [ ] **Step 1: Rewrite the partial**

Replace the whole file with:

```php
<?php
$fo_year = !empty($finance_year) ? $finance_year : date('Y');
$fo_max = 0;
if (!empty($finance_overview_by_year)) {
    foreach ($finance_overview_by_year as $v_fo) {
        $fo_max = max($fo_max, (float) $v_fo['total_income']);
    }
}
?>
<div class="hero g1 menu">
    <div class="hero-top">
        <div>
            <div class="h-title"><?= lang('finance') ?> <?= lang('overview') ?></div>
            <div class="h-sub" style="margin-top:0"><?= $fo_year ?></div>
        </div>
        <form role="form" action="<?php echo base_url(); ?>admin/dashboard/finance_overview" method="post" class="form-horizontal form-groups-bordered">
            <input type="text" id="finance_overview_year" name="finance_overview" value="<?php echo $fo_year; ?>" class="form-control years">
            <button type="button" style="font-size: 13px" id="finance_overview_btn" data-toggle="tooltip" data-placement="top" title="Search" class="btn btn-custom"><i class="fa fa-search"></i></button>
        </form>
    </div>
    <div class="h-grid">
        <div>
            <div class="lbl"><?= lang('total_annual') ?> <?= lang('income') ?></div>
            <div class="val" style="font-size:22px"><?= display_money($total_annual['total_income'], default_currency()); ?></div>
        </div>
        <div>
            <div class="lbl"><?= lang('total_annual') ?> <?= lang('expense') ?></div>
            <div class="val" style="font-size:22px"><?= display_money($total_annual['total_expense'], default_currency()); ?></div>
        </div>
    </div>
    <div class="h-bars">
        <?php if (!empty($finance_overview_by_year)) {
            foreach ($finance_overview_by_year as $m_name => $v_fo) {
                $fo_h = $fo_max > 0 ? max(6, round(((float) $v_fo['total_income'] / $fo_max) * 100)) : 6;
                $fo_hi = ((float) $v_fo['total_income'] >= $fo_max && $fo_max > 0) ? ' hi' : '';
                ?><i class="<?= $fo_hi ?>" style="height:<?= $fo_h ?>%"></i><?php
            }
        } ?>
    </div>
    <div class="hero-foot">
        <span class="h-sub" style="margin:0">Jan - Dec <?= $fo_year ?></span>
        <span class="h-chip mint"><?= lang('profit') ?> <?= display_money($total_annual['total_profit'], default_currency()); ?></span>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        initdatepicker();
    });
</script>
```

Notes: the `type="button"` keeps the click in the delegated `#finance_overview_btn` handler (the old markup used `type="submit"` inside a form the handler `preventDefault`s — matching the other widgets). The `.menu` class is kept so the existing sortable `connectWith: ".menu"` sees exactly what it saw before. The old Chart.js line block and its `$finance_overview_order` guard are removed (bars are server-rendered).

- [ ] **Step 2: Verify**

Load dashboard and evaluate:

```js
() => {
  const h = document.querySelector('#finance_overview_div .hero.g1');
  return JSON.stringify({
    hero: !!h,
    title: h ? h.querySelector('.h-title').textContent.trim() : null,
    bars: h ? h.querySelectorAll('.h-bars i').length : 0,
    profit: h ? h.querySelector('.h-chip').textContent.trim() : null,
    formWired: !!document.getElementById('finance_overview_btn')
  });
}
```

Expected: `hero: true`, 12 bars (one per month of the year), profit chip contains a money value, `formWired: true`. Click the year search button → widget re-renders without console errors.

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\dashboard\finance_overview.php"
```

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/dashboard/finance_overview.php
git commit -m "feat: A2-15 dashboard - finance overview as gradient hero with div bars"
```

---

### Task 6: recently_paid_invoices becomes a table

**Files:**
- Modify: `application/views/admin/dashboard/recently_paid_invoices.php` (full rewrite)

Data available per row: `invoices_id`, `reference_no` (COALESCE ''), `trans_id`, `amount`, `currency`, `method_name`, `label` (`success|danger|dark`), `client_id`. No client name column is selected — the demo's Client column is dropped (no query changes per spec). `#total_receipts` is filled by existing JS — keep the id.

- [ ] **Step 1: Rewrite the partial**

Replace the whole file with:

```php
<section class="panel panel-custom menu">
    <header class="panel-heading">
        <h3 class="panel-title"><?= lang('recently_paid_invoices') ?><span class="sub">Last 5 payments</span></h3>
    </header>
    <div class="panel-body inv-slim-scroll">
        <table class="table m-b-none text-sm">
            <thead>
            <tr>
                <th><?= lang('invoice') ?></th>
                <th><?= lang('amount') ?></th>
                <th><?= lang('method') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($recently_paid)) {
                foreach ($recently_paid as $v_paid) {
                    ?>
                    <tr>
                        <td><a href="<?= base_url() ?>admin/invoice/manage_invoice/payments_details/<?php echo $v_paid->invoices_id; ?>"><b><?= !empty($v_paid->reference_no) ? $v_paid->reference_no : $v_paid->trans_id ?></b></a></td>
                        <td style="font-weight:700"><?= display_money($v_paid->amount, $v_paid->currency) ?></td>
                        <td><span class="label label-<?= $v_paid->label ?>"><?= $v_paid->method_name ?></span></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <small><?= lang('total_receipts') ?>: <strong id="total_receipts"></strong></small>
    </div>
</section>
```

- [ ] **Step 2: Verify**

```js
() => {
  const t = document.querySelector('#recently_paid_invoices_div table');
  return JSON.stringify({
    rows: t ? t.querySelectorAll('tbody tr').length : 0,
    headers: t ? [...t.querySelectorAll('th')].map(x => x.textContent.trim()) : [],
    total: (document.getElementById('total_receipts') || {}).textContent || 'EMPTY'
  });
}
```

Expected: headers `Invoice`, `Amount`, `Method`; rows = number of recent payments (up to 5); `total` populated by JS.

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\dashboard\recently_paid_invoices.php"
```

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/dashboard/recently_paid_invoices.php
git commit -m "feat: A2-15 dashboard - recently paid invoices as table with tag pills"
```

---

### Task 7: income_report bars

**Files:**
- Modify: `application/views/admin/dashboard/income_report_old.php`

Data: `$all_income` (month => value), `$year`, `$Income`, `$income_report_order` (gate), form ids `income_report_id` / `income_report_btn` (wired at `main_content.php:786-793`).

- [ ] **Step 1: Replace the canvas + Chart.js with div bars**

Keep the file's PHP header (lines 1-7: user_type guard + `$income_report_order`), the form, and `initdatepicker()`. Replace the `<canvas id="income" ...>` element and the whole `<script>` Chart block (lines 33-82) with:

```php
            <div class="bars">
                <?php
                if (!empty($all_income) && !empty($income_report_order)) {
                    $inc_max = 0;
                    foreach ($all_income as $v_inc) {
                        $inc_max = max($inc_max, (float) $v_inc);
                    }
                    foreach ($all_income as $v_inc) {
                        $inc_h = $inc_max > 0 ? max(6, round(((float) $v_inc / $inc_max) * 100)) : 6;
                        ?><i style="height:<?= $inc_h ?>%"></i><?php
                    }
                }
                ?>
            </div>
            <div class="xaxis">
                <?php if (!empty($all_income)) {
                    foreach ($all_income as $name => $v_inc) {
                        ?><span><?= date('M', strtotime($year . '-' . $name)) ?></span><?php
                    }
                } ?>
            </div>
```

Add the heading sub by changing the heading line to:

```php
            <h3 class="panel-title"><?= lang('income_report') ?><span class="sub">12-month trend</span></h3>
```

Strip `style="height: 437px;"` from the opening `.panel` div (becomes `<div class="panel panel-custom menu">`).

- [ ] **Step 2: Verify**

```js
() => {
  const b = document.querySelector('#income_report_div .bars');
  return JSON.stringify({ bars: b ? b.querySelectorAll('i').length : 0,
    months: document.querySelectorAll('#income_report_div .xaxis span').length,
    canvasGone: !document.querySelector('#income_report_div canvas') });
}
```

Expected: bars = months = 12 (or the count of months with data), `canvasGone: true`. Click `#income_report_btn` after changing the year → widget re-renders with no console errors.

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\dashboard\income_report_old.php"
```

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/dashboard/income_report_old.php
git commit -m "feat: A2-15 dashboard - income report as teal div bars"
```

---

### Task 8: expense_report bars

**Files:**
- Modify: `application/views/admin/dashboard/expense_report_old.php`

Data: `$all_expense` (month => value), `$year`, `$expense_report_order` (gate), form ids `expense_report_id` / `expense_report_btn` (wired at `main_content.php:795-802`).

- [ ] **Step 1: Same replacement as Task 7, red bars**

Replace the `<canvas id="buyers" ...>` element and the whole Chart `<script>` (lines 33-78) with:

```php
            <div class="bars">
                <?php
                if (!empty($all_expense) && !empty($expense_report_order)) {
                    $exp_max = 0;
                    foreach ($all_expense as $v_exp) {
                        $exp_max = max($exp_max, (float) $v_exp);
                    }
                    foreach ($all_expense as $v_exp) {
                        $exp_h = $exp_max > 0 ? max(6, round(((float) $v_exp / $exp_max) * 100)) : 6;
                        ?><i class="red" style="height:<?= $exp_h ?>%"></i><?php
                    }
                }
                ?>
            </div>
            <div class="xaxis">
                <?php if (!empty($all_expense)) {
                    foreach ($all_expense as $name => $v_exp) {
                        ?><span><?= date('M', strtotime($year . '-' . $name)) ?></span><?php
                    }
                } ?>
            </div>
```

Heading becomes:

```php
    <h3 class="panel-title"><?= lang('expense_report') ?><span class="sub">12-month trend</span></h3>
```

Strip `style="height: 437px;"` from the opening `.panel` div.

- [ ] **Step 2: Verify**

```js
() => {
  const b = document.querySelector('#expense_report_div .bars i');
  return JSON.stringify({ firstRed: b ? getComputedStyle(b).backgroundColor : null,
    canvasGone: !document.querySelector('#expense_report_div canvas') });
}
```

Expected: background `rgb(220, 38, 38)` (or rgba with .85 → still red-family), `canvasGone: true`. Year button re-render works.

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\dashboard\expense_report_old.php"
```

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/dashboard/expense_report_old.php
git commit -m "feat: A2-15 dashboard - expense report as red div bars"
```

---

### Task 9: payments_report bars

**Files:**
- Modify: `application/views/admin/partials/payments_report_old.php`

Data: `$yearly_overview` (12 values from `get_yearly_overview($yearly)`), `$yearly`, `$payments_report_order` (gate), form ids `payment_year_id` / `payments_report_btn` (wired at `main_content.php:768-775`).

- [ ] **Step 1: Replace canvas + Chart.js**

Replace the `<canvas id="yearly_report" ...>` element and the Chart `<script>` (lines 36-73) with:

```php
            <div class="bars" style="height:100px">
                <?php
                if (!empty($yearly_overview)) {
                    $pay_max = 0;
                    foreach ($yearly_overview as $v_pay) {
                        $pay_max = max($pay_max, (float) $v_pay);
                    }
                    foreach ($yearly_overview as $v_pay) {
                        $pay_h = $pay_max > 0 ? max(6, round(((float) $v_pay / $pay_max) * 100)) : 6;
                        ?><i style="height:<?= $pay_h ?>%"></i><?php
                    }
                }
                ?>
            </div>
            <div class="xaxis">
                <?php for ($i = 1; $i <= 12; $i++) {
                    ?><span><?= date('M', strtotime($yearly . '-' . $i)) ?></span><?php
                } ?>
            </div>
```

Heading becomes:

```php
            <h3 class="panel-title"><?= lang('payments_report') ?><span class="sub">12-month collections</span></h3>
```

Strip `style="height: 437px;"` from the opening `.panel` div.

- [ ] **Step 2: Verify**

```js
() => JSON.stringify({ bars: document.querySelectorAll('#payments_report_div .bars i').length,
  months: document.querySelectorAll('#payments_report_div .xaxis span').length,
  canvasGone: !document.querySelector('#payments_report_div canvas') })
```

Expected: 12 bars, 12 month labels, no canvas. Year search button re-renders cleanly.

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\partials\payments_report_old.php"
```

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/partials/payments_report_old.php
git commit -m "feat: A2-15 dashboard - payments report as teal div bars"
```

---

### Task 10: income_expense becomes stat pair + comparison bars

**Files:**
- Modify: `application/views/admin/dashboard/income_expense_old.php`

Data: `$income_expense['total_income']`, `$income_expense['total_expense']` (numeric totals for the selected month), `$month`, form ids `income_vs_expense_month_id` / `income_vs_expense_btn` (wired at `main_content.php:777-784`). The old donut had only these two numbers — the demo's grouped daily bars have no live source, so the widget shows the stat pair plus a two-bar comparison (same data, no invention).

- [ ] **Step 1: Replace the donut**

Keep the file's header (lines 1-7) and the `if (!empty($income_expense)) {` guard, the form, and `initdatepicker()`. Replace the `<div class="chart" id="sales-chart" ...>` element and the Morris.Donut `<script>` (lines 32, 41-67) with:

```php
            <div class="two">
                <div>
                    <div class="stat-lbl"><?= lang('income') ?></div>
                    <div class="stat-big" style="color:#0d9488"><?= display_money($income_expense['total_income'], default_currency()); ?></div>
                </div>
                <div>
                    <div class="stat-lbl"><?= lang('expense') ?></div>
                    <div class="stat-big" style="color:#dc2626"><?= display_money($income_expense['total_expense'], default_currency()); ?></div>
                </div>
            </div>
            <?php
            $ive_max = max((float) $income_expense['total_income'], (float) $income_expense['total_expense']);
            ?>
            <div class="bars pair" style="height:80px">
                <i style="height:<?= $ive_max > 0 ? max(6, round(((float) $income_expense['total_income'] / $ive_max) * 100)) : 6 ?>%"></i>
                <i class="red" style="height:<?= $ive_max > 0 ? max(6, round(((float) $income_expense['total_expense'] / $ive_max) * 100)) : 6 ?>%"></i>
            </div>
            <div class="legend">
                <span><i style="background:#0d9488"></i><?= lang('income') ?></span>
                <span><i style="background:#dc2626"></i><?= lang('expense') ?></span>
            </div>
```

Heading becomes:

```php
        <h3 class="panel-title"><?= lang('income_expense') ?><span class="sub">Selected month</span></h3>
```

Strip `style="height: 437px;"` from the opening `.panel` div.

- [ ] **Step 2: Verify**

```js
() => JSON.stringify({
  stats: document.querySelectorAll('#income_vs_expense_div .stat-big').length,
  bars: document.querySelectorAll('#income_vs_expense_div .bars.pair i').length,
  donutGone: !document.querySelector('#sales-chart')
})
```

Expected: 2 stats, 2 bars, `donutGone: true`. Month search button re-renders cleanly.

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\dashboard\income_expense_old.php"
```

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/dashboard/income_expense_old.php
git commit -m "feat: A2-15 dashboard - income vs expense as stat pair and comparison bars"
```

---

### Task 11: goal_report becomes purple tracks

**Files:**
- Modify: `application/views/admin/partials/goal_report.php`

Data: `$goal_report` rows (`target`, `amount_or_count`, `type_name` from `get_goal_report`), `$goal_month`, form ids `goal_month_id` / `goal_report_btn` (wired at `main_content.php:759-766`). The whole output sits inside the existing `if (!empty($goal_report_order))` guard — keep it. The demo's goal rows are genuine ratios (achievement/target), so percentage tracks are honest here.

- [ ] **Step 1: Replace Morris.Bar with track rows**

Keep the header (lines 1-24: where-clause, `$goal_report_order`, month input handling, `$goal_report = ...get_goal_report(...)`), the panel shell, the form, and `initdatepicker()`. Replace the `<div id="goal_report"></div>` element and the entire Morris `<script>` (lines 57, 67-105) with:

```php
            <div class="goal-list">
                <?php if (!empty($goal_report)) {
                    foreach ($goal_report as $v_goal_report) {
                        $g_pct = ($v_goal_report->target > 0) ? min(100, round(((float) $v_goal_report->amount_or_count / (float) $v_goal_report->target) * 100)) : 0;
                        ?>
                        <div class="goal">
                            <div class="gr">
                                <span><?= lang($v_goal_report->type_name) ?></span>
                                <b style="color:#6d28d9"><?= $g_pct ?>%</b>
                            </div>
                            <div class="track"><span style="width:<?= $g_pct ?>%;background:#8b5cf6"></span></div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <p class="text-muted text-center"><?= lang('no_result_found') ?></p>
                <?php } ?>
            </div>
```

Heading becomes:

```php
            <h3 class="panel-title"><?= lang('goal') . ' ' . lang('report') ?><span class="sub">Achievement vs target</span></h3>
```

Strip `style="height: 437px;overflow-y: scroll;"`-type attributes from the panel if present (this file's panel has none — verify).

- [ ] **Step 2: Verify**

```js
() => JSON.stringify({
  goals: document.querySelectorAll('#goal_report_div .goal').length,
  tracks: document.querySelectorAll('#goal_report_div .track > span').length,
  morrisGone: !document.querySelector('#goal_report svg')
})
```

Expected: goals = tracks = number of active goals (0+), no Morris SVG. Month search re-renders cleanly.

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -l "application\views\admin\partials\goal_report.php"
```

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/partials/goal_report.php
git commit -m "feat: A2-15 dashboard - goal report as purple achievement tracks"
```

---

### Task 12: Strip fixed heights + heading subs in the CSS-only partials

**Files:**
- Modify: `application/views/admin/dashboard/announcements.php`
- Modify: `application/views/admin/dashboard/active_timers.php`
- Modify: `application/views/admin/dashboard/recent_activities.php`
- Modify: `application/views/admin/dashboard/my_projects_data.php`
- Modify: `application/views/admin/partials/my_task.php`
- Modify: `application/views/admin/dashboard/to_do_list_data.php`

These widgets keep their existing body markup (tables/feed) and are skinned by the Task 2 CSS. Two mechanical edits each: remove the inline fixed height so cards size to content, and add a heading `.sub`.

- [ ] **Step 1: announcements.php**

- Line 1: `<div class="panel panel-custom menu" style="height: 437px;overflow-y: scroll;">` → `<div class="panel panel-custom menu">`
- Line 3: `<h3 class="panel-title"><?= lang('announcements') ?></h3>` → `<h3 class="panel-title"><?= lang('announcements') ?><span class="sub">Company-wide</span></h3>`

- [ ] **Step 2: active_timers.php**

- Line 2: remove ` style="height: 437px;overflow-y: scroll;"` from the panel div.
- Line 4: `<h3 class="panel-title"><?= lang('active_timers') ?></h3>` → add `<span class="sub">Tracking now</span>` before `</h3>`.

- [ ] **Step 3: recent_activities.php**

- Line 2: remove ` style="height: 437px;overflow-y: scroll;"`.
- Line 4: heading → add `<span class="sub">Latest updates</span>` before `</h3>`.

- [ ] **Step 4: my_projects_data.php**

- Line 1: remove ` style="height: 437px;overflow-y: scroll;"`.
- Line 3: heading → add `<span class="sub">In progress</span>` before `</h3>`.

- [ ] **Step 5: my_task.php**

- Line 9: remove ` style="height: 437px;overflow-y: scroll;"`.
- Line 11: `<h3 class="panel-title"><?= lang('my_tasks') ?></h3>` → `<h3 class="panel-title"><?= lang('my_tasks') ?><span class="sub">Assigned to me</span></h3>`

- [ ] **Step 6: to_do_list_data.php**

- Line 4: remove ` style="height: 437px;overflow-y: scroll;"`.
- Line 6-8 heading: insert the sub directly after the lang text:

```php
        <h3 class="panel-title"><?= lang('to_do') . ' ' . lang('list') ?><span class="sub">My tasks</span> |
            <a class="text-sm" target="_blank"
               href="<?= base_url() ?>admin/dashboard/all_todo"><?= lang('view_all') ?></a>
```

(The existing `pull-right` add button stays as-is.)

- [ ] **Step 7: Lint all six + verify in browser**

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
foreach ($f in @(
  "application\views\admin\dashboard\announcements.php",
  "application\views\admin\dashboard\active_timers.php",
  "application\views\admin\dashboard\recent_activities.php",
  "application\views\admin\dashboard\my_projects_data.php",
  "application\views\admin\partials\my_task.php",
  "application\views\admin\dashboard\to_do_list_data.php"
)) { & $php -l $f }
```

Expected: six `No syntax errors detected` lines.

Browser: no widget in `#menu` may carry an inline `height:437px`:

```js
() => [...document.querySelectorAll('#menu [style*="437"]')].length
```

Expected: `0`.

- [ ] **Step 8: Commit**

```bash
git add application/views/admin/dashboard/announcements.php application/views/admin/dashboard/active_timers.php application/views/admin/dashboard/recent_activities.php application/views/admin/dashboard/my_projects_data.php application/views/admin/partials/my_task.php application/views/admin/dashboard/to_do_list_data.php
git commit -m "feat: A2-15 dashboard - content-sized cards with heading subs"
```

---

#
**Review amendment (post-Task-12 review):** with real data, content-sizing made table-heavy cards unbounded (my_tasks 6499px, document 13k px, empty float-row gaps). Added to the A2-15 block in ``assets/css/app.css`` (EOF):  ```css /* A2-15: content cap - cards scroll internally instead of growing unbounded */ .dash-page .panel .table-responsive, .dash-page .panel .panel-body, .dash-page .panel .todo-scroll, .dash-page .panel .ann-scroll {   max-height: 440px;   overflow-y: auto; } ```  plus ``announcements.php`` wrapped its item blocks in ``<div class="ann-scroll">`` (per-item caps did not bound the 10-item stack) and its preview snippet now strips all tags (``true`` allowlist let an unclosed ``<strong>`` swallow following blocks), ``to_do_list_data.php`` gained ``class="todo-scroll"``, goal % clamped to ``max(0, min(100, ...))``, two stale Chart.js comments removed, and the CSS cache buster bumped to ``app.css?v=11``. ## Task 13: Full verification against the demo + gates

**Files:** none (verification only; fixes if needed are committed as `fix: A2-15 ...`)

- [ ] **Step 1: PHP lint everything edited**

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
foreach ($f in @(
  "application\views\admin\main_content.php",
  "application\views\admin\components\htmlheader.php",
  "application\views\login\lock_screen.php",
  "application\views\admin\dashboard\finance_overview.php",
  "application\views\admin\dashboard\recently_paid_invoices.php",
  "application\views\admin\dashboard\income_report_old.php",
  "application\views\admin\dashboard\expense_report_old.php",
  "application\views\admin\partials\payments_report_old.php",
  "application\views\admin\dashboard\income_expense_old.php",
  "application\views\admin\partials\goal_report.php",
  "application\views\admin\dashboard\announcements.php",
  "application\views\admin\dashboard\active_timers.php",
  "application\views\admin\dashboard\recent_activities.php",
  "application/views/admin/dashboard/my_projects_data.php",
  "application\views\admin\partials\my_task.php",
  "application\views\admin\dashboard\to_do_list_data.php"
)) { & $php -l $f }
```

Expected: 16 × `No syntax errors detected`.

- [ ] **Step 2: Run the repo gates**

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php plan\_tools\menu-group-test.php
& $php plan\_tools\smoke.php
& $php plan\_tools\login-test.php
```

Expected: `59`, `22`, `7` passing respectively (same counts as before this work — no regressions).

- [ ] **Step 3: Browser checks on live `/admin/dashboard`**

Login `forid`/`Admin@123`, open dashboard, wait for XHR quiet (~3s), then:

a) No new console errors (favicon 404 and pre-existing Pusher quota warnings are allowed):

```js
() => 'check console messages panel after this evaluate'
```

b) Geometry at 1920 CSS px (viewport width 1728 at DPR 0.9):

```js
() => {
  const g = document.querySelector('#report_menu .hero.g2').getBoundingClientRect();
  const g3 = document.querySelector('#report_menu .hero.g3').getBoundingClientRect();
  const kpi = document.querySelector('#report_menu .panel-kpi').getBoundingClientRect();
  const ov = document.querySelector('#menu .nav-tabs-custom');
  return JSON.stringify({
    heroesPaired: Math.abs(g.y - g3.y) < 2 && (g3.x - (g.x + g.width)) < 30,
    kpiFullWidth: Math.round(kpi.width) > 1400,
    overdueFull: Math.round(ov.closest('[id]').getBoundingClientRect().width) > 1400,
    noHScroll: document.documentElement.scrollWidth <= window.innerWidth + 1
  });
}
```

Expected: all true.

c) Block A untouched (computed styles still A2-14):

```js
() => {
  const c = document.querySelector('.kpi-card');
  const s = getComputedStyle(c);
  return JSON.stringify({ bg: s.backgroundColor, radius: s.borderRadius, shadow: s.boxShadow.slice(0, 30) });
}
```

Expected: `bg: rgb(255, 255, 255)`, `radius` `14px` (per `--r-lg`), non-`none` shadow. If these changed, a Task 2 rule leaked — fix by tightening scope, do not restyle Block A.

d) Hero values filled, tabs work, close buttons appear on hover (use `playwright_browser_hover` on a widget, then check `getComputedStyle(document.querySelector('#menu .close-btn')).opacity === '1'`).

e) Repeat the no-horizontal-scroll check at 768 (viewport 691) and 360 (viewport 324).

f) Cache buster: page source contains `app.css?v=9`.

- [ ] **Step 4: Visual diff vs the approved demo**

Open `http://localhost/complete_crm/admin/dashboard` and `_design/dashboard-modern.html` side by side (the demo is linked from companion screen history). Compare: hero gradients, KPI row, pill tabs, card radius/borders, bar charts, chips. Note any divergence and either fix it or record it as a justified deviation (no live data / structure preserved).

- [ ] **Step 5: Final commit (if fixes were needed) and push**

```bash
git add -A
git commit -m "fix: A2-15 dashboard - verification fixes"
git push origin sakib
```

(If no fixes were needed, skip the commit and just `git push origin sakib` for Tasks 1-12.)

---

## Self-Review (completed)

1. **Spec coverage:** heroes (T3 invoice/tickets, T5 finance) ✓; Block A unchanged (explicitly excluded from CSS, verified in T13.3c) ✓; white-card skin (T2 over `.panel` markup) ✓; overdue pills + chips (T2 CSS + T4) ✓; bar charts (T7-T10) ✓; goal tracks (T11) ✓; structure/order/guards/endpoints untouched (no controller or query edits anywhere in the plan) ✓; cache bust (T1) ✓; verification per spec section (T13) ✓.
2. **Placeholder scan:** every code step contains complete markup/CSS; all commands have expected outputs; the only "compare visually" step names the exact two URLs. No TBDs, no "similar to Task N" shortcuts (T8 spells out its code even though it mirrors T7).
3. **Type consistency:** element ids kept identical across rewrite/JS wiring — `today_invo_amount`, `payment_today`, `paid_invo_amount`, `client_outstanding`, `tasks_in_progress`, `tickets_open`, `bugs_in_progress`, `project_in_progress`, `completed_*`, `total_*`, `finance_overview_year/_btn`, `goal_month_id/goal_report_btn`, `payment_year_id/payments_report_btn`, `income_vs_expense_month_id/_btn`, `income_report_id/_btn`, `expense_report_id/_btn`, `total_receipts`. Class names introduced in T2 (`hero g1/g2/g3`, `h-grid`, `h-div`, `bars pair`, `goal/track`, `ft/foot-totals`, `sub`, `hero-top/hero-foot`) are used consistently in T3-T11. Selector `.menu` / `.report_menu` retained on hero roots so the existing jQuery UI sortable wiring keeps matching.
