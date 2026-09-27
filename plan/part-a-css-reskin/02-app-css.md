# Part A · A2 — Restyle `app.css`

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development
> (recommended) or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** restyle the 155 KB theme file section by section to the Refined dense direction.

**Why split into 12:** `app.css` is 8,193 lines in 31 sections. One commit for the whole file
would be unreviewable and unrevertable. Each task below is one section group, one commit.
Section line ranges are in `plan/_data/part-a-css-inventory.md` §1 (regenerate with
`php plan/_tools/css-inventory.php` after each task).

### Section → task map

Verified by extracting the selectors actually present in each section, not by name.

| Task | Sections (inventory §1) | Anchor selectors | Last-known lines |
|---|---|---|---|
| **A2-1** base/typography | 1 *(reset portion only)*, 2, 3, 4, 10, 11 *(spacing portion)*, 16, 17, 18 | `body`, `h1–h6`, `.wrapper`, `.m0/.mt0…`, `.box-placeholder` | 13–1372, 2285–3280, 3551–3637 |
| **A2-2** panels → cards | 19, **31** | `.panel`, `.panel-flat`, `.dd` | 3638–3848, 6631–8193 |
| **A2-3** buttons | 1 *(`.btn` only)*, **29** | `.btn` L159, `.btn-inverse` | 159–~230, 5780–6270 |
| **A2-4** forms/inputs | 20, 21, 22, 23, 24 | `.c-radio`, `.parsley-*`, `.tagator-*`, `.tabcontrol` | 3849–4817 |
| **A2-5** badges/labels | 1 *(`.label-*`)*, 11 *(labels/alerts)*, **14** | `.label-inverse` L3066, `.label-green`, `.circle-*` | 3066–3130, 3401–3497 |
| **A2-6** tables/charts | 25, 26, **27**, **28**, **30**, 1 *(`.progress` only)* | `.dataTables_*`, `.table`, `.ui-jqgrid`, `.easypiechart`, `.flot-chart`, `.progress` L251 | 4818–5779, 6271–6630 |
| **A2-7** sidebar | 5, 7, 8, 13 | `.sidebar`, `.offsidebar`, `.user-block` | 1301–1372, 1700–2081, 3313–3400 |
| **A2-8** top bar | 6 | `.topnavbar` | 1373–1699 |
| **A2-9** modals | 1 *(`.modal-backdrop`)*, 3 *(`.modal-open`)* | `.modal-backdrop` L581, `.modal-open` L705 | 581, 705 |
| **A2-10** tabs/nav/pagination | 1 *(`.nav-tabs`/`.nav-pills`)*, 9, 15 | `.nav-tabs` L130, `.breadcrumb`, `.dropdown-*` | 130–158, 2082–2113, 3498–3550 |
| **A2-11** alerts/toasts | 11 *(`.alert-*` region)* | `.alert-css` L3111, `.alert-purple` L3117 | 3111–3130 |
| **A2-12** print | 12 | `@media print` | 3281–3312 |
| **A2-14** KPI stat cards | new (20b) | `.kpi-grid`, `.kpi-card`, `.panel-kpi` | new section, 3936–4068 |

> **Section 1 is shared by six tasks** (A2-1, A2-3, A2-5, A2-6, A2-9, A2-10). They do not overlap
> *within* it — each owns a different selector — but they do overlap *in line number*. Because
> tasks run in order and each edit shifts later lines **every task must locate its target by
> selector, never by the line number in this table.** Treat the numbers as "it was around here"
> navigation hints only.

> **All 31 sections are assigned.** The three that were originally unmapped
> (`chart-easypie`, `chart-flot`, `nestable`) are folded into A2-6 and A2-2 so nothing is
> silently skipped.

**Universal rules for every task in this file:**
- Only edit `assets/css/app.css`. No other file.
- Every new value must come from a `var(--…)` token. No new hardcoded hex.
- Do not rename any class — markup in 885 views depends on the current names.
- **Soft UI depth and shape (locked 2026-09-27, supersedes the strict-flat rule of
  2026-09-26):** surfaces *do* cast a soft shadow, and radii are generous. Use the
  three-step scale, matched to how far a surface floats:
  - `--sh-1` — cards and controls resting on the page (`.panel`, buttons, inputs)
  - `--sh-2` — raised: hovered cards, icon chips, the active sidebar pill
  - `--sh-3` — overlays: dropdowns, modals, popovers, tooltips

  **Selective, not blanket.** Shadows on cards, controls and overlays only. Tables, table
  rows, list-group items, badges, pagination, nav-tabs, alerts and calendar cells stay
  flat — a dense data grid with a shadow per row is unreadable. Nested panels take **no**
  shadow (a 1px `--c-border` marks them as inset instead) so a card never stacks two
  elevations.

  Gradients and inset/embossed effects are still banned everywhere. The `bg-*.css` theme
  files carry 12 gradients each; those are A3's job to strip, not A2's.
