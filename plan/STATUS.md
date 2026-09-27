# Progress Tracker

**Last updated:** 2026-09-27
**Overall:** Part A **11 / 35** · Part B **0 / 74** · **Total 11 / 109**

Legend: `[ ]` not started · `[~]` in progress · `[x]` complete

---

## Part A — CSS Reskin · 11/35

Branch: `sakib` · Detail: [`part-a-css-reskin/README.md`](part-a-css-reskin/README.md)

### A0 — Safety Net · 4/4 ✅ complete
File: [`00-safety-net.md`](part-a-css-reskin/00-safety-net.md)

- [x] `A0-1` Create branch, commit plan + pre-existing fixes
- [x] `A0-2` Capture 21 baseline screenshots (20 routes + invoice detail id 9) + `NOTES.md`
- [x] `A0-3` Colour/metric inventory of `app.css`
- [x] `A0-4` `composer smoke` script, 22/22 pass

### A1 — Design Tokens · 4/4 ✅ complete
File: [`01-tokens.md`](part-a-css-reskin/01-tokens.md)

- [x] `A1-1` Create `assets/css/tokens.css` (11 groups / 65 declarations)
- [x] `A1-2` Load `tokens.css` in all 6 shells (plan table corrected)
- [x] `A1-3` Fix `bg-custom.php` — 3 bugs, emit `:root` variables
- [x] `A1-4` Enable `active_custom_color` in both DBs, verify live theming

### A2 — Restyle `app.css` · 3/13
File: [`02-app-css.md`](part-a-css-reskin/02-app-css.md)

- [x] `A2-1` Base and typography
- [x] `A2-2` Panels to card surfaces
- [x] `A2-13` **Soft UI depth and shape** — shadow scale, radii, `#f4f4f5` page ⭐ do before A2-3
- [ ] `A2-3` **Button family** ⭐ highest visual impact
- [ ] `A2-4` Forms and inputs
- [ ] `A2-5` Status badges to soft chips
- [ ] `A2-6` Tables, DataTables and chart chrome
- [ ] `A2-7` Sidebar — white pill + icon chips
- [ ] `A2-8` Top bar
- [ ] `A2-9` Modals ⚠ most likely to break — revert if modals fail to open
- [ ] `A2-10` Tabs, nav, pagination, breadcrumbs
- [ ] `A2-11` Alerts and toasts
- [ ] `A2-12` Print stylesheet (63 print pages depend on it)

`A2-7` must land before `A3-1`: the sidebar has to *consume* `--c-sidebar-*` before
`themes.css` can drive 14 variants of it.

### A3 — Sidebar Themes · 0/4
File: [`03-sidebar-themes.md`](part-a-css-reskin/03-sidebar-themes.md)

- [ ] `A3-1` `themes.css` — 14 token sets over 7 sidebar tokens
- [ ] `A3-2` Consolidate `bg-white` / `bg-dark` layout into `app.css`
- [ ] `A3-3` Theme settings page — ⚠ `settings/theme.php`, **not** `appearance.php` (no such file)
- [ ] `A3-4` Verify all 14 themes render

### A4 — Dead CSS · 0/3
File: [`04-dead-css.md`](part-a-css-reskin/04-dead-css.md)

