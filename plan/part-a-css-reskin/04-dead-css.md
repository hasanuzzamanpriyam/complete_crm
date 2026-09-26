# Part A · A4 — Dead CSS Removal

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development
> (recommended) or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** delete 745 KB of CSS that nothing loads.

**Safety:** every file here is proven unreferenced **before** deletion. Run the verification
command for each file, and only delete if it returns zero references.

---

## A4-1 — Delete proven-dead files

- [ ] **Step 1: Re-verify `frontent.css` is unreferenced**

```powershell
Select-String -Path application,assets -Pattern 'frontent\.css' -Recurse -ErrorAction SilentlyContinue |
  Where-Object { $_.Filename -ne 'frontent.css' }
```

Expected: **no output**. (Note: `frontend` — with a `d` — is a different, real directory. Do not
confuse the two.)

- [ ] **Step 2: Re-verify `bg-new copy.css` is unreferenced**

```powershell
Select-String -Path application,assets -Pattern 'bg-new copy' -Recurse -ErrorAction SilentlyContinue
```

Expected: **no output**. It is a stray copy of a theme file, and its filename contains a space so
it could never be a valid `sidebar_theme` value.

- [ ] **Step 3: Re-verify the 8 replaced `bg-*.css` files are now unreferenced**

A3-3 removed the dynamic `<link>` to `config_item('sidebar_theme')`. Confirm nothing else
references them:

```powershell
Select-String -Path application,assets -Pattern 'bg-(white|dark|green|info|purple|danger)' -Recurse -ErrorAction SilentlyContinue
```

Expected: **no output** from PHP or CSS files.

- [ ] **Step 4: Delete only if Steps 1–3 were clean**

```powershell
git rm assets/css/frontent.css "assets/css/bg-new copy.css"
git rm assets/css/bg-white.css assets/css/bg-dark.css `
       assets/css/bg-green.css assets/css/bg-info.css `
       assets/css/bg-purple.css assets/css/bg-danger.css `
       assets/css/bg-green-dark.css assets/css/bg-info-dark.css `
       assets/css/bg-purple-dark.css assets/css/bg-danger-dark.css
```

- [ ] **Step 5: Verify + commit**

```powershell
composer smoke
git commit -m "A4-1: delete dead and superseded theme CSS"
```

**Verify:** `composer smoke` 20/20; browser network panel shows **no 404s** on any page;
Appearance settings still restyle the sidebar.
**Effort:** S

---

## A4-2 — Delete the RTL stylesheets

**Evidence RTL is unused — all three checks agree:**

| Source | Value |
|---|---|
| `tbl_config.RTL` | `0` |
| Accounts with `direction='rtl'` | **0 of 113** |
| Accounts using a non-English language | **0 of 113** (all English) |

`arabic` and `hebrew` language files exist in `application/language/` but are unused.

- [ ] **Step 1: Confirm the 4 RTL files and their total size**

```powershell
Get-ChildItem assets\css\*rtl* | Select-Object Name,Length
```

Expected: `app-rtl.css` 245,240 · `app-rtl.min.css` 218,165 · `bootstrap-rtl.css` 164,582 ·
`bootstrap-rtl.min.css` 116,842 — **744,789 bytes ≈ 727 KB**.

- [ ] **Step 2: ⚠️ Confirm the two `app-rtl` files are NOT identical**

```powershell
(Get-FileHash assets\css\app-rtl.css).Hash
(Get-FileHash assets\css\app-rtl.min.css).Hash
```

**These hashes will differ.** 245 KB vs 218 KB means the unminified and minified files have
**diverged**. Do not assume one is a build of the other. Record both hashes in
`plan/_data/part-a-css-inventory.md` before deleting, so a future Arabic/Hebrew effort knows the
exact state to restore from git history.

- [ ] **Step 3: Confirm the live condition is LTR**

```sql
SELECT config_key, config_value FROM tic_crm.tbl_config WHERE config_key = 'RTL';
```

Expected: `RTL = 0`.

- [ ] **Step 4: Delete**

```powershell
git rm assets/css/app-rtl.css assets/css/app-rtl.min.css `
       assets/css/bootstrap-rtl.css assets/css/bootstrap-rtl.min.css
```

- [ ] **Step 5: Verify + commit**

```powershell
composer smoke
git commit -m "A4-2: delete unused RTL stylesheets (727 KB)"
```

**Verify:** no 404s; the admin shell loads only `bootstrap.min.css` + `app.css`.
**Effort:** S

---

## A4-3 — Remove the RTL branch from the layout

`htmlheader.php` has an `if ($direction == 'rtl')` branch that selects the RTL stylesheets.
Removing the files without removing the branch will produce a 404 in any RTL session.

- [ ] **Step 1: Read the branch**

```powershell
Get-Content application\views\admin\components\htmlheader.php | Select-Object -Skip 28 -First 24
```

You will find a `$RTL` computation reading `session->userdata('direction')` and `config_item('RTL')`,
followed by conditional `<link>` tags for the RTL and LTR stylesheets.

- [ ] **Step 2: Replace the branch with unconditional LTR links**

```php
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/tokens.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" id="bscss">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/app.css?v=2" id="maincss">
```

Delete the `$RTL` variable and both conditional branches. **Do not** touch the
`config_item('sidebar_theme')` link — A3-3 already replaced that.

- [ ] **Step 3: Check the other 3 shells for the same branch**

```powershell
Select-String -Path application\views\client\components\htmlheader.php, `
                    application\views\frontend\components\htmlheader.php, `
                    application\views\booking\_layout_main.php `
                    -Pattern 'rtl|RTL' -CaseSensitive:$false
```

Apply the same removal wherever found.

- [ ] **Step 4: Remove the RTL control from the Appearance settings page**

```powershell
Select-String -Path application\views\admin\settings\appearance.php -Pattern 'rtl|RTL'
```

If an RTL toggle exists, remove the field. If it does not, note that and move on.

- [ ] **Step 5: Verify + commit**

```powershell
composer smoke
git commit -m "A4-3: remove RTL branch from layout shells"
```

**Verify:** `grep -ri rtl application/views/*/components/htmlheader.php` returns nothing;
no 404 for any `-rtl` asset; admin, client, frontend and booking shells all render.
**Effort:** S

---

## Exit gate

- [ ] `frontent.css`, `bg-new copy.css` and 10 superseded `bg-*.css` deleted
- [ ] 4 RTL stylesheets deleted, both `app-rtl` hashes recorded first
- [ ] RTL branch removed from all shells
- [ ] RTL control removed from Appearance settings (if present)
- [ ] `composer smoke` 20/20, **zero 404s** in the browser network panel
- [ ] ~772 KB of CSS removed total

## Tasks: 3 · Completed: 0 · Remaining: 3