- Radii: `--r-sm` 4px, `--r-md` 8px (buttons, inputs), `--r-lg` 12px (cards),
  `--r-xl` 16px (modals, large panels). Do not hardcode a `border-radius` in px.
- After each task: `composer smoke`, then re-screenshot the affected baseline pages, then commit.

---

## A2-1 — Base and typography

**Sections:** the first block (lines ~1–590) — reset, body, headings, links, fonts, helpers.

- [ ] **Step 1: Set the page background and base type**

```css
body {
  background: var(--c-bg);
  color: var(--c-text);
  font-size: var(--fs-base);
  line-height: var(--lh-normal);
  -webkit-font-smoothing: antialiased;
}
```

- [ ] **Step 2: Tighten the heading scale**

`h1`–`h6` currently use the default Bootstrap scale, which is too large for a dense UI:

```css
h1 { font-size: var(--fs-2xl); font-weight: 600; letter-spacing: -.01em; }
h2 { font-size: var(--fs-xl);  font-weight: 600; letter-spacing: -.01em; }
h3 { font-size: var(--fs-lg);  font-weight: 600; }
h4, h5, h6 { font-size: var(--fs-base); font-weight: 600; }
```

- [ ] **Step 3: Modernise link colour**

```css
a { color: var(--c-accent); text-decoration: none; transition: color var(--dur-fast) var(--ease); }
a:hover, a:focus { color: var(--c-accent-hover); text-decoration: underline; }
```

- [ ] **Step 4: Add a visible focus ring globally**

This is the single biggest accessibility improvement available without touching markup:

```css
a:focus-visible,
button:focus-visible,
input:focus-visible,
select:focus-visible,
textarea:focus-visible,
[tabindex]:focus-visible {
  outline: 2px solid var(--c-accent);
  outline-offset: 2px;
  border-radius: var(--r-sm);
}
```

- [ ] **Step 5: Replace `.text-muted` and muted text defaults with `--c-text-3`**
- [ ] **Step 6: Verify + commit**

```powershell
composer smoke
git add assets/css/app.css
git commit -m "A2-1: base and typography to Refined dense"
```

**Verify:** body background is `#f7f8fa`; headings visibly smaller; tabbing shows a focus ring.
**Effort:** M

---

## A2-2 — Panels to cards (visual only)

**Important:** Bootstrap 3 **class names stay**. `panel` is still `panel` in all 885 views. You
are changing only the visual treatment.

- [ ] **Step 1: Restyle the panel shell**

```css
.panel {
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: var(--r-lg);
  box-shadow: none;              /* superseded by A2-13 → box-shadow: var(--sh-1) */
  margin-bottom: var(--sp-5);
}

.panel-heading {
  padding: var(--sp-3) var(--sp-4);
  border-bottom: 1px solid var(--c-border);
  background: transparent;          /* was a grey gradient */
  border-top-left-radius: var(--r-lg);
  border-top-right-radius: var(--r-lg);
  font-weight: 600;
  font-size: var(--fs-base);
}

.panel-body { padding: var(--sp-4); }

.panel-footer {
  padding: var(--sp-3) var(--sp-4);
  border-top: 1px solid var(--c-border);
  background: var(--c-surface-2);
  border-bottom-left-radius: var(--r-lg);
  border-bottom-right-radius: var(--r-lg);
}
```

- [ ] **Step 2: Remove the gradient and heavy border from `.panel-default`**

- [ ] **Step 3: Restyle `.panel-custom`** (the first-party variant, 780 uses)

Give it the same shell but keep it visually distinct with a left accent edge:

```css
.panel-custom { border-left: 3px solid var(--c-accent); }
```

- [ ] **Step 4: Handle nested panels** — a `.panel` inside `.panel-body` should have
      `--r-md`, no outer shadow, and a lighter border so nesting reads clearly
- [ ] **Step 5: Nestable lists (`Component: nestable.less`, L6631–8193)**

      The largest single section in the file and the last thing assigned to this task. `.dd`
      trees render inside panels, so they take the card treatment: `.dd-handle` gets
      `--c-surface-2` + `--r-sm` + `--c-border`; `.dd-placeholder` dashed; `.dd-item`/
      `.dd-list` spacing from `--sp-*`. No drag behaviour changes — the plugin JS is untouched.

- [ ] **Step 6: Verify + commit**

```powershell
composer smoke
git commit -m "A2-2: panels to card-style surfaces; nestable lists tokenised"
```

**Verify:** dashboard and invoice detail show rounded, **border-only** cards — no gradients,
no shadows on surfaces (`box-shadow: none` on `.panel`), no doubled radii on nested panels. Any
page using a nestable list (settings, projects) keeps its drag-and-drop working with the new
surface.
**Effort:** M

---

## A2-3 — Buttons ⭐ highest visual impact

This single task does the most for "modern and attractive". Do it carefully.

