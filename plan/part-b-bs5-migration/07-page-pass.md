# Phase 07 — Page Pass

**Goal:** apply the DS-5 component spec to the remaining ~885 views, module by module.
**Gate to exit:** every route in `routes.json` matches the component spec.

One module per commit. Crawler green after each. This is the long tail — ~70% of total effort.

---

## Working rules

1. **Reference-first.** Convert the 10 reference pages in `SH-4` and treat them as the pattern.
   Copy their structure; do not re-improvise per module.
2. **Order by dependency**, then by view count. Shared modules first so later modules have
   patterns to copy.
3. **Every module gets:** page header, KPI row where data supports it, filter bar, empty state,
   loading state, responsive check, and a print check if it has a print button.
4. **Never change a query or a permission check** in this phase. Markup only. Logic changes are
   bug fixes and belong in their own commit.
5. Run `crawl.php` + `htmldiff.php` after each task.

---

## PP-1 — Foundation modules (reference implementations)

`client` (22 views) · `items` (18) · `leads` (23) · `opportunities` (15)

These are the archetypal CRM CRUD pages. Converting them first establishes the canonical
list/form/detail pattern for the rest of the pass.

Per module:
- Convert list pages: page header, filter/search bar, DataTable with the DS-5 toolbar, row
  actions via `btn_*` helpers, bulk actions, pagination
- Convert create/edit forms via `custom_form_Fields()` (SF-1 option B)
- Convert detail pages: header with status badge, tab set, related lists, action bar
- Add empty state ("No clients yet" + primary CTA) — several of these render a blank table today

**Verify:** all 4 modules' routes 200; forms validate; detail tabs render.
**Effort:** L

---

## PP-2 — `common` (36 views)

Shared partials (`activites`, `attachments`, `comments`, `notes`, `tab_view`) were converted in
`LP-6`. Here: the remaining shared components and the `sales`/document gallery templates
(browser-print half).

- Convert the 11 browser-print invoice templates to BS5 + tokens (PDF half in Phase 08)
- Convert remaining shared components to the DS-5 spec
- Consolidate `nav_pinned.php` / `nav_pinned_2.php`
- Convert `header.php` variants not covered in `LP-4`

**Verify:** all 19 invoice templates render (see `DC-3` for the PDF verification).
**Effort:** M

---

## PP-3 — `projects` (32) + `tasks` + milestones

Heaviest interactive module: kanban, gantt view, timeline, discussion threads, attachments.

- `assets/plugins/ganttView/` uses its own bundled jQuery 1.12.4 — isolate it, do not let it
  conflict with the global 3.7
- Project list/detail/gantt/discussion per the spec
- Add a progress indicator (tasks complete / total) using tokens

**Verify:** gantt renders; kanban drag works; task CRUD persists.
**Effort:** L

---

## PP-4 — `invoice` (30) + `proposals` (13) + `credit_note` (13) + `estimates`

The money pages. Highest visual and functional stakes.

- List, detail, create, edit, clone, print, PDF, record-payment, delete
- **De-duplicate** `payments_details` / `edit_payments` (currently copied verbatim into
  `client/`, `purchase/`, `return_stock/`) — one shared partial, per `LP-6`
- Status badges: draft / sent / partially paid / paid / overdue / cancelled
- Make overdue visually distinct without relying on colour alone (icon + text per DS-6)
- Line-item editor: clean table, add/remove row, live totals, tax/discount fields
- Totals panel aligned and readable

**Verify:** create → send → record partial payment → mark paid, end to end. Totals correct
against the baseline. All statuses visually distinct.
**Effort:** L

---

## PP-5 — `user` (27) + `settings` (64)

**The largest module — 91 views.**

`settings` sub-areas to convert individually (small tasks, one commit each):

| Batch | Sub-area | Views |
|---|---|---|
| PP-5a | Profile, change password | ~4 |
| PP-5b | General / company / localisation | ~6 |
| PP-5c | **Appearance** (theme, logo, favicon, sidebar colour) | ~8 |
| PP-5d | Email, templates, notification | ~8 |
| PP-5e | Payment gateways | ~8 |
| PP-5f | Tax, currency, units | ~6 |
| PP-5g | HR rules: leave, attendance, payroll config | ~10 |
| PP-5h | Client portal, module toggles, misc | ~14 |

`user`: list, profile, permissions matrix, activation, login-history.

> `PP-5c` is the highest-value settings task: it is what drives `tokens.css` and
> `bg-custom.php` (DS-4). Get it right and the rest of the app inherits the theming.

**Verify:** every settings tab saves and persists; appearance changes restyle the app live.
**Effort:** L

---

## PP-6 — `payroll` (26) + `attendance` (25) + `timesync` (15) = 66 views

