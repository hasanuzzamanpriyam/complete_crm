# Dashboard redesign - colour-forward demo, implemented in place

Date: 2026-09-28
Status: approved via `_design/dashboard-modern.html` (user: "good, implement the design")
Branch: sakib

## Goal

Give the live `admin/dashboard` the visual design shown in
`_design/dashboard-modern.html`: a modern, colour-forward skin on the
**existing** structure. The shell (A2-8b: dark sidebar, white bar beside
content, crumb, pill search) already matches the reference; this spec
carries the same language into the dashboard body.

## Decisions locked with the user

1. **Deliverable**: static target mockup first, then implement in the real
   page (chosen over restyling blind).
2. **Structure**: identical to the live page. Same sections, same order,
   same widgets, same close-buttons, same `tbl_dashboard` configurability.
   No schema change, no query change, no widget added or removed.
   "the structure will be same only the design layout will be different,
   like more modern version of existing ui".
3. **Coverage**: whole page, every widget a `user_type == 1` admin sees.
4. **Skin**: *Colour-forward* (chosen over Bento-match and Clean-minimal):
   dark gradient hero cards, saturated family chips, brighter bars.
5. **Colour scope**: *Top row = colour heroes* (chosen over one-hero-only
   and family-rails-everywhere).
6. **Block A stays as shipped**: the four KPI cards of
   `income_expenses_report` (today/total income, today/total expense)
   keep their current A2-14 `.kpi-card` design - "leave them as they are
   now its oky".

## The visual system

### Heroes (exactly three)

| Widget | Gradient | Content |
|---|---|---|
| `invoice_payment_report` | `--hero-2` `#111827 → #164e63` | 2x2 grid: invoice today, payment today (mint value), paid amount, due amount (amber value) + More-info links |
| `ticket_tasks_report` | `--hero-3` `#1c1917 → #3b2f52` | 2x2 grid: in-progress tasks, open tickets, in-progress bugs, in-progress projects + status chips row (overdue amber, review violet, week mint) |
| `finance_overview` | `--hero-1` `#0f172a → #134e4a` | annual income/expense pair, 12-bar year chart (translucent bars, mint highlight on max), profit chip |

Hero anatomy: 16px radius, 18px padding, white text, 10px uppercase
labels at 70% opacity, 26px/800 values, `rgba(255,255,255,.14)` divider
above the second row, chips `rgba(255,255,255,.14)` base.

### White cards (everything else)

`#fff`, 1px `#e3e6ea`, radius 16, shadow `0 1px 2px rgba(15,23,42,.06)`,
padding 16, grid gap 16 (A2-9 Comfortable tokens in `tokens.css`).
Card header: 15/700 title + 11px slate sub, optional right-side tag.
Hover reveals the close `x` and the drag `⠿` handle (current behaviour).

### Family palette (chips, dots, bars)

| Family | Colour | Used by |
|---|---|---|
| Finance | `#0d9488` teal | payments, income values, active tabs pill fill |
| Todo / invoices | `#06b6d4` cyan | todo tag + checks, invoice tags |
| Projects | `#f59e0b` amber | project progress bars, priority dots |
| Tasks | `#10b981` mint | timer running states, "open" counts |
| Goals / estimates | `#8b5cf6` purple | goal bars, transfer tags |
| Activities | `#64748b` slate | feed avatar fallback |
| Danger | `#dc2626` | overdue tags, expense bars/values |

No KPI rings anywhere: none of the values is a percentage - chips carry
the signal (carried forward from the 2026-09-27 spec's data-honesty
argument, which the user rejected as a document but this constraint
survives as part of the approved demo).

### Block A (unchanged)

`.kpi-grid` 4-up, gap 5, padding 5; `.kpi-card` flex, padding 14/16,
radius `--r-lg`, shadow `--sh-1`, 32px tinted icon, 24/600 value,
13px label, 12px accent link. Markup in `main_content.php:57-113` and CSS
in `app.css` `Component: kpi-stat.less` (4695-4826) are untouched.