- [ ] **Step 0: Kill the button gradients and embossing**

      The current button rules carry `background-image: linear-gradient(...)` on every variant
      (`Component: bootstrap-reset` region, ~5 gradient blocks) plus an inset shelf —
      `box-shadow: 0 -3px 0 #19a5d1 inset` on `.btn-inverse` and `box-shadow: 0 0 0 #000 inset`
      elsewhere. Delete the `background-image`/`filter: …gradient…` declarations outright (a
      `background:` shorthand in Steps 1/3 replaces them) and set `box-shadow: none` on every
      `.btn*` variant. `.btn-inverse` becomes a flat dark fill, not a bevel.

      **Then add the Soft UI shadow back** (A2-13 rule): `.btn` gets `box-shadow: var(--sh-1)`
      and `box-shadow: var(--sh-2)` on `:hover`/`:focus`. Do **not** leave buttons flat —
      buttons are the one control class where Soft UI's elevation is most visible.

- [ ] **Step 1: Base button**

```css
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--sp-1);
  height: var(--h-control);
  padding: 0 var(--sp-3);
  font-size: var(--fs-base);
  font-weight: 500;
  line-height: 1;
  border-radius: var(--r-md);
  border: 1px solid transparent;
  transition: background-color var(--dur-fast) var(--ease),
              border-color var(--dur-fast) var(--ease),
              box-shadow var(--dur-fast) var(--ease);
  box-shadow: none;
}
```

- [ ] **Step 2: Sizes**

```css
.btn-lg    { height: 40px; padding: 0 var(--sp-4); font-size: var(--fs-lg); }
.btn-sm, .btn-xs { height: var(--h-control-sm); padding: 0 var(--sp-2); font-size: var(--fs-sm); }
```

> `btn-xs` still exists in the markup — restyle it, do not remove it. Part B renames it.

- [ ] **Step 3: Primary, default and the status variants**

```css
.btn-primary {
  background: var(--c-accent); border-color: var(--c-accent); color: var(--c-on-accent);
}
.btn-primary:hover, .btn-primary:focus {
  background: var(--c-accent-hover); border-color: var(--c-accent-hover);
}

.btn-default {
  background: var(--c-surface); border-color: var(--c-border-strong); color: var(--c-text);
}
.btn-default:hover { background: var(--c-surface-2); border-color: var(--c-text-3); }

.btn-success { background: var(--c-success); border-color: var(--c-success); color: var(--c-on-accent); }
.btn-danger  { background: var(--c-danger);  border-color: var(--c-danger);  color: var(--c-on-accent); }
.btn-warning { background: var(--c-warning); border-color: var(--c-warning); color: var(--c-on-accent); }
.btn-info    { background: var(--c-info);    border-color: var(--c-info);    color: var(--c-on-accent); }
```

- [ ] **Step 4: Outlines and subtle variants** — `.btn-default` as a subtle fill, plus an
      outline style for secondary actions:

```css
.btn-outline { background: transparent; border-color: var(--c-border-strong); color: var(--c-text-2); }
.btn-outline:hover { background: var(--c-surface-2); color: var(--c-text); }
```

- [ ] **Step 5: Icon buttons and the `.close` control**

```css
.btn .fa, .btn .fas { font-size: .9em; }
.close {
  opacity: .5; font-weight: 400; text-shadow: none;
  transition: opacity var(--dur-fast) var(--ease);
}
.close:hover { opacity: 1; }
```

- [ ] **Step 6: Verify + commit**

```powershell
composer smoke
git commit -m "A2-3: restyle button family to dense system"
```

**Verify:** all button states look correct on login, dashboard, and an invoice list.
**Effort:** M

---

## A2-4 — Forms and inputs

- [ ] **Step 1: Input, select, textarea base**

```css
.input-group-addon,
.form-control,
.form-control-static {
  height: var(--h-control);
  font-size: var(--fs-base);
  color: var(--c-text);
  background: var(--c-surface);
  border: 1px solid var(--c-border-strong);
  border-radius: var(--r-md);
  box-shadow: none;
  transition: border-color var(--dur-fast) var(--ease), box-shadow var(--dur-fast) var(--ease);
}

.form-control:focus, .input-group-addon:focus {
  border-color: var(--c-accent);
  box-shadow: 0 0 0 3px var(--c-accent-subtle);
  outline: none;
}
```

- [ ] **Step 2: Placeholder and disabled states**

```css
.form-control::placeholder { color: var(--c-text-3); }
.form-control[disabled], .form-control[readonly] {
  background: var(--c-surface-2); color: var(--c-text-3); cursor: not-allowed;
}
```

- [ ] **Step 3: Validation states** — map the existing `.has-error` / `.has-success` wrappers
      (BS3 style) to the token colours:

```css
.has-error .form-control     { border-color: var(--c-danger); }
.has-error .help-block        { color: var(--c-danger); }
.has-success .form-control    { border-color: var(--c-success); }
.has-success .help-block      { color: var(--c-success); }
```

