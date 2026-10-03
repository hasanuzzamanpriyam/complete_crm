# Mailbox Redesign — Design Spec

**Date:** 2026-10-03
**Status:** Approved (design walkthrough approved in session; approach: in-place reskin)
**Reference demo:** `.superpowers/brainstorm/1374-1790861074/content/mailbox-full.html` (Design A selected by user)
**Pattern to follow:** WooCommerce redesign (`docs/superpowers/specs/2026-10-01-woocommerce-redesign-implementation-design.md`)

## 1. Context

The mailbox module (`application/controllers/{admin,client}/Mailbox.php`) is a Bootstrap-3-era UI: `panel panel-custom` wrappers, `nav-pills nav-stacked` folder menu, legacy list markup. Rendering flow (both roles):

```
Mailbox::index($action, $id)
  └─ views/{admin,client}/mailbox/mailbox.php   ← folder panel (col-3) + $view slot (col-9)
       └─ $view ∈ {inbox, sent, draft, favourites, trash, read_mail, compose_mail}
  └─ layouts {admin,client}/_layout_main.php
```

Admin and client view sets are near-identical duplicates. The existing drill-down flow (list → full-page read) already matches the chosen demo direction, so the redesign is a pure presentation-layer change.

## 2. Goals

- Reskin all **7 screens** — Inbox, Sent, Drafts, Favourites, Trash, Read mail, Compose — to **Design A (list + full-page read)**, for **both admin and client**.
- Match the approved demo's look: same tokens, cards, folder panel, message rows, read header, compose fields.
- Zero behavior change: controllers, models, queries, URLs, form actions, redirects, flash messages, and `lang()` usage stay untouched.
- Existing regression gates stay green; add a dedicated `test:mailbox` e2e gate.

## 3. Non-goals (out of scope)

- AJAX/modal compose (compose stays a full page — documented deviation from the demo).
- New endpoints, new features, DataTables, search redesign.
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
2. **View edits:** restructure markup inside the **10 view files × 2 roles** — wrapper, 5 folder views, read, compose, and the 3 Trash sublists (`mailbox/trash/{inbox,sent,draft}.php`, rendered inside the Trash screen) — to use the component classes. Existing `lang()` keys, form `action`s, `href`s, `name`s, and PHP logic remain verbatim.
3. **Controls:** keep every existing control (checkbox/select-all, delete, restore, star, search inputs, pagination if present) — restyle only; add none.
4. **Flash messages:** `message_box()` → toastr behavior unchanged.
5. **Version bump:** `mailbox_modern.css?v=1`; bump on later fixes (lesson from WooCommerce `?v` caching).

## 7. Testing & verification

- **New e2e gate:** `plan/_tools/mailbox-redesign-test.php` + `composer test:mailbox`:
  - All folder routes return 200 with `mailbox_modern.css` present (admin + client): `admin/mailbox/index/{inbox,sent,draft,favourites,trash}` and client equivalents.
  - Read + compose routes return 200 with CSS present (exact URIs confirmed during planning).
  - A non-mailbox page (e.g., dashboard) does **not** load `mailbox_modern.css` (no leak).
  - Response bodies contain no raw lang tokens (e.g., `lang(` output artifacts) in rendered mailbox pages.
- **Playwright pass:** all 7 screens × admin and client: visual match to demo, folder switching, read drill-down, compose render, 0 console errors.
- **Existing gates:** `composer smoke`, `test:login`, `test:menu` green; `test:theme` pre-existing baseline failure unchanged (documented in WooCommerce verification doc).
- **Lint:** `php -l` on every edited PHP file.

## 8. Deviations from the demo (agreed)

| Demo | Implementation | Why |
|---|---|---|
| Compose as modal | Compose stays full page (`compose_mail.php`) | Existing POST + redirect flow; modal needs new AJAX plumbing |
| Static folder counts | Real counts from existing queries | Data layer untouched |
| Design switcher pill bar | Not implemented | One approved direction |

## 9. Open questions

None — design and approach approved in session.
