# Mailbox redesign — verification notes

Date: 2026-10-10 · Branch `sakib` · Verification base `881493f7` (post-record follow-ups: `407a009f` star fix, `83912135` + this correction) · Plan `docs/superpowers/plans/2026-10-03-mailbox-redesign.md` · Spec `docs/superpowers/specs/2026-10-03-mailbox-redesign-design.md`

Task 9 (full verification) record. Tasks 1–8 committed individually:
`6bbcfd05` · `b4d5c886` · `a7bdb6b1` · `b6da678e` · `c77d09f4` · `621bc070` · `abb29fab` · `79e889dc` · `a689bbbd`,
plus review fixes `765b2282` · `5c3c7912` · `1b09f42a` · `ba79922a` · `4a8eee8e` · `53d88538` and the Task 8 review batch `4eb8bc7d` · `39404274`.

## 1. Automated gates (final run, 2026-10-10)

| Gate | Command | Result |
|---|---|---|
| smoke | `composer smoke` | `22 routes — 22 passed, 0 failed` — exit **0** |
| login | `composer test:login` | `7 assertions — 7 passed, 0 failed` — exit **0** |
| menu | `composer test:menu` | `59 passed, 0 failed` — exit **0** |
| mailbox | `composer test:mailbox` | `22 assertions — 22 passed, 0 failed, 0 skipped` — exit **0** |
| theme | `composer test:theme` | **FAIL — pre-existing baseline**, exit **1** |

Lint (Step 1): `php -l` on `application/views/admin/components/htmlheader.php`, `modules/mailbox/controllers/Mailbox.php`, and every `*.php` under `modules/mailbox/views` + `application/views/client/mailbox` (23 files) — every file reports `No syntax errors detected`.

**theme baseline-diff statement:** `FAIL: mode normal: expected 25 !important, got 26; undefined vars: --c-accent, --c-sidebar-text-hi, --c-on-accent` — byte-identical to the documented pre-existing baseline in `docs/superpowers/2026-10-01-woocommerce-redesign-verification.md` (lines 22–24), which notes it fails identically on a clean tree. Unrelated to mailbox work — not worse, not fixed here.

## 2. Playwright visual pass (live, both roles)

Console-error counts are per page load; "0" means zero errors observed (occasional Pusher WebSocket *warnings* are network noise, pre-existing).

| # | URL | Expectation | Result | Console errors |
|---|---|---|---|---|
| 1 | `admin/mailbox/index/inbox` | Design A folder panel (Inbox active) + rows w/ avatar/star + Compose + fixture row | **PASS** — `.f-item` ×5 (Inbox `on`), `.mrow` = fixture `Mailbox redesign fixture` with avatar initials + `.mb-star`, Compose link, `mailbox_modern.css` linked | 0 (1 Pusher warning) |
| 2 | `admin/mailbox/index/sent` | `To:` prefix; star for all rows | **PASS** — 2 rows, `.m-from` = `To: foridmondol1995@gmail.com` ×2, stars 2/2 | 0 |
| 3 | `admin/mailbox/index/draft` | Draft tag; subject opens compose | **PASS** (folder empty natively — verified against scratch row 900005): `.f-tag` Draft tag present; subject href → `compose/900005`; clicking it loads the compose card prefilled `FW: Scratch draft row`. Scratch removed afterwards | 0 (0 on draft list; 4 on compose = see §5.2) |
| 4 | `admin/mailbox/index/favourites` | Starred rows with amber stars (or `mb-empty`) | **PASS (view side)** — with scratch 900006 flagged `favourites=1`, row renders with `.mb-star.on`, computed color `rgb(245, 158, 11)` (amber); empty state `mb-empty` verified before flagging. **Star *action* broken — see §5.1** | 0 |
| 5 | `admin/mailbox/index/trash` | Single list (no tabs — spec §8) + restore + permanent-delete per row | **PASS** — `.trash-tabs`/`.ttab` = 0 (single list confirmed); scratch 900004 row carries `Restore` → `restore/remote_host/900004` and `Permanent Delete` → `delete_mail/remote_host/deleted/900004` | 0 |
| 6 | click inbox subject → `read_inbox_mail/900001` | Back, subject, sender/avatar, cc, body, attachment strip, favourite/view/reply/replyall/forward/delete tools | **PASS** — `read-card` ×1, Back, `.r-subj`, `.mb-av` = `MAILBOXFIXTURES`, `.who` = `From: Mailbox Fixtures <fixtures@tic.test>`, cc block, `.read-body` body; tools (title attrs): favourite, `Mark as unread`, Reply, Reply all, Forward, Delete. Attachment strip absent — fixture carries no attachments ("if any") | 0 |
| 7 | `admin/mailbox/index/compose` | compose card: To select2, subject, editor, dropzone, Draft/Send/Discard (`mb-compose`, `compose-card`) | **PASS** — `mb-compose` ×1, `compose-card` ×1, select2, subject, editor, `.dropzone` ×1, footer `Draft` / `Send` / `Discard` | **4** — all `Dropzone already attached`, pre-existing (§5.2) |
| 8 | `client/mailbox/index/inbox` | Same design under client sidebar | **PASS** — CSS linked, `.f-item` ×5 (Inbox `on`), 2 rows incl. fixture 900002, stars, Compose, `.list-card` border-style `solid` | 0 |
| 9 | `client/mailbox/index/trash` | Pill tabs (Inbox active) + sublist rows w/ restore/delete; tabs switch | **PASS** — `.trash-tabs` with `.ttab` Inbox `on`/Sent/Draft; sublist row (scratch 900007) carries `Restore` → `restore/inbox/900007` + `Permanent Delete` → `delete_mail/inbox/deleted/900007`; navigating `trash/sent` → Sent `on`, `trash/draft` → Draft `on` (200). Scratch removed afterwards | 0 |
| 10 | click client subject → `read_inbox_mail/900002` | Back/subject/body/attachments; favourite + reply/replyall/forward + delete (inbox reads) | **PASS** — `read-card`, Back, subject, `.read-body` seeded body; tools: favourite, Reply, Reply all, Forward, Delete. No attachment strip (fixture has none) | 0 |
| 11 | `admin/settings` | No mailbox CSS/markup (leak check) | **PASS** — no `mailbox_modern.css` link; `.list-card`/`.f-item`/`.mrow`/`.mb-compose`/`.read-card` = 0 | 0 |

