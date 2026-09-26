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
zoom** and a **cold cache**:

| # | Surface | Page | Route |
|---|---|---|---|
| 1 | Public | Login | `/login` |
| 2 | Public | Register | `/login/register` |
| 3 | Public | Forgot password | `/login/forgot_password` |
| 4 | Public | Career / job board | `/career` |
| 5 | Public | Book a consultation | `/book-free-consultation` |
| 6 | Admin | Login (portal) | `/admin/login` |
| 7 | Admin | **Dashboard** | `/admin/dashboard` |
| 8 | Admin | **Super-admin dashboard** | `/admin/dashboard_admin` |
| 9 | Admin | Clients list | `/admin/client` |
| 10 | Admin | Client detail | `/admin/client/view/{id}` |
| 11 | Admin | **Invoices list** | `/admin/invoice` |
| 12 | Admin | **Invoice detail** | `/admin/invoice/view/{id}` |
| 13 | Admin | Projects list | `/admin/projects` |
| 14 | Admin | Project detail | `/admin/projects/view/{id}` |
| 15 | Admin | Tasks | `/admin/tasks` |
| 16 | Admin | Employees list | `/admin/user/user_list` |
| 17 | Admin | Accounting / ledger | `/admin/accounting` |
| 18 | Admin | Reports | `/admin/report/sales_report` |
| 19 | Admin | **Settings → appearance** | `/admin/settings/appearance` |
| 20 | Client | Client portal dashboard | `/client/dashboard` |

Bolded pages are the highest-value comparisons — they contain the most distinct components.

Log in as `forid` / `Admin@123` for rows 6–19. Record which `{id}` values you used in
`plan/_baseline/part-a/NOTES.md` so the same records are captured later.

- [ ] **Step 1: Create the directories**

```powershell
New-Item -ItemType Directory -Force -Path plan\_baseline\part-a | Out-Null
```

- [ ] **Step 2: Capture all 20 screenshots**
- [ ] **Step 3: Write `plan/_baseline/part-a/NOTES.md`** listing each page, the exact URL, the
      record IDs used, viewport size, and the date captured.
- [ ] **Step 4: Verify no screenshot shows a PHP error overlay.** If one does, that is a
      pre-existing bug — record it in `NOTES.md` under "Pre-existing issues" and move on.

**Verify:** 20 PNGs + `NOTES.md` exist.
**Effort:** M

---

## A0-3 — Colour and metric inventory

Before overwriting `app.css`, record what is currently there so regressions are detectable and
so Part B can reuse the findings.

Create `plan/_data/part-a-css-inventory.md` containing:

1. **Every unique hex colour** in `app.css`, with occurrence count and the section it appears in
2. **Every unique hex colour** across the 10 `bg-*.css` files
3. **Every font-size** used, with count
4. **Every `!important`** with line number and reason
5. The 32 section headings in `app.css` with their line ranges

- [ ] **Step 1: Extract unique colours from `app.css`**

```powershell
$r = Get-Content assets\css\app.css -Raw
[regex]::Matches($r,'#[0-9a-fA-F]{3,8}\b') | ForEach-Object { $_.Value.ToLower() } |
  Group-Object | Sort-Object Count -Descending | Select-Object Count,Name
```

- [ ] **Step 2: Same for the 10 `bg-*.css` files**
- [ ] **Step 3: Extract font sizes and `!important` counts**
- [ ] **Step 4: Record the 32 section headings with line ranges**
- [ ] **Step 5: Write the results to `plan/_data/part-a-css-inventory.md`**

**Verify:** the file exists and lists the section line ranges — these become the A2 task map.
**Effort:** S

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

- [ ] Branch `sakib` created and pushed
- [ ] 20 baseline screenshots + `NOTES.md` captured
- [ ] `plan/_data/part-a-css-inventory.md` written with the 32 section line ranges
- [ ] `composer smoke` passes 20/20
- [ ] Pre-existing issues documented, not silently ignored

## Tasks: 4 · Completed: 0 · Remaining: 4
