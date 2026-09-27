# Header and Sidebar Redesign — Design

Date: 2026-09-27
Status: approved
Scope: admin top bar and left sidebar only
Tracked as: A2-7 (sidebar) and A2-8 (top bar) in the Part A CSS reskin

## Problem

The admin shell predates the Soft UI reskin. Three things are wrong:

1. **The sidebar is one flat list of 45 top-level items** (107 children), rendered
   from `tbl_menu` with no grouping. It is long and hard to scan.
2. **The top bar is visually inconsistent** with the rest of the app: a bare
   `fa-plus-circle` glyph for the primary action, a 20px title link, unstyled
   dropdowns, and default 3px button radii.
3. **The collapsed rail has no labels**, so 45 icons are unidentifiable.

The dashboard horizontal-clipping bug found during A2-14 investigation is fixed
separately and commits before this work (see "Ordering").

## Constraints

- **No framework change.** No AdminLTE/Metronic/Start Bootstrap, no Bootstrap 5
  yet — that stays in Part B.
- **Top bar: visual restyle only.** No behaviour, ID, href, handler or new JS.
  The magnifier still reveals the search overlay, the `+` dropdown stays a
  single-column list of the same 18 items, and every `data-toggle-state`
  handler is untouched.
- **All 14 themes must keep working.** Every colour reads a Soft UI token. No
  hex literals in the new rules.
- **PHP stays working.** All 18 `+` actions keep their current URLs and
  `data-toggle` modals.

## Decisions

| Decision | Choice | Why |
| --- | --- | --- |
| Navigation model | Keep left sidebar, improve it | Smallest change; every page works; themes keep applying |
| Top bar | Modern minimalist, white surface | Legible across all 14 themes |
| Sidebar grouping | New `menu_group` column | Admin-editable; does not break silently on reorder |
| `+` dropdown | Keep flat list, restyle rows | Top bar is visual-only |
| Route | CSS-first + targeted markup | Preserves collapse JS, awesomplete, themes |

## Token discipline

All new rules use `assets/css/tokens.css`. Sizes and colours are never literal.

| Need | Token | Value |
| --- | --- | --- |
| Sidebar surface | `--c-sidebar-bg` | `#14181c` |
| Sidebar text | `--c-sidebar-text` / `--c-sidebar-text-hi` | `#b6bdc6` / `#ffffff` |
| Sidebar active | `--c-sidebar-active` | `#0d9488` |
| Sidebar hairline | `--c-sidebar-border` | `#262c33` |
| Top-bar accent | `--c-accent` / `--c-on-accent` | `#0d9488` / `#ffffff` |
| Surface, field fill | `--c-surface` / `--c-surface-2` | `#ffffff` / `#f1f3f5` |
| Border, hairline | `--c-border` | `#e3e6ea` |
| Muted text | `--c-text-2` / `--c-text-3` | `#4a5058` / `#7a828c` |
| Radius | `--r-md` / `--r-lg` | `8px` / `12px` |
| Row height | `--row-h` | `40px` |
| Control height | `--h-control` / `--h-control-sm` | `34px` / `28px` |
| Type | `--fs-xs` / `--fs-base` | `11px` / `14px` |
| Elevation | `--sh-1` / `--sh-2` / `--sh-3` | cards / raised / overlays |

Two traps:

- **`--c-accent-subtle` (`#ecfdf9`) cannot be used on the sidebar.** The sidebar
  is dark, so a near-white mint fill would be wrong. The active pill derives
  from `--c-sidebar-active` instead.
- **`--c-overlay` is a 55% scrim for modals, not a hover tint.** Hover states
  derive from `--c-text` instead.

Where a value must be a transparent mix of a token (hover tint, active pill
fill), use `color-mix(in srgb, <token> N%, transparent)` and declare a plain
`rgba()` fallback on the line above it, so older engines degrade rather than
drop the declaration.

## Sidebar

### Geometry — unchanged contract

- 220px expanded, 70px collapsed rail.
- Every existing `.aside-collapsed` rule and the `section`/`footer` margin-left
  math stay as they are. Only colours, metrics and shape change.

### Grouping

Pinned items — the AJAX-filled `#nav_pinned_cont` and `#nav_pinned_cont_2`
lists — form a **Pinned** group at the top. Then labelled groups from the new
`menu_group` column:

| `menu_group` | Display label |
| --- | --- |
| `crm` | CRM & Clients |
| `projects` | Projects & Tasks |
| `hr` | HR & Payroll |
| `finance` | Finance & Billing |
| `tools` | Tools & Admin |
| `admin` | Super Admin |

Group **order lives in `application/config/menu_groups.php`**, not a column, so
no admin UI is needed to reorder. It is a single source of truth read by
`Menu.php`, `add_navigation.php` and `Navigation.php` via `config_item()`, and
by the verification test. Unknown or empty values fall through to `tools`.

### Item rows

