# Spreadsheet redesign — verification notes

Date: 2026-10-13 · Branch `sakib` · Plan `docs/superpowers/plans/2026-10-10-spreadsheet-redesign.md` · Spec `docs/superpowers/specs/2026-10-10-spreadsheet-redesign-design.md`

Task 12 (final verification) record. Tasks 1–11 committed individually, two-stage
review (spec + code-quality) per task: `bc0b75f0` (T1) · `3d7f860d` + demo mirror
`f4cd878a` (T2) · `4dfa7ac6` (T3) · `c7036bf4` + review `b8b95dce` (T4) ·
`22e52480` + review `fa9ad73c` (T5) · `43c67aca` (T6) · `d014dd93` + review
`2b654635` (T7) · `face302a` + review `461b4ad5` (T8) · `7f1f64b5` (T11).
Review notes landed in the plan as `2ac69d58`, `755559cc`, `9c1027bf`, `2d800368`.

## 1. Summary

Design-A spreadsheet redesign shipped per spec `2026-10-10-spreadsheet-redesign-design.md`:
the module's views (`manage.php` shell + `my_folder`, `share_folder`, `new_file_view`,
`share_file_view`), the new `spreadsheet_modern.css`
stylesheet with URI-guarded delivery, and the new `spreadsheet_shell.js`
(panel roots, drill filter, breadcrumb, counts, kebab menu, empty state).
Scope was views + CSS + shell JS + delivery only — `Spreadsheet.php`,
`Spreadsheet_model.php`, `manage.js`, `context_menu.js`, `manage_style.css` and
`htmlheader.php` were frozen and untouched, except for three forced bug fixes
outside the redesign (R1 menu link + demo mirror, R2 scope filter, PHP 8 widget
guard — §5a). Work happened on branch `sakib`; plan tasks 1–12 are complete, each
gated by spec review and code-quality review, with review-driven fixes committed
in-place rather than batched at the end. All seven automated gates re-run for this
record: six green, `test:theme` still the pre-existing baseline FAIL (byte-identical
to the WooCommerce record's baseline). The Task 11 Playwright pass left eight
screenshots (repo root, untracked) documenting admin + client surfaces.

## 2. Automated gates (re-run 2026-10-13, exact output lines)

| Gate | Result (quoted from run) | Exit |
|---|---|---|
| `composer test:spreadsheet` | `15 assertions — 15 passed, 0 failed, 0 skipped` | 0 |
| `composer smoke` | `22 routes — 22 passed, 0 failed` | 0 |
| `composer test:login` | `7 assertions — 7 passed, 0 failed` | 0 |
| `composer test:menu` | `59 passed, 0 failed` | 0 |
| `composer test:mailbox` | `22 assertions — 22 passed, 0 failed, 0 skipped` | 0 |
| `composer test:woo` | `20 assertions — 19 passed, 0 failed, 1 skipped` | 0 |
| `composer test:theme` | `FAIL: mode normal: expected 25 !important, got 26; undefined vars: --c-accent, --c-sidebar-text-hi, --c-on-accent` | 1 |

- `test:theme` failure line is **byte-identical** to the baseline recorded at
  `2026-10-01-woocommerce-redesign-verification.md:22-24` (re-read while writing this
  record: `expected 25 !important, got 26` + the same three undefined vars).
  Unrelated to spreadsheet work — pre-existing, not fixed here.
- `test:woo` SKIP is the documented soft-skip: order-detail `note-box` needs live
  WooCommerce API data, which this environment cannot reach (see woo record).
- Task 10 lint sweep re-run: **11 files, 0 failures** (`php -l` over `admin_helper.php`,
  migration 645, `install.php`, `spreadsheet_helper.php`, the 5 views, the lang file,
  the gate) — no `chore: spreadsheet redesign lint pass` commit was needed.
- Task 9/10 produced **no code delta**: `plan/_tools/spreadsheet-redesign-test.php`
  still has its single commit (`4dfa7ac6`, RED) and the tracked tree was clean at
  record time — the gate went green on the Task 5–8 + review code with no test edit.

## 3. Screenshots (Task 11, untracked in repo root — `Get-ChildItem task11-*.png`)

| File | Proves |
|---|---|
| `task11-admin-myfolder.png` | Admin `/admin/spreadsheet/manage/my_folder`: panel roots with counts, Design-A toolbar (Add Files / Add Folder / Share / Related + `21 items` chip), list-card rows (tile · name · subline, KIND, RELATED TO, kebab), no tab strip / caption buttons |
| `task11-admin-sharefolder.png` | Share-folder root switched from the panel: shared rows with folder sublines, panel footer counts (Files 2 / Folders 2 / Shared 4), no create-folder action in this scope |
| `task11-modal-addfolder.png` | Drilled state (`My Folder / SS Redesign Fixture Folder`, child rows, nested empty folder `0 items`) with the **Add New Folder** modal opened from the context menu — card chrome, Close/Submit |
| `task11-modal-share.png` | **Add New Share** modal restyled wholesale by the `.modal-content` block — Staff/Client selects + Permission, unchanged markup (spec §6.2) |
| `task11-admin-editor.png` | Editor `ed-head` chrome (Upload/Share/Related + Close/Save As/Download/Save) sitting **in flow** above the Luckysheet canvas — the sheet no longer overlaps it |
| `task11-admin-sharelink.png` | Share-by-link view `ss-share-view` card (shared file name + Edit) above the read-only sheet |
| `task11-client-myfolder.png` | **Client** layout reached from the bare URL: client sidebar, no admin `set_clocking` bar, `SS Redesign Client Folder` row + client-scoped counts |
| `task11-client-sharefolder.png` | Client Share Folder shows the shared tree (`SS Redesign Shared Folder`) with panel counts Files 1 / Folders 1 / Shared 2 |

## 4. Reachability proof (before → after, live re-checked 2026-10-13)

| Surface | Broken before | Working after | Evidence |
|---|---|---|---|
| Client menu link | `tbl_client_menu.menu_id=24` → `client/spreadsheet` → **404** (no such route) | row now `link = 'spreadsheet'` in **both** `tic_crm` and `tic_crm_demo`; target `/spreadsheet` → 200 | R1 `3d7f860d` (migration 645 + `modules/spreadsheet/install.php`) + demo mirror `f4cd878a`; live `mysql` returns `tic_crm → spreadsheet`, `tic_crm_demo → spreadsheet`; stale `/client/spreadsheet` still answers 404 (nothing links to it) |
| Client bare manage | `/spreadsheet/manage` → **500** `A Database Error Occurred - Unknown column 'tbl_users.created_by' in 'WHERE'` | **200** | R2 `bc0b75f0` (drops the never-existing column filter in `apply_user_scope()`); live curl 200 |
| Admin-prefixed client URL | `/admin/spreadsheet/manage/my_folder` as client → **500** (same scope bug) | **200**, serves the **client** layout | R2 `bc0b75f0`; live curl 200, body markers `client/dashboard`, no `set_clocking` |
| `GET /spreadsheet` (client) | 307 → `admin/spreadsheet/manage` → rendered the **admin** layout, because `tbl_account_details.company` was `NULL` so `client_id()` was empty | 307 `Location: /spreadsheet/manage` → **200 client layout** | company fixture (`company='1'` for user 1790) + R2; live curl `-L` → `200 http://localhost/complete_crm/spreadsheet/manage` (305351 bytes; markers `client/dashboard, ss-css, ss-panel`) |
| `GET /spreadsheet` (admin) | — | 307 → `/admin/spreadsheet/manage` → **200 admin layout** | live curl `-L` → `200 http://localhost/complete_crm/admin/spreadsheet/manage` (452472 bytes; markers `admin/dashboard, set_clocking, ss-css, ss-panel`) |
| Editor `ed-head` | legacy inline `top:162px` on the fixed `#luckysheet` overlapped the new in-flow chrome by 26 px | in-flow above the sheet on both editor pages | `7f1f64b5` (`#luckysheet{top:auto !important}`); `task11-admin-editor.png` |
| Client kebab menu | `position:absolute` resolved against the client-layout `position:relative` column → menu pushed off-screen | `position:fixed` (frozen menu math is viewport-based) | `7f1f64b5`; admin side unaffected (verified in the same pass) |

## 5. Deviations / hardening

**(a) Three forced bug fixes** (all outside the redesign surface, all required to make
the module reachable at all):

- **R1 — menu link 404.** `tbl_client_menu.menu_id=24` linked `client/spreadsheet`;
  fixed to the bare module URL by the idempotent migration 645 +
  `modules/spreadsheet/install.php` (`3d7f860d`), and by the same installer edit in
  the `demo/modules/` mirror (`f4cd878a`, R1b). Data applied to both `tic_crm` and
  `tic_crm_demo` (repo has no `demo/modules` runtime conflict — `MODULES_PATH`
  resolves to the root `modules/`).
- **R2 — client 500 `Unknown column 'tbl_users.created_by'`.** `apply_user_scope()`
  dropped the two `created_by` conditions (`bc0b75f0`); the column never existed, so
  the filter only ever produced fatals. This one fix clears both the bare
  `/spreadsheet/manage` and the admin-prefixed client URL.
- **Third — PHP 8 fatal in `client/main_content.php:190`** (`77458846`): once
  `company=1` linked the client account, the recently-paid widget's undefined
  `$payment_method` assignment 500'd `/client/dashboard` and failed **both** e2e
  gates' leak checks.

**(b) Fixture design decisions.** The client fixture had to link `mailbox_client` to
`tbl_client.client_id=1` through `tbl_account_details.company` — the account shipped
with `company=NULL`, so `client_id()` was empty and `manage()` served the **admin**
layout (observed live via `index()`'s redirect); spec §7's client-layout requirement
is impossible without a non-empty `client_id`. Side effects audited before adoption:
the mailbox gate's layout choice is unconditional, only the mailbox test logs in as
`mailbox_client`, and the login redirect order is unchanged. Demo-side `.av`/`.pill`
rules kept from the demo HTML have no live markup counterparts (live rows use
`td.qcont` pill styling).

**(c) Review-driven fixes (committed per task, not deferred).**

- `b8b95dce` (T4): `#spreadsheet-advanced` → `table#spreadsheet-advanced` +
  `background:transparent` — the `table` prefix outranks `manage_style.css:1`'s
  (1,0,1) rule; without it the legacy `#f1f8ee` fill / `#839E99` border survived the
  redesign and **no marker gate could see it**. Also cache key `?v=1 → ?v=2`.
- `fa9ad73c` (T5): `.p-foot .row` flex + `.f-item .cnt:empty{display:none}` —
  Bootstrap `.row` negative margins/clearfix mangled the footer; the inactive root
  showed an empty count pill.
- `2b654635` (T7): `.upload_file_class.ed-head` + `position:static` — legacy
  `manage.css:2422` absolute positioning otherwise overlays the sheet.
- `461b4ad5` (T8): kebab deferral, fresh parent reads, debounce.
- `7f1f64b5` (T11 visual pass): `#luckysheet{top:auto !important}`, kebab
  `position:fixed`, cache key `?v=2 → ?v=3`.

**(d) Spec-vs-reality reconciliations** (behavior kept, spec prose corrected):

- `shareModal.php` / `RelatedModal.php` needed **no markup change** — restyled
  wholesale by the `.modal-content` block, so spec §6.2's candidate list was achieved
  without markup churn.
- Context menu renders **5 items on files / 6 on folders** — frozen
  `context_menu.js` strips one `li` per type from its 7-item base; spec's "7 items"
  means that base list.
- Toolbar = **4 buttons + chip, no gear/settings control** (plan-canonical), and the
  **chip counts the tree-wide total**, not the current folder (plan-canonical
  `renderCounts`).
- `mrow` marker added at **view level** (model untouched); `.panel`/`.btn`/`.crumb`
  selectors scoped for collision safety.
- R1b installer link fix applied to both `modules/` and the `demo/modules/` mirror.

**(e) Review minors backlog (all non-blocking, no action taken).**

*From plan item 4:* test `db()` should call `mysqli_report(MYSQLI_REPORT_OFF)` so a
MySQL-down hit takes the test's `fatal()` path instead of an uncaught exception
(plan-authored test hygiene); `client/main_content.php:185` null-`$invoices_info`
guard (pre-existing, not triggered by current data); CSS `.ss-sub` dead rule
(`spreadsheet_modern.css:133`) + unscoped `.kebab` absolute positioning
(`:146`) — left as-is, additions block is plan-canonical and no live node matches;
`strpos($viewuri,'/spreadsheet')` query-string false positive — accepted, tightening
to `/spreadsheet/` would risk bare-URL misses; gate `mrow` marker could tighten to
`<tr class="mrow right-menu-position"` (Task 9 candidate); `Spreadsheet_model.php:630`
appends `"<tbody>"` instead of `</tbody>` (frozen pre-existing quirk);
`manage.css` `.new_file_margin !important` −7px legacy (cosmetic on the editor head);
share-view name echo is a new echo site but same-privilege / same app pattern (info);
client-row View/Share click → `ReferenceError: requestGet is not defined`
(admin-theme global absent on the client layout; frozen `context_menu.js`, silent
no-op, click-time only — page loads clean); legacy menu clamp uses `innerWidth` →
~6 px under the scrollbar (frozen, cosmetic).

*From the Task 8 shell review:* shell perf micro-patterns (repeated DOM reads inside
handlers) — no behavior change, left as-is; `.ss-hidden-table` dead class (set by
`spreadsheet_shell.js:158`, no CSS rule anywhere under `modules/spreadsheet/assets/css/`);
`manage_js.php:28` unguarded `JSON.parse(response)`; JSON parse informational
(malformed payload would throw in that one handler only); drill delay is **450 ms**
(`spreadsheet_shell.js:288`, plan-canonical, deliberately under the OS dblclick
interval) against the 500 ms debounce window cited in review.

## 6. Follow-ups (non-blocking)

1. Panel-root drag-and-drop targets (drag currently resolves to row targets only).
2. Per-row `edited …` secondary line — needs `updated_at` in the row attrs, i.e. a
   data-layer change; out of scope for this redesign.
3. i18n: new UI strings are keyed for English only; translate the new keys for the
   other language packs when those packs are next touched.

---

**Commit:** `docs: spreadsheet redesign verification record`
