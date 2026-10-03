# Mailbox Redesign — Design Spec

**Date:** 2026-10-03
**Status:** Approved (design walkthrough approved in session; approach: in-place reskin) — **amended 2026-10-03** after live-routing discovery (§1 architecture corrected; §2 constraint relaxed to one additive controller block per user decision; §6/§7/§8 updated)
**Reference demo:** `.superpowers/brainstorm/1374-1790861074/content/mailbox-full.html` (Design A selected by user)
**Pattern to follow:** WooCommerce redesign (`docs/superpowers/specs/2026-10-01-woocommerce-redesign-implementation-design.md`)

## 1. Context

**Routing reality (discovered 2026-10-03 via `application/third_party/MX/Router.php:110-115` custom code):** any URL of the form `admin/<x>` where `modules/<x>/` exists is rerouted to the module — so **all `admin/mailbox/*` URLs execute `modules/mailbox/controllers/Mailbox.php` and render `modules/mailbox/views/*`**. `application/controllers/admin/Mailbox.php` and `application/views/admin/mailbox/*` are unreachable dead code (loaded only by that dead controller). The `/client/mailbox/*` prefix has no such reroute.

Admin rendering flow (live):

```
admin/mailbox/<action>
  └─ (MX custom reroute) modules/mailbox/controllers/Mailbox.php::index()
       ├─ modules/mailbox/views/mailbox.php      ← folder panel (col-2, server-rendered counts) + $view slot (col-10)
       │    └─ $view ∈ {local_mails, read_mail, compose_mail}
       │         local_mails.php serves ALL 5 folders (inbox/sent/draft/favourites/trash)
       └─ application/views/admin/_layout_main.php → admin/components/htmlheader.php
```

Client rendering flow (live — matches the originally assumed architecture):

```
client/mailbox/<action>
  └─ application/controllers/client/Mailbox.php::index()
       └─ views/client/mailbox/mailbox.php   ← folder panel + $view slot
            └─ $view ∈ {inbox, sent, draft, favourites, trash, read_mail, compose_mail}
```

