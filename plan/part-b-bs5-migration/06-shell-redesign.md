# Phase 06 — Shell Redesign

**Goal:** the visible "modern and attractive" payoff. A handful of files, huge perceived impact.
**Gate to exit:** admin, client, frontend and booking shells all match the DS-2 direction.

**This is where the redesign actually becomes visible.** Everything up to here was migration —
making BS5 work while preserving the old look. From here you change the look on purpose.

Depends on: `SF-5` complete, `DS-2` visual direction chosen, `DS-4` tokens live, `DS-5`
components specified.

---

## SH-1 — New stylesheet architecture

Current: `app.css` is **155 KB** of hand-written CSS plus 11 `bg-*.css` variants plus
`frontent.css` (dead) plus `bg-custom.php` (malformed). Replace with a layered structure:

```
assets/css/
  tokens.css            ← DS-4, the single source of truth
  base.css              ← reset, typography, element defaults  (~2 KB)
  utilities.css         ← small app-specific helpers over BS5     (~3 KB)
  components/
    buttons.css  cards.css  forms.css  tables.css
    sidebar.css  topbar.css modals.css  tabs.css
    badges.css   toasts.css  empty-states.css
  layout/
    admin.css  client.css  frontend.css  booking.css
  bootstrap3-pdf.css    ← frozen, Phase 02 (PDF track only)
```

Delete `app.css` and the 11 `bg-*.css` files once parity is proven.

1. Inventory every rule in `app.css` into one of: **BS5 already does this** (delete) /
   **needs a component rule** / **needs a token** / **dead** (delete).
2. Port only what BS5 does not provide.
3. Expect `app.css` to shrink from 155 KB to roughly 40–50 KB of genuinely custom rules.

**Verify:** no page loses a style it needs. Compare 20 pages against baseline screenshots.
**Effort:** L

---

## SH-2 — Sidebar

Current: fixed `width:230px`, `rgba(2,53,60,.95)`, `#0f778e` active pill, no grouping state
beyond `nav li.active`, custom-built collapse toggles.

Redesign:
- Token-driven background, border, and active state
- Group headers expand/collapse with a chevron; persist state in `localStorage`
- Active item: filled accent pill, not just a colour change
- Icon + label alignment grid so labels line up regardless of icon width
- Collapsed rail mode (icons only) at ≤1200 px, full mode above
- Section separators between functional groups
- Keyboard navigable; `aria-current="page"` on the active link

**Verify:** expand/collapse, persistence, rail mode, keyboard nav.
**Effort:** M

---

## SH-3 — Top bar

Current: `navbar-header`, `left_side_bar`, `right_menu`, `navbar-nav`, hidden search, multiple
`col-xs-4`/`col-xs-8` cells, `pull-right` clusters.

Redesign:
- Single flex row: burger · search · spacer · notifications · user menu · quick actions
- Command-style global search (defer real search wiring; the UI is the deliverable)
- Notification badge with count
- User menu: avatar, name, role, dropdown with profile/settings/logout
- Sticky on scroll, with a subtle shadow on scroll state
- Collapse to a burger below `lg`

**Verify:** at 1440 / 1280 / 992 / 768 / 375 px the bar stays usable and nothing overlaps.
**Effort:** M

---

## SH-4 — Page header and breadcrumbs

There is no consistent page-header pattern today — each page improvises one. Define it once:

```html
<div class="page-header">
  <div class="d-flex align-items-start justify-content-between gap-3">
    <div>
      <nav aria-label="breadcrumb">…</nav>
      <h1 class="h4 mb-1">Invoices</h1>
      <p class="text-muted mb-0">42 records · updated 2 min ago</p>
    </div>
    <div class="d-flex gap-2">…actions…</div>
  </div>
</div>
```

- Consistent title size, breadcrumb style, action-button placement
- Record-count / last-updated line where data supports it
- Actions right-aligned on desktop, full-width stacked on mobile

**Verify:** 10 pages converted as reference implementations for the page pass.
**Effort:** M

---

## SH-5 — Dashboard refresh

15 widgets today, all on one scrolling page. Redesign:

- KPI row first: revenue, outstanding, overdue, new leads, active projects, open tickets
  (derive from existing data; no new queries beyond what exists)
- Each KPI: value, delta vs last period, sparkline
- Charts in consistent cards (Chart.js replaces the mixed Chartist/Morris usage)
- Two-column layout on wide screens, stacked on mobile
- Skeleton loading state, then populate
- Empty states when a widget has no data (the app currently renders blank boxes)

**Verify:** no PHP errors; widgets populate with the same numbers as the baseline.
**Effort:** L

---

## SH-6 — Login and public pages

First impression for every user. `login.php` is bespoke and the tracker modal blocks the first
click.

- Centered card, product mark, clear hierarchy
- Show/hide password, remember me, forgot-password link
- Social-login buttons only if actually configured
- **Fix the `#trackerDownloadModal` blocker** — render it collapsed, or gate on a
  `tracker_download_dismissed` localStorage flag set by *every* dismiss path (not just
  `#tracker-download-dismiss`)
- Apply the same treatment to `login/register.php`, `login/forgot_password.php`
- Public pages: `career`, `book-free-consultation`, `frontend/*` — consistent header/footer,
  tokens, mobile-first

**Verify:** login works first-try with no modal interference; register and reset complete.
**Effort:** M

---

## SH-7 — Icon set (optional, decide in DS-2)

Font Awesome 4.7 (2014) is in use. It has no coherent filled/outlined pair and lacks many
modern glyphs. If DS-2 selects a modern direction, migrating to **Lucide** or **Phosphor** is a
prerequisite for the look landing properly:

1. Map every `fa-*` / `fas` / `far` usage to the new set
2. `fa-*` → `data-lucide="*"` (or the chosen set's syntax)
3. Replace `<i class="fa…">` markup
4. Initialize the icon runtime after each AJAX render

**Verify:** no missing-glyph boxes anywhere; icon weight consistent.
**Effort:** M (skip if DS-2 keeps FA)

---

## Exit gate

- [ ] Layered stylesheet structure in place; `app.css` and 11 `bg-*.css` deleted
- [ ] `tokens.css` is the only place brand colours are defined
- [ ] Sidebar: groups, persistence, rail mode, keyboard nav
- [ ] Top bar: responsive at 5 breakpoints, sticky, no overlap
- [ ] Page-header pattern applied to 10 reference pages
- [ ] Dashboard redesigned with KPI row and consistent cards
- [ ] Login/register/reset clean and unblocked
- [ ] `bg-custom.php` emitting valid token variables (`!important;;` and `solid<?=` fixed)
- [ ] DS-5 component spec visually matched in all 15 components
