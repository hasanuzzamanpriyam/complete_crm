# Part A · A6 — Verification & Handover

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development
> (recommended) or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** prove the redesign is an improvement, catch anything broken, and leave the project in
a state Part B can build on.

---

## A6-1 — Screenshot comparison

- [ ] **Step 1: Re-capture all 20 baseline pages** into `plan/_after/part-a/`, using the same
      viewport, zoom, cache state and record IDs as `plan/_baseline/part-a/NOTES.md`.
- [ ] **Step 2: Place the pairs side by side** and review each of the 20.
- [ ] **Step 3: For every page, confirm all three:**

| Check | Failure means |
|---|---|
| Every element present in the baseline is still present | A rule hid or clipped something |
| No element overflows its container | A width/height token is wrong |
| No text is illegible or clipped | Contrast or height is wrong |

- [ ] **Step 4: Write the verdict** to `plan/_data/part-a-css-inventory.md` — one line per page:
      `date: IMPROVED / REGRESSION (reason) / NO CHANGE`.

**Verify:** 20 after-screenshots captured; every page has a verdict; zero unresolved regressions.
**Effort:** M

---

## A6-2 — Responsive check

The design must hold at four widths. Bootstrap 3 breakpoints: `sm` 768 px, `md` 992 px, `lg`
1200 px.

- [ ] **Step 1: Check all 20 pages at 375 px, 768 px, 1024 px and 1440 px**

| Width | Expectation |
|---|---|
| 375 | Sidebar collapses; controls stack; tables scroll horizontally; no horizontal page scroll |
| 768 | Sidebar collapsed; two-column forms stack to one |
| 1024 | Sidebar visible; content area has usable width |
| 1440 | Full layout; content does not stretch uncomfortably wide |

- [ ] **Step 2: Confirm no page scrolls horizontally** at any width. If one does, find the
      offending element in DevTools and constrain it.
- [ ] **Step 3: Check the 4 surfaces separately** — admin, client portal, public frontend, booking
- [ ] **Step 4: Record failures** in `plan/_data/part-a-css-inventory.md` and fix them
      (each fix is a new commit)

**Verify:** no horizontal page scroll at any of the 4 widths on any of the 4 surfaces.
**Effort:** M

---

## A6-3 — Print verification

63 `window.print()` call sites depend on A2-12's `print.css`. Check a representative sample of
**at least 10**, spanning every document type.

- [ ] **Step 1: Print-preview these 10**

| # | Document | Route |
|---|---|---|
| 1 | Invoice | `/admin/invoice/view/{id}` |
| 2 | Invoice list | `/admin/invoice` |
| 3 | Payslip | payroll payslip view |
| 4 | Sales report | `/admin/report/sales_report` |
| 5 | Project statement | project view |
| 6 | Client statement | client view |
| 7 | Attendance | `/admin/attendance` |
| 8 | Timesheet | timesync view |
| 9 | Client list | `/admin/client` |
| 10 | Job order | job recruitment view |

- [ ] **Step 2: For each, confirm:**

| Check | Detail |
|---|---|
| Chrome removed | No sidebar, no topbar, no footer |
| Table headers repeat | Multi-page tables repeat the header row |
| No broken rows | No row split awkwardly across pages |
| Legible | Black on white, ≥10 pt |
| Totals intact | No missing values |

- [ ] **Step 3: Fix any failure**, re-verify, commit
- [ ] **Step 4: Note that the remaining 53 print pages were not individually checked**, and that
      they use the same `print.css`. Record this limitation honestly in `STATUS.md`.

**Verify:** 10 documents print correctly; limitation documented.
**Effort:** M

---

## A6-4 — Final checks and cache-bust

- [ ] **Step 1: Confirm no PHP or JS was modified during Part A**

```powershell
git diff --stat main..HEAD -- '*.php' '*.js'
```

Expected: **only the 4 shell files**, and only single `<link>` line additions. If any logic
changed, Part A has overstepped — revert that commit.

- [ ] **Step 2: Confirm no PDF template was touched**

```powershell
git diff --name-only main..HEAD | Select-String -Pattern '_pdf\.php|sales\\pdf'
```

Expected: **no output**.

- [ ] **Step 3: Confirm no database dump was staged**

```powershell
git diff --name-only main..HEAD | Select-String -Pattern '\.sql$'
```

Expected: **no output**. `databases/` is gitignored and must stay that way.

- [ ] **Step 4: Bump the cache-buster** in all 4 shells, `?v=2` → `?v=3`

```powershell
Select-String -Path application\views\*\components\htmlheader.php, `
                    application\views\booking\_layout_main.php -Pattern 'app\.css\?v='
```

- [ ] **Step 5: Final smoke + full test pass**

```powershell
composer smoke
```

- [ ] **Step 6: Write `plan/_data/part-a-result.md`** — before/after summary: what changed, the
      CSS byte delta, what was deliberately not done, and the known Part B work that remains.
- [ ] **Step 7: Commit and open a PR**

```powershell
git add -A
git commit -m "A6: verification, cache-bust, and Part A result summary"
git push -u origin sakib
```

**Verify:** steps 1–3 all return empty; `composer smoke` 20/20; PR opened.
**Effort:** S

---

## Exit gate

- [ ] 20 after-screenshots captured, each with a verdict
- [ ] 4 widths × 20 pages × 4 surfaces — no horizontal scroll
- [ ] 10 print documents verified; the 53-untested limitation documented
- [ ] Zero PHP/JS logic changes (4 one-line `<link>` additions only)
- [ ] Zero PDF templates touched
- [ ] Zero SQL files staged
- [ ] Cache-buster bumped to `?v=3`
- [ ] `plan/_data/part-a-result.md` written
- [ ] Branch pushed and PR opened

## Tasks: 4 · Completed: 0 · Remaining: 4

---

## Part A complete criteria

When all 34 Part A tasks are done:

- [ ] All 4 surfaces look modern and consistent
- [ ] Live theming works via tokens, no CSS edits required
- [ ] ~772 KB of dead CSS removed
- [ ] Every first-party stylesheet is token-driven
- [ ] No markup, logic, or database change
- [ ] `tokens.css` ready for Part B to consume

**Then start Part B:** [`../part-b-bs5-migration/README.md`](../part-b-bs5-migration/README.md)
— begin with [`../part-b-bs5-migration/00-design-system.md`](../part-b-bs5-migration/00-design-system.md)
(DS-2 direction is already decided by Part A, so DS-2 is complete) and
[`../part-b-bs5-migration/01-safety-net.md`](../part-b-bs5-migration/01-safety-net.md).