Admin and client view sets are no longer near-identical duplicates: admin folder lists were a single AJAX/DataTables view (`local_mails.php` + row markup generated as PHP strings inside the module controller's `inboxList()`), while client folder views are per-folder server-rendered foreach loops. The existing drill-down flow (list → full-page read) already matches the chosen demo direction, so the redesign remains a presentation-layer change.

## 2. Goals

- Reskin all **7 screens** — Inbox, Sent, Drafts, Favourites, Trash, Read mail, Compose — to **Design A (list + full-page read)**, for **both admin and client**.
- Match the approved demo's look: same tokens, cards, folder panel, message rows, read header, compose fields.
- Zero behavior change except **one additive, query-safe block** in `modules/mailbox/controllers/Mailbox.php::index()` that passes the folder's message rows to the list view (user decision 2026-10-03 — required because admin rows were previously generated inside `inboxList()` and injected by AJAX). No existing controller/model/query/URL/form/redirect/flash/`lang()` logic is modified.
- Existing regression gates stay green; add a dedicated `test:mailbox` e2e gate.

## 3. Non-goals (out of scope)

- AJAX/modal compose (compose stays a full page — documented deviation from the demo).
- New endpoints, new features, DataTables redesign/search redesign (system-mail folders become server-rendered — see §6; the AJAX `inboxList`/`mailboxList` endpoints remain in place and untouched for remote/Gmail folders).
- Refactoring the inline trash-count query in `mailbox.php`, deduplicating admin/client views, or touching Postmaster/IMAP logic.
- Non-English languages, mobile-responsive work beyond the demo's desktop-first layout.

## 4. Design direction (Design A, from approved demo)

- **Folders card (left)** — icons + labels + counts (unread for Inbox, totals for Drafts/Favourites/Trash), teal active state with left indicator, storage footer.
- **List card (center)** — message rows: avatar, sender, subject, preview line, timestamp, unread dot, star, attachment/draft tags; toolbar with search pill + action buttons; status line ("N messages · Folder").
- **Read page** — full-page card: back link, action toolbar (star / mark unread / archive / delete), subject, sender block (avatar, name, email, when), body, attachment strip, reply footer (reply box + Forward + Send).
- **Compose page** — full-page card: To / Subject fields, formatting toolbar, body area, footer (Send, Save draft, Discard).
- **Empty states** — centered icon + title + hint (`.empty` pattern from demo).

## 5. Design system

- Global tokens from `assets/css/tokens.css` (already loaded app-wide): `--c-accent #0d9488`, `--c-bg`, `--c-surface`, `--c-border-2`, `--c-text*`, `--c-slate`, `--r-*`, `--sh-1`.
- New file **`assets/css/mailbox_modern.css`** contains component styles adapted from the demo (scoped where sensible to mailbox containers), mirroring how `woocommerce_modern.css` was built.
- Component classes to port: `.folders`, `.f-item`, `.f-foot`, `.list-card`, `.list-toolbar`, `.tsearch`, `.mrow`, `.udot`, `.star`, `.f-tag`, `.read-card`, `.read-head`, `.r-subj`, `.r-who`, `.read-body`, `.att-strip`, `.att-file`, `.read-foot`, `.reply-box`, compose `.c-field` / `.c-tools` / `.c-body` / `.c-foot`, `.empty`, plus the existing button styles (`.btn-accent`, `.btn-ghost`, `.tbtn`).

## 6. Integration architecture

1. **CSS delivery:** conditional `<link rel="stylesheet" ... mailbox_modern.css?v=1>` added once per role in `application/views/admin/_layout_main.php` and `application/views/client/_layout_main.php`, guarded by `strpos(uri_string(), 'mailbox') !== false` — same conditional pattern as the WooCommerce modal-layout link. Covers every mailbox screen (including fragments rendered into wrapper pages) with two lines total.
2. **View edits (true file set):**
   - **Admin** — `modules/mailbox/views/mailbox.php` (wrapper/folder panel), `modules/mailbox/views/local_mails.php` (all 5 folder lists, now server-rendered), `modules/mailbox/views/read_mail.php`, `modules/mailbox/views/compose_mail.php`; plus the single additive row-fetch block in `modules/mailbox/controllers/Mailbox.php::index()`.
   - **Client** — the per-folder set under `application/views/client/mailbox/` (10 files: wrapper, inbox, sent, draft, favourites, trash + 3 trash sublists, read_mail, compose_mail), edited directly (they are independent files, prefix-swapped variants of the dead admin set, not generated from module files).
   - Existing `lang()` keys, form `action`s, `href`s, `name`s, and PHP logic remain verbatim. Admin list rows keep every current control/URL (star toggle, `#parent_present`/`.child_present`, delete/restore buttons with their exact current hrefs, draft→compose link, attachments flag).
3. **Controls:** keep every existing control (checkbox/select-all, delete, restore, star, search inputs, pagination if present) — restyle only; add none.
4. **Admin server-rendered lists (user decision 2026-10-03):** `local_mails.php` renders Design A `.rows/.mrow` markup from a controller-passed `$local_messages` array (filters mirror `inboxList()` verbatim: `user_id`; trash → `deleted=Yes`; favourites → `favourites=1 AND deleted!=Yes`; else `mail_folder=$action AND deleted!=Yes AND smtp_host_name=system`; order `view_status DESC, message_time DESC`). The `list` global is no longer emitted, so the guarded DataTables bootstrap (`assets/plugins/dataTables/js/dataTables.php:188`) skips; the AJAX endpoints stay for remote folders.
5. **Flash messages:** `message_box()` → toastr behavior unchanged.
6. **Version bump:** `mailbox_modern.css?v=1`; bump on later fixes (lesson from WooCommerce `?v` caching).

## 7. Testing & verification

- **New e2e gate:** `plan/_tools/mailbox-redesign-test.php` + `composer test:mailbox`:
  - All folder routes return 200 with `mailbox_modern.css` present (admin + client): `admin/mailbox/index/{inbox,sent,draft,favourites,trash}` and client equivalents.
  - Read + compose routes return 200 with CSS present (exact URIs confirmed during planning).
  - A non-mailbox page (e.g., dashboard) does **not** load `mailbox_modern.css` (no leak); the client leak check runs on the **client** session.
  - Response bodies contain no raw lang tokens (e.g., `lang(` output artifacts) in rendered mailbox pages.
  - **Fixtures (idempotent, committed at `plan/_tools/mailbox-fixtures.sql`):** admin inbox row `inbox_id=900001` (`user_id=1`, `to=hostnate247@gmail.com`, `mail_folder=inbox`, `smtp_host_name=system`, subject `Mailbox redesign fixture`); client user `mailbox_client` / `ClientMail@123` (role 2, activated, `tbl_account_details`, `tbl_client_role` grants copied from user 13) + its inbox row `inbox_id=900002`.
  - **Two-session flow:** login as `forid` for all admin assertions, then a second login as `mailbox_client` for all client assertions (folders, read, compose) and the client leak check.
  - **Admin read assertion:** direct GET `admin/mailbox/index/read_inbox_mail/900001` (no SKIPs — server-rendered, independent of list HTML).
  - **Admin folder rows:** after server-render, inbox folder HTML must contain `.mrow` + the fixture subject + a `read_inbox_mail/900001` link.
- **Playwright pass:** all 7 screens × admin and client: visual match to demo, folder switching, read drill-down, compose render, 0 console errors.
- **Existing gates:** `composer smoke`, `test:login`, `test:menu` green; `test:theme` pre-existing baseline failure unchanged (documented in WooCommerce verification doc).
- **Lint:** `php -l` on every edited PHP file.

## 8. Deviations from the demo (agreed)

| Demo | Implementation | Why |
|---|---|---|
| Compose as modal | Compose stays full page (`compose_mail.php`) | Existing POST + redirect flow; modal needs new AJAX plumbing |
| Static folder counts | Real counts from existing queries | Data layer untouched |
| Design switcher pill bar | Not implemented | One approved direction |
| (n/a — routing) | Admin screens reskinned in `modules/mailbox/views/*`, not `application/views/admin/mailbox/*` | MX Router custom code reroutes `admin/mailbox/*` to the module; application admin views are dead code |
| AJAX DataTables folder lists (admin) | System-folder lists server-rendered from a controller-passed array; AJAX kept for remote/Gmail folders | User decision 2026-10-03 (fidelity + e2e testability); requires one additive controller block (§2) |
| Trash tabs + 3 sublists (admin) | Admin trash stays a single list (current live behavior — `inboxList('trash')`); tabs + sublists exist only on the client side | Admin module has no trash-sublist views; inventing them would add behavior |

## 9. Open questions

None — design and approach approved in session.
