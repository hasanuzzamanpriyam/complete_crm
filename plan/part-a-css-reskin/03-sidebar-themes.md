# Part A · A3 — Sidebar Themes

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development
> (recommended) or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** replace 10 near-duplicate colour files with one token-driven stylesheet covering
**14 themes**, and refresh the 2 that also carry layout.

> **Revised 2026-09-27.** Three corrections to the original plan, all found by inspection:
> - The theme chooser is `application/views/admin/settings/theme.php`. There is **no**
>   `settings/appearance.php`; the original A3-3 pointed at it.
> - Only **1** shell links `sidebar_theme` (`admin/components/htmlheader.php:55`), not 4.
> - The count is **14** themes, not 10 — four new files, and the Soft UI white-pill sidebar
>   needs 7 tokens per theme, not the 6 the old `bg-*.css` files carried.

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

## A3-1 — One token file, 14 themes

> **Depends on A2-7.** The sidebar must already *consume* `--c-sidebar-*` before this task can
> give it 14 different values. Running A3-1 before A2-7 produces themes that do nothing.

- [ ] **Step 1: Create `assets/css/themes.css`**

  Seven tokens per theme. The two new ones — `--c-sidebar-pill` and `--c-sidebar-pill-shadow`
  — exist because Soft UI's active sidebar item is a **white floating pill** with a soft
  shadow, not a filled bar. The old `bg-*.css` files expressed active state as a flat
  `background-color`, which cannot do this.

  ```css
  /* =============================================================
     TIC CRM — Sidebar theme variants (Soft UI)
     Each theme is a token override; all layout lives in app.css.
     14 themes: 2 neutral + 6 hue pairs.
     ============================================================= */

  /* ---------- neutral ---------- */
  .theme-white  { --c-sidebar-bg:#f4f4f5; --c-sidebar-bg-2:#e9eaec; --c-sidebar-text:#515253;
                  --c-sidebar-text-hi:#27272a; --c-sidebar-accent:#5e72e4;
                  --c-sidebar-pill:#ffffff; --c-sidebar-pill-shadow:var(--sh-1); }
  .theme-dark   { --c-sidebar-bg:#1a1d2b; --c-sidebar-bg-2:#252a3a; --c-sidebar-text:#9aa3bd;
                  --c-sidebar-text-hi:#7b8ff7; --c-sidebar-accent:#7b8ff7;
                  --c-sidebar-pill:#252a3a; --c-sidebar-pill-shadow:var(--sh-2); }

  /* ---------- teal ---------- */
  .theme-teal      { --c-sidebar-bg:#f2f7f6; --c-sidebar-bg-2:#e3efec; --c-sidebar-text:#4a5a58;
                     --c-sidebar-text-hi:#0f766e; --c-sidebar-accent:#0d9488;
                     --c-sidebar-pill:#ffffff; --c-sidebar-pill-shadow:var(--sh-1); }
  .theme-teal-dark { --c-sidebar-bg:#0f1a19; --c-sidebar-bg-2:#172524; --c-sidebar-text:#8fa8a5;
                     --c-sidebar-text-hi:#2dd4bf; --c-sidebar-accent:#2dd4bf;
                     --c-sidebar-pill:#172524; --c-sidebar-pill-shadow:var(--sh-2); }

  /* ---------- cyan ---------- */
  .theme-info      { --c-sidebar-bg:#f1f7fb; --c-sidebar-bg-2:#e2f0f8; --c-sidebar-text:#43536b;
                     --c-sidebar-text-hi:#0369a1; --c-sidebar-accent:#0ea5e9;
                     --c-sidebar-pill:#ffffff; --c-sidebar-pill-shadow:var(--sh-1); }
  .theme-info-dark { --c-sidebar-bg:#0d1a24; --c-sidebar-bg-2:#132634; --c-sidebar-text:#8aa6bb;
                     --c-sidebar-text-hi:#38bdf8; --c-sidebar-accent:#38bdf8;
                     --c-sidebar-pill:#132634; --c-sidebar-pill-shadow:var(--sh-2); }

  /* ---------- green ---------- */
  .theme-green      { --c-sidebar-bg:#f1f8f5; --c-sidebar-bg-2:#e2f2ea; --c-sidebar-text:#456055;
                      --c-sidebar-text-hi:#047857; --c-sidebar-accent:#10b981;
                      --c-sidebar-pill:#ffffff; --c-sidebar-pill-shadow:var(--sh-1); }
  .theme-green-dark { --c-sidebar-bg:#0c1a14; --c-sidebar-bg-2:#132a20; --c-sidebar-text:#87a698;
                      --c-sidebar-text-hi:#34d399; --c-sidebar-accent:#34d399;
                      --c-sidebar-pill:#132a20; --c-sidebar-pill-shadow:var(--sh-2); }

  /* ---------- violet ---------- */
  .theme-purple      { --c-sidebar-bg:#f5f3fc; --c-sidebar-bg-2:#eae5f9; --c-sidebar-text:#544e73;
                       --c-sidebar-text-hi:#6d28d9; --c-sidebar-accent:#8b5cf6;
                       --c-sidebar-pill:#ffffff; --c-sidebar-pill-shadow:var(--sh-1); }
  .theme-purple-dark { --c-sidebar-bg:#16121f; --c-sidebar-bg-2:#221b32; --c-sidebar-text:#a396c2;
                       --c-sidebar-text-hi:#a78bfa; --c-sidebar-accent:#a78bfa;
                       --c-sidebar-pill:#221b32; --c-sidebar-pill-shadow:var(--sh-2); }

  /* ---------- red ---------- */
  .theme-danger      { --c-sidebar-bg:#fdf4f4; --c-sidebar-bg-2:#f9e6e6; --c-sidebar-text:#6b4a4a;
                       --c-sidebar-text-hi:#b91c1c; --c-sidebar-accent:#ef4444;
                       --c-sidebar-pill:#ffffff; --c-sidebar-pill-shadow:var(--sh-1); }
  .theme-danger-dark { --c-sidebar-bg:#1c1010; --c-sidebar-bg-2:#2d1818; --c-sidebar-text:#bb8f8f;
                       --c-sidebar-text-hi:#f87171; --c-sidebar-accent:#f87171;
                       --c-sidebar-pill:#2d1818; --c-sidebar-pill-shadow:var(--sh-2); }

  /* ---------- amber ---------- */
  .theme-amber      { --c-sidebar-bg:#fdf8ef; --c-sidebar-bg-2:#f8eed9; --c-sidebar-text:#6b5a3d;
                      --c-sidebar-text-hi:#b45309; --c-sidebar-accent:#f59e0b;
                      --c-sidebar-pill:#ffffff; --c-sidebar-pill-shadow:var(--sh-1); }
  .theme-amber-dark { --c-sidebar-bg:#1c160a; --c-sidebar-bg-2:#2b2110; --c-sidebar-text:#bda877;
                      --c-sidebar-text-hi:#fbbf24; --c-sidebar-accent:#fbbf24;
                      --c-sidebar-pill:#2b2110; --c-sidebar-pill-shadow:var(--sh-2); }
  ```

  Every hex above is deliberate. `--c-sidebar-text-hi` is the *active item's text and icon* and
  must clear **4.5:1** against `--c-sidebar-pill`; `--c-sidebar-text` must clear 4.5:1 against
  `--c-sidebar-bg`. A2-13's verify step should measure both with a contrast check, not by eye.

