# Part A baseline — A0-2

**Captured:** 2026-09-26
**Viewport:** 1440 × 900
**Account:** `forid` / `Admin@123` (local only) for rows 6–21; logged out for rows 1–5.
**Base URL:** `http://localhost/complete_crm`

Rows 1–5 are captured logged out; rows 6–21 require an authenticated session. When a session is
active, `/login` redirects to `/admin/dashboard`, so re-capturing the public rows requires logging
out first (`/login/logout`).

| # | File | URL | Notes |
|---|------|-----|-------|
| 1 | `01-login.png` | `/login` | logged out |
| 2 | `02-register.png` | `/login/register` | logged out |
| 3 | `03-forgot-password.png` | `/login/forgot_password` | logged out |
| 4 | `04-career.png` | `/career` | logged out |
| 5 | `05-book-consultation.png` | `/book-free-consultation` | logged out |
| 6 | `06-admin-dashboard.png` | `/admin/dashboard` | |
| 7 | `07-admin-superadmin.png` | `/admin/superadmin` | |
| 8 | `08-clients-list.png` | `/admin/client/manage_client` | |
| 9 | `09-invoices-list.png` | `/admin/invoice/manage_invoice` | source for row 21 |
| 10 | `10-quotations.png` | `/admin/quotations` | |
| 11 | `11-proposals.png` | `/admin/proposals` | |
| 12 | `12-projects.png` | `/admin/projects` | |
| 13 | `13-tasks.png` | `/admin/tasks/all_task` | |
| 14 | `14-employees.png` | `/admin/user/user_list` | |
| 15 | `15-chart-of-accounts.png` | `/admin/accounting/chart_of_accounts` | |
| 16 | `16-transactions-report.png` | `/admin/transactions/transactions_report` | |
| 17 | `17-attendance.png` | `/admin/attendance/daily_attendance` | |
| 18 | `18-payroll-summary.png` | `/admin/payroll/payroll_summary` | |
| 19 | `19-sales-report.png` | `/admin/report/sales_report` | |
| 20 | `20-settings.png` | `/admin/settings` | |
| 21 | `21-invoice-detail.png` | `/admin/invoice/invoice_details/{id}` | **record id: `9`** (`INV-2026-Mar-15-0009`) |

## Record IDs

Re-capture with these same records so before/after comparisons are like-for-like:

- **Invoice detail:** id `9` — `INV-2026-Mar-15-0009`, Moktadir Rahman, $0,00, Fully Paid.

## Detail-route correction

`plan/part-a-css-reskin/00-safety-net.md` guessed the detail route as
`/admin/invoice/view/{id}`. That path does not exist. The real routes (discovered from the
invoice list markup) are:

- Invoice detail: `/admin/invoice/manage_invoice/invoice_details/{id}` → redirects to `/admin/invoice/invoice_details/{id}`
- Client detail: `/admin/client/manage_client/...` (not yet resolved)
- Project detail: `/admin/projects/...` (not yet resolved)

## Pre-existing issues

None found in this capture: all 21 screenshots contain no PHP error overlay, no
`Fatal error`, no `An Error Was Encountered`, and every route resolved to its intended path
(no auth bounce on rows 6–21).

Observed on `21-invoice-detail.png` (console): `GET http://localhost/assets/img/dragger.png`
→ **404**. The URL is missing the `/complete_crm` base path — a pre-existing base-URL bug, not
introduced by this work. No visual impact beyond a missing image.

## Verification performed

- 21 PNGs exist, all 21 distinct MD5 hashes (an earlier capture run produced 15 byte-identical
  copies of the login page because the session was not authenticated — discarded and re-captured).
- Byte scan of all 21 PNGs for `Fatal error` / `An Error Was Encountered` / `htmlspecialchars()`:
  0 matches.