### Interactions

| Interaction | Result |
|---|---|
| check-all toggles row checkboxes | **PASS** — `#parent_present` click: row box `false → true → false` |
| computed `border-style` of `.list-card` = `solid` (Task 2 token canary) | **PASS** — `solid` on admin inbox **and** client inbox (0.74px computed width) |
| starring reloads into Favourites | **ADMIN FAIL during pass (pre-existing, §5.1 — fixed later in `407a009f`)** — `added_favourites/900006/1` → HTTP 500 `A Database Error Occurred` `Error Number: 1064 … near 'SET favourites = 1' … UPDATE SET …` (missing table name). **CLIENT PASS** — star on non-fixture row 60 persisted `favourites=1` and redirected to inbox; row then renders amber in `client/mailbox/index/favourites`; flagged back to `favourites=0` after |
| admin trash restore | **PASS** — `restore/remote_host/900004` → `deleted: Yes → No` (SQL-verified), row leaves trash list (`mb-empty`) |
| admin trash permanent-delete | **PASS** — `delete_mail/remote_host/deleted/900004` → row gone (`COUNT(*) = 0`) |
| client trash tabs switch sublists | **PASS** — Inbox/Sent/Draft each render their own `.ttab on` |

**Mutation guardrails observed:** interactions ran only against seeded scratch rows (900004 trash, 900005 draft, 900006 star, 900007 client trash) — fixtures 900001/900002/900003 were never starred, restored, or deleted. All scratch rows removed after the pass.

**Fixture integrity (final, post-pass):**

```
mysql> SELECT inbox_id, favourites, view_status, deleted FROM tbl_inbox WHERE inbox_id IN (900001,900002);
inbox_id  favourites  view_status  deleted
900001    0           1            No
900002    0           1            No

mysql> SELECT sent_id FROM tbl_sent WHERE sent_id=900003;
sent_id
900003
```

Both fixtures `favourites=0, deleted=No` as required; `view_status=1` is the known harmless drift (reads during earlier passes; guardrail allows it). Scratch-id range 900004–900007: **0 rows**. `favourites=1` anywhere in `tbl_inbox`: **0 rows**.

## 3. Fixtures

`plan/_tools/mailbox-fixtures.sql` seeds:
- client user `mailbox_client` (uid **1790**, `mailbox_client@tic.test`) + account detail + **12** `tbl_client_role` menu grants;
- admin fixture inbox row **900001** (`to=hostnate247@gmail.com`, subject `Mailbox redesign fixture`);
- client fixture inbox row **900002** (`to=mailbox_client@tic.test`, subject `Mailbox redesign fixture (client)`);
- client fixture **sent** row **900003** (`tbl_sent`, subject `Mailbox redesign fixture (client sent)`).