- [ ] **Step 4: Labels and help text**

```css
label { font-weight: 500; color: var(--c-text-2); margin-bottom: var(--sp-1); }
.help-block { color: var(--c-text-3); font-size: var(--fs-sm); margin-top: var(--sp-1); }
```

- [ ] **Step 5: Checkboxes and radios** — restyle `.checkbox` / `.radio` / `.c-radio` only.
      **Do not restructure the markup** — Part B does that (see `SF-2`).

```css
.checkbox, .radio { margin: 0 var(--sp-4) var(--sp-2) 0; }
.checkbox label, .radio label { font-weight: 400; color: var(--c-text); }
input[type="checkbox"], input[type="radio"] { accent-color: var(--c-accent); }
```

- [ ] **Step 6: Select2 and datepicker** — restyle via their containers so the third-party
      widgets inherit the token look:

```css
.select2-container .select2-selection--single { height: var(--h-control); border-color: var(--c-border-strong); border-radius: var(--r-md); }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: calc(var(--h-control) - 2px); color: var(--c-text); }
```

- [ ] **Step 7: Verify + commit**

```powershell
composer smoke
git commit -m "A2-4: restyle form controls"
```

**Verify:** open a settings form and a create-invoice form. Fields align, focus ring is visible,
Select2 and datepicker match.
**Effort:** M

---

## A2-5 — Badges and labels

BS3 `.label-*` is used as a status badge in 476 places. Restyle in place.

```css
.label {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 2px var(--sp-2);
  font-size: var(--fs-sm); font-weight: 500; line-height: 1.5;
  border-radius: var(--r-full);
}

.label-primary   { background: var(--c-accent-subtle);   color: var(--c-accent-hover); }
.label-success   { background: var(--c-success-subtle);  color: var(--c-success); }
.label-info      { background: var(--c-info-subtle);     color: var(--c-info); }
.label-warning   { background: var(--c-warning-subtle);  color: var(--c-warning); }
.label-danger    { background: var(--c-danger-subtle);   color: var(--c-danger); }
.label-default, .label-inverse { background: var(--c-surface-3); color: var(--c-text-2); }
```

Soft backgrounds with strong text — the current solid saturated chips are the loudest dated
element in the app.

- [ ] **Step 1: Apply the block above**
- [ ] **Step 2: Verify + commit**

```powershell
composer smoke
git commit -m "A2-5: restyle status badges to soft chips"
```

**Verify:** invoice statuses and project states read as subtle chips, not saturated blocks.
**Effort:** S

---

## A2-6 — Tables and DataTables chrome

- [ ] **Step 0: Kill the progress-bar stripes (strict flat 2D)**

      Bootstrap's `.progress-striped` / `.progress-bar-striped` and this file's own
      `.progress-striped .progress-bar-*` blocks (section 1, ~5 gradient blocks incl. the
      vendor-prefixed variants) paint diagonal stripes — 138 of them on the dashboard alone.
      Set `background-image: none` on `.progress-striped .progress-bar` /
      `.progress-bar-striped` and drop the `background-size`/`animation` that only exist to
      move them. Also flatten Bootstrap's `.progress` well: `box-shadow: none` (it carries an
      inset shadow) and use `background: var(--c-surface-3)`.

- [ ] **Step 1: Table base**

```css
.table { border-color: var(--c-border); }
.table > thead > tr > th {
  background: var(--c-surface-2);
  border-bottom: 1px solid var(--c-border);
  color: var(--c-text-2);
  font-size: var(--fs-sm);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .03em;
  padding: var(--sp-2) var(--sp-3);
  white-space: nowrap;
}
.table > tbody > tr > td { padding: var(--sp-2) var(--sp-3); border-color: var(--c-border); }
.table-hover > tbody > tr:hover > td { background: var(--c-surface-2); }
.table-striped > tbody > tr:nth-of-type(odd) > td { background: var(--c-surface-2); }
```

- [ ] **Step 2: Row height** — set a consistent dense row height on `td`:

```css
.table > tbody > tr > td { height: var(--row-h); }
```

- [ ] **Step 3: DataTables controls** — the BS3 integration renders `col-xs-6` cells; keep the
      layout but restyle the chrome:

```css
.dataTables_wrapper .row { margin-bottom: var(--sp-3); }
.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select {
  height: var(--h-control); border-color: var(--c-border-strong);
  border-radius: var(--r-md); font-size: var(--fs-base);
}
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_filter label { color: var(--c-text-3); font-size: var(--fs-sm); }
```

> Do **not** change the `col-xs-*` classes — that is `CM-3` in Part B.

- [ ] **Step 4: Chart chrome** (`Component: chart-easypie.less` L5722–5779,
      `Component: chart-flot.less` L6271–6630)

      Sections 28 and 30 have no other owner, so they land here. Only the *chrome* changes:
      `.easypiechart span` label colour/size → `var(--fs-sm)` + `var(--c-text-2)`,
      `.flot-chart .legend > table tr td` swatch spacing → `var(--sp-*)`. Do **not** restyle
      the plotted series colours — those are set per-chart in JS and are a Part B concern.

