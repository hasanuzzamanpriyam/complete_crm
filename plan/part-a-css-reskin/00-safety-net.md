# Part A · A0 — Safety Net

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development
> (recommended) or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** capture the current appearance and establish a branch before any CSS changes, so
every later task has something to compare against.

**Why lighter than Part B:** Part B changes markup on 885 pages and needs a full route crawler.
Part A changes zero markup, so a screenshot baseline plus a smoke check is sufficient.

---

## A0-1 — Switch to `sakib` and commit the plan

> **`sakib` is the only branch this work is pushed to. `main` is off-limits.**

- [ ] **Step 1: Confirm the working tree state**

```powershell
git status --short
```

Expected — exactly these three modified files, plus the untracked `plan/` folder:

```
 M .gitignore
 M demo/index.php
 M index.php
?? plan/
```

- [ ] **Step 2: Confirm `sakib` is level with `main` before switching**

```powershell
git fetch origin
git log --oneline origin/main..origin/sakib
git log --oneline origin/sakib..origin/main
```

Expected: **both empty**. `sakib` and `main` were both at `19858cbc`, so switching loses nothing.

- [ ] **Step 3: Switch to `sakib`**

```powershell
git checkout sakib
git branch --set-upstream-to=origin/sakib sakib
```

Verify: `git rev-parse --abbrev-ref HEAD` returns `sakib`, and `git status --short` still lists
the same three modified files — uncommitted work follows you across the checkout.

- [ ] **Step 4: Commit the plan and the three pre-existing fixes together**

```powershell
git add .gitignore index.php demo/index.php plan/
git commit -m "docs: add Part A/B redesign plan; suppress E_DEPRECATED on PHP 8.3"
```

> The three PHP/config changes are pre-existing from the local-setup work, not Part A. They are
> committed here so every later Part A commit is CSS-only and trivially revertable.

- [ ] **Step 5: Push to `sakib`**

```powershell
git push origin sakib
```

- [ ] **Step 6: Verify the push landed and `main` is untouched**

```powershell
git log --oneline -1 origin/sakib
git log --oneline -1 origin/main
```

Expected: `origin/sakib` shows the new commit. `origin/main` still shows `19858cbc` — **unchanged**.

**Verify:** `git log --oneline -1` shows the commit; `git status --short` is empty;
`origin/sakib` advanced and `origin/main` did not.
**Effort:** S

---

## A0-2 — Baseline screenshots

Capture the "before" state. Without this you cannot prove the redesign improved anything.

Create `plan/_baseline/part-a/` and capture these **20 pages** at **1440×900** with a **100%
zoom** and a **cold cache**.

> **All routes below were verified on 2026-09-26** by requesting each one. Guessed paths are
> wrong far more often than expected here — `admin/client`, `admin/invoice` and
> `admin/accounting` all 404; the real paths include the method name. Use this table, not a
> guess. A `307` is the correct unauthenticated response (auth redirect) and means the route
> exists.

| # | Surface | Page | Route | Unauth |
|---|---|---|---|---|
| 1 | Public | Login | `/login` | 200 |
| 2 | Public | Register | `/login/register` | 200 |
| 3 | Public | Forgot password | `/login/forgot_password` | 200 |
| 4 | Public | Career / job board | `/career` | 200 |
| 5 | Public | Book a consultation | `/book-free-consultation` | 200 |
| 6 | Admin | **Dashboard** | `/admin/dashboard` | 307 |
| 7 | Admin | Super-admin | `/admin/superadmin` | 307 |
| 8 | Admin | Clients list | `/admin/client/manage_client` | 307 |
| 9 | Admin | Invoices list | `/admin/invoice/manage_invoice` | 307 |
| 10 | Admin | Quotations | `/admin/quotations` | 307 |
| 11 | Admin | Proposals | `/admin/proposals` | 307 |
| 12 | Admin | Projects | `/admin/projects` | 307 |
| 13 | Admin | Tasks | `/admin/tasks/all_task` | 307 |
| 14 | Admin | Employees | `/admin/user/user_list` | 307 |
| 15 | Admin | Chart of accounts | `/admin/accounting/chart_of_accounts` | 307 |
| 16 | Admin | Transactions report | `/admin/transactions/transactions_report` | 307 |
| 17 | Admin | Attendance | `/admin/attendance/daily_attendance` | 307 |
| 18 | Admin | Payroll summary | `/admin/payroll/payroll_summary` | 307 |
| 19 | Admin | Sales report | `/admin/report/sales_report` | 307 |
| 20 | Admin | **Settings** | `/admin/settings` | 307 |

**Additional verified routes** — useful for `A6-1` spot checks (all `307` unauthenticated):
`/admin/leads` · `/admin/calendar` · `/admin/mailbox` · `/admin/knowledgebase` · `/admin/bugs` ·
`/admin/items/items_list` · `/admin/filemanager` · `/admin/departments` · `/admin/credit_note` ·
`/admin/estimates` · `/admin/timesync` · `/admin/consultation` ·
`/admin/job_circular/jobs_posted` · `/admin/mark_attendance` · `/client/dashboard`

