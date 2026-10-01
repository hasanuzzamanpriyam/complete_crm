# WooCommerce Redesign — Implementation Design

**Date:** 2026-10-01 · **Status:** approved design, awaiting user spec review
**Demo of record:** `_design/woocommerce-full.html` (served at `http://localhost:8788/woocommerce-full.html`)
**Companion demo spec:** `docs/superpowers/specs/2026-10-01-woocommerce-redesign-demo-design.md`
**Precedent:** calendar redesign (spec `2026-09-30-calendar-redesign-design.md`, commits `07fec5ff..6831f24e`)

## Goal

Restyle the admin WooCommerce section — 4 list pages, order detail, and 4 modal forms — to the approved dashboard design language (Approach A of the demo: KPI strip + card toolbar + new table component + calendar modal system), inside the existing admin chrome, with zero functional loss.

## Decisions (user-confirmed)

| # | Question | Decision |
|---|----------|----------|
| 1 | Redesign scope | **WooCommerce pages only** — existing header/sidebar chrome untouched (calendar precedent) |
| 2 | Delivery shape | **Everything in one plan** — 4 lists + detail + modals as ordered tasks, single review |
| 3 | Toolbar parity | **Keep everything** — CSV/PDF/Print exports AND row-select bulk print/delete preserved |
| 4 | Verification | **New e2e test + existing composer gates** |
| 5 | Architecture | **Approach A — reskin the server-side DataTables pipeline**; KPIs server-rendered; small backward-compatible `fire_datatable()` extension |

## Architecture

- **Engine:** keep the existing server-side DataTables pipeline (`fire_datatable(list)` → `make_datatables()` → `Datatables::make_query()`). No new list endpoints; no client-side row rendering.
- **Presentation:** a new page-scoped stylesheet `assets/css/woocommerce_modern.css` (tokens already global via `assets/css/tokens.css?v=3`) restyles the DataTables-generated DOM, toolbars, KPI cards, modals, and order detail.
- **KPIs:** computed in the controller page methods (`orders()`, `products()`, `customers()`, `stores()`), passed to the views, rendered as static HTML — no AJAX.
- **Data flow unchanged:** lists read local mirror tables; order detail keeps its live WooCommerce API call; all form POSTs and AJAX actions keep their current URLs.

## Files

| File | Change |
|------|--------|
| `assets/css/woocommerce_modern.css` | **new** — KPI strip, card, toolbar, table skin, pills, pager, empty state, modal overrides, order-detail cards; all rules scoped under `.woo-modern` |
| `assets/plugins/dataTables/js/dataTables.php` | extend `fire_datatable(req_url, ttable = null)` → `fire_datatable(req_url, ttable = null, opts = {})`; `opts` supplies per-page `columns`/`columnDefs`/`language`; default `{}` reproduces today's behavior exactly |
| `modules/woocommerce/views/orders.php` | new markup: KPI strip + card + toolbar + `.woo-modern` table wrapper; pass `opts` to `fire_datatable` |
| `modules/woocommerce/views/products.php` | same |
| `modules/woocommerce/views/customers.php` | same; drop dead `can_action('100')` block |
| `modules/woocommerce/views/stores.php` | same; keep `wooco_test()` / `woo_reset()` / `updateWooStore()` AJAX and the `#myModal_lg` New-store button |
| `modules/woocommerce/views/order.php` | restyle to detail layout (action bar, 3 cards, line items, totals, note callout); move inline `<style>` into the stylesheet; drop `assets/js/sales.php` include only if the implementation plan verifies nothing uses it (otherwise keep) |
| `modules/woocommerce/views/new_stores.php` | restyle form fields inside existing modal machinery |
| `modules/woocommerce/views/edit_order.php` | restyle + live status-pill preview JS |
| `modules/woocommerce/views/edit_products.php` | restyle; fix `lang('client')` label on productId |
| `modules/woocommerce/views/edit_customer.php` | restyle |
| `modules/woocommerce/controllers/Woocommerce.php` | KPI computation in 4 page methods; store join in `orderslist()`; store join + per-customer order count in `customerlist()`; store join in `productlist()`; `edit_customer()` not-found redirect → customers list; error strings → `lang()` |
| `application/views/admin/_layout_modal_lg.php` | one conditional `<link>` to `woocommerce_modern.css` when `strpos(uri, 'woocommerce') !== false` |
| `modules/woocommerce/language/english/woocommerce_lang.php` | new keys for KPI labels, toolbar, pager, empty states, detail labels, fixed error messages |
| `plan/_tools/woocommerce-redesign-test.php` | **new** e2e test |
| `composer.json` | add `test:woo` script |

No changes to: routes/router, menu/permissions, sidebar/header, DataTables server helpers (`make_datatables`, `Datatables` model), sync helpers.

## Page anatomy (each list page, inside `$subview`)

