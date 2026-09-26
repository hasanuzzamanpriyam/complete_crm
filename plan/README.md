# TIC CRM — Frontend Modernization Plan

Two parts. **Do Part A first** — you get a modern UI quickly with near-zero risk. Part B then
replaces the 2014 framework underneath.

| | **Part A — CSS Reskin** | **Part B — BS5 Migration** |
|---|---|---|
| **Status** | **Ready to execute** | Planned, deferred |
| **Branch** | `sakib` | `sakib (Part B, after A6)` |
| **Changes** | CSS only | Markup + JS + framework |
| **Risk** | Near zero | Moderate |
| **Effort** | ~1–2 weeks | ~4–8 weeks |
| **Result** | Modern, attractive UI | Modern UI + modern foundation |

**You are starting here → [`part-a-css-reskin/README.md`](part-a-css-reskin/README.md)**

---

## Why two parts

The UI looks dated because of what it inherited: an **Angle 3.2 admin theme on Bootstrap 3.3.5**
(2014), a 155 KB hand-rolled `app.css`, 10 near-duplicate `bg-*.css` theme files, and **22,434
hardcoded inline styles**.

Part A fixes the *appearance* without touching markup — weeks of work compressed, almost nothing
can break. Part B fixes the *foundation* — a much larger effort with real regression risk, but it
clears the debt permanently.

**Nothing in Part A is thrown away.** The `tokens.css` file created in `A1-1` is the same token
system Part B consumes.

---

## Repository state

| | |
|---|---|
| Remote | `https://github.com/hasanuzzamanpriyam/complete_crm.git` |
| Current branch | `sakib` · `main` stays at `19858cbc` and is never pushed to |
| Remote branches | `main`, `sakib` |
| Live URL | `http://localhost/complete_crm/` |
| Demo URL | `http://localhost/complete_crm/demo/` |
| Local login | `forid` / `Admin@123` (local only) |
| Smoke check | `cmd /c "composer smoke"` — 22 routes, exit 0 |

See [`BRANCHES.md`](BRANCHES.md) for the full branch strategy.

---

## Tooling gotchas

Things that cost time during A0. Check here before debugging a phantom failure.

| Gotcha | Consequence | Fix |
|---|---|---|
| PowerShell 5.1 `Get-Content` defaults to **ANSI**, not UTF-8 | Renders `—` as `â€"`; a byte dump looks corrupted when the file is fine | `Get-Content -Encoding UTF8`, or read via PHP `file_get_contents` |
| `tbl_config` has **no `config_value` column** | `SELECT config_value` returns no rows; `UPDATE ... SET config_value` fails | Column is `value` — see STATUS "Blocked / decisions" |
| curl drops the session cookie across redirects | Protected routes silently test the **login page** and pass anyway | Use `CURLOPT_COOKIEJAR` + `CURLOPT_COOKIEFILE` (real file), not just `CURLOPT_COOKIE` |
| Login requires `lat` and `long` POST fields | Validation fails and the form is re-rendered with no error shown | Supply both; they are normally injected by `navigator.geolocation` in `login_form.php` |
| Shell links **`app.min.css`**, not `app.css` | A check for `app.css` in the HTML reports a false failure | Match `app(\.min)?\.css`; bump `?v=2` → `?v=3` in `A6-4` |
| `composer.bat` is not a native executable | PowerShell cannot invoke it directly | `cmd /c "composer smoke"` |

---

## Track progress

**[`STATUS.md`](STATUS.md)** — live task tracker. Tick boxes there as tasks complete.

---

## Layout