**Detail pages** — resolve a real ID from each list page at capture time and record it in
`NOTES.md`. They carry the most distinct components, so include at least the invoice detail.
Verified routes (the `view/{id}` form below was a guess and does not exist — see
`plan/_baseline/part-a/NOTES.md`):

- Invoice detail: `/admin/invoice/manage_invoice/invoice_details/{id}` (redirects to
  `/admin/invoice/invoice_details/{id}`) — **captured with id `9`**
- Client detail: `/admin/client/...` (resolve from the list page)
- Project detail: `/admin/projects/...` (resolve from the list page)

Log in as `forid` / `Admin@123` for rows 6–19. Record which `{id}` values you used in
`plan/_baseline/part-a/NOTES.md` so the same records are captured later.

- [x] **Step 1: Create the directories**

```powershell
New-Item -ItemType Directory -Force -Path plan\_baseline\part-a | Out-Null
```

- [x] **Step 2: Capture all 20 screenshots** (+ `21-invoice-detail.png`, record id `9`)
- [x] **Step 3: Write `plan/_baseline/part-a/NOTES.md`** listing each page, the exact URL, the
      record IDs used, viewport size, and the date captured.
- [x] **Step 4: Verify no screenshot shows a PHP error overlay.** If one does, that is a
      pre-existing bug - record it in `NOTES.md` under "Pre-existing issues" and move on.

**Verify:** 21 PNGs + `NOTES.md` exist — all 21 distinct hashes, 0 error overlays.
**Effort:** M

---

## A0-3 — Colour and metric inventory

Before overwriting `app.css`, record what is currently there so regressions are detectable and
so Part B can reuse the findings.

**Done via `plan/_tools/css-inventory.php`** (reproducible — regenerate after each A2 task to
confirm no colour was lost):

```powershell
php plan/_tools/css-inventory.php > plan/_data/part-a-css-inventory.md
```

The output contains:

1. **Every section heading in `app.css` with its line range** — these become the A2 task map
2. **Every unique hex colour** in `app.css`, with occurrence count
3. **Every unique hex colour** across the `bg-*.css` theme files, per file
4. **Every `font-size`** used, with count
5. **Every `!important`** with line number
6. The SHA-256 of each `*rtl*.css` file, recorded before `A4-2` deletes them
7. Live theming values from `tbl_config` in both databases
8. Account `direction` distribution (the RTL-removal evidence)

**Verified baseline (from the generated file):**

| Metric | Value |
|---|---|
| `app.css` | 8,193 lines / 155,229 bytes |
| Sections | **31** |
| Unique hex in `app.css` | 180 |
| Unique hex in theme files | 83 (892 occurrences) |
| Distinct font sizes | 30 |
| `!important` in `app.css` | **272** |
| RTL CSS removable | 744,829 bytes |

**Verify:** the file exists and lists the section line ranges — these become the A2 task map.
**Effort:** S

**Schema gotcha found here:** `tbl_config` is `(config_key PRIMARY, value TEXT)`. There is **no
`config_value` column** — six SQL statements elsewhere in this plan used it and have been
corrected to `value`.

---

## A0-4 — Smoke check script

A cheap regression detector. CSS changes cannot cause PHP errors, but they can break a page
layout badly enough to be worth catching automatically.

Create `plan/_tools/smoke.php` — a CLI script that:

1. Logs in as `forid` / `Admin@123` and reuses the session cookie
2. Requests each of the 20 baseline routes
3. Asserts: HTTP 200, body length > 2000 bytes, no `Fatal error|Parse error|Uncaught`
4. Asserts `assets/css/app.css` is referenced in the HTML
5. Prints a pass/fail table and **exits non-zero on any failure**

Then register it in `composer.json`:

```json
{
  "scripts": {
    "smoke": "php plan/_tools/smoke.php"
  }
}
```

- [ ] **Step 1: Write `plan/_tools/smoke.php`**
- [ ] **Step 2: Add the `smoke` script to `composer.json`**
- [ ] **Step 3: Run it against the current codebase**

```powershell
composer smoke
```

Expected: 20/20 pass. **Any failure here is pre-existing** — record it in
`plan/_baseline/part-a/NOTES.md` before proceeding.

- [ ] **Step 4: Commit**

```powershell
git add plan/_tools/smoke.php composer.json plan/_baseline/ plan/_data/
git commit -m "A0: baseline screenshots, CSS inventory, and smoke check"
```

---

## Exit gate

- [x] Branch `sakib` created and pushed
- [x] 21 baseline screenshots + `NOTES.md` captured
- [x] `plan/_data/part-a-css-inventory.md` written with the 31 section line ranges
- [x] `composer smoke` passes 22/22
- [ ] Pre-existing issues documented, not silently ignored

## Tasks: 4 · Completed: 0 · Remaining: 4