- [ ] **Step 5: Verify + commit**

```powershell
composer smoke
git commit -m "A2-6: restyle tables, DataTables and chart chrome"
```

**Verify:** clients list, invoices list, and reports all show the dense table style; search,
length selector and pagination still work. Dashboard charts render with correct legends and no
label clipping.
**Effort:** M

---

## A2-7 — Sidebar

- [ ] **Step 1: Sidebar shell**

```css
.sidebar,
.left_side_bar { background: var(--c-sidebar-bg); border-right: 1px solid var(--c-sidebar-border); }

.sidebar .nav > li > a {
  color: var(--c-sidebar-text);
  padding: var(--sp-2) var(--sp-4);
  font-size: var(--fs-base);
  border-radius: 0 var(--r-md) var(--r-md) 0;
  transition: background-color var(--dur-fast) var(--ease), color var(--dur-fast) var(--ease);
}
.sidebar .nav > li > a:hover { background: var(--c-sidebar-bg-2); color: var(--c-sidebar-text-hi); }
.sidebar .nav > li.active > a,
.sidebar .nav > li > a.active {
  background: var(--c-sidebar-active);
  color: var(--c-sidebar-text-hi);
  font-weight: 500;
}
```

- [ ] **Step 2: Section headers** — `.nav-header` / `.sidebar-group` get uppercase micro-labels:

```css
.sidebar .nav-header {
  padding: var(--sp-4) var(--sp-4) var(--sp-1);
  font-size: var(--fs-xs); font-weight: 600; text-transform: uppercase;
  letter-spacing: .06em; color: var(--c-text-3);
}
```

- [ ] **Step 3: Icon alignment** — give nav icons a fixed width so labels line up:

```css
.sidebar .nav > li > a > .fa,
.sidebar .nav > li > a > .fas { width: 20px; text-align: center; margin-right: var(--sp-2); }
```

- [ ] **Step 4: Verify + commit**

```powershell
composer smoke
git commit -m "A2-7: restyle sidebar to dense dark system"
```

**Verify:** sidebar is dark, the active item is a clear teal pill, group headers read as
micro-labels, icons align in a column.
**Effort:** M

#### Database

Additive, applied by hand to `tic_crm` and `tic_crm_demo`. See
`application/migrations/1003_version_1003.php` for the canonical form. The CI
migrator is not a usable path here: `application/config/migration.php` pins
`migration_version = 643` while `tbl_migrations` already records 643 **and** 644
(one row each), and the library is only ever loaded by
`Admin_model::upgrade_database_silent()`. Applying 1003 through it would mean a
359-version jump, so the SQL below is applied manually per database instead.

```sql
ALTER TABLE `tbl_menu`
  ADD COLUMN `menu_group` VARCHAR(32) NOT NULL DEFAULT 'tools' AFTER `parent`;

UPDATE `tbl_menu` SET `menu_group` = 'crm'      WHERE `menu_id` IN (1,2,4,5,227,228,139,141);
UPDATE `tbl_menu` SET `menu_group` = 'projects' WHERE `menu_id` IN (6,54,55,56,57,58,233,234,269);
UPDATE `tbl_menu` SET `menu_group` = 'hr'       WHERE `menu_id` IN (70,72,73,85,100,101,102,105,209,210,211,212);
UPDATE `tbl_menu` SET `menu_group` = 'finance'  WHERE `menu_id` IN (12,29,75,89,150,213);
UPDATE `tbl_menu` SET `menu_group` = 'tools'    WHERE `menu_id` IN (24,25,26,42,110,235,236,248,263);
UPDATE `tbl_menu` SET `menu_group` = 'admin'    WHERE `menu_id` IN (243);
```

45 top-level rows; children inherit their parent's group at render time. Group
labels and order live in `application/config/menu_groups.php`.

Two side effects worth knowing when re-running this:

- The `tools` UPDATEs are no-ops on a database that has just had the column
  added, because the column `DEFAULT 'tools'` already gave every row that
  value. `ROW_COUNT()` will report 0 for that statement. Verify with a
  `SELECT`, not with affected-row counts.
- `tbl_menu.time` is `ON UPDATE CURRENT_TIMESTAMP`, so the 36 rows whose
  `menu_group` genuinely changes get their `time` refreshed as a side effect.
  The other 7 pre-existing columns are untouched.

Pre-change snapshots of the affected rows are in `tbl_menu_bak_a27` in both
`tic_crm` and `tic_crm_demo` (192 rows each, taken before the `ALTER`). Drop
them once this change is accepted.

---

## A2-8 — Top bar

