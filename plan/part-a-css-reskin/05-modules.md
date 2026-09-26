# Part A · A5 — Module CSS

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development
> (recommended) or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** bring the three module-specific stylesheets onto the token system, and make a decision
about the booking design.

## Files in scope

| File | Size | Origin | Action |
|---|---|---|---|
| `assets/css/booking.css` | 8,408 b | first-party | A5-1 — decision required |
| `assets/css/calendar_modern.css` | 9,624 b | first-party | A5-2 |
| `assets/css/timesync-dashboard.css` | 9,591 b | first-party | A5-2 |
| `assets/css/ai_chat.css` | 3,393 b | first-party | A5-2 |

**Out of scope — do not edit:** `datepicker.css` (17,433 b) and `timepicker.css` (5,430 b).
These are third-party widget styles. They are *themed* in A2-4 via their containers, never
rewritten.

---

## A5-1 — Booking: align or keep distinct?

`booking/_layout_main.php` and `assets/css/booking.css` are an **independent visual design**,
not a variant of the admin shell. It has its own layout and its own colours.

**Decision (already taken — Refined dense applies everywhere):** align booking to the main design
system, so a customer moving between the public site and the booking flow sees one product.

- [ ] **Step 1: Capture the current booking pages** as baseline

Routes: `/book-free-consultation`, `/booking`, and the calendar/availability step. Save to
`plan/_baseline/part-a/booking/`.

- [ ] **Step 2: Read `booking.css` and classify every rule**

| Category | Action |
|---|---|
| Colour value | Replace with the matching `var(--c-…)` |
| Spacing value | Replace with `var(--sp-…)` |
| Radius value | Replace with `var(--r-…)` |
| Layout/structure | **Keep as-is** |
| Colour that does not map to a token | Add a new token to `tokens.css` in a clearly-marked block, then use it |

- [ ] **Step 3: Confirm `tokens.css` loads in the booking layout**

`A1-2` added the link. Verify on `/book-free-consultation`:

```js
getComputedStyle(document.documentElement).getPropertyValue('--c-accent').trim()
```

- [ ] **Step 4: Apply the mapping**
- [ ] **Step 5: Align the two structural details** that most affect consistency:
      control height (`--h-control`) and corner radius (`--r-md`) for the form fields and
      buttons in the booking flow.
- [ ] **Step 6: Verify + commit**

```powershell
composer smoke
git commit -m "A5-1: align booking design to token system"
```

**Verify:** booking pages render; form fields and buttons match the main design; calendar step
still functions.
**Effort:** M

---

## A5-2 — Calendar, timesync and AI chat

- [ ] **Step 1: `calendar_modern.css` (9,624 b)**

Replace hardcoded colours and radii with tokens. Keep all calendar layout intact — a calendar is
a dense, fixed-geometry component and restyling its structure invites bugs.

Pay attention to: event chip colours (map to the status tokens), today-highlight, selected-day
state, and the weekend shading. These must all use tokens so they stay consistent with the
sidebar and table accents.

- [ ] **Step 2: `timesync-dashboard.css` (9,591 b)**

This styles a timer dashboard. Map colours, radii and spacing to tokens. Verify the running-timer
state and the start/stop buttons against the A2-3 button restyle.

- [ ] **Step 3: `ai_chat.css` (3,393 b)**

Map the chat bubble, user/assistant distinction, and input area to tokens. Ensure the user
bubble uses `--c-accent` and the assistant bubble uses `--c-surface-2` for contrast.

- [ ] **Step 4: Verify all three routes**

| Route | Check |
|---|---|
| `/admin/timesync` (or the timesync dashboard) | timer runs, buttons styled |
| the calendar page | events render, day selection works |
| the AI chat panel | bubbles render, input aligned |

- [ ] **Step 5: Commit**

```powershell
composer smoke
git commit -m "A5-2: bring module CSS onto tokens"
```

**Verify:** no hardcoded hex remains in the 3 files; all 3 routes render correctly.
**Effort:** M

---

## A5-3 — Sweep for remaining hardcoded colours

- [ ] **Step 1: Confirm every first-party stylesheet is token-driven**

```powershell
Get-ChildItem assets\css -Filter *.css |
  Where-Object { $_.Name -notmatch 'rtl|bootstrap|min\.css|datepicker|timepicker|toastr' } |
  ForEach-Object {
    $hex = ([regex]::Matches((Get-Content $_.FullName -Raw), '#[0-9a-fA-F]{6}\b')).Count
    if ($hex -gt 0) { "{0,-28} {1,4} hex values" -f $_.Name, $hex }
  }
```

Expected: **no output**, or only entries in files with a documented reason (e.g. a chart library
palette that must stay fixed).

- [ ] **Step 2: For any remaining value, either tokenise it or add a comment explaining why not**
- [ ] **Step 3: Confirm the token file is the single source of truth**

```powershell
Select-String -Path assets\css\tokens.css -Pattern '#[0-9a-fA-F]{3,8}' | Measure-Object
```

This is the **expected** place for hex values. Everywhere else should be `var(--…)`.

- [ ] **Step 4: Commit**

```powershell
git commit -m "A5-3: sweep remaining hardcoded colours onto tokens"
```

**Verify:** every first-party stylesheet uses tokens; hex values exist only in `tokens.css` and
documented third-party exceptions.
**Effort:** S

---

## Exit gate

- [ ] `booking.css` aligned to tokens
- [ ] `calendar_modern.css`, `timesync-dashboard.css`, `ai_chat.css` on tokens
- [ ] `datepicker.css` and `timepicker.css` untouched
- [ ] All 3 module routes render correctly
- [ ] No hardcoded hex outside `tokens.css` (except documented exceptions)

## Tasks: 3 · Completed: 0 · Remaining: 3