- [ ] `A4-1` Delete `frontent.css`, `bg-new copy.css`, 12 superseded `bg-*.css`
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
| Visual direction | **Resolved 2026-09-27** | **Soft UI** (Creative Tim) — soft shadows on cards and controls, generous radii, muted accent colours, Source Sans Pro retained. The earlier "Refined dense" direction is superseded. |
| Depth / effects | **Resolved 2026-09-27 — reversed** | **Soft shadows**, not strict flat. Three-step scale: `--sh-1` cards/controls, `--sh-2` raised (hover, icon chips, active sidebar pill), `--sh-3` overlays. **Selective** — tables, table rows, list-group items, badges, pagination, nav-tabs, alerts and calendar cells stay flat; nested panels take no shadow so a card never stacks two elevations. Gradients and inset/embossed effects still banned everywhere. *Supersedes the strict-flat rule of 2026-09-26, which A2-13 reversed in one commit.* |
| Font | **Resolved 2026-09-27** | Keep **Source Sans Pro**. Soft UI uses Inter; adding it would need a `<link>` in 6 shells (breaking Part A's CSS-only rule) or ~100 KB of self-hosted woff2. Not worth it — this is the one place the result will read differently from the reference. |
| Theme count | **Resolved 2026-09-27** | **14** themes, up from 10. Existing `bg-*` names preserved so stored `sidebar_theme` values keep working; added `bg-teal`, `bg-teal-dark`, `bg-amber`, `bg-amber-dark`. Slate was considered and dropped as indistinguishable from `bg-dark`. |
| Framework target | **Resolved** | Bootstrap 3 now → 5.3.3 in Part B |
| Dark mode | **Deferred** | Part B |
| Icon set | **Deferred** | Part B `SH-7` — swapping needs markup |
| `tbl_config` schema | **Corrected 2026-09-26** | Table is `(config_key PRIMARY, value TEXT)` — there is **no `config_value` column**. Six SQL statements in A1-3, A3-3, A3-4, A4-2 used it and now write `value`. A `SELECT config_value` returns no rows, so the error was silent. |
| `sidebar_theme` live value | **Corrected 2026-09-26** | Both DBs store `bg-purple-dark`, not `bg-dark`. A3-3 migrates it to `theme-purple-dark` to preserve the current appearance rather than to a fixed default. |
| Theme settings file | **Corrected 2026-09-27** | The theme chooser is `application/views/admin/settings/theme.php`. A3-3 named `settings/appearance.php`, **which does not exist** — and `/admin/settings/appearance` 404s. |
| Shell count for `sidebar_theme` | **Corrected 2026-09-27** | Only **1** shell links it: `admin/components/htmlheader.php:55`. A3-1/A3-3 said "all 4 shells"; there are 6 shells total and only the admin one themes the sidebar. |
| Custom colour precedence | **Open — found 2026-09-27** | `htmlheader.php` loads `bg-custom.php` at **L51** but `sidebar_theme.css` at **L55**, so the theme file loads *after* the custom colours and always wins. The 11 custom colourpickers appear to have no effect on the sidebar. `themes.css` must be linked **before** `bg-custom.php`. Confirm before fixing. |
| Arabic / Hebrew (i18n) | **Open** | `A4-2` drops RTL CSS. Language packs kept. If i18n is a future requirement, the RTL work returns — restore from git history using the hashes recorded in `A4-2` |
| Print page coverage | **Limitation** | `A6-3` checks 10 of 63. The other 53 share the same `print.css` but are not individually verified |

---

## Known issues (pre-existing, not caused by this work)

Recorded in `SN-3` for Part B. None are regressions.

| Issue | Location |
|---|---|
| Demo auto-login HTTP 500 — demo users missing from dump | `application/controllers/Login.php:352` |
| `#trackerDownloadModal` blocks first click; dismiss flag not persisted on × | **Fixed 2026-09-26** — promo is now an in-flow banner in `login.php`, no modal, both dismiss paths persist. Guarded by `composer test:login` (7 assertions) |
| Malformed tooltip attribute `data-toggle="tooltip data-placement="top"` | `admin_helper.php` |
| `<input id="" type="radio">` — empty/duplicate IDs, labels unattached | `admin_helper.php:1070` |
| Duplicate `'data-toggle'` key — PHP keeps only the last | `btn_edit_modal`, `btn_banned_modal` |
| Invalid `btn text-danger` class | `btn_delete` |
| `bg-custom.php` malformed CSS: `!important;;`, `solid<?=` | lines ~104, ~199 → **fixed in A1-3** |
| Pusher WebSocket warning when offline | realtime notifications |
| `last_login` written to session, never persisted to `tbl_users` | login flow |
| `$.fn.modal.Constructor` does not exist in BS5 → dead Select2 focus guard | `add_domain.php:960` |
| `GET http://localhost/assets/img/dragger.png` → 404 — missing `/complete_crm` base path | invoice detail page (seen in A0-2 capture) |

---

## Update procedure

After completing tasks:

1. Tick the boxes in this file
2. Update the `Last updated` date and the counts at the top
3. Update the `Part A complete criteria` checklist in
   [`part-a-css-reskin/06-verification.md`](part-a-css-reskin/06-verification.md)
4. Commit with the task ID, e.g. `A2-3: restyle button family to dense system`