```
h1 page title
KPI strip: 4 × .kpi-card (icon chip · big value · label)
.card
  .list-toolbar: [live search input] [Status ▾] [Store ▾*]  tb-right: [CSV][XLS][PDF][Print] [+ New store**]
  table.tbl (DataTables-generated DOM + renderers): status pills · store tags · thumbnails/avatars · ghost icon actions
  pager: "Showing start–end of N" + ‹ ›  ·  styled empty state
```

\* Store pill: Orders, Products, Customers (all store-scoped tables). Status pill: Orders (7 Woo statuses), Products (publish/draft/pending/private). Stores page: search only.
\*\* Stores page only — opens the existing `#myModal_lg` New-store modal.

### Column definitions (demo parity)

- **Orders:** Order `#` · Customer (name + sub: email/phone/address) · Store tag · Total · Status pill · Date · Actions (view → detail, edit status, delete)
- **Products:** Thumbnail + name (+ sub: SKU · category) · Price · Sales · Status pill · Store tag · Actions (open permalink, edit, delete)
- **Customers:** Avatar + name (+ sub: username) · Email · Phone · Store tag · Orders count · Actions (edit, delete)
- **Stores:** Name + URL · Assignee chips · Synced (orders / products / customers) · Created · Actions (sync, test connection, edit, reset, delete)

### List-endpoint changes (module-owned)

- `orderslist()`: `JOIN tbl_woocommerce_stores` → select store name; keep existing customer LEFT JOIN.
- `productlist()`: `JOIN tbl_woocommerce_stores` → store name (already selects `sku`, `category`, `picture` — now rendered).
- `customerlist()`: `JOIN tbl_woocommerce_stores` → store name; add per-customer order count (`COUNT` of `tbl_woocommerce_orders` grouped by `woo_customer_id`); fix phantom `phone_number` column → `phone`.
- `storeslist()`: add per-store synced counts (from `tbl_woocommerce_summary`, see KPI rules) to the row payload.

## KPI sources (formulas verified against live DB)

**Orders** (`tbl_woocommerce_orders`, plain SQL):
- Total orders `COUNT(*)` → **1,496**
- Revenue `SUM(total+0) WHERE status='completed'` → **$21,100.65**
- Pending/processing `COUNT(*) WHERE status IN ('pending','processing','on-hold')` → **11**
- Completed `COUNT(*) WHERE status='completed'` → **591**

**Products** (`tbl_woocommerce_products`):
- Total `COUNT(*)` → **741** · Published `status='publish'` → **728** · Draft `status='draft'` → **7** · Total sales `SUM(sales+0)` → **826**

**Customers:**
- Total `COUNT(*) tbl_woocommerce_customers` → **2,133**
- **Paying** — from `tbl_woocommerce_summary`: `json_decode(customers)` per row, sum `total` where `slug='paying'` → **15**
- Active stores `COUNT(*) tbl_woocommerce_stores` → **3** · Orders `COUNT(*) tbl_woocommerce_orders` → **1,496**

**Stores:**
- Total stores `COUNT(*) tbl_woocommerce_stores` → **3**
- Synced orders — sum over summary rows of Σ`total` across the `orders` JSON blob → **3,039**
- Synced products — same over `products` blob → **776**
- Synced customers — same over `customers` blob → **2,961**
- Per-row Stores "Synced" column uses the same Σ rules per store row (1,387/194/2,055 · 821/256/705 · 831/326/201)

All parsing: `json_decode(..., true) ?? []` with defensive `?? 0` per element — a malformed blob degrades a KPI to `0`, never breaks the page. Implement as small helpers in `modules/woocommerce/helpers/woocommerce_helper.php` (e.g. `woo_summary_paying()`, `woo_summary_totals($col)`).

## Table engine wiring

- Each view calls `fire_datatable(list, null, $opts)` where `$opts` (JSON-encoded inline) provides:
  - `columnDefs` renderers: status pill (`st st-{slug}` span via existing `statusLabel` semantics), store tag, product thumbnail, customer avatar, order sub-line, action buttons (existing `ajax_anchor()`-style markup)
  - `language`: `info` → `"Showing _START_–_END_ of _TOTAL_"`, `emptyTable` → page-specific empty message, `paginate` labels
  - `select`/`buttons`/`order` defaults unchanged unless the page overrides them