- [ ] **Step 2: Add a default `theme-dark` to `tokens.css`** so a missing or stale
      `sidebar_theme` value still renders a sane sidebar instead of an unstyled one.

- [ ] **Step 3: Confirm `app.css` reads the tokens, not fixed colours**

      Every sidebar rule must use `var(--c-sidebar-*)` and `var(--c-sidebar-pill*)`. A2-7
      established this. Verify:

      ```powershell
      Select-String -Path assets\css\app.css -Pattern '#(fff|ffffff|14181c|1c2126|1e1e2d)' -Context 1,1 |
        Where-Object { $_.Context.PreContext -match 'sidebar' -or $_.Context.PostContext -match 'sidebar' }
      ```

      Expected: **no matches** inside sidebar rules. Fix any that appear.

- [ ] **Step 4: Link `themes.css` in the one shell that themes the sidebar**

      `application/views/admin/components/htmlheader.php`, **after `app.css` but BEFORE
      `bg-custom.php` at L51.** Order matters: `bg-custom.php` is the user's custom colour
      override, so it must win. The current file has the same bug in reverse —
      `sidebar_theme.css` is linked at L55, *after* `bg-custom.php` at L51, so the theme
      always beats the custom picker. Confirm the bug in the browser before "fixing" it, then:

      ```html
      <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/themes.css">
      ```

      and delete the L55 per-file `<link>`.

- [ ] **Step 5: Apply the theme class.** Confirm which element carries it:

      ```js
      document.querySelector('.sidebar').closest('[class*="theme-"]')?.className
      ```

      If this returns `null`, find the element that does. If the class lands on `<body>`,
      no change is needed — `:root` variables cascade to all descendants.