```css
.navbar-default { background: var(--c-surface); border-bottom: 1px solid var(--c-border); min-height: var(--h-control); }
.navbar-default .navbar-nav > li > a { color: var(--c-text-2); transition: color var(--dur-fast) var(--ease); }
.navbar-default .navbar-nav > li > a:hover { color: var(--c-text); background: transparent; }
.navbar-default .navbar-brand { font-weight: 600; color: var(--c-text); height: auto; padding: var(--sp-3) var(--sp-4); }
.navbar-default .navbar-toggle { border-color: var(--c-border); border-radius: var(--r-md); }
```

- [ ] **Step 1: Apply the block above**
- [ ] **Step 2: Restyle the topbar's own background rules** — the current topbar is
      `rgba(2,53,60,.95)`; keep it dark or switch to white per the A2-1 direction. **Decision:**
      keep it dark (`--c-sidebar-bg`) so the sidebar and topbar read as one frame.
- [ ] **Step 3: Notification badge** — restyle `.label` inside the topbar to `--c-danger`
- [ ] **Step 4: Verify + commit**

```powershell
composer smoke
git commit -m "A2-8: restyle top bar"
```

**Verify:** topbar and sidebar form one continuous dark frame; navbar collapses correctly below
the Bootstrap 3 breakpoint.
**Effort:** S

---

## A2-9 — Modals

**Critical:** Bootstrap 3 modal **markup and JS stay exactly as they are**. Only the visuals change.

```css
.modal-content {
  border: none;
  border-radius: var(--r-lg);
  box-shadow: var(--sh-3);
  background: var(--c-surface);
}
.modal-header {
  padding: var(--sp-4);
  border-bottom: 1px solid var(--c-border);
  border-radius: var(--r-lg) var(--r-lg) 0 0;
}
.modal-title { font-size: var(--fs-lg); font-weight: 600; }
.modal-body { padding: var(--sp-4); }
.modal-footer {
  padding: var(--sp-3) var(--sp-4);
  border-top: 1px solid var(--c-border);
  background: var(--c-surface-2);
  border-radius: 0 0 var(--r-lg) var(--r-lg);
}
.modal-backdrop { background: var(--c-overlay); }
```

- [ ] **Step 1: Apply the block above**
- [ ] **Step 2: Verify modals still open and close** — this is the task most likely to break
      something, because `.modal` positioning comes from Bootstrap 3's own CSS, not `app.css`.
      If modals fail to open, **revert this task immediately**; it means a rule in `app.css`
      is overriding `.modal`'s `display` or `position`.
- [ ] **Step 3: Verify + commit**

```powershell
composer smoke
git commit -m "A2-9: restyle modals"
```

**Verify:** open 5 different modals (add client, add invoice payment, edit item, confirm delete,
a `_modal_` partial). Each opens centred, scrolls internally, and closes via ×, Cancel and
backdrop.
**Effort:** M

---

## A2-10 — Tabs, nav, pagination, breadcrumbs

- [ ] **Step 1: Tabs**

```css
.nav-tabs { border-bottom-color: var(--c-border); }
.nav-tabs > li > a {
  color: var(--c-text-2); border: none; border-bottom: 2px solid transparent;
  padding: var(--sp-3) var(--sp-4); font-weight: 500;
  transition: color var(--dur-fast) var(--ease), border-color var(--dur-fast) var(--ease);
}
.nav-tabs > li.active > a,
.nav-tabs > li:hover > a { color: var(--c-accent); border-bottom-color: var(--c-accent); background: transparent; }
.tab-content { padding: var(--sp-4) 0; }
```

- [ ] **Step 2: Sidebar-style vertical nav** (`.nav-stacked`, used by `tabs.php`)

```css
.nav-stacked > li > a { border-radius: var(--r-md); padding: var(--sp-2) var(--sp-3); color: var(--c-text-2); }
.nav-stacked > li.active > a { background: var(--c-accent-subtle); color: var(--c-accent-hover); font-weight: 500; }
```

- [ ] **Step 3: Pagination**

```css
.pagination > li > a, .pagination > li > span {
  color: var(--c-text-2); border-color: var(--c-border);
  padding: var(--sp-2) var(--sp-3); border-radius: var(--r-md); margin: 0 2px;
}
.pagination > .active > a { background: var(--c-accent); border-color: var(--c-accent); color: var(--c-on-accent); }
.pagination > li > a:hover { background: var(--c-surface-2); color: var(--c-text); }
```

- [ ] **Step 4: Breadcrumbs** (from `application/libraries/Breadcrumbs.php`)

```css
.breadcrumb { background: transparent; padding: 0; margin-bottom: var(--sp-2); font-size: var(--fs-sm); }
.breadcrumb > li > a { color: var(--c-text-3); }
.breadcrumb > li.active { color: var(--c-text-2); }
.breadcrumb li + li::before { content: "/"; color: var(--c-text-3); padding: 0 var(--sp-2); }
```

- [ ] **Step 5: Verify + commit**

```powershell
composer smoke
git commit -m "A2-10: restyle tabs, nav, pagination, breadcrumbs"
```