Reapply (idempotent — every insert is guarded by `WHERE NOT EXISTS`):

```powershell
Get-Content plan\_tools\mailbox-fixtures.sql -Raw | mysql -u root tic_crm
```

## 4. Architecture & deviations

- **Routing (architecture finding):** `MX/Router.php` reroutes every `admin/mailbox/*` URL to `modules/mailbox/controllers/Mailbox.php` + `modules/mailbox/views/*`. `application/controllers/admin/Mailbox.php` + `application/views/admin/mailbox/*` are dead code and were deliberately left untouched. (`client/mailbox/*` routes conventionally to `application/controllers/client/Mailbox.php` — a separate, live controller.)
- **Server-render deviation:** additive `$local_messages` block in the module controller (`Mailbox.php:141-158`) + rows rendered in `local_mails.php`; DataTables AJAX dropped for the five system folders (endpoints `inboxList`/`mailboxList` kept for remote folders).
- **Admin trash = single list** — no tabs on `admin/mailbox/index/trash` (`.trash-tabs`/`.ttab` = 0); client trash keeps the pill tabs + 3 sublists.
- **Incidental fixes shipped:** client favourites view now loops `$favourites_mail` (was `$get_inbox_message` — pre-existing empty-list bug); client read attachment name link now points at `download_file` (old `base_url().attach_file` href opened the raw JSON and 404'd); client read drops its inline `<style>` + outer `<section>` (covered by the wrapper + `mailbox_modern.css`); admin sent rows now show a `To:` prefix (demo parity — legacy showed raw `from`).
- **Spec deviations actually shipped:** client read omits the mark-read/unread toggle (no `change_view` action exists in the client controller); client reply/replyall/forward tools render only on inbox reads (`$reply`) because client `compose/<id>/<status>` resolves messages from `tbl_inbox` only; client delete renders only on inbox reads and uses the **soft** `delete_inbox_mail` path (the single-id `delete_mail` branch is permanent — old view parity); the read page shows a single identity line (`From:` in reply mode / `To:` otherwise) where the old admin view showed From and To in reply mode; sent/draft reads show no delete/favourite tools (old view had none that worked); replyall/forward got a client compose-mode branch in Task 8 Step 2b (subject/to/body/attachment prefill; no cc field exists client-side).
- **Post-review hardening shipped with Task 7:** avatar initials escaped via `html_escape($ini)` in all 10 redesign views (stored-XSS fix — `from` is attacker-controlled); client read `from` access guarded (`!empty($reply) ? ($read_mail->from ?? '') : ''` — `tbl_sent`/`tbl_draft` have no `from` column); favourite-star toggle fixed to render (old `!empty($favs)` logic hid it in every real state) and inbox-guarded; gate grew to 19 assertions with the 2 sent-read fetches (client `tbl_sent` fixture 900003) so the `Undefined property` error-scan covers the sent/draft read path.
- **Post-review hardening shipped with Task 8:** admin module compose `unserialize($…->to)` ×3 hardened with `array('allowed_classes' => false)` (pre-existing object-injection surface — client mirror already shipped it); gate grew to 22 assertions with 3 client compose-mode checks (`compose/900002/reply` → `RE:` + sender, `…/replyall` → + original `to` prefilled selected, `…/forward` → `FW:` + body, each under the `$errorPattern` scan) so Step 2b's branch is regression-gated. (The replyall marker initially cited fixture 900001's `to`; corrected to `value="mailbox_client@tic.test" selected` — fixture 900002's actual `to`.)

## 5. Findings from the visual pass

### 5.1 Admin favourite-star action is broken (pre-existing) — FIXED `407a009f`
`admin/mailbox/index/added_favourites/<id>/<favs>` returns **HTTP 500** with `UPDATE SET favourites = … WHERE inbox_id = …` (SQL 1064 — missing table name). Root cause: the `added_favourites` branch (pre-fix lines 109-113; now 110-115) sets only `_primary_key` before `mailbox_model->save()`; `MY_Model::save()` updates `$this->_table_name`, which is never set on this request path (default `''`; `Mailbox_Model` even redeclares `public $_table_name;`). The `change_view` branch (pre-fix lines 104-108; now 104-109) has the same latent defect.

**Pre-existing, not a redesign regression:** `git show c77d09f4 -- modules/mailbox/controllers/Mailbox.php` contains a single hunk at line 138 with **zero removed lines** (purely additive `$local_messages` block); the branch, `MY_Model`, and `Mailbox_Model` are untouched since before the redesign; and the legacy DataTables row builder (line 197) emitted the *same* `added_favourites` URL unconditionally, so the path was equally reachable (and broken) pre-redesign. The client-side star works because the client controller flow has `_table_name` set before `save()` — verified live (row 60 → `favourites=1`, redirect to client inbox, amber star in client favourites; reset to 0 after).

**Fix (commit `407a009f`, post-record):** set `_table_name = 'tbl_inbox'` in both branches (mirrors `read_inbox_mail`, line 100). Coordinator-verified live on scratch row 900008: `added_favourites/900008/1` → HTTP 200 + `favourites=1`; `/900008/0` → `favourites=0`; `change_view/900008/0` → HTTP 200 + `view_status=0`; scratch row removed, fixtures 900001/900002 intact (`0/1/No`). `composer test:mailbox` still 22/22 after the fix.

### 5.2 Compose pages log 4 pre-existing console errors
Both admin compose URLs (plain and `compose/<id>`) log `Error: Dropzone already attached.` ×4. Cause: `application/views/admin/_layout_main.php` loads `components/footer` (dropzone #1) **plus all four `_layout_modal*` layouts (lines 326-329)**, each including `dropzone.min.js` — 5 copies register autoDiscover handlers that race the single `#comments_file-dropzone` instance (the view sets `Dropzone.autoDiscover = false` too late for copies 2-5). These shared-layout files are outside the redesign (`git diff a201a2d9..HEAD -- application/views/admin/` is empty); every `_layout_main` page with a dropzone element shows this. Non-compose pages: 0 errors.

### 5.3 Raw language key on the favourite toggle
Both read pages render `title="added_into_favourites"` — the lang key is missing from `application/language/english/*`, so the raw key shows as the tooltip. Cosmetic; pre-existing key gap (Task 7 only re-rendered the control).

### 5.4 Other observations
- Client star redirect targets **inbox**, while the admin branch intends `admin/mailbox/index/favourites` (reachable since the §5.1 fix). Not covered by the spec; noted only.
- **Ownership/status guards absent (pre-existing, backlog hardening):** with the 500 removed, `added_favourites` and `change_view` still accept any logged-in user with mailbox menu access and any `<status>` value — constructor auth (`Mailbox.php:11-44`) is session/menu-level only, no per-row `user_id` check, `$status` unvalidated. The client mirror (`client/Mailbox.php:88-92`) has identical posture. The missing-guard structure predates the redesign (legacy row builder emitted the same URL); suggested hardening = ownership filter + status whitelist.
- The shared page header shows breadcrumb `Mailbox / Overview` with H1 `Dashboard` on mailbox pages — layout-header behavior outside the mailbox views; out of scope here.
- Inbox folder badge counts read 0 while rows exist (counts derive from unread/`view_status`, and fixtures sit at `view_status=1` after reads — consistent with the allowed drift).

## 6. Notes for this file (record-for-this-file bullets)

- `tbl_inbox.to` holds BOTH plain strings and serialized flat arrays in real data (the unserialize paths are live — verified against DB).
- Compose `message_body` is echoed raw into the textarea (inherited posture: draft prefill + read page already do this — ckeditor needs HTML).
- Admin compose's `set_value()` escapes where the client else-branch passes raw (asymmetry accepted, noted only).
- **`.compose-card` spacing verdict (Step 3 visual):** the card intentionally drops `.box`'s 20px margin + border-top (Design A). Confirmed visually (`task9-compose-spacing.png`): balanced gutter between folder panel and card, comfortable internal padding top/bottom, Draft/Send/Discard footer sits cleanly inside the card surface, no double-spacing or clipping — **spacing reads correctly**.

## 7. Gate summary vs plan expectations

All Step 2 expectations met: smoke PASS 22/22, login PASS, menu PASS, mailbox PASS 22/22 (0 failed, 0 skipped), theme FAIL byte-identical to the documented pre-existing baseline. Every Step 1 file lint-clean. Playwright: 11/11 rows meet their expectations; the admin star action failed during the pass (pre-existing, §5.1) and was fixed afterwards in `407a009f` with live verification (§5.1) — mailbox gate re-run green.