- [ ] **Step 6: Do NOT delete the old files yet.** They are still referenced by
      `config_item('sidebar_theme')` in the settings page. A3-3 fixes that first.

- [ ] **Step 7: Commit**

      ```powershell
      git add assets/css/themes.css assets/css/tokens.css assets/css/app.css application/views/admin/components/htmlheader.php
      git commit -m "A3-1: 14 token-driven sidebar themes"
      ```

**Verify:** `themes.css` loads with no 404; all 14 classes render a distinct sidebar;
`composer test:theme` still exits 0.
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
  .bg-white  { --c-sidebar-bg:#f4f4f5; --c-sidebar-bg-2:#e9eaec; --c-sidebar-text:#515253; --c-sidebar-text-hi:#27272a; --c-sidebar-accent:#5e72e4; --c-sidebar-pill:#ffffff; }
  .bg-dark   { --c-sidebar-bg:#1a1d2b; --c-sidebar-bg-2:#252a3a; --c-sidebar-text:#9aa3bd; --c-sidebar-text-hi:#7b8ff7; --c-sidebar-accent:#7b8ff7; --c-sidebar-pill:#252a3a; }
  .bg-teal   { --c-sidebar-bg:#f2f7f6; --c-sidebar-bg-2:#e3efec; --c-sidebar-text:#4a5a58; --c-sidebar-text-hi:#0f766e; --c-sidebar-accent:#0d9488; --c-sidebar-pill:#ffffff; }
  .bg-info   { --c-sidebar-bg:#f1f7fb; --c-sidebar-bg-2:#e2f0f8; --c-sidebar-text:#43536b; --c-sidebar-text-hi:#0369a1; --c-sidebar-accent:#0ea5e9; --c-sidebar-pill:#ffffff; }
  .bg-green  { --c-sidebar-bg:#f1f8f5; --c-sidebar-bg-2:#e2f2ea; --c-sidebar-text:#456055; --c-sidebar-text-hi:#047857; --c-sidebar-accent:#10b981; --c-sidebar-pill:#ffffff; }
  .bg-purple { --c-sidebar-bg:#f5f3fc; --c-sidebar-bg-2:#eae5f9; --c-sidebar-text:#544e73; --c-sidebar-text-hi:#6d28d9; --c-sidebar-accent:#8b5cf6; --c-sidebar-pill:#ffffff; }
  .bg-danger { --c-sidebar-bg:#fdf4f4; --c-sidebar-bg-2:#f9e6e6; --c-sidebar-text:#6b4a4a; --c-sidebar-text-hi:#b91c1c; --c-sidebar-accent:#ef4444; --c-sidebar-pill:#ffffff; }
  .bg-amber  { --c-sidebar-bg:#fdf8ef; --c-sidebar-bg-2:#f8eed9; --c-sidebar-text:#6b5a3d; --c-sidebar-text-hi:#b45309; --c-sidebar-accent:#f59e0b; --c-sidebar-pill:#ffffff; }
  /* the four *-dark values map 1:1 onto .theme-*-dark */
  ```

- [ ] **Step 5: Commit**

```powershell
git commit -m "A3-2: consolidate sidebar layout into app.css"
```

**Verify:** `bg-white` and `bg-dark` are now each under 1 KB. Sidebar layout is unchanged at
1440 px and 1024 px.
**Effort:** M

---

## A3-3 — Update the theme chooser

The chooser is `application/views/admin/settings/theme.php` — the `sidebar_theme` radio
block is at **L315-466**, ten hardcoded swatches, each with a `data-load-css` preview
attribute pointing at its `bg-*.css`.

- [ ] **Step 1: Confirm the options source**

  ```powershell
  Select-String -Path application\views\admin\settings\theme.php -Pattern 'bg-|sidebar_theme' -Context 2,2
  ```

- [ ] **Step 2: Map old values to new ones.** The 10 existing radios get the 4 new ones
      added beside them — 14 total. The `data-load-css` preview attribute is a live-preview
      nicety; once `themes.css` is unconditional the attribute can go, but keeping it costs
      nothing and preserves the hover-preview behaviour.

  | Old `sidebar_theme` value | New class |
  |---|---|
  | `bg-white` | `theme-white` |
  | `bg-dark` | `theme-dark` |
  | `bg-teal` *(new)* | `theme-teal` |
  | `bg-teal-dark` *(new)* | `theme-teal-dark` |
  | `bg-info` | `theme-info` |
  | `bg-info-dark` | `theme-info-dark` |
  | `bg-green` | `theme-green` |
  | `bg-green-dark` | `theme-green-dark` |
  | `bg-purple` | `theme-purple` |
  | `bg-purple-dark` | `theme-purple-dark` |
  | `bg-danger` | `theme-danger` |
  | `bg-danger-dark` | `theme-danger-dark` |
  | `bg-amber` *(new)* | `theme-amber` |
  | `bg-amber-dark` *(new)* | `theme-amber-dark` |
  | *(custom)* | `bg-custom` → driven by `bg-custom.php` |

- [ ] **Step 3: Add 4 new CSS preview classes**

  The swatch previews are `.color.bg-*` utility classes in `app.css` (the block around
  **L2940-3097** holds `bg-info-light`, `bg-danger-light`, `bg-green-light`,
  `bg-purple-light`, `bg-gray-darker`, `bg-gray-dark`). The 4 new themes need
  `bg-teal`, `bg-teal-dark`, `bg-amber`, `bg-amber-dark` in that same shape. While there,
  round `.setting-color > label` from its hardcoded `border-radius: 3px` (app.css ~L5734)
  to `var(--r-md)` so the swatches match the new card radius.

- [ ] **Step 4: Migrate the stored value in both databases**

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

- [ ] **Step 5: Update the link in `htmlheader.php:55`**

  Current:

  ```php
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/<?= config_item('sidebar_theme') ?>.css">
  ```

  Replace with the single unconditional include of `themes.css` (already added in A3-1 Step 4,
  positioned *before* `bg-custom.php`), plus the theme class on the wrapper element. The
  per-file `<link>` is no longer needed — and leaving it in place is what causes the
  custom-colour precedence bug recorded in `STATUS.md`.

- [ ] **Step 6: Commit**

  ```powershell
  git add application/views/admin/settings/theme.php assets/css/app.css application/views/admin/components/htmlheader.php
  git commit -m "A3-3: 14 theme swatches, point chooser at token themes"
  ```

**Verify:** changing the theme in settings restyles the sidebar immediately, with no
missing-CSS 404; a custom colour set with `active_custom_color` on now actually overrides
the theme.
**Effort:** M

---

## A3-4 — Verify all 14 themes

- [ ] **Step 1: For each of the 14 theme values, set it via SQL, reload, and screenshot**

  ```sql
  UPDATE tic_crm.tbl_config SET value = '<value>' WHERE config_key = 'sidebar_theme';
  ```

  (`value`, not `config_value` — see the schema note in A3-3 Step 4.) Remember to restore
  `theme-purple-dark` afterwards so the app is left on the theme it was shipped with.

  Save the 14 screenshots to `plan/_baseline/part-a/themes/`.

- [ ] **Step 2: For each, confirm three things — measured, not eyeballed:**

  1. `--c-sidebar-text` clears **4.5:1** against `--c-sidebar-bg` (body nav items readable)
  2. `--c-sidebar-text-hi` clears **4.5:1** against `--c-sidebar-pill` (active item readable)
  3. The active pill's `--c-sidebar-pill-shadow` is actually rendering — a white pill on a
     white sidebar with no shadow is invisible, which is the specific failure mode of the
     light themes

  The risky pairs are `theme-amber` (low-chroma yellow on a light pill) and
  `theme-teal-dark` (light accent on a near-black sidebar).

- [ ] **Step 3: Test the custom theme** — set a custom colour in the settings page with
  `active_custom_color` on and confirm `bg-custom.php` wins over `themes.css`.

- [ ] **Step 4: Reset to `theme-purple-dark` and commit**

  ```powershell
  git commit -m "A3-4: verify all 14 sidebar themes"
  ```

**Verify:** 14 theme screenshots captured; every theme passes both contrast checks and the
active state is visible.
**Effort:** M

---

## Exit gate

- [ ] `themes.css` exists and is loaded in the admin shell, before `bg-custom.php`
- [ ] 12 colour-only `bg-*.css` reduced to token overrides, then deleted (A4-1)
- [ ] `bg-white.css` and `bg-dark.css` each under 1 KB
- [ ] Sidebar layout rules live in `app.css`
- [ ] Settings chooser offers 14 swatches
- [ ] `sidebar_theme` migrated in **both** databases
- [ ] 14 theme screenshots captured, all passing 4.5:1
- [ ] Custom theme via `bg-custom.php` overrides the chosen theme

## Tasks: 4 · Completed: 0 · Remaining: 4
