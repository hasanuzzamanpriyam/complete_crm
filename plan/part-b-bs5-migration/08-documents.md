# Phase 08 — Documents

**Goal:** redesign all document surfaces while keeping the **PDF track on frozen Bootstrap 3**.
**Gate to exit:** all 19 invoice templates, 68 PDFs and all emails render correctly.

> **Hard architectural rule.** Dompdf 2.0.x is pinned (`composer.json`:
> `phenx/php-font-lib: >=0.5.4 <1.0.0`; Dompdf 3.0 requires `>=1.0.0`). It executes **no
> JavaScript** and its engine is **CSS 2.1**. Bootstrap 5 is JS-driven and needs flexbox/grid.
> **Migrating a PDF template to BS5 renders nothing.** PDF templates stay on
> `bootstrap3-pdf.css` and are redesigned *within* BS3 constraints.

---

## DC-1 — Inventory and classify every document

Build `plan/_data/documents.json` listing each document with: `type`, `path`, `track`
(browser / PDF / email / standalone), `used by`, `has inline styles`, `baseline screenshot`.

| Surface | Count | Track |
|---|---|---|
| Invoice templates — browser print | 11 | **A — BS5** |
| Invoice templates — Dompdf | 8 | **B — frozen BS3** |
| Other `*_pdf.php` | 60 | **B — frozen BS3** |
| Standalone `<html>` documents | 91 | Mixed — classify each |
| Bespoke email bodies | 6 | **C — email CSS** |
| `email_template.php` | 1 | **C — email CSS** |
| `window.print()` pages | 63 | **A — print stylesheet** |

Of the 91 standalone documents, ~25 are genuine standalone content pages; the rest are layouts
and the sales gallery. Classify before touching.

**Verify:** every document appears in the JSON with a track assigned.
**Effort:** S

---

## DC-2 — Freeze the PDF stylesheet

Per `VS-6`: `assets/css/bootstrap3-pdf.css` is the only stylesheet PDF templates may use.

1. Extract the Bootstrap 3 subset Dompdf can actually render: grid, typography, tables, `.btn`,
   `.label`, `.panel`, borders, backgrounds.
2. Remove anything JS-dependent (`.collapse`, `.modal`, `.tooltip`, carousel).
3. Add a header comment stating the constraint and forbidding BS5 classes in PDF templates.
4. Add a **guard script** `plan/_tools/check-pdf.php` that fails CI if a BS5-only class
   (`d-flex`, `text-bg-*`, `form-check`, `btn-close`, …) appears under a PDF path.

**Verify:** the guard runs in CI; one PDF of each of the 19 invoices is byte-comparable to
baseline.
**Effort:** M

---

## DC-3 — Redesign the 19 invoice templates

**All 19 are kept and redesigned.** Split by track:

### Track A — 11 browser-print templates (`admin/common/sales/*.php`)
Fully modernized. BS5 + tokens + a proper print stylesheet.
- Clear typographic hierarchy: company block, client block, meta table, line items, totals
- Consistent table with right-aligned numerics, tabular figures
- Status badge, payment-due callout
- Remove the 63-site `window.print()` fragility where possible in favour of a dedicated
  `@media print` layout
- Every one must be screenshot-compared against its baseline

### Track B — 8 Dompdf templates (`admin/common/sales/pdf/*.php`)
Redesigned **within BS3 constraints**:
- Restyle via tokens-injected values (read CSS variables at render time in PHP, or hardcode the
  same hex values — Dompdf cannot read `var()`)
- Tables, typography and colour are fine; flexbox/grid is not
- Verify each renders identically in content, with only intentional visual change

**Verify:** all 19 render; Track A prints correctly at A4; Track B produces valid PDFs.
Baseline screenshot for each of the 19.
**Effort:** L

---

## DC-4 — `invoice_view.php` (the 4.6 MB file)

`application/views/admin/common/invoice_view.php` is **4.6 MB** and holds **17,181 inline
style attributes — 77% of the repository's entire inline-style debt.** It is almost certainly
a generated, machine-written file.

1. **Establish what it is.** Inspect the generator. Is it produced by a build step, an import,
   or hand-maintained? This determines everything below.
2. If generated → fix the generator, then regenerate. Do not hand-edit 4.6 MB.
3. If it is a sample/demo document → move it out of `views/` (it should not be web-reachable)
   or reduce it to a single representative sample.
4. Either way, get it out of the redesign's blast radius.

**Verify:** the 4.6 MB no longer sits in the views tree, or is provably regenerable.
**Effort:** M (unknown — could be trivial once identified)

---

## DC-5 — The other 60 PDFs

Apply the DS-5 document design language to the 60 `*_pdf.php` templates:

| Group | Examples | Change |
|---|---|---|
| Statements | account statement, project statement | table + totals |
| Reports | sales, project, timesheet, expense, payroll | add a header block, footer with page number |
| Receipts | payment receipt | compact, high-contrast |
| Contracts | contract, job order | long-form typography |
| MISC | estimate, proposal, purchase order, delivery challan, quotation, lead, job circular, attendance, timesheet, payslip, voucher, bank recon | per-type |

Common improvements: consistent margins, a branded header, page numbers, token colours
(inlined as hex for Dompdf), tabular figures, no orphaned section headers.

**Verify:** each PDF opens, paginates correctly, and matches baseline content.
**Effort:** L

---

## DC-6 — Print stylesheet

63 `window.print()` call sites make this load-bearing, not cosmetic.

1. Build `assets/css/print.css` from DS-5: hide chrome, show only content, force black text on
   white, repeat table headers, avoid page breaks inside rows/cards, add print-only URLs.
2. Migrate the 182 `hidden-print` occurrences to BS5 print utilities
   (`d-print-none`, `d-print-block`, `d-print-inline`).
3. For every one of the 63 print pages: verify what prints. Several likely print the full admin
   chrome today.

**Verify:** open 63 print previews; each shows only intended content.
**Effort:** M

---

## DC-7 — Email templates

6 bespoke email bodies plus `application/views/email_template.php`. Email clients are the most
constrained renderer in the stack — worse than Dompdf.

1. **Table-based layout only.** No flexbox, no grid, no external CSS, no background images
   without a `bgcolor` fallback.
2. Inline styles only. Keep them maintainable by generating from tokens in PHP rather than
   hand-writing hex in the markup.
3. Include: dark-mode support via `<meta name="color-scheme">` and `:root { color-scheme }`,
   Outlook.com `<o:ConditionalComments>` fallback if MSO is in scope, 600 px width, 14 px base
4. Plain-text alternative for each template (deliverability, not just rendering)
5. **Send real test emails** to Gmail, Outlook and a mobile client. Rendering in a browser
   preview tells you almost nothing.

**Verify:** each template tested in 3 real clients; plain-text part present.
**Effort:** M

---

## Exit gate

- [ ] `documents.json` complete; every document has an assigned track
- [ ] `bootstrap3-pdf.css` frozen and documented
- [ ] CI guard rejects BS5 classes in PDF paths
- [ ] All 11 browser-print invoice templates redesigned, screenshot-verified
- [ ] All 8 Dompdf invoice templates redesigned within BS3, PDF-verified
- [ ] `invoice_view.php` identified, and either regenerated or relocated
- [ ] All 60 other PDFs redesigned
- [ ] `print.css` in place; all 63 print pages verified
- [ ] 7 email templates table-based, inline-styled, tested in 3 real clients
