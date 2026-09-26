# Phase 00 — Design System

**Goal:** define the target look before writing any of it.
**Gate to exit:** DS-2 visual direction approved by you; DS-4 token file exists and renders.

This phase is mostly decisions. Nothing here touches production pages, so it can proceed in
parallel with Phase 01.

---

## DS-1 — Diagnose the current visual problems

Document what actually looks bad, so the redesign fixes causes rather than symptoms.

| Problem | Evidence |
|---|---|
| Dated shell | Angle 3.2 (2014) admin theme — squared corners, heavy gradients, cramped sidebar |
| No design system | 11 near-duplicate `bg-*.css` files; colours hardcoded in 22,587 inline styles |
| Inconsistent components | Same "panel" built 5+ different ways; buttons vary per page |
| Broken responsiveness | `col-xs-*` used 654× — the `xs` breakpoint is ~30% of all column usage |
| Poor information density | 63 `window.print()`-oriented views, wide tables, no sticky toolbars |
| Accessibility debt | `label-*` misused as badges, icon-only buttons with no `aria-label`, `hidden-print` 182× |

**Verify:** `plan/_data/visual_audit.md` exists with before-screenshots per problem.
**Effort:** S

---

## DS-2 — Choose a visual direction ⚠️ NEEDS YOUR INPUT

Pick a target aesthetic before any visual work. Everything from `SH-1` onward depends on it.

Candidate directions — choose one, or supply references:

1. **Refined dense** — Linear/Vercel-style. Compact rows, subtle borders, one accent colour,
   high information density. Best for a 633-page back-office CRM where users live all day.
2. **Modern corporate** — Stripe/Chargebee-style. Generous whitespace, card-based, soft
   shadows, friendly. Best if the CRM is also customer-facing.
3. **Minimal monochrome** — Vercel/Notion-style. Near-neutral palette, colour reserved for
   status only. Lowest risk, easiest to keep consistent at scale.
4. **Keep-and-refresh** — preserve the current teal/cyan brand, modernize spacing, radius,
   typography and shadows only. Lowest stakeholder friction.

Also decide:

- **Corner radius** (recommend 6–8px; today it is mixed 0/2/3/4px)
- **Type scale** (recommend 14px base, 1.5 line-height; today 14px with inconsistent line-heights)
- **Dark mode?** (recommend defer to a later phase — it roughly doubles token work)
- **Icon set** — keep Font Awesome 4.7 (2014) or move to a modern set such as Lucide or
  Phosphor. Note: 4.7 lacks a coherent filled/outlined pair and many modern glyphs.

**Verify:** direction recorded in `plan/_data/design_direction.md` with 3–5 reference links/screens.
**Effort:** S (you) — blocks the visual phases

---

## DS-3 — Token inventory

Map every visual constant currently in use to a future token.

| Token group | Examples found in current CSS/DB |
|---|---|
| Brand | `#1f9494` top bar, `rgba(2,53,60,.95)` sidebar, `#0f778e` active, `#1c7086` |
| Status | success/warning/danger/info across `btn-*` and `label-*` |
| Surfaces | body `rgba(229,252,252,.81)`, card, sidebar, topbar, overlay |
| Text | primary, secondary, muted, inverse, link |
| Spacing | 4/8/12/16/24 scale — currently ad hoc |
| Radius | 0/2/3/4px mixed |
| Shadow | 3 variants, inconsistent |
| Type | 6 sizes, 3 weights, inconsistent line-heights |
| Motion | durations and easings — currently only in `animate.css` |

**Verify:** `plan/_data/tokens_current.json` enumerates every hardcoded colour found in
`app.css` + the 11 `bg-*.css` + `bg-custom.php`.
**Effort:** M

---

## DS-4 — Token file

Create `assets/css/tokens.css` as the single source of truth.

