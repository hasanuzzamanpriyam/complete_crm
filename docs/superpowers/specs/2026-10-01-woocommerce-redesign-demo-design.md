# WooCommerce Section Redesign — Demo Spec

**Status:** approved in chat (Approach A, "clickable demo only") · **Date:** 2026-10-01
**Deliverable:** `_design/woocommerce-full.html` — a single, self-contained, clickable mockup served locally. **No changes to the real application.**

## Goal

Let the user see the redesigned WooCommerce section (Orders, Products, Customers, Stores) as a fully clickable demo, following the approved dashboard/calendar design language.

## Decisions (from brainstorm)

- **Demo only** — no app code, no real data wiring, no charts.
- **One-page app-style:** real app chrome (dark sidebar + white topbar); sidebar switches the 4 pages in-place; modals and order detail open as overlays; one URL.
- **Scope = everything:** 4 list pages + all modals (new/edit store, edit order status, edit product, edit customer) + order detail page.
- **Approach A — calendar-style lists:** KPI strip + full-width card with toolbar + new dashboard table style; modal system = calendar `.cal-modal`.
- Design source of truth: this spec + approved references `_design/dashboard-modern.html` (table `.tbl`, tags), `.superpowers/brainstorm/108-1790752228/content/calendar-full.html` (KPI strip, toolbar, modal), `assets/css/tokens.css` (values).

## Demo chrome

- Dark sidebar (`#14181c`) with the real grouped menu; **Woocommerce** group expanded, items: Orders, Products, Customers, Stores; active item teal-highlighted; clicking switches page in-place.
- White topbar: menu button, search input, `+ New` teal button, bell/user chip — modeled on the live header/dashboard mockups.
- Toast stack (bottom-right) for demo actions.

## Shared list-page anatomy

1. **Page header:** title (e.g. "Orders") + subtitle line.
2. **KPI strip** — 4 cards (calendar `.kpi-strip` pattern: icon chip, big value, label):
   - Orders: Total orders · Revenue (Σ total) · Pending · Completed
   - Products: Total products · Published · Draft · Total sales (Σ sales)
   - Customers: Total customers · Paying (per `tbl_woocommerce_summary`) · Active stores · Orders
   - Stores: Total stores · Synced orders · Synced products · Synced customers (mirrors real `tbl_woocommerce_summary`, currently never displayed)
3. **Card toolbar:** live search input · filter pills `Status ▾` (orders, products) and `Store ▾` (orders, products, customers — multi-store data) · export icon buttons (CSV / PDF / Print, matching today's DataTables exports) · primary button `+ New store` on Stores only (the one real primary action).
4. **New data-table style** (first table component in the new language):
   - 10px uppercase `#94a3b8` headers on `#fafbfc`, 10/16 padding, hairline `#f1f3f5` separators, hover row, tabular-nums money right-aligned, status **pills**, avatar/thumbnail cells, actions as ghost icon buttons.
   - Columns (grounded in real DB columns; fixes known display bugs):
     - **Orders:** Order # · Customer (name + email sub) · Store · Total · Status · Date · **Actions** (view, edit status, delete — fixes today's actions hiding under an "Invoice Number" header)
     - **Products:** thumbnail + Name (SKU sub) · Category chip · Price · Sales · Status · Store · Actions (view permalink, edit, delete)
     - **Customers:** avatar + Name (username sub) · Email · Phone · Store · Orders (count via orders.customer_id) · Actions (edit, delete)
     - **Stores:** Name + URL sub · Assigned to · Synced (orders/products/customers mini-counts) · Created · Actions (sync, test connection, edit, reset, delete)
   - Status pills: pending=amber, processing=blue, completed=teal-green, on-hold=slate, cancelled/failed=red, refunded=purple; publish=green, draft=slate, pending=amber, private=purple.
   - Live client-side filtering (search + status pill + store pill) with no-results state; simple pager ("Showing x–y of N", 10/page) when >10 rows; empty state if a table has no rows.
   - ~12 realistic sample rows per table (synthetic-but-plausible; store names/URLs patterned like real data).

## Order detail (overlay sub-page)

Back button + `Order #1234` + status pill + date · 3 cards: Customer / Billing / Shipping · styled line-items table (item, qty, price, tax, total) · totals panel (subtotal, discount, shipping, tax, **total**) · customer note callout · action bar: Edit status (opens modal), Print, Back to orders.

## Modals (calendar `.cal-modal` system)

- **New/Edit Store:** store name, site URL, consumer key, consumer secret, assignees (chip multi-select), Save/Cancel.
- **Edit Order Status:** readonly order #, status `<select>` (pending, processing, on-hold, completed, cancelled, refunded, failed) with live pill preview, Save.
- **Edit Product:** name, price, status select, short description, Save.
- **Edit Customer:** username (readonly), first name, last name, email, Save.
- All demo actions (save/sync/test/reset/delete/export) show a toast; data changes are client-side only (row updates where trivial, e.g. status change).

## Visual system

- Tokens only: accent `#0d9488`, bg `#f4f6f8`, surface `#fff`, borders `#e3e6ea`, text `#1a1d21`/`#4a5058`/`#7a828c`, radii 4/8/12/16, shadows `--sh-1..3`, font scale 11/12/14/16/26.
- White cards radius 16, 1px border, soft shadow; teal focus rings on inputs; buttons: teal accent, ghost, danger-ghost (calendar set).

## Non-goals

No application code changes; no charts/hero cards; no add-product form (does not exist in app); no real API/sync wiring; no dark mode; no i18n (demo strings English).

## Verification

Serve locally → open in browser → check: all 4 pages via sidebar; KPI values match sample data sums; search/status/store filtering; pager; every modal opens with correct content; order detail overlay open/back; toasts fire; 0 console errors; screenshots at 1422px.
