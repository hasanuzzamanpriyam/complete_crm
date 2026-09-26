# Progress Tracker

**Last updated:** 2026-09-26
**Overall:** Part A **0 / 34** · Part B **0 / 74** · **Total 0 / 108**

Legend: `[ ]` not started · `[~]` in progress · `[x]` complete

---

## Part A — CSS Reskin · 0/34

Branch: `sakib` · Detail: [`part-a-css-reskin/README.md`](part-a-css-reskin/README.md)

### A0 — Safety Net · 0/4
File: [`00-safety-net.md`](part-a-css-reskin/00-safety-net.md)

- [ ] `A0-1` Create branch, commit plan + pre-existing fixes
- [ ] `A0-2` Capture 20 baseline screenshots
- [ ] `A0-3` Colour/metric inventory of `app.css`
- [ ] `A0-4` `composer smoke` script, 20/20 pass

### A1 — Design Tokens · 0/4
File: [`01-tokens.md`](part-a-css-reskin/01-tokens.md)

- [ ] `A1-1` Create `assets/css/tokens.css` (14 token groups)
- [ ] `A1-2` Load `tokens.css` in all 4 shells
- [ ] `A1-3` Fix `bg-custom.php` — 2 syntax bugs, emit variables
- [ ] `A1-4` Enable `active_custom_color`, verify live theming

### A2 — Restyle `app.css` · 0/12
File: [`02-app-css.md`](part-a-css-reskin/02-app-css.md)

- [ ] `A2-1` Base and typography
- [ ] `A2-2` Panels to card surfaces
- [ ] `A2-3` **Button family** ⭐ highest visual impact
- [ ] `A2-4` Forms and inputs
- [ ] `A2-5` Status badges to soft chips
- [ ] `A2-6` Tables and DataTables chrome
- [ ] `A2-7` Sidebar
- [ ] `A2-8` Top bar
- [ ] `A2-9` Modals ⚠ most likely to break — revert if modals fail to open
- [ ] `A2-10` Tabs, nav, pagination, breadcrumbs
- [ ] `A2-11` Alerts and toasts
- [ ] `A2-12` Print stylesheet (63 print pages depend on it)

### A3 — Sidebar Themes · 0/4
File: [`03-sidebar-themes.md`](part-a-css-reskin/03-sidebar-themes.md)

- [ ] `A3-1` Replace 8 duplicate `bg-*.css` with `themes.css`
- [ ] `A3-2` Consolidate `bg-white` / `bg-dark` layout into `app.css`
- [ ] `A3-3` Point settings dropdown at new theme names
- [ ] `A3-4` Verify all 10 themes render

### A4 — Dead CSS · 0/3
File: [`04-dead-css.md`](part-a-css-reskin/04-dead-css.md)

- [ ] `A4-1` Delete `frontent.css`, `bg-new copy.css`, 10 superseded `bg-*.css`
- [ ] `A4-2` Delete 4 RTL stylesheets (727 KB) — record `app-rtl` hashes first
- [ ] `A4-3` Remove RTL branch from all 4 shells

### A5 — Module CSS · 0/3
File: [`05-modules.md`](part-a-css-reskin/05-modules.md)

- [ ] `A5-1` Align `booking.css` to tokens
- [ ] `A5-2` `calendar_modern.css`, `timesync-dashboard.css`, `ai_chat.css`
- [ ] `A5-3` Sweep remaining hardcoded colours onto tokens

### A6 — Verification · 0/4
File: [`06-verification.md`](part-a-css-reskin/06-verification.md)

- [ ] `A6-1` Re-capture 20 screenshots, compare, record verdicts
- [ ] `A6-2` Responsive check — 4 widths × 20 pages × 4 surfaces
- [ ] `A6-3` Print verification — 10 documents (53 unchecked; document the gap)
- [ ] `A6-4` Confirm zero PHP/JS/PDF/SQL changes, bump `?v=3`, push + PR

---

## Part B — BS5 Migration · 0/74

Branch: `sakib (Part B, after A6)` · Detail: [`part-b-bs5-migration/`](part-b-bs5-migration/)

**Not started.** Begin only after Part A ships. Note: `DS-2` (visual direction) is already
satisfied by Part A's locked decisions, so skip it and start at `SN-1`.

### Foundations · 0/17
- [ ] `DS-1…6` Design system — **DS-2 complete via Part A**
- [ ] `SN-1…5` Safety net — route crawler, baselines, known issues
- [ ] `VS-1…6` Vendor bumps — jQuery 3.7, BS5.3.3, DataTables, plugins, PDF freeze

### Migration · 0/17
- [ ] `LP-1…6` Leverage points — 29 helpers, tabs, 4 layouts, 7 modal shells
- [ ] `CM-1…6` Codemod — 15,040 occurrences, one mapping per commit
- [ ] `SF-1…5` Structural — 451 horizontal forms, dropdowns, JS APIs

### Redesign · 0/21
- [ ] `SH-1…7` Shell redesign — CSS architecture, sidebar, topbar, dashboard, login
- [ ] `PP-1…14` Page pass — 885 views across 14 module batches

### Documents & cleanup · 0/15
- [ ] `DC-1…7` Documents — 19 invoices, 68 PDFs, 6 emails, print
- [ ] `DB-1…8` Debt cleanup — dead code, `strftime`, inline-style budget

### Verification · 0/4
- [ ] `VF-1…4` CI guards, first-party test suite, documentation

---

## Blocked / decisions

| Item | Status | Note |
|---|---|---|
| Visual direction | **Resolved** | Refined dense, approved 2026-09-26 |
| Framework target | **Resolved** | Bootstrap 3 now → 5.3.3 in Part B |
| Dark mode | **Deferred** | Part B |
| Icon set | **Deferred** | Part B `SH-7` — swapping needs markup |
| Arabic / Hebrew (i18n) | **Open** | `A4-2` drops RTL CSS. Language packs kept. If i18n is a future requirement, the RTL work returns — restore from git history using the hashes recorded in `A4-2` |
| Print page coverage | **Limitation** | `A6-3` checks 10 of 63. The other 53 share the same `print.css` but are not individually verified |

---

## Known issues (pre-existing, not caused by this work)

Recorded in `SN-3` for Part B. None are regressions.

| Issue | Location |
|---|---|
| Demo auto-login HTTP 500 — demo users missing from dump | `application/controllers/Login.php:352` |
| `#trackerDownloadModal` blocks first click; dismiss flag not persisted on × | modal shell |
| Malformed tooltip attribute `data-toggle="tooltip data-placement="top"` | `admin_helper.php` |
| `<input id="" type="radio">` — empty/duplicate IDs, labels unattached | `admin_helper.php:1070` |
| Duplicate `'data-toggle'` key — PHP keeps only the last | `btn_edit_modal`, `btn_banned_modal` |
| Invalid `btn text-danger` class | `btn_delete` |
| `bg-custom.php` malformed CSS: `!important;;`, `solid<?=` | lines ~104, ~199 → **fixed in A1-3** |
| Pusher WebSocket warning when offline | realtime notifications |
| `last_login` written to session, never persisted to `tbl_users` | login flow |
| `$.fn.modal.Constructor` does not exist in BS5 → dead Select2 focus guard | `add_domain.php:960` |

---

## Update procedure

After completing tasks:

1. Tick the boxes in this file
2. Update the `Last updated` date and the counts at the top
3. Update the `Part A complete criteria` checklist in
   [`part-a-css-reskin/06-verification.md`](part-a-css-reskin/06-verification.md)
4. Commit with the task ID, e.g. `A2-3: restyle button family to dense system`
