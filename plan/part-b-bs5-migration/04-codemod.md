# Phase 04 — Codemod

**Goal:** mechanically rewrite the remaining ~14,000 Bootstrap-3 class and `data-*` occurrences
across 885 views.
**Gate to exit:** 0 obsolete classes remain; crawler green; HTML diffs reviewed.

Run only **after** Phase 03. The leverage points removed ~900 occurrences and the remaining set
is now genuinely page-local, so a scripted rewrite is safe.

**Rule: one mapping per commit. Never combine mappings.** If a mapping breaks layout you need to
know which one caused it.

---

## CM-1 — Build the codemod harness

`plan/_tools/codemod.php` — dry-run by default, `--write` to apply.

Requirements:
- Token-boundary regex only. `panel` must not match `panels`, `panel-title` must not match
  `panel-title-bar`, `label` must not match `label-inverse`.
- Scope to `application/views/**` and `modules/*/views/**`. **Never** touch
  `application/helpers/dompdf/**` (PDF track, Phase 08) or `vendor/**`.
- Report per-file counts so you can see the blast radius of each mapping.
- `-` `--backup` writes `.bs3` copies, or rely on git.

**Verify:** dry run on one directory prints counts identical to the audit.
**Effort:** S

---

## CM-2 — `data-*` renames

BS5 renamed every data attribute with a `bs` infix. These are mechanical and carry almost no
layout risk, so do them first.

| BS3 | Count | BS5 |
|---|---|---|
| `data-toggle` | 1,420 | `data-bs-toggle` |
| `data-placement` | 691 | `data-bs-placement` |
| `data-dismiss` | 400 | `data-bs-dismiss` |
| `data-target` | 272 | `data-bs-target` |
| `data-parent` | 17 | `data-bs-parent` |

**Do NOT rename these 13 custom attributes** — they are first-party, unrelated to Bootstrap:
- `data-toggle-state` (collapsible groups, `header.php:31`, `vertical_sidebar.php:118`, +6)
- `data-toggle-fullscreen` (fullscreen toggle, +6)

Also inside the renamed set, audit each value for semantic changes:
- `data-bs-toggle="button"` — `type="button"` is now required on `<button>`
- `data-bs-toggle="dropdown"` — the parent needs `data-bs-toggle` and the trigger an `href="#"`
- `data-bs-toggle="modal|offcanvas"` — target must resolve; `#` self-targeting is gone
- `data-bs-dismiss="modal"` — only valid on a child of `.modal`, never on `.modal` itself

**Verify:** grep → 0 of the 5 renamed attributes remain un-prefixed; 13 custom ones intact.
**Effort:** S

---

## CM-3 — Layout and grid renames

| BS3 | Count | BS5 |
|---|---|---|
| `col-xs-6` | 169 | `col-6` |
| `col-xs-4` | 66 | `col-4` |
| `col-xs-3` | 50 | `col-3` |
| `col-xs-5` | 32 | `col-5` |
| `col-xs-2` | 24 | `col-2` |
| `col-xs-7` | 18 | `col-7` |
| `col-xs-8` | 13 | `col-8` |
| `col-xs-12` | 13 | `col-12` |
| `col-xs-9` | 11 | `col-9` |
| `col-xs-10` | 8 | `col-10` |
| `col-xs-push-*` | 8 | `order-*` |
| `col-xs-pull-*` | 8 | `order-*` (inverted) |
| `col-sm-offset-*` | 17 | `offset-sm-*` |
| `img-responsive` | 5 | `img-fluid` |
| `img-circle` | 113 | `rounded-circle` |
| `img-rounded` | 0 | `rounded` |
| `img-thumbnail` | 0 | `img-thumbnail` (kept, deprecated) |
| `center-block` | 0 | `d-block mx-auto` |
| `hidden-*` / `visible-*` | 27 | `d-*` / `d-*-none` |
| `pull-right` | 1,006 | `float-end` |
| `pull-left` | 20 | `float-start` |

BS5 has **no `xs` breakpoint** — it is the unprefixed base, so `col-xs-6` → `col-6`.

> `pull-right` alone is **1,006 occurrences** and the single largest mechanical win in the plan.
> Note the semantic trap: `pull-right` means "float right" but in the JS sense means "push to the
> end". `float-end` is correct in the vast majority of cases, but **`push`/`append` call sites
> that relied on it for ordering need `me-auto`/`ms-auto`, not `float-end`**. Review the diff
> for those.

Visibility mapping (`d-*` pairs):

| BS3 | BS5 |
|---|---|
| `hidden-xs` | `d-none d-md-block` |
| `visible-xs` | `d-block d-md-none` |
| `hidden-sm` | `d-none d-lg-block` |
| `visible-sm` | `d-lg-none` |
| `hidden-md` | `d-none d-xl-block` |
| `visible-md` | `d-xl-none` |
| `hidden-lg`, `visible-lg` | `d-xl-none`, `d-none d-xl-block` |

**Verify:** open 10 pages that previously wrapped differently at ≥768 px. Confirm no content
overflows and nothing is hidden that should be visible.
**Effort:** M

---

## CM-4 — Component renames