```
plan/
├── README.md                  ← you are here — master index
├── STATUS.md                  ← live task tracker
├── BRANCHES.md                ← branch strategy for GitHub
├── ROLLBACK.md                ← landmines + rollback, shared by both parts
│
├── part-a-css-reskin/         ← PART A — start here
│   ├── README.md              scope, locked decisions, task list
│   ├── 00-safety-net.md       A0-1…4    baselines, branch, smoke check
│   ├── 01-tokens.md           A1-1…4    tokens.css, bg-custom.php fix
│   ├── 02-app-css.md          A2-1…12   the visual work
│   ├── 03-sidebar-themes.md   A3-1…4    10 themes → 1 token file
│   ├── 04-dead-css.md         A4-1…3    delete 772 KB
│   ├── 05-modules.md          A5-1…3    booking, calendar, chat
│   └── 06-verification.md     A6-1…4    compare, responsive, print, ship
│
└── part-b-bs5-migration/      ← PART B — later
    ├── 00-design-system.md    DS-1…6
    ├── 01-safety-net.md       SN-1…5    route crawler
    ├── 02-vendor-bumps.md     VS-1…6    jQuery 3.7, BS5, DataTables
    ├── 03-leverage-points.md  LP-1…6    helpers, layouts, modals
    ├── 04-codemod.md          CM-1…6    the 15,040 renames
    ├── 05-structural-fixes.md SF-1…5    forms, dropdowns, JS APIs
    ├── 06-shell-redesign.md   SH-1…7    modern components
    ├── 07-page-pass.md        PP-1…14   885 views by module
    ├── 08-documents.md        DC-1…7    19 invoices, 68 PDFs, email
    ├── 09-debt-cleanup.md     DB-1…8    dead code, strftime, inline styles
    └── 10-verification.md     VF-1…4    CI guards, tests, docs
```

---

## Scope guarantee

**Part A edits only CSS files, plus one `<link>` tag in each of 4 layout shells.** No PHP logic,
no JavaScript, no database, no markup, no class renames.

Verified insulation: **0 of the 68 PDF templates reference `app.css`** — the document output
cannot be affected by Part A at all.

Part B is where markup changes. By then the safety net (`SN-1`…`SN-5`) exists and
`tokens.css` is already live.

---

## The three findings that shaped this plan

**1. Dompdf forces two CSS tracks.** Dompdf 2.0.x runs no JavaScript and has a CSS 2.1 engine.
Bootstrap 5 is JS-driven and needs flexbox/grid. So the browser app moves to BS5, while **68 PDF
templates + 8 Dompdf invoice variants stay on a frozen Bootstrap 3 stylesheet.** A deliberate
architectural fork, not technical debt.

**2. The real risk in Part B is JavaScript, not CSS.** 20 listeners on `loaded.bs.modal` — an
event **removed** in BS5. It does not error; it silently never fires, so modals open with
un-initialised dropdowns. This is why `SN-*` builds a crawler before anything moves.

**3. Inline styles are concentrated, not spread.** 17,181 of 22,434 occurrences (**77%**) live
in one pathological file, `invoice_view.php` (4.6 MB). Every other view combined is only 5,253.
That is why inline styles do not block Part A.

---

## Key numbers

| Metric | Value |
|---|---|
| Views needing BS5 migration | 885 of 1,065 (83%) |
| Bootstrap-3 occurrences | 15,040 across 82 classes |
| `data-*` attributes to rename | ~2,800 |
| Inline `style=""` attributes | 22,434 (5,253 excluding `invoice_view.php`) |
| PDF templates | 68 + 8 Dompdf invoice variants |
| Invoice templates (all kept) | 19 — 11 browser-print, 8 Dompdf |
| `strftime()` calls (PHP 8.1 deprecated) | 392 across 223 files |
| Dead CSS removable in Part A | ~772 KB |
| First-party tests | **0** |
| RTL usage | **0 of 113** accounts set `direction = rtl` (106 `ltr`, 7 NULL) |

---

## Locked decisions

| Decision | Value | Set in |
|---|---|---|
| Direction | **Refined dense** (Linear/Vercel-like) | Part A README |
| Framework | Bootstrap 3 now → 5.3.3 in Part B | Part B `VS-2` |
| Corner radius | 4 / 6 / 10 px | Part A README |
| Base font | 14px, line-height 1.5 | Part A README |
| Dark mode | **Deferred to Part B** | Part A README |
| Icons | **Keep Font Awesome 4.7** — swapping needs markup, breaks the CSS-only guarantee | Part A README |
| Invoice templates | **Keep all 19**, redesign each | Part B `DC-3` |
| RTL | **Dropped** — 0 of 113 accounts, `RTL=0` | Part A `A4-2` |
| Sequencing | Part A entirely, then Part B entirely | This file |

---

## Read this before any find-and-replace

[`ROLLBACK.md`](ROLLBACK.md) documents two landmines that will silently corrupt the codebase —
the `label` → `badge` rewrite and the first-party `panel-custom` class — plus the rollback
procedure for every phase.
