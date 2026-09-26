# Phase 02 — Vendor Bumps

**Goal:** get the third-party layer onto Bootstrap-5-compatible versions before touching
first-party markup.
**Gate to exit:** app runs on jQuery 3.7 + Bootstrap 5.3.3 with DataTables rendering correctly.

Bumping vendors first means the codemod in Phase 04 lands on a working target rather than
debugging markup and vendor bugs at the same time.

---

## VS-1 — Consolidate jQuery ⚠️ highest-risk single task

The runtime is **jQuery 1.9.1** (2013) loaded by `admin/components/htmlheader.php:96`. Four
competing copies also exist:

| File | Version |
|---|---|
| `assets/js/jquery.min.js` | **1.9.1** ← main runtime |
| `assets/plugins/fullcalendar/jquery.min.js` | 3.2.1 |
| `assets/plugins/ganttView/jquery-1.12.4.min.js` | 1.12.4 |
| `assets/plugins/install/jquery-2.2.4.min.js` | 2.2.4 |

Why this matters: **Bootstrap 5.3.3 still ships a jQuery bridge** — `defineJQueryPlugin`
registers Alert, Button, Carousel, Collapse, Dropdown, Modal, Offcanvas, Tooltip, Popover,
ScrollSpy, Tab and Toast when `window.jQuery` exists at registration. Load order is already
valid (jQuery in `htmlheader`, Bootstrap in `footer`), so `$('#x').modal('show')` keeps
working. But that bridge registers against *whatever jQuery is present* — 1.9.1 predates
several plugin expectations.

1. Install jQuery 3.7.1 as `assets/js/jquery-3.7.1.min.js`.
2. Point `htmlheader.php:96` at it. Keep the old file until the suite is green.
3. Remove the 3 duplicate copies; if a plugin genuinely needs an old jQuery, vendor its own
   copy under `assets/plugins/<name>/` rather than reintroducing a global conflict.
4. Audit the 18 `.DataTable(`, 82 `.select2(`, 33 `.datepicker(`, 16 `.summernote(`
   call sites for jQuery-3 removals: `$.isFunction`, `$.parseJSON`, `$.trim`,
   `$.isArray`, `$.type`, `size`/`andSelf`/`bind`/`delegate` shorthands,
   `.load()/.unload()/.error()` events, and `$.browser`.

**Verify:** crawler reports 0 new `php_errors`; login, dashboard, and one DataTable page each
render fully; browser console is clean.
**Rollback:** revert `htmlheader.php:96` and restore the file.
**Effort:** M

---

## VS-2 — Bootstrap 5.3.3

1. Add `assets/css/bootstrap-5.3.3.min.css` and `assets/js/bootstrap.bundle.min.js`.
2. Point `htmlheader.php:44` and `footer.php:14` at them.
3. **Do not delete Bootstrap 3 yet** — the PDF track still needs it (see VS-6).
4. Expect a broken-looking UI at this point. That is expected; Phase 03–05 fix markup.
5. Confirm the jQuery bridge registered: `typeof jQuery.fn.modal === 'function'` in the console.

**Verify:** that console check passes; BS5 CSS is the one being served.
**Effort:** S

---

## VS-3 — DataTables ⚠️ hard break

`assets/plugins/dataTables/` is **DataTables 1.10.10** with the Bootstrap 3 integration. Its
`dataTables.bootstrap.js` hardcodes three classes that **do not exist in BS5**:

- `col-xs-6` (no `xs` breakpoint in BS5) — the whole table layout collapses to unstyled stacking
- `form-inline` (removed)
- `input-sm` (now `form-control-sm`)

Actions:
1. Upgrade DataTables core to 1.11+ (1.14 recommended) and swap in the **Bootstrap 5**
   integration build.
2. Fix the 18 `.DataTable(` call sites — check for removed options and the
   `sDom`/`dom` string, which many of them customise with BS3-era column markup.
3. Re-point the 4 DataTables stylesheets in `htmlheader.php:76-79`.
4. Verify the responsive extension and ColReorder still initialise.

**Verify:** every list page using `render_table()`/`make_datatables()` shows header controls
side by side (not stacked), working pagination, search and column toggles.
**Effort:** M

---

## VS-4 — Bootstrap-3-era plugins needing bumps