```css
:root {
  /* brand */
  --c-primary:            #1f9494;
  --c-primary-hover:      #1b8181;
  --c-primary-subtle:     #e6f4f4;
  --c-primary-contrast:   #ffffff;

  /* status */
  --c-success: #18b689;  --c-success-subtle: #e6f7f1;
  --c-warning: #f5a524;  --c-warning-subtle: #fef6e7;
  --c-danger:  #e5484d;  --c-danger-subtle:  #fdecec;
  --c-info:    #3e8eff;  --c-info-subtle:    #e8f1ff;

  /* surfaces */
  --c-body-bg:     #f6f8fa;
  --c-surface:     #ffffff;
  --c-surface-alt: #f1f4f7;
  --c-border:      #e3e8ee;
  --c-overlay:     rgb(16 24 40 / .55);

  /* text */
  --c-text:        #1a202c;
  --c-text-muted:  #5f6b7a;
  --c-text-subtle: #8b95a5;
  --c-text-inverse:#ffffff;

  /* spacing scale */
  --sp-1: 4px;  --sp-2: 8px;  --sp-3: 12px; --sp-4: 16px;
  --sp-5: 24px; --sp-6: 32px; --sp-7: 48px; --sp-8: 64px;

  /* radius */
  --r-sm: 4px; --r-md: 6px; --r-lg: 10px; --r-full: 999px;

  /* elevation */
  --sh-1: 0 1px 2px rgb(16 24 40 / .06);
  --sh-2: 0 2px 8px rgb(16 24 40 / .08);
  --sh-3: 0 8px 24px rgb(16 24 40 / .12);

  /* type */
  --fs-xs: 12px; --fs-sm: 13px; --fs-base: 14px;
  --fs-lg: 16px;  --fs-xl: 20px; --fs-2xl: 26px;
  --lh-tight: 1.25; --lh-normal: 1.5; --lh-loose: 1.7;

  /* motion */
  --dur-fast: 120ms; --dur-base: 200ms;
  --ease: cubic-bezier(.4, 0, .2, 1);
}
```

Wire `bg-custom.php` to emit these variables from `tbl_config` instead of the 11 static files.
Set `active_custom_color=1` so the token path is the live one, and keep exactly one static
`bg-fallback.css` for the case where tokens fail to load.

**Verify:** toggling a `tbl_config` value restyles the app with no CSS file changes. The 11
`bg-*.css` files become dead (`DB-3`).
**Effort:** M

---

## DS-5 — Component specification

Specify the target look for every repeated component **before** converting pages, so
conversion is mechanical rather than creative.

Components to spec (with before/after reference):

| Component | Occurrences | Change |
|---|---|---|
| Data table + toolbar | `render_table()` / `make_datatables()` | sticky header, zebra off, 40px rows, hover tint |
| Filter/search form | ~300 pages | single row, inline labels above fields |
| Button family | `btn_*` helpers | 4 sizes, consistent icon+label spacing |
| Status badge | `label-*` 476× | → `badge` with subtle bg + strong text |
| Card / panel | `panel` 985× | → `card`, border instead of shadow, 16px padding |
| Modal | 4 shells + 34 partials | BS5 sizes, sticky footer |
| Tabs | `common/tabs.php` | underline style, count pill |
| Toast / alert | `message_box()` | already toastr — restyle only |
| Empty state | ad hoc | illustration + single clear CTA |
| Loading state | ad hoc | skeleton rows for tables |
| Form field | `custom_form_Fields()` | label above, help/error below, consistent height |
| Sidebar | `components/sidebar.php` | collapsible groups, active pill |
| Top bar | `components/header.php` | search-first, user menu right |
| Breadcrumb | `Breadcrumbs.php` | muted, chevron separators |
| Pagination | DataTables BS5 | consistent |

**Verify:** `plan/design/components.md` has a before/after image or code sample per component.
**Effort:** L

---

## DS-6 — Accessibility baseline

Set the standard the redesign will be held to.

- WCAG 2.1 AA contrast for all text and interactive states
- Full keyboard operability: sidebar, modals, DataTables, dropdowns, datepickers
- Visible focus ring on every interactive element (today largely absent)
- `aria-label` on all icon-only buttons
- Landmarks: `header`/`nav`/`main`/`aside`/`footer` on every layout
- Reduced-motion support via `prefers-reduced-motion`

Add `pa11y` or `axe-core` to the `SN` crawler so this is enforced, not aspirational.

**Verify:** crawler reports 0 critical/serious violations on the redesigned reference pages.
**Effort:** M

---

## Exit gate

- [ ] `visual_audit.md` documents the current problems with screenshots
- [ ] **Visual direction approved and recorded in `design_direction.md`**
- [ ] `tokens_current.json` inventories every hardcoded colour
- [ ] `assets/css/tokens.css` exists and is live
- [ ] `components.md` specifies all 15 components
- [ ] Accessibility standard defined and an automated check exists
