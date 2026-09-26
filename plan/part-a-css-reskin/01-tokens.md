# Part A · A1 — Design Tokens

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development
> (recommended) or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** create one source of truth for every visual constant, so the theme can be changed
without editing CSS — and so Part B reuses the same tokens.

**Shared with Part B:** `DS-3` and `DS-4` in [`../part-b-bs5-migration/00-design-system.md`](../part-b-bs5-migration/00-design-system.md)
cover the same work. **The token file created here is the one Part B consumes** — do not create
a second one.

---

## A1-1 — Create `tokens.css`

Create `assets/css/tokens.css` with exactly this content. These are the **Refined dense**
values approved in the Part A README — do not substitute.

```css
/* =============================================================
   TIC CRM — Design Tokens
   Direction: Refined dense (Linear/Vercel-inspired)
   Single source of truth. Do not hardcode these values elsewhere.
   ============================================================= */

:root {
  /* ---------- brand / accent (teal, modernised) ---------- */
  --c-accent:          #0d9488;
  --c-accent-hover:    #0f766e;
  --c-accent-active:   #115e59;
  --c-accent-subtle:   #ecfdf9;
  --c-accent-border:   #99f6e4;
  --c-on-accent:       #ffffff;

  /* ---------- status ---------- */
  --c-success:         #0f9b6c;
  --c-success-subtle:  #ecfdf5;
  --c-success-border:  #a7f3d0;
  --c-warning:         #d97706;
  --c-warning-subtle:  #fffbeb;
  --c-warning-border:  #fde68a;
  --c-danger:          #dc2626;
  --c-danger-hover:    #b91c1c;
  --c-danger-subtle:   #fef2f2;
  --c-danger-border:   #fecaca;
  --c-info:            #2563eb;
  --c-info-subtle:     #eff6ff;
  --c-info-border:     #bfdbfe;

  /* ---------- surfaces ---------- */
  --c-bg:              #f7f8fa;
  --c-surface:         #ffffff;
  --c-surface-2:       #f1f3f5;
  --c-surface-3:       #e9ecef;
  --c-border:          #e3e6ea;
  --c-border-strong:   #d0d5dd;
  --c-overlay:         rgb(16 24 40 / 55%);

  /* ---------- text ---------- */
  --c-text:            #1a1d21;
  --c-text-2:          #4a5058;
  --c-text-3:          #7a828c;
  --c-text-inverse:    #ffffff;

  /* ---------- sidebar (dark, refined) ---------- */
  --c-sidebar-bg:      #14181c;
  --c-sidebar-bg-2:    #1c2126;
  --c-sidebar-text:    #b6bdc6;
  --c-sidebar-text-hi: #ffffff;
  --c-sidebar-active:  #0d9488;
  --c-sidebar-border:  #262c33;

  /* ---------- spacing scale ---------- */
  --sp-1: 4px;
  --sp-2: 8px;
  --sp-3: 12px;
  --sp-4: 16px;
  --sp-5: 24px;
  --sp-6: 32px;
  --sp-7: 48px;
  --sp-8: 64px;

  /* ---------- radius ---------- */
  --r-sm: 4px;
  --r-md: 6px;
  --r-lg: 10px;
  --r-full: 999px;

  /* ---------- elevation (subtle; dense UI needs almost none) ---------- */
  --sh-1: 0 1px 2px rgb(16 24 40 / 5%);
  --sh-2: 0 2px 6px rgb(16 24 40 / 7%);
  --sh-3: 0 8px 24px rgb(16 24 40 / 10%);

  /* ---------- typography ---------- */
  --fs-xs: 11px;
  --fs-sm: 12px;
  --fs-base: 14px;
  --fs-lg: 16px;
  --fs-xl: 20px;
  --fs-2xl: 26px;
  --lh-tight: 1.25;
  --lh-normal: 1.5;

  /* ---------- density (Refined dense: compact controls) ---------- */
  --h-control:    34px;
  --h-control-sm: 28px;
  --row-h:        40px;

  /* ---------- motion ---------- */
  --dur-fast: 120ms;
  --dur-base: 200ms;
  --ease: cubic-bezier(.4, 0, .2, 1);
}
```

- [ ] **Step 1: Create the file with the content above**
- [ ] **Step 2: Verify it parses** — open it in a browser via the app, or run a CSS linter.
      There must be no unclosed brace.
- [ ] **Step 3: Commit**

```powershell
git add assets/css/tokens.css
git commit -m "A1-1: add design token file (Refined dense direction)"
```

**Verify:** `tokens.css` is valid CSS and byte-identical to the block above — **11 comment
groups, 65 variable declarations** (the earlier "14 token groups" count was wrong).
**Effort:** S

---

## A1-2 — Load `tokens.css` in all 4 shells

The token file must load **before** `app.css` so `app.css` can consume the variables.

- [ ] **Step 1: Insert the link in the admin shell**

In `application/views/admin/components/htmlheader.php`, immediately **before** line 45
(`app.css?v=2`), insert:

```php
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/tokens.css">
```

- [ ] **Step 2: Insert the same link in the 3 remaining shells**

| Shell | File | Insert before |
|---|---|---|
| Admin RTL | `application/views/admin/components/htmlheader.php` | the `app-rtl.min.css` link (line 40) |
| Client | `application/views/client/components/htmlheader.php` | its `app.css` link |
| Frontend | `application/views/frontend/components/htmlheader.php` | its `app.css` link |
| Booking | `application/views/booking/_layout_main.php` | its `app.css` link |