| Plugin | Current | Target | Risk |
|---|---|---|---|
| `summernote` | unversioned 73 KB legacy build | 0.8.20 | Medium — 16 init sites, WYSIWYG is used in letters, email templates, descriptions |
| `bootstrap-select` | 1.12.3 | 1.14.3 | Low |
| `jasny-bootstrap` | 3.1.3 | 4.0.0 | Low — file inputs in uploads |
| `bootstrap-toggle` | 2.2.0 | 3.0.0 | Low — but note the path bug in `htmlheader.php:98-99` (`asset/`, not `assets/`) |
| `bootstrap-tagsinput` | 0.7.1 | 0.8.0 | Low |
| `bootstrap-slider` | 5.2.6 | 6.7.0 | Low |
| `bootstrap-colorpicker` | 2.4.0 | 3.2.0 | Low — 5 init sites |
| `x-editable` | 1.5.1 | 1.7.3 | Medium — inline editing on list pages |
| `select2-bootstrap` theme | 0.1.0-beta.4 | 1.x | Medium — theme carries 13 `form-group` rules that no longer apply |
| `chartist` / `morris` / `ganttView` | legacy | verify | Chart-only; CSS is not Bootstrap-dependent |
| `nestable` | legacy | 4.x | Low — 5 sites, drag ordering |
| `typeahead` (1.x) | legacy | 0.11 / bullseye | Medium — bloodhound API changed |
| `daterangepicker` | legacy | 3.x | Low — 7 sites |

Do these **one plugin per commit**, running the crawler between each. A combined bump makes
regressions untraceable.

**Verify:** per plugin, exercise 2–3 real pages that use it.
**Effort:** L

---

## VS-5 — Remove dead vendor CSS

Two dead stylesheets plus two empty references:

- `assets/css/frontent.css` — 113 bytes, zero references
- `assets/css/bg-new copy.css` — stray copy, not a valid `sidebar_theme` value
- `assets/css/app-rtl.css` / `app-rtl.min.css` — 245 KB + 218 KB. **RTL is unused**
  (`tbl_config.RTL=0`, 0 of 113 accounts `direction='rtl'`, all English). Delete unless
  Arabic/Hebrew is a stated future requirement. See `ROLLBACK.md` for the restore note.
- `bootstrap-rtl.css` / `.min.css` — same reasoning

**Verify:** crawler shows no missing-asset console errors after deletion.
**Effort:** S

---

## VS-6 — Freeze the PDF CSS track

Dompdf **2.0.x** is pinned by `composer.json` (`phenx/php-font-lib: >=0.5.4 <1.0.0`; Dompdf 3.0
requires `>=1.0.0`). It executes **no JavaScript** and its engine is CSS 2.1. Therefore:

1. Create `assets/css/bootstrap3-pdf.css` — a **frozen, minimal, documented** subset of
   Bootstrap 3: grid, typography, tables, `.btn`, `.label`, `.panel`. No JS-dependent component.
2. This file is the only stylesheet the 68 PDF templates may rely on.
3. **Explicitly forbid** migrating PDF templates to BS5 classes. Dompdf will silently render
   nothing.
4. Document the constraint in `plan/08-documents.md` and in a header comment in the file.

Track map:

| Track | Stylesheet | Consumers |
|---|---|---|
| **A — browser** | `bootstrap-5.3.3.min.css` + `tokens.css` | 633 admin + 82 client + 15 frontend + 11 browser invoices |
| **B — PDF** | `bootstrap3-pdf.css` (frozen BS3) | 60 `*_pdf.php` + 8 `sales/pdf/*` |

**Verify:** generate one PDF of each of the 19 invoice templates and confirm identical output
to the pre-change baseline.
**Effort:** M

---

## Exit gate

- [ ] jQuery 3.7.1 is the only global copy; 3 duplicates removed
- [ ] `jQuery.fn.modal` is a function (BS5 bridge registered)
- [ ] Bootstrap 5.3.3 served; BS3 still on disk for the PDF track
- [ ] DataTables ≥1.11 with the BS5 integration renders side-by-side controls
- [ ] Each plugin bumped and individually verified
- [ ] Dead CSS removed; RTL decision applied
- [ ] `bootstrap3-pdf.css` frozen and all 19 invoice PDFs render identically to baseline
- [ ] Crawler reports 0 new `php_errors`