- Height `--row-h` (40px), radius `--r-md` (8px), 16px icon in a fixed 20px
  slot, label `--fs-base` (14px) in `--c-sidebar-text`.
- Active: background `color-mix(in srgb, var(--c-sidebar-active) 16%, transparent)`,
  icon and label in `--c-sidebar-text-hi`, plus a 3px left bar in
  `--c-sidebar-active`.
- Hover: `color-mix(in srgb, var(--c-text) 6%, transparent)`.
- `--sp-4` (16px) gap between groups, 2px between items.
- No hard edges, no gradients.

### Group headers

`--fs-xs` (11px), uppercase, `letter-spacing: .04em`, `--c-sidebar-text` at 65%
opacity, with a hairline rule in `--c-sidebar-border`. Rendered only in the
expanded rail; hidden when collapsed.

### Collapsed rail

Icons centred. Label tooltip on hover, taken from the existing `title`
attribute that `buildMenu()` already emits — no new JS. Submenus fly out to the
right on hover. Tooltips use `--sh-3` per the elevation scale.

### Menu search

`#s-menu` restyled: `--h-control-sm` (28px) tall, `--r-md` radius,
`--c-sidebar-bg-2` fill, no border, 13px icon in `--c-sidebar-text`.

## Top bar

### Surface

56px tall, `--c-surface`, 1px hairline bottom border in `--c-border`, one soft
`--sh-2` shadow so it reads as floating over content. Accent colour appears
only on the active nav item and the `New` button.

### Left

- Hamburger — the existing `data-toggle-state="aside-collapsed"` is untouched.
- Brand logo, then the website title as `--fs-base` (14px) in `--c-text-3`
  instead of today's 20px link.

### Search — unchanged behaviour, restyled surface

The magnifier still toggles `.open` on `.navbar-form`, and ESC/dismiss still
behaves as today (`assets/js/app.js:1811-1863`). Only the overlay's appearance
changes: 55px tall, `--c-surface`, hairline bottom border, inner field
`--h-control` (34px) / `--r-md` radius / `--c-surface-2` fill, type selector as
a compact pill, `--sh-3` when open.

`#searchType` and `#all_search` are preserved so awesomplete keeps working.

### Right

- Language flag — icon only, no text, to save width.
- Notifications, count badge restyled to a soft pill in `--c-danger` with
  `--c-on-accent` text (or `--c-accent` if the count is informational).
- User avatar 28px circle + chevron; name `--fs-base`, hidden below 1200px.
- The existing notebook/calculator (`offsidebar`) toggle, kept.
- Icon buttons: 36px hit area, 16px icon, `--c-text-2`, hover
  `color-mix(in srgb, var(--c-text) 6%, transparent)`, radius `--r-md`.

### Primary action

The bare `fa-plus-circle` glyph becomes a soft accent pill — `--h-control` (34px)
tall, `--r-md` radius, `--c-accent` fill, `--c-on-accent` plus icon, with a
`New` label.

Its dropdown keeps the same single-column 18-item list and hrefs. Only the rows
change: `--row-h` (40px) tall, `--r-sm` inner radius, hover overlay, and
`max-height: 70vh` with scroll so 18 items do not run off screen.

## Data and markup changes

### Migration

New `application/migrations/1003_version_1003.php`, class
`Migration_Version_1003`, next after the existing 1002:

```sql
ALTER TABLE `tbl_menu`
  ADD COLUMN `menu_group` VARCHAR(32) NOT NULL DEFAULT 'tools' AFTER `parent`;
```

`application/config/migration.php` has `migration_version = 643` and `tic_crm`
has no `migrations` tracking table, yet 1002's `tbl_letter_variables` exists —
so this project's convention is author-a-migration-then-apply-the-SQL-by-hand.
Follow it: file for the record, statement applied to **both** `tic_crm` and
`tic_crm_demo`, and the statement recorded in the plan doc.

### Backfill

Set on the 45 top-level rows only — 45 ids, no duplicates, every live top-level
`menu_id` assigned. Children inherit their parent's group at render time.

| `menu_group` | `menu_id`s | count |
| --- | --- | --- |
| `crm` | 1, 2, 4, 5, 227, 228, 139, 141 | 8 |
| `projects` | 6, 54, 55, 56, 57, 58, 233, 234, 269 | 9 |
| `hr` | 70, 72, 85, 100, 101, 102, 105, 211, 212 | 9 |
| `finance` | 12, 29, 75, 89, 150, 210, 213 | 7 |
| `tools` | 24, 25, 26, 42, 73, 110, 209, 235, 236, 248, 263 | 11 |
| `admin` | 243 | 1 |

`client` (menu_id 4) sits at `sort` 13, between `tickets` and `departments`, and
belongs in `crm`.

### `application/config/menu_groups.php` — new

The canonical, ordered group map. CodeIgniter auto-loads
`application/config/*.php`, so it is available everywhere as
`config_item('menu_groups')` with no wiring — the same mechanism the app already
uses for `config_item('logo_or_icon')`, `config_item('layout-h')` and
`config_item('sidebar_theme')`.

