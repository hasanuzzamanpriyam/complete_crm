# Part B — Bootstrap 5 Migration

> **⚠ Do not start yet.** Complete Part A first: [`../part-a-css-reskin/`](../part-a-css-reskin/README.md)
> This part changes markup on 885 pages. The safety net (`SN-*`) must exist first.

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development
> (recommended) or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** replace Bootstrap 3.3.5 with Bootstrap 5.3 and modernise the markup, clearing the debt
that Part A deliberately left in place.

**Architecture:** Two CSS tracks. The browser app moves to BS5 + the `tokens.css` built in Part A.
Dompdf 2.0.x — no JavaScript, CSS 2.1 engine — cannot run BS5, so **68 PDF templates + 8 Dompdf
invoice variants stay on a frozen Bootstrap 3 stylesheet.** A deliberate fork, not debt.

**Tech stack:** CodeIgniter 3.1.13 HMVC · PHP 8.3 · Bootstrap 5.3.3 · jQuery 3.7 ·
DataTables 1.11+ · Dompdf 2.0.x *(frozen)*

---

## What Part A already did for you

| Done in Part A | Reused here |
|---|---|
| `assets/css/tokens.css` — 14 token groups | Consumed directly by `SH-1`. **Do not create a second token file.** |
| `assets/css/themes.css` — 10 sidebar themes | Replaced by `A3` work; BS5 inherits the same tokens |
| Visual direction **decided** | `DS-2` is **complete**. Skip it. |
| `print.css` | Extended in `DC-6` |
| `app.css` reduced to token-driven rules | Restructured in `SH-1` |
| 772 KB dead CSS removed | Fewer files to migrate |

**Also already done:** `DS-3` (token inventory) and `DS-4` (token file) — completed as `A1-1`
and `A1-3`.

---

## Execution order

| # | File | Tasks | Gate |
|---|---|---|---|
| 1 | [`00-design-system.md`](00-design-system.md) | DS-1…6 | ⚠ DS-2, DS-3, DS-4 done in Part A |
| 2 | [`01-safety-net.md`](01-safety-net.md) | SN-1…5 | Crawler green, baselines captured |
| 3 | [`02-vendor-bumps.md`](02-vendor-bumps.md) | VS-1…6 | jQuery 3.7, BS5, DataTables working |
| 4 | [`03-leverage-points.md`](03-leverage-points.md) | LP-1…6 | ~900 occurrences fixed at source |
| 5 | [`04-codemod.md`](04-codemod.md) | CM-1…6 | 0 obsolete classes remain |
| 6 | [`05-structural-fixes.md`](05-structural-fixes.md) | SF-1…5 | No broken form, no console error |
| 7 | [`06-shell-redesign.md`](06-shell-redesign.md) | SH-1…7 | Modern components land |
| 8 | [`07-page-pass.md`](07-page-pass.md) | PP-1…14 | All routes conform |
| 9 | [`08-documents.md`](08-documents.md) | DC-1…7 | 19 invoices, 68 PDFs, 7 emails |
| 10 | [`09-debt-cleanup.md`](09-debt-cleanup.md) | DB-1…8 | Dead code, `strftime`, inline styles |
| 11 | [`10-verification.md`](10-verification.md) | VF-1…4 | CI blocks regressions |

**74 tasks.** Phase 3 onward is invisible work — nothing looks different until `SH-1`.

---

## The real risk is JavaScript, not CSS

Three patterns that **fail silently** — no error, no exception, just wrong behaviour:

| Pattern | Count | What happens in BS5 |
|---|---|---|
| `loaded.bs.modal` | 20 | Event was **removed**. Listeners never fire. Modals open with un-initialised Select2, datepickers and tooltips. |
| `removeData('bs.modal')` | 6 | BS5 stores instances in an internal `Map`, so state is never cleared → "modal already open" errors. |
| `$.fn.modal.Constructor` | 1 | Does not exist in BS5. The guard in `add_domain.php:960` is always false, so its Select2 focus fix has been dead. |

Plus **1,420** `data-toggle` → `data-bs-toggle` renames and **20** `hidden.bs.modal` listeners
that no longer bubble to jQuery.

This is why `SN-*` builds a crawler before anything moves.

---

## Two landmines

Read [`../ROLLBACK.md`](../ROLLBACK.md) before running any find-and-replace.

**1. `label` must never be a plain string match.** `label-inverse`, `label-danger`,
`label-required` (first-party), `<label>` elements, and JS variables named `label` all exist.
Anchor on class-attribute boundaries; sort longest-first.

**2. `panel-custom` is NOT a Bootstrap class.** 780 occurrences, first-party, defined in the
project's own CSS. A blind `panel-*` → `card-*` rewrite produces `card-custom`, which does not
exist. **Resolve the decision in `DS-5` before running `CM-4`.**

---

## The two CSS tracks

| Track | Stylesheet | Consumers | Rule |
|---|---|---|---|
| **A — browser** | `bootstrap-5.3.3.min.css` + `tokens.css` | 633 admin + 82 client + 15 frontend + 11 browser invoices | Full BS5 |
| **B — PDF** | `bootstrap3-pdf.css` *(created in `VS-6`)* | 60 `*_pdf.php` + 8 `sales/pdf/*` | **BS3 only. BS5 classes render nothing.** |

A CI guard (`DC-2`) fails the build if a BS5 class appears in a PDF path.

---

## Working rules

1. **One task per commit.** Message starts with the task ID, e.g. `LP-1: btn_* helpers → BS5`.
2. **One codemod mapping per commit** (`CM-2`…`CM-6`). Never combine.
3. **Never mix a codemod with a visual change.**
4. **Run `composer crawl` after every task.** It is the regression gate for all of Part B.
5. **Never change a query or a permission check** during `PP-*`. Markup only. Logic changes are
   bug fixes and get their own commit.
6. **The PDF track is frozen.** Do not "modernise" Dompdf templates to BS5.

---

## Part B complete criteria

- [ ] `crawl.php` green across all of `routes.json`
- [ ] 0 Bootstrap-3 classes in `application/views` and `modules/*/views`
- [ ] 0 BS5 classes in PDF templates
- [ ] All 19 invoice templates redesigned and verified on their correct track
- [ ] All 4 surfaces match the Refined dense direction
- [ ] Accessibility checker reports 0 critical/serious
- [ ] 0 `strftime` calls; `E_DEPRECATED` suppression removed
- [ ] Inline-style budget under 5,000 and enforced in CI
- [ ] CI blocks all of the above
- [ ] Documentation complete

---

**Also relevant:** [`../STATUS.md`](../STATUS.md) ·
[`../BRANCHES.md`](../BRANCHES.md) ·
[`../ROLLBACK.md`](../ROLLBACK.md)
