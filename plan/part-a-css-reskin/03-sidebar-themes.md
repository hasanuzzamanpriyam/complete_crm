# Part A · A3 — Sidebar Themes

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development
> (recommended) or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** replace 8 near-duplicate colour files with one token-driven stylesheet, and refresh
the 2 that also carry layout.

## Current state

`assets/css/` contains 10 real theme files plus one stray copy:

| File | Size | Contains | Action |
|---|---|---|---|
| `bg-white.css` | 62,735 b | **layout + colour** | restyle (A3-2) |
| `bg-dark.css` | 32,305 b | **layout + colour** | restyle (A3-2) |
| `bg-danger.css` | 6,008 b | colour only | replace (A3-1) |
| `bg-danger-dark.css` | 5,911 b | colour only | replace (A3-1) |
| `bg-green-dark.css` | 5,974 b | colour only | replace (A3-1) |
| `bg-green.css` | 5,919 b | colour only | replace (A3-1) |
| `bg-info-dark.css` | 5,857 b | colour only | replace (A3-1) |
| `bg-info.css` | 5,620 b | colour only | replace (A3-1) |
| `bg-purple-dark.css` | 5,935 b | colour only | replace (A3-1) |
| `bg-purple.css` | 5,850 b | colour only | replace (A3-1) |
| `bg-new copy.css` | 6,255 b | stray duplicate | delete (A4-1) |
| `bg-custom.php` | 7,094 b | generator | already fixed in A1-3 |

The 8 colour-only files are ~6 KB each and differ only in hex values — **48 KB of near-duplicate
CSS.**

---

## A3-1 — Replace the 8 colour-only themes with one file

- [ ] **Step 1: Create `assets/css/themes.css`**

```css
/* =============================================================
   TIC CRM — Sidebar theme variants
   Replaces 8 near-duplicate bg-*.css files (~48 KB).
   Each theme is a token override; the layout lives in app.css.
   ============================================================= */

/* dark (default) */
.theme-dark {
  --c-sidebar-bg:        #14181c;
  --c-sidebar-bg-2:      #1c2126;
  --c-sidebar-text:      #b6bdc6;
  --c-sidebar-text-hi:   #ffffff;
  --c-sidebar-active:    #0d9488;
  --c-sidebar-border:    #262c33;
}

/* light */
.theme-light {
  --c-sidebar-bg:        #ffffff;
  --c-sidebar-bg-2:      #f1f3f5;
  --c-sidebar-text:      #4a5058;
  --c-sidebar-text-hi:   #1a1d21;
  --c-sidebar-active:    #0d9488;
  --c-sidebar-border:    #e3e6ea;
}

/* accent variants — same structure, different active colour */
.theme-green      { --c-sidebar-active: #0f9b6c; --c-sidebar-border: #262c33; }
.theme-info       { --c-sidebar-active: #2563eb; --c-sidebar-border: #262c33; }
.theme-purple     { --c-sidebar-active: #7c3aed; --c-sidebar-border: #262c33; }
.theme-danger     { --c-sidebar-active: #dc2626; --c-sidebar-border: #262c33; }
.theme-green-dark  { --c-sidebar-active: #0f9b6c; --c-sidebar-bg: #101413; --c-sidebar-bg-2: #171c1b; }
.theme-info-dark   { --c-sidebar-active: #2563eb; --c-sidebar-bg: #0f1319; --c-sidebar-bg-2: #161b23; }
.theme-purple-dark { --c-sidebar-active: #7c3aed; --c-sidebar-bg: #131017; --c-sidebar-bg-2: #1b1723; }
.theme-danger-dark { --c-sidebar-active: #dc2626; --c-sidebar-bg: #180f0f; --c-sidebar-bg-2: #221616; }
```

- [ ] **Step 2: Confirm `app.css` reads the tokens, not fixed colours**

Every sidebar rule must use `var(--c-sidebar-*)`. A2-7 established this. Verify:

```powershell
Select-String -Path assets\css\app.css -Pattern '#(fff|14181c|1c2126)' -Context 1,1 |
  Where-Object { $_.Context.PreContext -match 'sidebar' -or $_.Context.PostContext -match 'sidebar' }
```

Expected: **no matches** inside sidebar rules. Fix any that appear.

- [ ] **Step 3: Link `themes.css` after `app.css` in all 4 shells**

```html
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/themes.css">
```

- [ ] **Step 4: Verify the class is applied.** Confirm the theme wrapper element receives the
      class. Open `/admin/dashboard`, view source, and locate the element wrapping the sidebar.
      In the browser console:

```js
document.querySelector('.sidebar').closest('[class*="theme-"]')?.className
```

If this returns `null`, the theme class is applied to a different element — find it and note the
selector. **If the class is applied to `<body>`**, no change is needed; `.theme-*` on `body` works
because `:root` variables cascade to all descendants.

- [ ] **Step 5: Do NOT delete the 8 old files yet.** They are still referenced by
      `config_item('sidebar_theme')` in the settings dropdown. A3-3 fixes that first.

- [ ] **Step 6: Commit**

```powershell
git add assets/css/themes.css assets/css/app.css application/views/*/components/htmlheader.php
git commit -m "A3-1: replace 8 duplicate sidebar themes with token file"
```

**Verify:** `themes.css` loads with no 404; sidebar colours still come from the current setting.
**Effort:** M

---

## A3-2 — Refresh the 2 layout-carrying themes

`bg-white.css` (62 KB) and `bg-dark.css` (32 KB) are far larger because they contain **layout
rules**, not just colour. These must be consolidated into `app.css` so the sidebar has one
definition.

- [ ] **Step 1: Diff `bg-white.css` against `bg-dark.css` to isolate the layout rules**

```powershell
Compare-Object (Get-Content assets\css\bg-dark.css) (Get-Content assets\css\bg-white.css) |
  Group-Object { $_.SideIndicator } | Select-Object Count,Name
```

- [ ] **Step 2: Extract the shared layout rules** into `app.css` (they belong with the A2-7
      sidebar rules), converting their hardcoded colours to `var(--c-sidebar-*)`.
- [ ] **Step 3: Reduce both files to colour-token overrides only** — i.e. each becomes a
      10-line block identical in shape to the `.theme-*` entries in `themes.css`.
- [ ] **Step 4: Map the legacy names to the new theme classes**

Add to `themes.css` so existing `sidebar_theme` values keep working during transition:

```css
/* legacy sidebar_theme values → new theme classes */
.bg-white  { --c-sidebar-bg: #ffffff; --c-sidebar-bg-2: #f1f3f5; --c-sidebar-text: #4a5058; --c-sidebar-text-hi: #1a1d21; --c-sidebar-border: #e3e6ea; }
.bg-dark   { --c-sidebar-bg: #14181c; --c-sidebar-bg-2: #1c2126; --c-sidebar-text: #b6bdc6; --c-sidebar-text-hi: #ffffff; --c-sidebar-border: #262c33; }
.bg-green  { --c-sidebar-active: #0f9b6c; }
.bg-info   { --c-sidebar-active: #2563eb; }
.bg-purple { --c-sidebar-active: #7c3aed; }
.bg-danger { --c-sidebar-active: #dc2626; }
```

- [ ] **Step 5: Commit**

```powershell
git commit -m "A3-2: consolidate sidebar layout into app.css"
```

**Verify:** `bg-white` and `bg-dark` are now each under 1 KB. Sidebar layout is unchanged at
1440 px and 1024 px.
**Effort:** M

---

## A3-3 — Update the settings dropdown

The Appearance settings page lists theme options by filename. Point it at the new names.

- [ ] **Step 1: Find the options source**

```powershell
Select-String -Path application\views\admin\settings\appearance.php -Pattern 'bg-|sidebar_theme' -Context 2,2
```

- [ ] **Step 2: Map old values to new ones.** The dropdown options become:

| Old `sidebar_theme` value | New class |
|---|---|
| `bg-dark` | `theme-dark` |
| `bg-white` | `theme-light` |
| `bg-green` | `theme-green` |
| `bg-info` | `theme-info` |
| `bg-purple` | `theme-purple` |
| `bg-danger` | `theme-danger` |
| `bg-green-dark` | `theme-green-dark` |
| `bg-info-dark` | `theme-info-dark` |
| `bg-purple-dark` | `theme-purple-dark` |
| `bg-danger-dark` | `theme-danger-dark` |
| *(custom)* | `bg-custom` → driven by `bg-custom.php` |

- [ ] **Step 3: Migrate the stored value in both databases**

> **Column name:** `tbl_config` is `(config_key PRIMARY, value TEXT)` — there is **no**
> `config_value` column, so the `UPDATE`s below must write `value`.
>
> **Migrate to the equivalent, not to a fixed default.** Both databases currently store
> `sidebar_theme = bg-purple-dark` (verified — `plan/_data/part-a-css-inventory.md` §7), and
> the mapping table above translates that to `theme-purple-dark`. Writing `theme-dark` here
> would silently change the user's chosen sidebar colour during a refactor.

```sql
UPDATE tic_crm.tbl_config     SET value = 'theme-purple-dark' WHERE config_key = 'sidebar_theme';
UPDATE tic_crm_demo.tbl_config SET value = 'theme-purple-dark' WHERE config_key = 'sidebar_theme';
```

Confirm:

```sql
SELECT config_key, value FROM tic_crm.tbl_config WHERE config_key = 'sidebar_theme';
```

Expected: `sidebar_theme` → `theme-purple-dark`.

- [ ] **Step 4: Update the 3 new CSS files in `htmlheader.php:53`**

Current:

```php
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/<?= config_item('sidebar_theme') ?>.css">
```

Replace with a single unconditional include of `themes.css` (already added in A3-1), plus the
theme class on the wrapper element. The per-file `<link>` is no longer needed.

- [ ] **Step 5: Commit**

```powershell
git add application/views/admin/settings/appearance.php application/views/admin/components/htmlheader.php
git commit -m "A3-3: point theme setting at token themes"
```

**Verify:** changing the theme in Appearance settings restyles the sidebar immediately, with no
missing-CSS 404.
**Effort:** M

---

## A3-4 — Verify all 10 themes

- [ ] **Step 1: For each of the 10 theme values, set it via SQL, reload, and screenshot**

```sql
UPDATE tic_crm.tbl_config SET value = '<value>' WHERE config_key = 'sidebar_theme';
```

(`value`, not `config_value` — see the schema note in A3-3 Step 3.) Remember to restore
`theme-purple-dark` afterwards so the app is left on the theme it was shipped with.

Save the 10 screenshots to `plan/_baseline/part-a/themes/`.

- [ ] **Step 2: For each, confirm:** sidebar renders, active item is visible against its
      background (check contrast on `theme-green-dark` and `theme-danger-dark` — the lightest
      active colour on the darkest background is the risky combination), text is readable.
- [ ] **Step 3: Test the custom theme** — set a custom colour in Appearance settings and
      confirm `bg-custom.php` applies it.
- [ ] **Step 4: Reset to `theme-dark` and commit**

```powershell
git commit -m "A3-4: verify all 10 sidebar themes"
```

**Verify:** 10 theme screenshots captured; every theme has readable text and a visible active state.
**Effort:** M

---

## Exit gate

- [ ] `themes.css` exists and is loaded in all 4 shells
- [ ] 8 colour-only `bg-*.css` reduced to token overrides, then deleted (A4-1)
- [ ] `bg-white.css` and `bg-dark.css` each under 1 KB
- [ ] Sidebar layout rules live in `app.css`
- [ ] Settings dropdown maps to the new theme names
- [ ] `sidebar_theme` migrated in **both** databases
- [ ] 10 theme screenshots captured, all readable
- [ ] Custom theme via `bg-custom.php` still works

## Tasks: 4 · Completed: 0 · Remaining: 4
