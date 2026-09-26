# Part A — CSS Reskin

> **For agentic workers:** implement task-by-task. Steps use checkbox (`- [ ]`) syntax.
> Track progress in [`../STATUS.md`](../STATUS.md). Each task is one commit.

**Goal:** make the entire CRM look modern and attractive **without changing any markup,
JavaScript, PHP, or database code.**

**Architecture:** Restyle the existing theme layer only. `assets/css/app.css` (155 KB, 8,193
lines in **31 sections**) and the `bg-*.css` sidebar themes are rewritten to a token-driven
design. The Bootstrap 3 framework CSS, all plugin CSS, and all PHP/JavaScript stay untouched.

**Tech stack:** CSS custom properties · Bootstrap 3.3.5 (unchanged) · Font Awesome 4.7
(unchanged) · existing PHP/JS stack (unchanged)

---

## Scope guarantee

Part A edits **only** CSS files plus one `<link>` tag per layout shell. Concretely:

| Never touched in Part A | Why |
|---|---|
| Any `.php` file except 4 one-line `<link>` insertions | Keeps risk near zero |
| Any `.js` file | Bootstrap 3 JS keeps working unchanged |
| `bootstrap.min.css`, `bootstrap-rtl.min.css` | Third-party; PDFs and components depend on it |
| All 10 plugin stylesheets | Third-party |
| Any of the 68 PDF templates | Verified: **0** of them load `app.css` — fully insulated |
| Any inline `style=""` attribute in views | Out of scope; handled in Part B |

**Consequence:** if a Part A change looks wrong, it is a CSS-only revert. No data, no logic,
no markup to unwind.

---

## Locked design decisions

Approved 2026-09-26. These are settled — do not re-litigate during execution.

| Decision | Value | Rationale |
|---|---|---|
| **Direction** | **Refined dense** | Linear/Vercel-like. Compact rows, subtle borders, one accent colour, high information density. Fits a 633-page back office where users live all day. |
| **Corner radius** | `--r-sm: 4px` · `--r-md: 6px` · `--r-lg: 10px` | Currently a mix of 0/2/3/4px. 6px reads modern without looking bubbly. |
| **Base font** | 14px / line-height 1.5 | Matches current base size, so no layout reflow. |
| **Dark mode** | **Deferred to Part B** | Roughly doubles token + component work. Not a reskin. |
| **Icons** | **Keep Font Awesome 4.7** | Swapping to Lucide/Phosphor requires **markup** changes — that breaks the CSS-only guarantee. Revisit in Part B (`SH-7`). |
| **Framework** | **Stay on Bootstrap 3.3.5** | Part B replaces it. Part A only changes how it looks. |

---

## Execution order

| # | File | Tasks | Gate |
|---|---|---|---|
| 1 | [`00-safety-net.md`](00-safety-net.md) | A0-1 … A0-4 | Baselines captured, branch created |
| 2 | [`01-tokens.md`](01-tokens.md) | A1-1 … A1-4 | Theming works live |
| 3 | [`02-app-css.md`](02-app-css.md) | A2-1 … A2-12 | All 4 shells match the direction |
| 4 | [`03-sidebar-themes.md`](03-sidebar-themes.md) | A3-1 … A3-4 | Every theme option renders |
| 5 | [`04-dead-css.md`](04-dead-css.md) | A4-1 … A4-3 | No 404s, no RTL references |
| 6 | [`05-modules.md`](05-modules.md) | A5-1 … A5-3 | Module CSS on tokens |
| 7 | [`06-verification.md`](06-verification.md) | A6-1 … A6-4 | All checks green |

**34 tasks total.** A2 is the bulk of the visual work and is deliberately split into 12
single-commit tasks so each is independently reviewable and revertable.

---

## What you will still have after Part A

Stated plainly so there are no surprises:

- ✅ Modern, attractive UI across all 4 surfaces
- ✅ Live theming via tokens (colour changes in settings, no CSS edits)
- ✅ 745 KB of dead CSS removed
- ❌ Still Bootstrap 3 **markup** — no offcanvas, no BS5 toasts, no modern form controls
- ❌ 5,253 residual inline styles outside `invoice_view.php` remain hardcoded
- ❌ BS3-era accessibility gaps remain (see Part B `DS-6`)

Part B fixes all of it and **reuses every token created here**. Nothing in Part A is thrown
away.

---

## Working rules

1. **One task per commit.** Message format: `A2-3: restyle buttons to dense system`.
2. **Never edit PHP except the 4 `<link>` insertions in A1-2.** If a task seems to need PHP,
   stop — that means it belongs in Part B.
3. **Screenshot after every task** and compare against the A0 baseline. A silent layout break
   is the main risk of CSS work.
4. **Bump the cache-buster** `app.css?v=2` only once, in A6-4 — not per task.
5. **Run the smoke check** (`composer smoke`) before every commit.
