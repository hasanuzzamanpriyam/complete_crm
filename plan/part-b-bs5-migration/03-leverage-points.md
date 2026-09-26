# Phase 03 — Leverage Points

**Goal:** rewrite the shared generators so that ~900 of the 15,040 Bootstrap-3 occurrences
disappear at once.
**Gate to exit:** helper and shared-partial output is BS5-clean; crawler green.

This is the **lowest-effort, highest-leverage phase in the plan.** Do it before the codemod.

Why it matters: the bulk of the 15,040 occurrences is not spread evenly. It is concentrated in
a few generators that produce markup for hundreds of pages. Fixing a generator fixes its call
sites automatically.

> **Correction to an early assumption:** `message_box()` (545 call sites, 299 files) emits
> **zero** Bootstrap classes — it is a pure `toastr` wrapper reading
> `session->flashdata()`. It is **not** a migration risk and needs only visual restyling.

---

## LP-1 — Button family (`admin_helper.php`)

Rewrite the `btn_*` generators to emit BS5 markup. Call-site counts:

| Function | Line | Call sites | BS3 emitted |
|---|---|---|---|
| `btn_edit` | 122 | 101 | `btn-xs`, `data-toggle`, `data-placement` |
| `btn_delete` | 153 | 33 | `btn-xs` + 2 data attrs |
| `btn_view` | 222 | 28 | `btn-xs` |
| `btn_pdf` | ~200 | 23 | `btn-xs` |
| `btn_update` | ~132 | 16 | `btn-xs` |
| `btn_cancel` | ~140 | 16 | `btn-xs` |
| `btn_view_modal` | ~185 | 15 | `btn-xs` |
| `btn_add` | ~110 | 13 | `btn-xs` |
| `btn_edit_modal` | 144 | — | duplicate `data-toggle` key bug |
| `btn_banned_modal` | 150 | — | duplicate `data-toggle` key bug |

Changes per function:
- `btn-xs` → `btn-sm` (and apply the DS-5 size scale)
- `data-toggle` → `data-bs-toggle`, `data-placement` → `data-bs-placement`
- Tooltip markup → BS5 (`data-bs-toggle="tooltip"` needs initialization; the data API does not
  auto-init tooltips in BS5)
- **Fix the duplicate-key bug** in `btn_edit_modal`/`btn_banned_modal`: PHP silently keeps only
  the last `'data-toggle'`, so these have never had tooltips. Split into distinct attributes.
- **Fix `btn_delete`**: it builds `"btn $btn-danger btn-xs deleteBtn"` where `$btn` is `btn` or
  `text`. The `text` branch produces `btn text-danger`, which is not valid Bootstrap and
  renders broken today. Pick one and make it explicit.

**Verify:** grep the crawler HTML for `btn-xs` → 0. Spot-check 10 pages that use action buttons.
**Effort:** M

---

## LP-2 — Form field generators

| Function | Line | Call sites | BS3 emitted |
|---|---|---|---|
| `get_permission` | 1070 | **209** | `checkbox-inline`×3, `checkbox`×3, `form-group`, `img-circle` |
| `custom_form_Fields` | 734 | 33 | `form-group`×20, `input-group-addon`×3, `checkbox`×3 + 6 data attrs |
| `get_permission_modal` | 1191 | 13 | `checkbox`×3, `checkbox-inline`×3, `form-group` + 4 data attrs |
| `render_form_builder_field` | 3946 | 1 | `control-label`, `input-group-addon`, `form-group` |
| `get_form_field` | 4087 | 1 | `form-group`, `checkbox` |

- `form-group` → `mb-3` (or a project token class from DS-5)
- `input-group-addon` → `input-group-text`
- `checkbox`/`checkbox-inline` → `form-check` / `form-check-inline`
- `control-label` → `form-label`, and move it above the field per DS-5
- `img-circle` → `rounded-circle`

Note `get_permission` alone touches **209 pages** — the single densest generator in the codebase.

**Verify:** grep for `form-group` → count must drop by ~2,300 before the codemod even runs.
**Effort:** M

---

## LP-3 — `admin/common/tabs.php`

The record-detail tab pattern, used by clients, leads, projects, transactions, users, tasks,
bugs and opportunities. Referenced 6 times, fully recursive via `common/tab_view.php`.

Emits: `col-sm-2/10`, `nav nav-pills nav-stacked navbar-custom-nav`, `pull-right`,
`label label-inverse`, `tab-content`.

- `nav-pills` → underline tabs per DS-5
- `label label-inverse` → `badge`
- `pull-right` → `float-end`
- `nav-stacked` + `col-sm-2` layout → BS5 vertical nav