Time-and-attendance cluster. Heavy on tables, forms, filters.

- Payroll: run payroll, payslips, salary structure, payslip template, reports
- Attendance: mark attendance, monthly view, overtime, leave
- Timesync: timer, timesheet, weekly summary

Convert per spec. Payslip printing needs a print stylesheet check.

**Verify:** mark attendance persists; payroll run produces correct figures vs baseline;
payslip prints.
**Effort:** L

---

## PP-7 — `transactions` (26) + `accounting` (23)

- Transactions: categories, types, invoice/payment/expenditure lists
- Accounting: chart of accounts, journal entries, ledger, trial balance, P&L, balance sheet,
  bank reconciliation

Chart-of-accounts and ledger pages are table-heavy with expandable rows. Design an expandable
row pattern once and reuse it.

**Verify:** ledger balances; reports match baseline figures.
**Effort:** L

---

## PP-8 — `report` (31) + `mailbox` (13)

Reports: sales, project, timesheet, task, expense, invoice ageing, employee, lead, client,
item, contractor, profit, payroll, and PDF exports.

- One shared report-shell layout: filter row + result table + export actions
- Every report must be responsive and printable
- `mailbox`: list, thread view, compose, attachments

**Verify:** open all 31 reports; each produces data and exports.
**Effort:** M

---

## PP-9 — Productive modules

| Module | Views | Notes |
|---|---|---|
| `job_circular` | 16 | Applicant tracking, job board |
| `bugs` / `bugs_type` | 13 | Bug tracker list + detail |
| `tickets` / `ticket_type` | 13 | Support tickets + SLA display |
| `contracts` | 9 | Contract lifecycle + signing |
| `knowledgebase` | 9 | Article list + editor |
| `complaints` / `complaint_type` | 9 | Complaint intake + resolution |
| `proposal` templates | 7 | Proposal template editor |
| `estimate` templates | 5 | Estimate template editor |

Ticket and bug detail pages share a shape (tabs, activity, attachments, comments) — convert the
first, then clone the pattern.

**Effort:** L

---

## PP-10 — Integrations

`zoom` (11) · `spreadsheet` (11) · `woocommerce` (12) · `jitsi` (3) · `mollie` · `braintree` ·
`razorpay` · `paypal` · `sms` · `whatsapp`

- Each gets the shell treatment and a spec-conformant form/list
- Mark any third-party widget as **frozen** — style around it, do not restyle its internals
- `server_management` (29 views) is a separate vendored admin surface — decide explicitly
  whether it gets the redesign or is left as-is and marked legacy

**Verify:** each integration page renders; third-party widgets still function.
**Effort:** M

---

## PP-11 — `file_manager` + `filemanager` (2) + `fileloc` (5)

Upload surfaces. Convert to a token-driven card/tile layout, preserve the upload JS
(`dropzone`, `jasny-bootstrap`).

**Effort:** S

---

## PP-12 — Client portal (82 views)

A separate surface with its own `_layout_main.php`, 3 header partials, 3 modal shells and its
own `admin_helper`-adjacent helpers. **Treat it as its own project** — do not assume admin
conversions carry over.

Sub-batches: dashboard · invoices · quotations · estimates · proposals · projects · tasks ·
tickets · bugs · contracts · files · profile · settings.

**Verify:** portal client login works; every portal route 200; no admin-only link leaks.
**Effort:** L

---

## PP-13 — Public frontend (15 views) + `booking` (4) + `career` (3)

- `frontend/*` — the public marketing/landing pages
- `booking/*` + `assets/css/booking.css` — an **independent visual design** today
- `career/*` — public job board

Convert the header/footer first, then pages. `booking` needs a decision in DS-2: align it with
the main design system, or keep it distinct.

**Verify:** all public routes render at 375/768/1440 px.
**Effort:** M

---

## PP-14 — Final sweep

1. `grep` for every mapped BS3 class — 0 remaining
2. Crawler over all of `routes.json` — 0 new failures
3. Compare all ~40 baseline screenshots
4. Run the accessibility checker (DS-6) — 0 critical/serious
5. Sweep for orphan elements: labels without inputs, buttons without accessible names
6. Check every one of the 63 `window.print()` pages prints correctly
7. Confirm no `panel-custom` / legacy token classes remain unmapped

**Effort:** M

---

## Exit gate

- [ ] Every route in `routes.json` renders and matches the component spec
- [ ] 885 views converted; 0 obsolete classes
- [ ] All 4 surfaces (admin, client, frontend, booking) consistent
- [ ] Accessibility checker clean
- [ ] 63 print pages verified
- [ ] Baseline screenshot comparison reviewed and signed off
