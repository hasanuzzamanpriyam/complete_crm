# Phase 09 — Debt Cleanup

**Goal:** remove the dead weight the audit found.
**Gate to exit:** no zero-reference code remains; no deprecated API calls remain.

> **Sequencing note.** You originally asked for cleanup first. These tasks moved later on
> purpose: deleting files does not make the migration safer, and doing it first just makes every
> subsequent diff harder to read. The *leverage* cleanup (Phase 03) stayed early because it pays
> for itself immediately.

---

## DB-1 — Delete zero-call-site helpers

Verified **0 call sites** for all of these in `application/helpers/admin_helper.php`:

`btn_approve` · `btn_reject` · `btn_print` · `btn_atndc_print` · `btn_delete_disable` ·
`btn_active` · `btn_excel` · `btn_save` · `btn_make_pdf` · `btn_edit_disable` · `get_html_tags`

Delete, then run the crawler to confirm nothing breaks.

**Verify:** crawler green; `grep` confirms no references.
**Effort:** S

---

## DB-2 — Delete duplicate and stray files

| File | Evidence |
|---|---|
| `assets/css/frontent.css` | 113 bytes, 0 references |
| `assets/css/bg-new copy.css` | stray copy; not a valid `sidebar_theme` value |
| `.../accounting/_sales_report_pdf.php` (15 KB) | vs `_sales_report_pdf1.php` (14 KB) — near-duplicate, keep one |
| `.../job_recruitment/_job_recruitment_pdf.php` | vs `_job_recruitment_pdf1.php` — identical, keep one |
| `assets/css/app-rtl.css` (245 KB) + `.min.css` (218 KB) | **RTL unused** — `tbl_config.RTL=0`, 0 of 113 accounts `direction='rtl'`, all English |
| `assets/css/bootstrap-rtl.css` + `.min.css` | same |
| `application/language/{arabic,hebrew,...}` | only if the decision is to drop i18n; see `ROLLBACK.md` |

> The `arabic` and `hebrew` language files are **not** deleted by default. RTL is dropped, but
> the language packs are cheap to keep and the decision to remove i18n is a product call, not a
> styling one.

**Verify:** crawler green; no 404s in browser network log.
**Effort:** S

---

## DB-3 — Consolidate RTL removal

Once confirmed, remove the `RTL` branch from `htmlheader.php` (the `app-rtl` / `bootstrap-rtl`
link tags) and the RTL column in `settings/appearance`.

**Safety:** `app-rtl.css` and `app-rtl.min.css` are **not identical** — LTR is 245 KB, RTL is
218 KB, and they have diverged. Do not assume the RTL file is redundant; if Arabic/Hebrew ever
becomes a requirement it will need its own port.

**Verify:** no CSS 404s; appearance settings save.
**Effort:** S

---

## DB-4 — Replace `strftime()` (392 call sites, 223 files)

PHP 8.1 deprecated `strftime()`; it is slated for removal. The project runs PHP 8.3 and the logs
prove it is firing.

1. Inventory all 392 call sites. Nearly all are `strftime('%d %b %Y', $ts)`-style date
   formatting in views and helpers.
2. Create `application/helpers/date_helper.php` with `format_date($ts, $format)`, `format_time()`,
   `format_datetime()`, backed by `IntlDateFormatter` (locale-aware, honours the user's language
   choice) or `DateTimeInterface::format()` where locale is irrelevant.
3. Replace all 392 call sites.
4. **Un-suppress `E_DEPRECATED`** in `index.php` and `demo/index.php` once clean — the current
   suppression is a diagnostic aid, not a fix. Keep the other three error levels enabled.
5. Confirm `application/logs/log-2026-04-26.php` stops growing.

**Verify:** 0 `strftime` occurrences; logs clean; dates render identically (spot-check 50 pages,
especially non-English locales).
**Effort:** M

---

## DB-5 — JavaScript deprecations

`assets/js/app.js` contains 51 uses of removed jQuery APIs:

| Pattern | Fix |
|---|---|
| `$.isFunction()` | `typeof x === 'function'` |
| `$.isArray()` | `Array.isArray()` |
| `$.trim()` | native `.trim()` |
| `$.parseJSON()` | `JSON.parse()` |
| `$.type()` | removed — use `typeof`/`instanceof` |
| `.bind()/.unbind()` | `.on()/.off()` |
| `.delegate()` | `.on(…, selector)` |
| `.size()` | `.length` |
| `andSelf()` | removed — re-collect the elements |
| `.load()/.unload()/.error()` | `.on('load')` etc. |

`assets/js/jquery.min.js` itself contains 153 — that is the 1.9.1 build; removing the file in
`VS-1` resolves them.

**Verify:** global error trap (`SF-5`) reports 0 errors; 0 occurrences of the above.
**Effort:** S

---

## DB-6 — Inline style budget

22,587 inline `style=""` attributes across 604 views (excluding the 4.6 MB `invoice_view.php`,
handled in `DC-4`, and the 68 PDF templates, which are correct to keep inline).

Target: **< 5,000**.

1. Add a CI budget check: fails if the count rises above a committed threshold.
2. Batch by pattern — the largest offenders are repeated colour strings, widths, and
   `margin/padding` nudges that should be spacing tokens.
3. Move to utility classes or component rules. Where a truly dynamic value is needed, use a CSS
   custom property set inline (`style="--w: 42%"`) so the rule lives in CSS, not the markup.

**Verify:** budget check in CI; count under target; no visual regressions.
**Effort:** L

---

## DB-7 — Two dashboards

`dashboard_view` and `dashboard_admin` (plus the client's `dashboard_client`) duplicate layout
and logic. After `SH-5`, consolidate the shared parts into one partial with a role switch.

**Verify:** admin, super-admin and client dashboards all render with the same numbers as baseline.
**Effort:** M

---

## DB-8 — Upgrades still blocked

Not part of the redesign. Recorded so they are not lost:

| Upgrade | Blocker |
|---|---|
| **Dompdf 3.0.x** | `composer.json` pins `phenx/php-font-lib: >=0.5.4 <1.0.0`; Dompdf 3.0 requires `>=1.0.0`. Needs a font-lib major bump first. |
| **CodeIgniter 3.1.13 → 3.1.14** | Security fix, low risk, small diff. Independent of the redesign — schedule separately. |
| **jQuery → native** | Not now. `VS-1` lands 3.7; removing jQuery entirely is a future project. |
| **HMVC** | 6 modules on a CI1.3-era extension. Deep change. Out of scope. |

---

## Exit gate

- [ ] 11 dead helpers deleted
- [ ] Duplicate/stray files removed; no 404s
- [ ] RTL removed, with the divergence caveat documented
- [ ] 0 `strftime` calls; `E_DEPRECATED` suppression removed
- [ ] 0 deprecated jQuery APIs in first-party JS
- [ ] Inline-style budget under 5,000 and enforced in CI
- [ ] Dashboards consolidated
- [ ] Blocked upgrades documented for later
