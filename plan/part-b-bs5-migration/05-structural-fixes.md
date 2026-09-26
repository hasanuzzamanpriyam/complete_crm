# Phase 05 — Structural Fixes

**Goal:** fix the markup patterns a rename cannot express — forms, tables, dropdowns, JS APIs.
**Gate to exit:** no form renders incorrectly; no JS console errors app-wide.

Everything here requires judgement per page, not a regex. Budget real time.

---

## SF-1 — Horizontal forms (451 instances) ⚠️ largest single task

BS5 **removed `.form-horizontal` entirely.** There is no drop-in replacement. Three options,
per form:

| Option | Use when | Work |
|---|---|---|
| **A — keep vertical** | Most forms. Vertical is the DS-5 default. | Delete `form-horizontal`; done |
| **B — grid row** | Dense filters, search bars, 2–4 field rows | `<div class="row"><div class="col-md-6 mb-3">…` |
| **C — inline** | Single-field search | `d-flex align-items-end gap-2` |

Alongside every form:
- `control-label` (2,217) → `form-label`, and **move it above the field** if it is still
  side-by-side. A `form-label` sitting to the left of an input is the single most common
  post-codemod visual defect.
- `.form-control-static` → `.form-control-plaintext`
- `input-group-addon` → `input-group-text`, and fix the flex: BS5 `input-group` children need
  `flex-wrap: nowrap`
- `has-error` / `has-success` wrappers → `is-invalid` / `is-valid` on the control itself, with
  `.invalid-feedback` / `.valid-feedback` as siblings
- `input-sm`/`input-lg` → `form-control-sm`/`form-control-lg`

Target the ~300 pages that use `custom_form_Fields()` and `get_permission()` first — the rest
follow the established pattern.

**Verify:** submit 30 forms across the app; validation still fires, error styling appears,
no field is orphaned from its label.
**Effort:** L

---

## SF-2 — Checkbox and radio restructure

`checkbox` → `form-check` is a **structural** change, not a rename: the input must become a
direct child of `.form-check`, and the text goes in a `<label>` **sibling**.

```html
<!-- BS3 -->
<div class="checkbox"><label><input type="checkbox"> Remember me</label></div>

<!-- BS5 -->
<div class="form-check">
  <input class="form-check-input" type="checkbox" id="remember">
  <label class="form-check-label" for="remember">Remember me</label>
</div>
```

- Every `form-check` needs a unique `id`/`for` pair. Generate them where the label text is
  dynamic (209 sites in `get_permission()`).
- `.form-check-inline` for the inline variant.
- Radio groups: BS5 requires `.form-check` per option; a wrapper class is optional.

**Verify:** click a label toggles its checkbox, on 20 forms. Keyboard: space toggles, arrow
keys move within a radio group.
**Effort:** M

---

## SF-3 — Dropdown menus

BS5 rewrote dropdown markup entirely and moved the JS from the `button`/`a` to the parent.

1. Wrap in `class="dropdown"` (not `btn-group` unless you want grouped buttons).
2. Toggle attribute moves from the trigger to the **parent**:
   `data-bs-toggle="dropdown"` on `<div class="dropdown">`
3. The trigger needs `data-bs-toggle="dropdown"` **and** `href="#"` if it is an `<a>`.
4. Menu becomes `<ul class="dropdown-menu">` with `<li><a class="dropdown-item">`.
5. BS5 uses Popper for positioning — the bundle includes it, so no extra script.
6. Add `dropdown-menu-end` where the old menu was right-aligned.

Applies to all 192 sidebar entries that use dropdowns, plus every `custom_dropdown`-style
block in the page pass.

**Verify:** 30 dropdowns open, align correctly, and close on outside click and Escape.
**Effort:** M

---

## SF-4 — Tables

DataTables is handled in `VS-3`. These are the plain HTML tables:

- `.table-condensed` / `.table-hover` gone → use `.table-sm` / keep `.table-hover`
- `.table-responsive` is still valid
- Wrap every table in `.table-responsive` for mobile
- `thead` needs `table-light`/`table-dark` per DS-5
- Sortable header markup (`<a href="?sort=col">` in `th`) must not break the DataTables init

**Verify:** 20 tables scroll horizontally on narrow viewports instead of overflowing the page.
**Effort:** M

---

## SF-5 — JS API migration audit

BS5 dropped jQuery event shorthands and changed several plugin APIs. Audit every first-party
`.js` and inline script:

| Pattern | Count | Fix |
|---|---|---|
| `loaded.bs.modal` | 20 | → `shown.bs.modal` (event was **removed**) |
| `removeData('bs.modal')` | 6 | BS5 stores state in an internal `Map`; use `bootstrap.Modal.getInstance(el)?.dispose()` |
| `.modal('toggle')` on a parent | — | BS5 `hide` is **not** cancelable — a closing modal cannot be prevented |
| `.tooltip()` auto-init | — | BS5 removed data-API tooltip auto-init; initialize explicitly |
| `.popover()` | — | same |
| `.button('toggle')` | — | BS5: `btn-check`/`btn-check-active` class toggle |
| `$.fn.modal.Constructor` | 1 | does not exist in BS5 — the dead `add_domain.php:960` guard |
| `hide.bs.*` via jQuery `.on()` | 7 | BS5 events do not bubble to jQuery; use native `addEventListener` |
| `e.hijack()` / `e.preventDefault()` | — | `preventDefault` is gone; control `hide` via `hidePrevented.bs.modal` or restructure |
| `.input-group-addon` in JS selectors | — | renamed in CM-4 |
| `$.isFunction` / `$.trim` | audit | removed in jQuery 3 (VS-1) |
| `.load()/.unload()/.error()` events | audit | removed in jQuery 3 |
| `Pusher.logToConsole` | 1 | causes the offline console warning — gate behind config |

Add a **global error trap** in development so silent failures surface:

```js
window.addEventListener('error', e => console.error('[crm]', e.message, e.filename, e.lineno));
window.addEventListener('unhandledrejection', e => console.error('[crm]', e.reason));
```

**Verify:** crawler console check reports 0 errors on every route except the documented
Pusher warning.
**Effort:** M

---

## Exit gate

- [ ] 0 occurrences of `form-horizontal`
- [ ] Every `form-label` is positioned above or beside its control deliberately
- [ ] Every `form-check` has a working `id`/`for` pair
- [ ] 192 sidebar dropdowns + page dropdowns all work
- [ ] All tables horizontally scrollable
- [ ] `loaded.bs.modal`, `removeData('bs.modal')` and the dead `$.fn.modal.Constructor` guard
      all resolved
- [ ] Global error trap in place; console clean app-wide
