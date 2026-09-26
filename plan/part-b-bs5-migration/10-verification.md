# Phase 10 — Verification

**Goal:** make the redesign permanently safe, not just safe during the migration.
**Gate to exit:** CI runs on every push and blocks regressions.

The safety net built in Phase 01 protects you *during* the migration. This phase makes it
permanent, so the next person cannot quietly undo it.

---

## VF-1 — Wire the tools into composer

`composer.json` currently has no scripts. Add:

```json
{
  "scripts": {
    "crawl":            "php plan/_tools/crawl.php",
    "crawl:diff":       "php plan/_tools/crawl.php --diff",
    "htmldiff":         "php plan/_tools/htmldiff.php",
    "lint:bs3":         "php plan/_tools/codemod.php --dry --report",
    "lint:pdf":         "php plan/_tools/check-pdf.php",
    "lint:styles":      "php plan/_tools/check-inline-styles.php",
    "lint:php":         "php -l over all changed files",
    "a11y":             "pa11y-ci over routes.json"
  }
}
```

**Verify:** each script runs and exits 0 on the current codebase.
**Effort:** S

---

## VF-2 — CI pipeline

GitHub Actions (or your CI of choice):

1. `php -l` on all changed PHP
2. `lint:bs3` — fail if any Bootstrap-3 class reappears
3. `lint:pdf` — fail if a BS5 class lands in a PDF template
4. `lint:styles` — fail if the inline-style budget is exceeded
5. `crawl:diff` against the committed baseline — fail on any new `php_error` or non-2xx
6. `a11y` — fail on critical/serious violations
7. Screenshot diff on the ~40 baseline pages (tolerate small threshold; flag large deltas)

**Verify:** introduce a deliberate regression in a scratch branch; CI fails. Revert; CI passes.
**Effort:** M

---

## VF-3 — First-party test suite

There is currently **no first-party test coverage** — all 78 `*Test.php` files are vendored
(Dompdf, stripe-php). Add:

1. `phpunit.xml` at the root, scoped to `application/tests/` (do not pick up vendor tests)
2. Smoke tests: every route in `routes.json` returns 200 with no `php_errors`
3. Auth tests: unauthenticated access redirects; a user without permission gets 403
4. `date_helper` unit tests (from `DB-4`) — this is pure logic and easy to test properly
5. `message_box` and the `btn_*` helpers: assert the emitted markup contains no BS3 classes —
   a cheap, high-value regression guard for exactly the migration being performed
6. Permission tests around `get_permission` (209 call sites)

**Verify:** `composer test` runs green and is required by CI.
**Effort:** M

---

## VF-4 — Documentation and handover

1. **`CONTRIBUTING.md`** — the rules: no BS3 classes, no new inline styles beyond budget, no BS5
   in PDF paths, always run `composer crawl` before pushing.
2. **Component guide** — how to use each DS-5 component, with copy-paste examples. This is what
   prevents the next 885-file drift.
3. **Token reference** — every token in `tokens.css` and when to use it.
4. **PDF-track warning** — prominent, in `CONTRIBUTING.md` and in
   `bootstrap3-pdf.css`, because it is the easiest mistake to make and the most expensive.
5. **Baseline-issues list** — the pre-existing bugs from `SN-3`, so nobody re-reports them.
6. **Archive this plan** — mark completed tasks with a tick and a date; the plan is the audit
   trail for a very large diff.

**Verify:** a developer unfamiliar with the project can add a new page using only the docs.
**Effort:** S

---

## Full-plan exit criteria

- [ ] `crawl.php` green across all of `routes.json`
- [ ] 0 Bootstrap-3 classes in `application/views` and `modules/*/views`
- [ ] 0 BS5 classes in PDF templates
- [ ] All 19 invoice templates redesigned and verified on their correct track
- [ ] All 4 surfaces (admin, client, frontend, booking) match the DS-2 direction
- [ ] Accessibility checker clean
- [ ] 0 `strftime`; `E_DEPRECATED` no longer suppressed
- [ ] Inline-style budget under 5,000
- [ ] CI blocks all of the above
- [ ] Documentation complete