**Verify:** the 8 `*_details_tabs()` entity functions all render; counts in tab labels survive.
**Effort:** S

---

## LP-4 — Layout shells and chrome

| File | Change |
|---|---|
| `admin/components/htmlheader.php` | BS5 asset links, token link, drop RTL branch, fix `asset/` → `assets/` path bug on lines 98–99 |
| `admin/components/header.php` | BS5 navbar: `navbar-header` → `navbar-expand-*`, `nav navbar-nav` → `navbar-nav`, `hidden-xs` → `d-none`, `img-responsive` → `img-fluid` |
| `client/components/header.php` | same + 3 `data-toggle="dropdown"`, 3 `col-xs-4`, pull-left/right |
| `frontend/components/header.php` | same + `navbar-collapse collapse`, `pull-right` |
| `admin/components/sidebar.php` | collapsible groups, active pill |
| `admin/components/horizontal_sidebar.php` | horizontal variant |
| `admin/components/offsidebar.php` | BS5 offcanvas |
| `admin/components/footer.php` | script order + BS5 bundle |
| `admin/components/notifications.php` | badge markup |
| `admin/components/nav_pinned.php` | consolidate with `nav_pinned_2.php` (near-duplicate, 10 KB each) |
| `application/libraries/Breadcrumbs.php` | muted, chevron separators (1 instance — trivial) |
| `admin/_layout_main.php` | add landmark elements, load tokens |
| `client/_layout_main.php`, `frontend/_layout_main.php` | same |
| `booking/_layout_main.php` + `assets/css/booking.css` | independent surface — separate pass, but must use tokens |

**Verify:** every top-level layout renders in admin, client, frontend and booking.
**Effort:** M

---

## LP-5 — Modal system

4 admin shells (`_layout_modal`, `_lg`, `_large`, `_extra_lg`) + 3 client shells + **34
`_modal_*.php` partials**.

- BS5 modal markup and sizes
- Sticky header/footer per DS-5
- `data-dismiss` → `data-bs-dismiss` (400 occurrences project-wide, most flow from here)
- **Fix 20 `loaded.bs.modal` listeners** — this event **was removed in BS5** and will silently
  never fire. Rewrite as `shown.bs.modal`.
- **Fix 6 `removeData('bs.modal')` calls** — BS5 stores instances in an internal `Map`, so this
  silently stops clearing state and produces "cannot open a modal that is already open".
- Also check the 5 `hidden.bs.modal` and 1 `shown.bs.modal` listeners, and the 7 native-only
  `hide.bs.*` handlers (jQuery `.on('hide.bs.modal')` no longer fires).
- The dead workaround in `add_domain.php:960-961` (`$.fn.modal.Constructor.prototype.enforceFocus`)
  must be rewritten — BS5's bridge defines no `.Constructor`, so that condition is always false
  and the Select2 focus fix has silently stopped applying.

**Verify:** open 10 different modals across the app; each opens, closes and traps focus.
**Effort:** M

---

## LP-6 — Shared fragments and confirmations

- `admin/common/{activites,attachments,attachment_list,attachment_details,new_attachment,comments,comments_list,comments_reply,notes,tab_view}.php` — shared across entities
- `render_table()` / `make_datatables()` in `admin_helper.php` — the DataTables wrapper
- `admin/invoice/{payments_details,edit_payments}.php` is duplicated verbatim into
  `client/`, `purchase/` and `return_stock/` — **de-duplicate into one shared partial**
  rather than converting four copies
- Dead helpers with 0 call sites: `btn_approve`, `btn_reject`, `btn_print`, `btn_atndc_print`,
  `btn_delete_disable`, `btn_active`, `btn_excel`, `btn_save`, `btn_make_pdf`, `btn_edit_disable`,
  `get_html_tags` — delete now (`DB-3` tracks the rest)

**Verify:** `grep -c 'btn-xs' crawl output` → 0. Comments, attachments and notes render on
client, project, task, ticket and invoice detail pages.
**Effort:** M

---

## Exit gate

- [ ] All 29 BS3-emitting helpers in `admin_helper.php` emit BS5
- [ ] `message_box()` confirmed Bootstrap-free (no change needed beyond restyling)
- [ ] `tabs.php` + 8 `*_details_tabs()` render correctly
- [ ] 4 layout shells + 11 chrome partials converted
- [ ] 7 modal shells + 34 modal partials converted; `loaded.bs.modal` and `removeData` fixed
- [ ] Duplicated `payments_details` partial de-duplicated
- [ ] Occurrence count for `btn-xs`, `form-group`, `label-`, `control-label` each down by the
      expected amount **before** the codemod runs
- [ ] Crawler green, 0 new `php_errors`