**Verify:** open a client detail page (tabs), a project detail (vertical nav), any list with
pagination, and any page with breadcrumbs.
**Effort:** M

---

## A2-11 — Alerts and toasts

- [ ] **Step 1: Alerts**

```css
.alert { border-radius: var(--r-md); border: 1px solid; padding: var(--sp-3) var(--sp-4); font-size: var(--fs-base); }
.alert-success { background: var(--c-success-subtle); border-color: var(--c-success-border); color: var(--c-success); }
.alert-info    { background: var(--c-info-subtle);    border-color: var(--c-info-border);    color: var(--c-info); }
.alert-warning { background: var(--c-warning-subtle); border-color: var(--c-warning-border); color: var(--c-warning); }
.alert-danger  { background: var(--c-danger-subtle);  border-color: var(--c-danger-border);  color: var(--c-danger); }
```

- [ ] **Step 2: Toastr** — restyle via its own CSS variables, **not** by editing
      `toastr.min.css`:

```css
.toast { border-radius: var(--r-md); box-shadow: var(--sh-2); font-size: var(--fs-base); }
.toast-success { background: var(--c-success); }
.toast-error   { background: var(--c-danger); }
.toast-warning { background: var(--c-warning); }
.toast-info    { background: var(--c-info); }
```

- [ ] **Step 3: Verify + commit**

```powershell
composer smoke
git commit -m "A2-11: restyle alerts and toasts"
```

**Verify:** trigger a Toastr message (save a setting) and a BS `.alert` (a form validation error).
**Effort:** S

---

## A2-12 — Print stylesheet

**Load-bearing:** 63 `window.print()` call sites depend on this.

- [ ] **Step 1: Create `assets/css/print.css`**

```css
/* TIC CRM — print styles. Used by 63 window.print() call sites. */
@media print {
  .sidebar, .left_side_bar, .navbar-default, .topbar,
  .footer, .hide-print, [class*="hidden-print"] { display: none !important; }

  body { background: #fff !important; color: #000 !important; font-size: 12pt; }
  .panel { border: 1px solid #999 !important; box-shadow: none !important; page-break-inside: avoid; }
  .panel-heading { background: #f0f0f0 !important; color: #000 !important; }
  .panel-body { padding: 8pt !important; }

  table { page-break-inside: auto; width: 100% !important; }
  tr    { page-break-inside: avoid; page-break-after: auto; }
  thead { display: table-header-group; }   /* repeat headers on every page */
  tfoot { display: table-footer-group; }

  a[href]::after { content: " (" attr(href) ")"; font-size: 9pt; color: #555; }
  .no-print-url a[href]::after { content: ""; }
}
```

- [ ] **Step 2: Link it in all 4 shells** — after `tokens.css`:

```html
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/print.css" media="print">
```

- [ ] **Step 3: Verify + commit**

```powershell
composer smoke
git commit -m "A2-12: add print stylesheet"
```

**Verify:** print-preview 5 of the 63 print pages — an invoice, a payslip, a report, a project
statement, a client list. Sidebar and topbar must be absent.
**Effort:** M

---

## A2-13 — Soft UI depth and shape (foundation)

**Do this before A2-3.** It rewrites the token scale that every later A2 task reads, and it
reverses the strict-flat rule of 2026-09-26. A2-2's panel shell is re-applied here rather
than edited in place, so this is the single commit that flips the whole surface treatment.

- [ ] **Step 1: Replace the shadow scale in `tokens.css`**

  Soft UI's measured values, not invented ones:

  ```css
  --sh-1: 0 1px 3px rgb(0 0 0 / 10%), 0 1px 2px -1px rgb(0 0 0 / 10%);  /* cards, controls */
  --sh-2: 0 4px 6px -1px rgb(0 0 0 / 10%), 0 2px 4px -2px rgb(0 0 0 / 10%);  /* raised */
  --sh-3: 0 10px 20px -3px rgb(0 0 0 / 10%), 0 4px 8px -4px rgb(0 0 0 / 10%);  /* overlays */
  ```

  The old `--sh-*` values were deliberately near-invisible (5–7% alpha) and must not survive.

- [ ] **Step 2: Soften the radii**

  `--r-md` 6px → **8px**, `--r-lg` 10px → **12px**, add `--r-xl: 16px`. Soft UI's card radius
  is 12px and its controls are 8px. Anything still hardcoding a `border-radius` in px in a
  rule this file owns should move to a token.

- [ ] **Step 3: Page background → `#f4f4f5`**

  Not just `tokens.css`. `bg-custom.php` is linked at `htmlheader.php:51`, *after*
  `tokens.css` at L37, so it wins — `--c-bg` is driven by the DB value `body_background`:

  ```sql
  UPDATE tic_crm.tbl_config     SET value = '#f4f4f5' WHERE config_key = 'body_background';
  UPDATE tic_crm_demo.tbl_config SET value = '#f4f4f5' WHERE config_key = 'body_background';
  ```

  (`value`, not `config_value` — see the schema note in `STATUS.md`.)