```php
$config['menu_groups'] = [
    'crm'      => 'CRM & Clients',
    'projects' => 'Projects & Tasks',
    'hr'       => 'HR & Payroll',
    'finance'  => 'Finance & Billing',
    'tools'    => 'Tools & Admin',
    'admin'    => 'Super Admin',
];
```

Array order is the display order. The `tools` key is the documented fallback
for unknown values.

### `application/libraries/Menu.php`

`buildMenu()` gains an opt-in `$group` flag.

- Only `dynamicMenu()` passes it. `clientMenu()` and the horizontal
  (`layout-h`) layout render byte-identical to today.
- Group header is one non-link row:
  `<li class="sidebar-group-header">CRM &amp; Clients</li>`, which CSS hides
  when collapsed.
- Nothing else in the `<li>`/`<a>` markup changes, so `active_menu_id()`
  highlighting and the existing `title` attributes are untouched.
- `dynamicMenu()` checks for the `menu_group` column once per request. If it is
  absent, grouping is skipped and the flat menu renders.

### Admin UI

- `application/views/admin/menu/add_navigation.php` — add a "Group"
  `<select name="menu_group">` beside Parent, populated from
  `config_item('menu_groups')`.
- `application/controllers/admin/Navigation.php` — `save_navigation()` adds
  `'menu_group'` to its `array_from_post` list and rejects values not in the
  configured set.
- `application/views/admin/menu/manage_navigation.php` — add a "Group" column.

## Files

- `assets/css/app.css` — sidebar section, topnavbar section, group-header
  rules, collapsed-rail tooltip
- `assets/css/tokens.css` — only if a token is missing
- `application/config/menu_groups.php` — new, canonical ordered group map
- `application/libraries/Menu.php` — `$group` flag
- `application/migrations/1003_version_1003.php` — new
- `application/controllers/admin/Navigation.php` — whitelist `menu_group`
- `application/views/admin/menu/add_navigation.php` — Group select
- `application/views/admin/menu/manage_navigation.php` — Group column
- `application/views/admin/components/header.php` — title markup, `New` label
  only; no ID, href or handler changes
- `plan/_tools/menu-group-test.php` — new verification gate
- `plan/part-a-css-reskin/02-app-css.md`, `plan/STATUS.md`,
  `plan/_data/part-a-css-inventory.md` — progress and inventory

## Ordering

1. **Responsive fix** — already in flight and verified; 3 files, uncommitted.
   Commits first so the redesign sits on a clean base.
2. **A2-7 sidebar** — migration, backfill, `Menu.php`, admin UI, CSS.
3. **A2-8 top bar** — CSS, plus the title and `New` label in `header.php`.

Each is a separate commit pushed to `origin sakib`.

## Verification

### Gates per task

- `composer smoke` — 22/22
- `composer test:login` — 7/7
- `composer test:theme` — both modes
- `php -l` on every touched PHP file
- `php plan/_tools/menu-group-test.php` — new gate, Task 1 onward
- Regenerate `plan/_data/part-a-css-inventory.md`

### Browser checks

Computed geometry only — screenshots cannot be inspected.

- 6 widths (1920, 1366, 1024, 768, 600, 375) across the key routes, asserting
  `documentElement.scrollWidth === clientWidth` and zero *unreachable* overflow.
  Exclude scroll containers and `#loader-wrapper`, the known transient.
- Regression guards: `#report_menu` stays 12/6/6; KPI grid stays 4/2/1; table
  grids stay 4/2/1.
- New for this work:
  - sidebar 220px expanded / 70px collapsed
  - collapse toggle round-trips
  - group-header count equals non-empty group count
  - group headers hidden when collapsed
  - all 45 top-level items still present and linked
  - backfill covers exactly 45 distinct `menu_id`s, matching the live count
  - `+` dropdown still has all 18 links
  - magnifier still reveals the search overlay
  - `#all_search` still autocompletes
  - offsidebar open/close round-trips
- All 14 themes checked for readable contrast on the sidebar and top bar.

## Failure handling

- Unknown or empty `menu_group` → falls through to `tools`; never renders an
  unlabelled group.
- `dynamicMenu()` checks for the `menu_group` column once per request. If it is
  missing, grouping is skipped and today's flat menu renders — so an unmigrated
  database degrades instead of white-screening.
- The migration is additive with a `DEFAULT 'tools'`, so rolling back the commit
  leaves an unused column. The column is not dropped on rollback.

## Out of scope

- Bootstrap 5 migration — Part B.
- Theme chooser in the top bar.
- Pinned-menu drag interaction — reused as-is.
- Content-area redesign — continues under the existing Part A plan.
- One pre-existing 404 in the console on some list pages was observed during the
  responsive sweep and not yet attributed. It is not caused by this work and is
  tracked separately.