- [ ] **Step 3: Verify load order in the rendered HTML**

```powershell
composer smoke
```

Then open any admin page, view source, and confirm `tokens.css` appears **before** `app.css`.

- [ ] **Step 4: Confirm the tokens are live** — open the browser console on any admin page:

```js
getComputedStyle(document.documentElement).getPropertyValue('--c-accent').trim()
```

Expected: `" #0d9488"` (leading space is normal).

- [ ] **Step 5: Commit**

```powershell
git add application/views/admin/components/htmlheader.php `
        application/views/client/components/htmlheader.php `
        application/views/frontend/components/htmlheader.php `
        application/views/booking/_layout_main.php
git commit -m "A1-2: load tokens.css in all 4 layout shells"
```

> This is the **only** PHP touched in Part A. Every other Part A task is CSS-only.

**Verify:** console returns `#0d9488`; `composer smoke` still 20/20; no 404 for `tokens.css`.
**Effort:** S

---

## A1-3 — Fix `bg-custom.php` and make it emit variables

`assets/css/bg-custom.php` is a PHP script that generates the sidebar/topbar theme CSS from
`tbl_config`. It currently has **two syntax bugs** and emits hardcoded values.

- [ ] **Step 1: Read the file and locate the two defects**

```powershell
Select-String -Path assets\css\bg-custom.php -Pattern 'important|solid<'
```

Expected findings (lines ~104 and ~199):

```
104:  ... !important;;
199:  ... solid<?= ...
```

`!important;;` is a doubled semicolon — harmless but wrong. `solid<?=` opens a PHP tag inside a
CSS declaration, which **breaks the stylesheet from that point on**.

- [ ] **Step 2: Fix both**

Replace `!important;;` with `!important;`.

For the `solid<?=` defect, read the surrounding lines to determine the intended value, then
close the PHP tag correctly — the intent is a CSS `border` declaration, e.g.:

```php
$css .= ".topbar { border-bottom: 1px solid " . $border_color . " !important; }";
```

- [ ] **Step 3: Replace hardcoded hex values with the token variables**

Wherever the script emits a fixed colour, emit the variable instead, e.g.:

```php
$css .= ":root { --c-sidebar-active: " . $active_color . "; }";
```

rather than writing `$active_color` into a dozen separate rules. This is what makes live
theming work in A3.

- [ ] **Step 4: Verify the output is valid CSS**

```powershell
php -l assets/css/bg-custom.php
```

Then request it over HTTP and confirm the response contains no PHP notice and no unterminated
`<?php`.

- [ ] **Step 5: Commit**

```powershell
git add assets/css/bg-custom.php
git commit -m "A1-3: fix bg-custom.php syntax bugs, emit CSS variables"
```

**Verify:** `php -l` clean; the HTTP response is valid CSS; no `!important;;` or `solid<?=`
remains.
**Effort:** S

---

## A1-4 — Prove theming works live

The point of tokens: change a setting, the whole app restyles, with no CSS edit.

- [ ] **Step 1: In the browser console, override the accent and confirm it propagates**

```js
document.documentElement.style.setProperty('--c-accent', '#7c3aed');
```

Expected: any element already using `var(--c-accent)` turns violet immediately. If nothing
changes, that is expected at this stage — no rule consumes it yet. A2 introduces the consumers.
Confirm only that the variable sets without error.

- [ ] **Step 2: Update `tbl_config` so the sidebar uses the new token path**

In phpMyAdmin (or your SQL client), run:

> **Column name:** `tbl_config` is `(config_key PRIMARY, value TEXT)`. There is no
> `config_value` column — an `UPDATE ... SET config_value = ...` fails as an error, and a
> `SELECT config_value` returns nothing. Both databases currently hold `active_custom_color = 0`
> (verified, see `plan/_data/part-a-css-inventory.md` §7).

```sql
UPDATE tic_crm.tbl_config
SET value = '1'
WHERE config_key = 'active_custom_color';
```

Verify:

```sql
SELECT config_key, value
FROM tic_crm.tbl_config
WHERE config_key IN ('active_custom_color', 'sidebar_theme', 'RTL');
```

Expected: `active_custom_color = 1`, `sidebar_theme = bg-purple-dark`, `RTL = 0`.

- [ ] **Step 3: Confirm the sidebar still renders with correct colours**

Open `/admin/dashboard`. The sidebar must be dark with a working active state. If it turns
white or unstyled, `bg-custom.php` has a bug — revert A1-3 and investigate.

- [ ] **Step 4: Reset the accent override from Step 1** (reload the page; it is not persisted)
- [ ] **Step 5: Commit the config change**

```powershell
git commit -m "A1-4: enable custom colour theming path"
```

> `tbl_config` lives in the database, not the repo, so this commit will be empty. That is
> expected — record the SQL in `plan/_data/part-a-css-inventory.md` so it is reproducible on the
> demo database too. Apply the same statement to `tic_crm_demo.tbl_config`.

**Verify:** sidebar renders correctly; `active_custom_color = 1` in both databases.
**Effort:** S

---

## Exit gate

- [ ] `assets/css/tokens.css` exists with all 14 token groups
- [ ] Loaded in all 4 shells, before `app.css`
- [ ] Console confirms `--c-accent` resolves to `#0d9488`
- [ ] `bg-custom.php` syntax bugs fixed; `php -l` clean
- [ ] `active_custom_color = 1` in both `tic_crm` and `tic_crm_demo`
- [ ] No PHP file modified other than the 4 one-line `<link>` insertions

## Tasks: 4 · Completed: 0 · Remaining: 4