## Structure mapping (live `tbl_dashboard` order - verified 2026-09-28)

Report row (`report=1`, `order_no ASC`):
1. `invoice_payment_report` col-sm-6 -> hero 2
2. `ticket_tasks_report` col-sm-6 -> hero 3
3. `income_expenses_report` col-sm-12 -> **unchanged KPI row**

Widget grid (`report=0`, `status=1`, `order_no ASC`; `my_calendar` has
no guard in the view and renders nothing):

| # | Widget | Col | Treatment |
|---|---|---|---|
| 1 | overdue_report | 12 | white card, pill tabs (active = teal fill), striped table, footer total chips |
| 2 | income_report | 6 | white card, year select, 12 teal bars + month axis |
| 3 | expense_report | 6 | white card, year select, 12 red bars |
| 4 | active_timers | 6 | white card, timer rows with priority dot + elapsed + stop/resume |
| 5 | my_project | 6 | white card, amber progress bars + due dates |
| 6 | recent_activities | 6 | white card, avatar-tile feed |
| 7 | income_expense | 6 | white card, income/expense stat pair + grouped teal/red bars |
| 8 | todo_list | 6 | white card, cyan checkboxes, done = strikethrough |
| 9 | announcements | 6 | white card, title/date/snippet items |
| 10 | my_tasks | 6 | white card, priority dots + due labels |
| 11 | payments_report | 6 | white card, 6 teal bars |
| 12 | finance_overview | 6 | **hero 1** |
| 13 | recently_paid_invoices | 6 | white card, 4-col table with method tags |
| 14 | goal_report | 6 | white card, purple progress bars |

## Implementation surface

- `assets/css/app.css` - new `A2-15 dashboard` block: hero system, white
  card shell, family tags, pill tabs, widget row variants (timer, feed,
  todo, goal, bars, table). Existing panel/row-table rules stay (other
  pages still use them); dashboard selectors are scoped to `.dashboard`.
- `application/views/admin/main_content.php` - replace the two legacy
  `.row-table` report blocks (B and C) with hero markup; add card
  header/sub structure to widgets where the AJAX partials do not already
  provide it. Block A markup untouched. `tbl_dashboard` queries, guards,
  order loops, `ins_data()` calls untouched.
- AJAX partials under `application/views/admin/dashboard/` and
  `application/views/admin/partials/` - restyle fragment markup where the
  widget body is server-rendered (overdue tables, goal_report,
  finance_overview, to-do, activities, announcements, my tasks/projects,
  timers, payments, recently paid). Data, permissions and endpoints
  unchanged.
- `application/views/admin/components/htmlheader.php` +
  `application/views/login/lock_screen.php` - cache-bust `app.css` to
  `v=9`.
- `assets/css/bg-custom.php` - only if a dashboard accent is overridden
  by the theme layer (expected: none; verify).

## Out of scope

- No change to `tbl_dashboard` rows, `Settings::save_dashboard()`, the
  settings/dashboard config UI, or client dashboard (`report=2/3`).
- No change to the 18 unconditional `ins_data()` boot calls, chart
  libraries, or endpoint payloads.
- No merging/reordering of widgets; no new widgets (stock gauge stays
  rejected); no sidebar-gradient customiser (rejected 2026-09-27).
- Block A stays as-is.

## Verification

1. PHP gates: `plan/_tools/menu-group-test.php` (59), `smoke.php` (22),
   `login-test.php` (7), plus `bg-custom-test.php` and
   `plan/_tools/css-inventory.php` if applicable.
2. Browser checks on live `/admin/dashboard` (forid/Admin@123):
   - geometry mirrors the demo (heroes side by side, KPI row full width,
     widget pairs 6+6, no horizontal overflow at 1920/768/360);
   - Block A computed styles identical to before the change;
   - widgets still populate (18 `ins_data` targets non-empty), tabs,
     close-buttons and drag still work;
   - zero new console errors.
3. Compare key sections against `_design/dashboard-modern.html` in the
   browser (same viewport).
4. Cache-bust confirmed (`app.css?v=9` in page source).