| BS3 | Count | BS5 | Note |
|---|---|---|---|
| `panel` | 985 | `card` | see CM-5 for the structural part |
| `panel-heading` | 809 | `card-header` | |
| `panel-custom` | 780 | `card` + `.card-*` | custom variant — see below |
| `panel-body` | 636 | `card-body` | |
| `panel-title` | 509 | `card-title` | |
| `panel-default` | 51 | `card` | |
| `panel-footer` | ~40 | `card-footer` | |
| `label` | 476 | `badge` | **token-boundary critical** |
| `label-danger` | 124 | `badge text-bg-danger` | |
| `label-success` | 93 | `badge text-bg-success` | |
| `label-primary` | 37 | `badge text-bg-primary` | |
| `label-info` | 25 | `badge text-bg-info` | |
| `label-warning` | 18 | `badge text-bg-warning` | |
| `label-inverse` | 4 | `badge text-bg-dark` | |
| `checkbox` | 313 | `form-check` | **before** `checkbox-inline` |
| `checkbox-inline` | 68 | `form-check-inline` | order matters |
| `form-horizontal` | 451 | (none) | **SF-1**, not a rename |
| `form-control-static` | 455 | `form-control-plaintext` | |
| `control-static` | 1 | `form-control-plaintext` | |
| `input-group-addon` | 316 | `input-group-text` | |
| `btn-default` | 429 | `btn-secondary` | |
| `btn-xs` | 748 | `btn-sm` | should already be ~0 from LP-1 |
| `close` | 246 | `btn-close` | |
| `close-icon` | 0 | `btn-close` | |
| `well` | 0 | `card card-body` | |
| `page-header` | 0 | `d-flex` | |
| `alert` | 0 | `alert alert-*` | unchanged — was never used |
| `btn-info` | 2 | `btn-info` | fine — still valid |
| `btn-link` | 2 | `btn-link` | fine |

Two specific hazards:

1. **`label` must be a token-boundary match.** A naive `str_replace('label', 'badge')` corrupts
   `label-inverse`, `label-danger`, `col-sm-2 label-required`, `<label>` element names and any
   JS variable named `label`. Sort the mapping longest-first and anchor on class-attribute
   boundaries only.
2. **`panel-custom` (780) is a first-party variant**, not a Bootstrap class. It has its own CSS.
   Do not blind-rename it. Either map `panel-custom` → `card` and port the variant into a token
   class, or keep the name and restyle it. **This is the single largest decision in the codemod**
   — resolve it in DS-5 before running.

`label-*` is currently being abused as a status badge. In BS5 use `badge text-bg-*` for solid
and `badge bg-*-subtle text-*` for soft, per DS-5.

**Verify:** grep → 0 of the 24 mapped classes remain. Then open 20 pages covering every mapped
component.
**Effort:** L

---

## CM-5 — `panel` → `card` structural pass

A pure rename leaves a card that looks like a panel (border, no shadow, wrong padding, wrong
header treatment). After CM-4, do a per-file pass to apply DS-5:

1. `.panel` → `.card`
2. `.panel-heading` → `.card-header` (+ `.d-flex.align-items-center.justify-content-between`
   since the heading usually contained float children)
3. `.panel-body` → `.card-body`
4. `.panel-footer` → `.card-footer`
5. Replace `border` with `border-0 shadow-sm` per the elevation scale
6. Nested `.panel` inside `.panel-body` → `.card` inside `.card-body`; verify the border radius
   does not double up

**Verify:** 20 panel-heavy pages render as cards with correct radius, padding and elevation.
**Effort:** M

---

## CM-6 — Long tail

After CM-2…CM-5, the residue should be single digits. Fix each individually:

| BS3 | Count | BS5 |
|---|---|---|
| `input-lg` | 7 | `form-control-lg` |
| `form-control-lg` | 5 | kept |
| `input-sm` | 2 | `form-control-sm` |
| `help-block` | 1 | `form-text` |
| `navbar-form` | 1 | `d-flex` |
| `navbar-btn` | 1 | `nav-item` |
| `navbar-right` | 1 | `ms-auto` |
| `navbar-left` | 1 | `me-auto` |
| `nav-stacked` | 4 | `flex-column` |
| `dropup` | 2 | BS5 handles it via `data-bs-popper` |
| `text-help` | 1 | `form-text` |
| `hidden-print` | 182 | `@media print { .d-print-none }` |

`hidden-print` is the biggest of these. In BS5, use the print utilities
(`d-print-none` / `d-print-block` / `d-print-inline`) rather than a custom class. Since **63
`window.print()` call sites** depend on this, the print stylesheet must be verified explicitly —
see `DC-6`.

**Verify:** `grep -rE 'panel|label-|btn-xs|form-group|control-label|pull-right|input-group-addon|hidden-xs|data-toggle='`
returns only legitimate matches (BS5 `data-bs-toggle`, first-party `label-required`).
**Effort:** M

---

## Exit gate

- [ ] `grep` finds 0 obsolete Bootstrap-3 classes across `application/views` and `modules`
- [ ] 13 custom `data-toggle-*` attributes intact
- [ ] Every mapping landed as its own commit, each crawler-green
- [ ] `panel-custom` decision documented and applied consistently
- [ ] HTML diff reviewed for accidental matches (spot-check 50 files)
- [ ] 0 new `php_errors`; no page lost a control
