# Spreadsheet Redesign — Design Spec

**Date:** 2026-10-10
**Status:** Approved (approach screen A + full demo approved in session; scope decision A: redesign **+ client reachability fixes**, user decision 2026-10-10) — **amended 2026-10-10** after cascade-order verification (§6.1 CSS delivery moved to the module's head-component helper so it loads *after* `manage_style.css`)
**Reference demo:** `.superpowers/brainstorm/1827-1791618467/content/spreadsheet-full.html` (Design A — folder panel + card rows, selected by user)
**Pattern to follow:** Mailbox redesign (`docs/superpowers/specs/2026-10-03-mailbox-redesign-design.md`)

## 1. Context

**Dual-URL routing (verified live 2026-10-10):** `modules/spreadsheet/` resolves from **two URL styles**: the custom MX reroute `admin/<x>` (`application/third_party/MX/Router.php:111-114`) and the standard HMVC bare-module segment (`locate()` at `MX/Router.php:121-151` resolves `spreadsheet/...` directly). The module's own `admin_url()` override (`modules/spreadsheet/helpers/spreadsheet_helper.php:123-138`) emits `admin/...` URLs for staff and **bare** `spreadsheet/...` URLs for clients (`client_id()`), and `Spreadsheet::index()` (`controllers/Spreadsheet.php:16-23`) redirects client → `spreadsheet/manage`, staff → `admin/spreadsheet/manage`. Role-specific layout is picked inside `manage()` (`Spreadsheet.php:44-48`): `client/_layout_main` vs `admin/_layout_main`. Both layouts render `echo $subview` (admin `_layout_main.php:201`, client `_layout_main.php:27`) beneath `admin/components/htmlheader.php` (loaded at line 1 of **both** layouts).

```
admin/spreadsheet/manage/<tab>            (staff convention)
spreadsheet/manage/<tab>                  (client convention)
  └─ modules/spreadsheet/controllers/Spreadsheet.php::manage($tab)
       ├─ data: get_result(departments/clients/groups), get_staff_details('', 'array')   ← client 500 (below)
       ├─ $tab ∈ {my_folder (default, tree_my_folder()), share_folder (tree_my_folder_share())}
       ├─ modules/spreadsheet/views/manage.php   ← tab strip + $tab view + notifications settings modal
       │    └─ my_folder.php / share_folder.php  ← treetable #spreadsheet-advanced fed by
       │         html_entity_decode($folder_my_tree)   ← server-built <tr data-tt-id/-parent-id/-type/-name/-role> rows
       └─ layout → admin/components/htmlheader.php (both roles)
```

**Screens (single shared view set — no admin/client duplicates):** `manage.php` (shell), `my_folder.php` (context menu of **7** actions edit·delete·share·related·download·create file·create folder, popup-overlay action sheet with the same 7, 4 toolbar buttons → `#AddFileModal`/`#AddFolderModal`/`#ShareModal`/`#RelatedModal`, inline modals `AddFileModal`/`AddFolderModal`/`sharedetailModal`/`relateDetailModal`/`fsModal`, includes `RelatedModal` + `shareModal`), `share_folder.php` (columns **Name/Kind/Size**, context menu of **2** actions edit·view, `#AddFolderModal`), `new_file_view.php` + `share_file_view.php` (full-page editor/hash-view fragments closing `</body></html>`, `#luckysheet`, `SaveAsModal`, Upload/Share/Related controls), asset bootstrap via URI-gated `spreadsheet_add_head_component()` / `spreadsheet_load_js()` (`spreadsheet_helper.php:20-120`).

**Client reachability — broken today (verified live, both pre-existing):**
1. **Menu link 404:** `tbl_client_menu.menu_id=24` (`label=spreadsheet`) links to `client/spreadsheet` → no such controller/route (404). The intended convention is the bare module URL (per `index()` and the module `admin_url()` client branch).
2. **Module 500:** a logged-in client session gets `A Database Error Occurred — Unknown column 'tbl_users.created_by' in 'WHERE'` on both `admin/spreadsheet/manage` and `spreadsheet/manage`: `manage():32` → `admin_helper.php::get_staff_details():2543` → `apply_user_scope():2564` → `admin_helper.php:16,18` filters `tbl_users.created_by`. The column exists in **no migration** and no other table (`SHOW COLUMNS tbl_users` verified); super admins skip the clause via the `is_super_admin()` early return (`admin_helper.php:7-9`), which is why the admin session works (200). `tbl_client_role` has **no** grant row for menu 24 (granting is a per-client admin-UI feature; e2e fixtures seed it).

## 2. Goals

- Reskin all **4 screens** — My Folder, Share Folder, Editor chrome, Share-by-link view — plus context menu, action sheet, the 4 toolbar-triggered modals, share detail/related/settings modals, to **Design A (folder panel + card rows)**, for **both roles** (views are shared; role only selects layout).
- Replace the tab strip and the Expand-all/Collapse-all caption buttons with the demo's **folder panel roots + carets + breadcrumb** — same capability, Mailbox vocabulary.
- Drill-down navigation is **presentation-side only**: panel/folder navigation filters the already-rendered `data-tt-*` rows client-side. **Zero controller/model/query/endpoint changes** for the redesign.
- **Reachability fix R1:** migration sets `tbl_client_menu.menu_id=24` link to `spreadsheet` (bare, matching `index()`'s client branch and module `admin_url()` for clients).
- **Reachability fix R2:** `apply_user_scope()` drops the `tbl_users.created_by` conditions (lines 16, 18) — the column has never existed, so the filter never executed for any non-super-admin (it only produced fatal errors); the remaining filters (`is_super_admin = 0`, `role_id != 1 OR user_id = self`, `exclude_self` handling) stay as written. Super-admin path untouched (early return before the WHERE is built).
- Every existing control, form, `lang()` call, `href`, `name`, and JS behavior is preserved (restyle only, add none — demo's own note: "nothing removed, nothing added").
- Existing regression gates stay green; add a dedicated `test:spreadsheet` e2e gate covering **both sessions**.

## 3. Non-goals (out of scope)

- Luckysheet interior (toolbar/grid = plugin UI; only page chrome around it is reskinned — per scope A).
- New features: Starred/Trash panel items, quotas/storage bar, search, new endpoints, new modals, permission semantic changes (chips display existing `data-tt-role` / `hash_share.role` data only).
- The embedded tree-table on other modules (projects/proposals/estimates/invoices/expenses/leads keep today's styles — `spreadsheet_add_head_component():39-44` already URI-gates those to the old CSS).
- Refactoring the helper's hardcoded `admin/` redirects (e.g. `Spreadsheet.php:78`), deduplicating anything under `demo/modules/spreadsheet/` (frozen snapshot), non-English languages, mobile beyond the demo's desktop layout.

## 4. Design direction (Design A, from approved demo)

- **Folder panel (left of content)** — `.p-head` title + count badge; two roots `.f-item` **My Folder** / **Share Folder** with icons + counts (these replace the tabs — full page navigation between the two server views, same as tabs today); indented sub-folder carets `▸/▾` with counts (expand/collapse subtree = replaces the caption buttons); `.p-sep`; `.p-foot` with computed counts only (Files / Folders / Shared-with-me — derived from rendered rows, no quota data).
- **Breadcrumb `.crumb`** — root / current folder path; segments navigate back (drill-down up-navigation).
- **Toolbar `.toolbar`** — the **existing** per-screen trigger buttons restyled (My Folder: New file · New folder · Share · Related); right side `.chipstat` status ("N items"), display-only.
- **List card** — `.list-card` with `.lhead` + `.mrow` rows built from the existing server rows: `.ftile` type tile, `.fname` (name + secondary line: kind detail and "edited …" from existing `updated_at` data where the row provides it), `.pill` (Folder/Sheet/CSV/ODS), `.rel` (existing Related-to value; Share Folder rows may show permission from existing `data-tt-role`), `.kebab` opening the **same** context menu. Columns stay per-screen live: My Folder **Name/Kind/Related to**, Share Folder **Name/Kind/Size**. Rows keep drag/drop and right-click.
- **Context menu / action sheet** — same entries (My Folder: 7, Share Folder: edit·view), restyled `.ctx` look; kebab = new trigger for the existing menu, right-click behavior unchanged.
- **Modals** — the existing Bootstrap modal markup (same ids, forms, fields, `data-toggle`/`data-dismiss`) restyled as Design A cards (`.m-head`/`.m-body`/`.m-foot` look over `.modal-content`), including Add file, Add folder, Share detail (staff/clients tabs), Related, Related detail, Share-by-link select, notifications settings.
- **Editor chrome** — existing controls only: Upload link + file input, Share, Related (when `$id`), Save-as modal — restyled as a chrome bar above the untouched `#luckysheet`.
- **Share-by-link view** — existing `share_file_view.php` content (role-hidden form + `#luckysheet` + Save-as) wrapped in Design A card chrome (title block + actions area restyled around what exists).
- **Empty states** — `.empty`-style centered icon + hint where lists are empty.

## 5. Design system

- Global tokens from `assets/css/tokens.css` (already app-wide): `--c-accent #0d9488`, `--c-bg`, `--c-surface`, `--c-border-2`, `--c-text*`, `--c-slate`, `--r-*`, `--sh-1`.
- New file **`assets/css/spreadsheet_modern.css`** contains component styles adapted from the demo, mirroring `mailbox_modern.css`/`woocommerce_modern.css`.
- Classes to port (collision-safe where names overlap Bootstrap): `.panel`, `.p-head`, `.f-item`, `.cnt`, `.p-sep`, `.p-foot`, `.crumb`/`.sep`, `.toolbar`/`.btn.accent`/`.ghost`/`.spacer`, `.chipstat`, `.list-card`, `.lhead`, `.mrow`, `.ftile`, `.fname`, `.pill`, `.rel`, `.av`, `.kebab`, `.ctx` menu, `.empty`; modal styling is applied **to the existing Bootstrap modal structure** (`.modal-content` restyled, file scoped to spreadsheet pages only — safe because conditional delivery below).
- **Not ported:** demo `.screenbar` (demo navigation), `.hintbar`/`.notes` (demo annotations), `.shell`/`.sidebar` (app chrome already exists), `.overlay`/`.modal` custom markup (Bootstrap markup kept).

## 6. Integration architecture

1. **CSS delivery (amended 2026-10-10):** one guarded `<link>` emitted at the **end of `spreadsheet_add_head_component()`** (`modules/spreadsheet/helpers/spreadsheet_helper.php`, after the existing `manage_style.css`/Luckysheet links), guarded by `strpos($viewuri, '/spreadsheet') !== false`: `…assets/css/spreadsheet_modern.css?v=1`. The helper is called by every module screen (`manage.php`, `new_file_view.php`, `share_file_view.php`) — coverage identical to an `htmlheader` guard — but unlike an `htmlheader` link it loads **after** `manage_style.css` (which the helper echoes into the body, later in document order and therefore winning cascade ties), so the new file reliably overrides legacy styles. The `htmlheader` approach was rejected after verifying that body-emitted `manage_style.css` would beat an earlier `<head>` link. The guard also keeps the CSS off the other modules that call this helper (proposals/estimates/invoices/expenses/leads/projects embeds keep their current styles).
2. **View edits (true file set):** `modules/spreadsheet/views/manage.php`, `my_folder.php`, `share_folder.php`, `new_file_view.php`, `share_file_view.php`, `shareModal.php`, `RelatedModal.php`. Views keep every `lang()`, `form_open*` action, `href`, `name`, `id`, and PHP block verbatim; markup wrappers/classes change to Design A. **Controller and model: no redesign changes** — `tree_my_folder()`/`tree_my_folder_share()` row HTML stays as-is.
3. **JS:** `assets/js/manage.js` and `assets/js/context_menu.js` selector/markup adaptations **only where the new markup requires it** to keep current behaviors: right-click + kebab context menu, action dispatch (7/2 actions), popup-overlay sheet, drag/drop moves, Add/Share/Related modal wiring, `treetable` expand/collapse (caret + breadcrumb equivalents), double-click → editor/share view, and the client-side drill-down filter (folder row activation shows descendants from the already-rendered rows; breadcrumb/panel root restores). No network behavior added or removed.
4. **Panel navigation:** root clicks navigate to `manage/my_folder` / `manage/share_folder` (server pages, same as today's tabs); sub-folder carets + breadcrumb act on the rendered tree client-side.
5. **Reachability fixes:** R1 = one idempotent data migration (`UPDATE tbl_client_menu SET link='spreadsheet' WHERE menu_id=24 AND label='spreadsheet' AND link='client/spreadsheet'`); R2 = delete lines 16/18's `created_by` conditions in `apply_user_scope()` (signature kept; `$exclude_self` becomes a no-op clause removal, remaining filters unchanged). R2 rationale: the referenced column has never existed in the schema, so the clause could never execute successfully for anyone reaching it — removing it changes no working behavior, only converts the fatal error into the results of the filters that do exist; the super-admin early return is untouched. Both fixes are **pre-existing bug fixes**, independent of the visual work but required for the "both roles" goal (scope A, user decision 2026-10-10).
6. **Version bump:** `spreadsheet_modern.css?v=1`; bump on later fixes (WooCommerce/Mailbox `?v` caching lesson).

## 7. Testing & verification

- **New e2e gate:** `plan/_tools/spreadsheet-redesign-test.php` + `composer test:spreadsheet`:
  - **Admin session** (`forid`): 200 + `spreadsheet_modern.css` present on `admin/spreadsheet/manage/my_folder`, `admin/spreadsheet/manage/share_folder`, `admin/spreadsheet/new_file_view/<parent>/<id>` (fixture), `admin/spreadsheet/file_view_share/<hash>` (fixture).
  - **Client session** (`mailbox_client` / `ClientMail@123`): 200 + CSS present on bare `spreadsheet/manage/my_folder` and `spreadsheet/manage/share_folder` (proves R1+R2), `tbl_client_menu` row 24 link = `spreadsheet`, `tbl_client_role` grant seeded; also asserts the admin-prefixed URL still serves the client.
  - **Leak check:** a non-spreadsheet page (dashboard) does **not** load `spreadsheet_modern.css`, asserted on **both** sessions.
  - **Content markers:** `.list-card` + `.mrow` + fixture names; context menu items (7 on my folder, 2 on share folder); breadcrumb + panel roots present; no raw `lang(` artifacts; client response uses `client/_layout_main` content markers.
  - **Fixtures (idempotent, committed at `plan/_tools/spreadsheet-fixtures.sql`):** folder+file rows in `tbl_spreadsheet_my_folder` (ids `910001+`, parent chain covering nested drill-down), a `tbl_spreadsheet_hash_share` row (`910001`) for the share view, the `tbl_client_role` grant `(mailbox_client, menu 24)`.
- **Playwright pass:** 4 screens × admin and client: visual match to demo, root switching, folder caret drill-down + breadcrumb back, right-click/kebab menu, all 4 modals, editor chrome, share-by-link view, 0 console errors.
- **Existing gates:** `composer smoke`, `test:login`, `test:menu` green; `test:theme` pre-existing baseline failure unchanged (documented in WooCommerce verification).
- **Lint:** `php -l` on every edited PHP file (views + `admin_helper.php` + migration).
- **Reachability proof:** record before/after status codes (menu 404 / client 500 → 200) in the verification record.

## 8. Deviations from the demo (agreed)

| Demo | Implementation | Why |
|---|---|---|
| `.screenbar` screen switcher | Not implemented | Demo-only navigation; real navigation = panel roots + breadcrumbs |
| `.hintbar` / `.notes` annotation blocks | Not implemented | Demo scaffolding explaining the design, not product UI |
| Panel items Starred, Trash | Not implemented | No backend exists (Mailbox precedent: feature not invented) |
| `.p-foot` storage progress bar | Counts only (Files/Folders/Shared) | No quota/storage data exists; counts derive from rendered rows |
| Tab strip (`nav-tabs-custom`) | Folder panel roots + breadcrumb | User-approved layout A; same two server pages, same links |
| Expand-all / Collapse-all caption buttons | Panel carets + breadcrumb | Same capability (treetable expand/collapse), Mailbox vocabulary |
| Demo Share Folder column "Shared by" | Live column **Size** kept (Name/Kind/Size) | Columns unchanged rule; permission shown only where `data-tt-role` exists |
| Demo Share Folder toolbar (New file etc.) | No toolbar added | `share_folder.php` has no existing toolbar triggers — zero-add rule |
| Demo custom `.overlay`/`.modal` markup | Existing Bootstrap modal markup restyled in place | Keep ids/forms/`data-toggle` machinery; no re-plumbing |
| Demo editor chrome (back arrow, title, saved badge, download) | Existing editor controls only (Upload/Share/Related/Save-as) restyled | Zero-add rule; browser back + current flows stay |
| Demo share-view action buttons (Download/Open) | Existing `share_file_view.php` controls restyled as-is | Zero-add rule |
| "Both roles" claim | Requires reachability fixes R1+R2 (pre-existing bugs) | Scope decision A, user 2026-10-10 |
| Demo static rows/counts | Real data from existing tree queries; counts computed from rendered rows | Data layer untouched |

## 9. Open questions

None — layout approach A, full demo, and scope A (redesign + reachability fixes) approved in session on 2026-10-10.
