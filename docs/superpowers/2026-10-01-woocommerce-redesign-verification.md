# WooCommerce redesign — verification notes

Date: 2026-10-01 · Branch `sakib` · Plan `2026-10-01-woocommerce-redesign-implementation.md` · Spec `2026-10-01-woocommerce-redesign-implementation-design.md`

Task 10 (full verification) record. Tasks 1–9 committed individually:
`424dd116` · `96972dda` · `137dedd6` · `89ddbb29` · `4087e4c9` · `fee045bc` · `cbdc0bd2` · `d848d318` · `3b305d52`.

## 1. Automated gates (final run)

| Gate | Result |
|---|---|
| `composer smoke` | 22/22 routes, exit 0 |
| `composer test:login` | 7/7, exit 0 |
| `composer test:menu` | 59/59, exit 0 |
| `composer test:woo` | 19 passed / 0 failed / **1 skipped**, exit 0 |
| `composer test:theme` | **FAIL — pre-existing**, exit 1 |

- `test:woo` SKIP is the plan's soft-skip: order detail `note-box` needs live API data;
  the WooCommerce REST API is unreachable from this environment (`woocommerce_rest_cannot_edit`
  on write, empty read). The detail page degrades to its `Order details are unavailable`
  empty state with structural markers intact.
- `test:theme` fails identically on a clean tree at Task 1 (same error: `mode normal:
  expected 25 !important, got 26; undefined vars: --c-accent, --c-sidebar-text-hi,
  --c-on-accent`). Unrelated to woo work — pre-existing, not fixed here.

## 2. Regression spot-checks

GET `admin/invoice/manage_invoice`, `admin/calendar`, `login`: 200, tables render,
bytes unchanged from baseline (505516 / 399884 / 498678), **no** `woocommerce_modern.css`
link on any non-woo page (also asserted by the e2e test).

## 3. Playwright pass (demo side-by-side)

Demo `http://localhost:8788/woocommerce-full.html` vs live, DPR 0.9.

**Pages** (all 0 console errors, KPI values match spec):

- Orders — 1,496 / $21,100.65 / 11 / 591 · thead 7 = row 7
- Products — 741 / 728 / 7 / 826 · 6 cols
- Customers — 2,133 / 15 / 3 / 1,496 · 6 cols · phone digits render for `eridena` (D1)
- Stores — 3 / 3,039 / 776 / 2,961 · 5 cols (phantom column gone, D10)

**Interactions (orders):**

- Search debounce (300 ms) → `Johnson` filters 1,496 → 3 rows (matches via address column)
- Status pill → Processing (6 rows, POST carries `woo_status=processing`)
- Store pill combined → DigitalSoftwareKey + Processing = 3 rows (`woo_store=2` in POST)
- Escape clears search; both pills reset back to 1,496
- Page 2 → `Showing 11–20 of 1,496`
- Row click (`td`) → select highlight (`odd selected`, API `rows({selected:true})` = 1)
- Exports: Excel/CSV/PDF download files, Print + Print-selected open the preview,
  0 console errors after all four; bulk-delete fires `confirm()` (auto-dismissed = no-op,
  no POST emitted)
- DataTables info line renders plan format: `Showing 1–10 of 1,496` / `Showing 1–3 of 3`

**Modals:**

- Stores `+ New Stores` → restyled modal, form fields present, assignees selectpicker
  renders (33 users, "Select Users" placeholder)
- Edit-status → select2 status select, **live pill updates** (`st st-processing` →
  `st st-completed` on change)
- Save (same status, no mutation) → redirects to `woocommerce/orders` (full layout) with
  flash: `toastr.error("Something went wrong: Error: … [woocommerce_rest_cannot_edit]")`
  — API write rejection is environment, flash + redirect work
- Edit-product → labels resolve (`Product ID *` etc.), no raw tokens, conditional CSS, 0 errors

**Order detail:** back link, status pill, dt-bar/dt-grid/tot-row/dt-title markers,
line-items empty state (API-down), Print button calls `window.print()` (verified with a
stub to avoid blocking the automation), edit link → `edit_orders/{id}`.

Screenshots (untracked, repo root): `woo-demo-orders.png`, `woo-live-{orders,products,
customers,stores,stores-modal,order-detail,edit-status-modal,edit-product-modal}.png`.

## 4. Defect checklist

| # | Defect | Status |
|---|---|---|
| D1 | customers `phone_number` → `phone` | ✅ phone renders (`8094790768`) |
| D2 | thead/count mismatches | ✅ orders 7/7, products 6/6, customers 6/6, stores 5/5 |
| D3 | dead `can_action` | ✅ removed |
| D4 | `admin/` URL prefixes | ✅ all list/POST endpoints use `admin/woocommerce/*` (modal hrefs keep pre-existing bare `woocommerce/*`, verified working) |
| D5 | missing lang keys | ✅ + fixed `Something_went_wrong` found during save-flash test |
| D6 | toasts → lang | ✅ flash renders through `lang()` |
| D7 | `delete_stores` raw msg, `edit_products` stray token / `lang('client')` | ✅ labels resolve, no raw tokens |
| D8 | customer join fan-out | ✅ rows not duplicated, totals correct |
| D9 | orders ambiguous `phone` | ✅ global search works (e2e + `Johnson` probe) |
| D10 | phantom `assignees` column | ✅ stores thead 5 = row 5, search healthy |

## 5. Verification-time fixes (committed with this doc)

1. **Legacy `sInfo` keys in the 4 list views** — this DataTables build renders the info
   line from legacy `sInfo`/`sInfoFiltered` (defaults come from
   `jquery.dataTables.min.php`); the plan's new-style `info`/`infoFiltered` keys are
   stored but ignored. Views now set both, so `Showing 1–10 of 1,496` renders and the
   `(filtered from …)` suffix is suppressed.
2. **`$lang['Something_went_wrong']`** — the edit-status error flash concatenated a
   nonexistent key (`lang()` returns the key itself). Added with a trailing `: ` to match
   the controller's separator-less concatenation.

## 6. Notes / known limits

- Standalone navigation to modal-only endpoints (`edit_orders/{id}` etc.) renders
  `_layout_modal_lg` without jQuery by design — its inline scripts assume the parent
  page's jQuery. Pre-existing pattern shared by all module modals; real modal flows
  verified at 0 console errors.
- Playwright's headed print preview blocks the MCP connection until dismissed (ESC via
  OS keystroke); recovered each time, page unaffected.
- Revenue KPI mixes per-order currencies (demo parity, documented in plan deviations).
- Out of scope per plan: sync cursors, `woocommerce_cron()`, real bulk-delete endpoints,
  per-currency revenue, non-English.
