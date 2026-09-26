# Rollback & Landmines

Read this before running any find-and-replace.

---

## Two landmines that will silently corrupt the codebase

### 1. `label` must never be replaced with a plain string match

A naive `label` → `badge` rewrite corrupts:

| Pattern | What breaks |
|---|---|
| `label-inverse`, `label-danger`, `label-success`, `label-primary`, `label-info`, `label-warning` | Double-mangled if the mapping order is wrong — `badge-danger` is not a class |
| `col-sm-2 label-required` | A first-party validator class. **Must survive.** |
| `<label>` element names | Corrupts HTML |
| `$label`, `label:` in JS/CSS | Corrupts script |

**Rules:** anchor on class-attribute boundaries; sort mappings longest-first; add a test that
asserts `label-required` is untouched.

### 2. `panel-custom` is NOT a Bootstrap class

There are **780** occurrences, and it is a first-party variant defined in the project's own
CSS. A blind `panel-*` → `card-*` rewrite produces `card-custom`, which has no definition.

**Resolve in DS-5 first:** either map `panel-custom` → `card` and port the variant into a token
class, or keep the name and restyle it. Do not run CM-4 before that decision.

---

## Other high-risk patterns

| Pattern | Risk |
|---|---|
| `data-toggle` → `data-bs-toggle` | 13 first-party `data-toggle-state` / `data-toggle-fullscreen` attributes must **not** be renamed |
| `pull-right` → `float-end` (1,006) | Correct for layout. Wrong where it was used to push a sibling — those need `ms-auto` |
| `col-xs-6` → `col-6` | BS5 has no `xs`; it is the unprefixed base. Verify at ≥768 px |
| `col-xs-push-*` / `col-xs-pull-*` | → `order-*`; `push` and `pull` **invert** |
| `hidden-xs` / `visible-*` | → paired `d-*`; a one-way conversion hides content permanently |
| `checkbox` → `form-check` | Must run **before** `checkbox-inline`, and is structural, not textual |
| `loaded.bs.modal` (20) | Event was **removed** in BS5. Fails silently — no error, just never runs |
| `removeData('bs.modal')` (6) | BS5 uses an internal `Map`; silently stops clearing state |
| `$.fn.modal.Constructor` | Does not exist in BS5 → the `add_domain.php:960` guard is always false and its Select2 fix is dead |
| `strftime` (392) | Locale-sensitive; a naive `date()` swap changes month names and non-Gregorian calendars |
| `bootstrap3-pdf.css` | BS5 classes here render **nothing**. CI guard required (`DC-2`) |
| `app-rtl.css` | LTR is 245 KB, RTL is 218 KB — **they diverged**. Not redundant. |

---

## Rollback strategy

### Per task
Every task in this plan is one commit. Roll back with:

```powershell
git revert <sha>
```

That is why the plan mandates one task per commit and forbids mixing a codemod with a visual
change.

### Whole phases
| Phase | Rollback |
|---|---|
| 00 Design system | `git revert` the token commit; no production impact until `DS-4` ships |
| 01 Safety net | Pure addition — safe to keep even if abandoned |
| 02 Vendor bumps | Revert per plugin. **VS-1 (jQuery) first** — if anything breaks, suspect this |
| 03 Leverage points | Revert per helper. Highest blast radius per line, so commits are small |
| 04 Codemod | Revert per mapping (`CM-2`…`CM-6`), never the whole phase at once |
| 05 Structural | Manual — no reliable automated revert. This is why it comes after a green crawler |
| 06 Shell redesign | Revert; the previous CSS is intact in history |
| 07 Page pass | Revert per module |
| 08 Documents | Revert; PDFs are file-scoped |
| 09 Debt cleanup | Deletions revert cleanly with `git revert` **only if nothing was rewritten in the same commit** — hence the separation rule |

### The two-file safety net
Keep Bootstrap 3.3.5 files in the repo for the whole project. They are required by
`bootstrap3-pdf.css` anyway. Only remove them after the PDF track is migrated — which is not in
this plan.

---

## Environment-specific notes

- **Not in git — never commit:** `databases/*.sql`. Contains production PII, SMTP credentials,
  API keys and resumes. Already covered by `.gitignore`.
- **Do not delete `.playwright-mcp/`** without `git status` first. It was previously tracked;
  deleting it wholesale creates a large unintended diff.
- **Keep the junction** `C:\laragon\www\complete_crm` → project root. Apache serves from it.
- **`http://complete_crm.test/` does not resolve** — the hosts file is not editable without
  elevation. Use `http://localhost/complete_crm/`.
- **The live/demo database switch** in `application/config/database.php:83` is a single line.
  Verify which database you are on before trusting any data comparison.

---

## Definition of "done" for a task

A task is complete only when **all** of the following hold:

1. The code change is made
2. `composer crawl` reports 0 new failures
3. `composer htmldiff` shows no unexplained content loss
4. The task's stated verification command has been run and passed
5. The task is a single commit with a message referencing its task ID (e.g. `LP-1: btn_* helpers → BS5`)