- Filter pills: on change call `table.column(i).search(value)` + `draw()`; search box debounced → `table.search()`; both compose naturally with server-side processing.
- Export icons in the toolbar call the existing Buttons API: **4 icons — CSV, Excel, PDF, Print** (Excel added beyond the demo's 3 to honor decision #3 "keep everything"). The default DataTables button row is hidden on woo pages (page `opts.dom` without `B`); bulk row-select and print-selected/bulk-delete remain enabled and functional.
- **Scoping:** stylesheet loads only from the 4 woo list views + order view + the conditional modal link; every selector additionally nests under `.woo-modern` (wrapper div added in each of the 4 list views **and** the order-detail view). Modal overrides are the one exception (modal layout has no page wrapper) — they are guarded solely by the URI-conditional link.

## Order detail (`order.php`)

- Data source, `order($id)` flow, missing-store redirect: unchanged. API-failure message → `lang()` key.
- Markup: `.dt-bar` (Back · `#id` · status pill · date · store · Print · Edit status → existing `edit_orders` modal) · grid of 3 cards (Customer / Billing / Shipping from the API object's `billing`/`shipping`) · line-items table · totals panel (subtotal, discount, shipping, tax, total; keep `number_to_words` line when the API provides it) · amber `.note-box` callout when `customer_note` is non-empty.
- Inline `<style>` block removed (styles live in `woocommerce_modern.css`).

## Modals

- Existing Bootstrap machinery (`_layout_modal_lg` / `#myModal_lg`), form fields, POST endpoints, and server-side validation are preserved; presentation is restyled via `.modal-content` overrides in `woocommerce_modern.css`, loaded by the URI-conditional link in `_layout_modal_lg.php`.
- `edit_order`: add live pill preview (inline JS on `#status` change).
- Assignees stay the existing `assignees[]` select2 multi-select (restyled), not rebuilt as chips.
- Notifications keep toastr (app standard).

## Defect fixes in scope

1. `customerlist()` column `phone_number` → `phone` (search/sort currently broken).
2. Orders view header cell "invoice number" sitting over the actions column → corrected headers.
3. Dead `can_action('100', …)` block in `customers.php` (wrong menu id, unused vars) → removed.
4. Endpoint prefix inconsistency in views (`admin/woocommerce/…` vs `woocommerce/…`) → normalized to `admin/woocommerce/…`.
5. `edit_customer()` not-found redirect targets `edit_products` → redirect to customers list.
6. Hardcoded English strings (`"No Record Found"`, `"woocommerce reset successfully"`, `"there is issue"`, …) → `lang()` keys.
7. `edit_products.php` productId label uses `lang('client')` → correct key.

**Out of scope** (pre-existing, unrelated to UI; recorded, not fixed): swapped sync cursors (`checkProducts` writes `orderPage`, `checkOrders` writes `productPage`), `woocommerce_cron()` loading a nonexistent model, `delete()` always resolving the store through `tbl_woocommerce_products`.

## i18n

All new user-facing strings live in `modules/woocommerce/language/english/woocommerce_lang.php` (loader already wired via `module_languagesFiles()`): KPI labels, toolbar placeholders, pill labels, pager/empty-state text, detail-card labels, fixed error messages. English-only, matching the module's current convention.

## Testing

1. **New e2e test** `plan/_tools/woocommerce-redesign-test.php` (pattern: `plan/_tools/calendar-event-test.php`) + `composer test:woo`:
   - login, load all 4 pages; assert `.woo-modern` wrapper, KPI strip with expected values (`1,496`, `2,133`, `3,039`), toolbar pills, styled rows
   - open status modal → assert pill preview; save-free close
   - open an order detail → assert Back button, totals panel, `Total`
   - assert no PHP warnings/notices in output
2. **Existing gates:** `composer smoke` · `test:menu` · `test:login` · `test:theme` · `test:woo` — all green.
3. **Regression spot-check:** one non-woo DataTables page (e.g. invoices list) renders identically (proves `opts` default path).
4. **Manual browser pass** at 1422px: screenshots of 4 pages + detail + 4 modals; search/pill/pager/bulk-select/export smoke; 0 console errors.

## Risks & mitigations

| Risk | Mitigation |
|------|------------|
| New CSS leaks to other DataTables pages | Stylesheet loaded only on woo views + `.woo-modern` scoping on every rule |
| `fire_datatable` opts regress other pages | Default `opts = {}` is bit-identical current code; gates + non-woo spot-check |
| Modal conditional affects other modules | Strict `strpos(uri, 'woocommerce')`; other modals byte-identical |
| Summary JSON blobs malformed | Defensive decode; KPI degrades to 0, page never breaks |
| Live API order detail unavailable | Existing error redirect preserved (wording via `lang()`) |

## Rollout

Branch `sakib`, commit style `feat:`/`fix:`/`docs:` (calendar convention), one commit per logical unit: stylesheet → `fire_datatable` opts → 4 list pages → order detail → modals → lang/defect fixes → e2e test. Design doc committed first; implementation proceeds via the writing-plans skill task plan.

## References

- Demo mockup: `_design/woocommerce-full.html`
- Demo spec: `docs/superpowers/specs/2026-10-01-woocommerce-redesign-demo-design.md`
- Calendar precedent: `docs/superpowers/specs/2026-09-30-calendar-redesign-design.md`, `assets/css/calendar_modern.css`, `plan/_tools/calendar-event-test.php`
- Design tokens: `assets/css/tokens.css` · table/tag reference: `_design/dashboard-modern.html`
- Module: `modules/woocommerce/` (controller 533 ln, model, helper, 4 list views + detail + 4 modals)
