# Phase 01 — Safety Net

**Goal:** build the regression detection that the entire migration depends on.
**Gate to exit:** crawler runs clean against the current (pre-migration) codebase and a baseline
archive exists.

The project has **zero first-party tests**. All 78 `*Test.php` files belong to vendored
libraries. There is no `phpunit.xml`, no `composer` scripts, and no linters. Without this
phase, Phases 3–9 are unverifiable.

---

## SN-1 — Route inventory

Build the canonical list of every reachable page. Everything downstream consumes it.

1. Enumerate admin routes from `tbl_menu` where `status=1` and `link` is a real path
   (131 entries, plus 21 group headers to exclude).
2. Enumerate client-portal routes from `tbl_client_menu`.
3. Enumerate module routes from each `modules/*/install.php` menu inserts
   (complaints, contracts, jitsi, mailbox, spreadsheet, woocommerce, zoom).
4. Add public routes: `login`, `login/register`, `login/forgot_password`, `career`,
   `book-free-consultation`, `frontend/*`, `booking/*`, `payment/*`.
5. Add the detail/edit pages that menus do not link (e.g. `admin/user/user_list`,
   `admin/mark_attendance`, `admin/report/sales_report` — the real routes differ from the
   obvious guesses).
6. Write to `plan/_data/routes.json` as `[{method, path, auth, group}]`.

> Verified correct paths, to avoid the false 404s a naive guess produces:
> `admin/user/user_list` (not `employees/manage_employees`),
> `admin/mark_attendance` (not `attendance/mark_attendance`),
> `admin/report/sales_report` (not `reports/sales_report`),
> `admin/estimates` (not `estimate/manage_estimate`).

**Verify:** file exists, contains no path matching the known-bad list above.
**Effort:** S

---

## SN-2 — Crawler

`plan/_tools/crawl.php` — a CLI script that walks `routes.json` and records, per route:

| Field | Purpose |
|---|---|
| `status` | must be 200 |
| `php_errors` | regex for `Fatal error\|Parse error\|<b>Warning\|<b>Notice\|Uncaught` |
| `bytes` | detect truncated/blank renders |
| `title` | detect wrong-page renders |
| `sha256` | detect unintended content change between runs |
| `elapsed_ms` | catch pathological regressions from the codemod |

Log in as `forid` first and reuse the session cookie for all authenticated routes.
Use one batched DB update per module rather than per page — this runs often.

**Verify:** run against the current codebase. Every route must be 200 with zero `php_errors`.
Anything else is a **pre-existing** bug — record it in `plan/_data/baseline_known_issues.md`
before proceeding, so later phases are not blamed for it.
**Effort:** M

---

## SN-3 — Known-issue baseline

Some routes already misbehave. Capture them so the migration is judged against reality.

Already known — do not treat as regressions:
- `demo/login/auto_login/{admin,manager,employee}` → **HTTP 500**. `application/controllers/Login.php:352`
  calls `show_error()` because `demo_admin`/`demo_manager`/`demo_employee` are absent from the dump.
- `#trackerDownloadModal` renders with `class="modal fade in"`, covering the viewport at
  `z-index:1050` and intercepting clicks on the login button. The `×` hides it but does not set
  the localStorage flag (only `#tracker-download-dismiss` does), so it returns on every reload.
- Pusher WebSocket to `ws-ap2.pusher.com` fails offline (1 console warning). Harmless.
- `last_login` is written to the session but never persisted to `tbl_users.last_login`.
- `bg-custom.php:104,199` emit malformed CSS (`!important;;`, `solid<?=`).
- `btn_edit_modal` / `btn_banned_modal` declare `'data-toggle'` twice in one array; PHP keeps
  the last key, so their tooltips are silently discarded.
- `btn_delete` emits invalid `btn text-danger` when the text branch is taken.

**Verify:** `baseline_known_issues.md` exists and is referenced from the crawler output.
**Effort:** S

---

## SN-4 — Visual baseline

Full-page screenshots of ~40 representative pages, before any change:

- Login, register, forgot password
- Dashboard, super-admin dashboard
- One page per major module: clients, leads, invoices (list + detail), quotations, estimates,
  proposals, projects (list + detail), tasks, bugs, tickets, employees, attendance, payroll,
  timesync, accounting, reports ×3, settings, file manager, items, contracts, complaints,
  zoom, spreadsheet, knowledgebase, mail
- Client portal: dashboard, invoices, projects, tickets
- Public: career, book-free-consultation
- Every one of the 19 invoice templates (11 browser + 8 PDF) at 1280 px

Store in `plan/_baseline/`. Use the same viewport and font stack every run so diffs are
meaningful.

**Verify:** all screenshots render with no PHP error overlay.
**Effort:** M

---

## SN-5 — HTML golden archive

Per route, store the response body gzipped under `plan/_baseline/html/`. This catches the
codemod changing markup in ways screenshots cannot see (a wrong class silently dropped, a
`data-toggle` typo).

Add a diff tool `plan/_tools/htmldiff.php` that reports per-route:
- `changed` (expected during codemod, unexpected afterwards)
- `php_errors introduced`
- `bytes delta > 5%` (usually a silently dropped section)

**Verify:** `composer crawl` and `composer crawl:diff` are wired and documented.
**Effort:** M

---

## Exit gate

- [ ] `plan/_data/routes.json` exists and contains no known-bad paths
- [ ] `crawl.php` reports 0 unexpected failures on the current codebase
- [ ] `baseline_known_issues.md` documents the 7 pre-existing issues
- [ ] ~40 baseline screenshots captured
- [ ] HTML golden archive populated
- [ ] A baseline run record is committed so later runs can be diffed against it