- [ ] **Step 4: Re-apply the panel shell as a Soft UI card**

  Re-run A2-2's Step 1 selectors with `border: 0` and `box-shadow: var(--sh-1)`. Nested
  panels keep **no** shadow and gain an explicit `1px solid var(--c-border)` so a card never
  stacks two elevations. `.panel-custom` and the `.panel-group .panel-flat` rules get the
  same treatment; the `box-shadow: 0 0 0 #000` no-shadow hacks become `box-shadow: none`.

- [ ] **Step 5: Verify + commit**

```powershell
composer smoke
composer test:login
composer test:theme
php -l assets/css/bg-custom.php
php plan/_tools/css-inventory.php > plan/_data/part-a-css-inventory.md
git commit -m "A2-13: Soft UI depth and shape — soft shadows, softer radii, #f4f4f5 page"
```

**Verify (computed styles, not eyeballing):** on `/admin/dashboard`, every `.panel` reports
`box-shadow` = the `--sh-1` pair and `border-top-width` = `0px`; nested `.panel .panel`
reports `box-shadow: none`; `getComputedStyle(body).backgroundColor` = `rgb(244, 244, 245)`.
**Effort:** S

---

## A2-14 — KPI stat cards (dashboard income/expense row)

**This is the seed of the frontend-wide redesign.** The legacy dashboard small-box pattern was a
single `.panel.report_menu` containing a `display: table` 2×2 grid split by 1px `.bb`/`.br`
dividers. Dividers are not a gutter: the four KPIs read as one slab, and at `col-sm-4` they were
squeezed into a third of the page. Replaces it with standalone Soft UI stat tiles.

- [x] **Step 1: New component in `app.css`** — section `Component: kpi-stat.less`

  ```css
  .kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:5px; padding:5px; }
  ```

  5px between cards **and** 5px on the grid's outer edge. Card internal padding is deliberately
  roomier (`14px 16px`) so the value and label stay readable. Tile: `--sh-1`, `--r-lg` 12px,
  `border: 0`, `display:flex`, icon + body. Icon is a 32px `--r-md` chip with a soft tint
  (`--c-*-subtle` bg, `--c-*` glyph) instead of a bare `fa-2x` glyph. Value `--fs-2xl` /
  `--c-text` / tabular numerals; label `--fs-sm` / `--c-text-3`; link `--c-accent` with a
  2px hover nudge. `.panel-kpi` makes the wrapper transparent so a card never stacks inside a card.

  Breakpoints: `≤991px` → 2 columns, `≤575px` → 1 column.

- [x] **Step 2: Markup in `main_content.php`**

  The `income_expenses_report` block only. The four AJAX target IDs are injected by
  `application/controllers/admin/Dashboard.php:223-226` and **must not be renamed**:
  `today_income`, `today_expense`, `total_income`, `total_expense`. The `.bb`/`.br` dividers
  and the nested `.row-table` wrappers are deleted.

- [x] **Step 3: Rebalance the `report=1` row**

  The four tiles need full width, and the `report=1` group was `col-sm-4 ×3` — an exact 12
  columns. Widening one member alone left a 456px hole beside the other two, so all three move:

  ```sql
  UPDATE tic_crm.tbl_dashboard     SET col = 'col-sm-12' WHERE name = 'income_expenses_report';
  UPDATE tic_crm.tbl_dashboard     SET col = 'col-sm-6'  WHERE name IN ('invoice_payment_report','ticket_tasks_report');
  -- same two statements against tic_crm_demo
  ```

  Result is 12 + 6 + 6 — KPIs get a full-width row, the other two reports share the row below.
  Note `order_no` is duplicated across `report` groups, so filter by `report=1`, not `order_no=1`.

- [x] **Step 4: Verify + commit**

**Verify (computed styles):** `grid` reports `display: grid`,
`grid-template-columns` 4 equal tracks, `gap: 5px`, `padding: 5px`; all four cards share one
`top` (single row) with a card-width + 5px pitch; card reports `--sh-1`, `12px`, `border 0px`,
`padding 14px 16px`; `.panel-kpi` reports transparent bg, `box-shadow: none`, `border 0px`;
all four IDs present and populated. `#report_menu` reports 12/6/6 at 1366/683/683px with no
horizontal overflow. Breakpoints confirmed 4-across / 2×2 / stacked at 1440 / 889 / 444px.
Console: 0 errors.
**Effort:** S

---

## Exit gate

- [ ] All 14 tasks committed individually
- [ ] `composer smoke` 20/20 after every task
- [ ] Zero hardcoded hex values added to `app.css` — all from tokens
- [ ] No class renamed
- [ ] Only `app.css` and `print.css` modified in this phase (plus the 4 shell `<link>` tags)
- [ ] Baseline screenshots re-captured for all 20 pages

## Tasks: 14 - Completed: 5 - Remaining: 9
