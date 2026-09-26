# Branch Strategy

## ⛔ Hard rule

> **`sakib` is the only branch this work is pushed to. `main` is never pushed to.**

`main` is not yours. Do not push to it, do not open a PR against it, do not force-push anything.
Every completed and tested task goes to `sakib`.

---

## Current state

| | |
|---|---|
| Remote | `https://github.com/hasanuzzamanpriyam/complete_crm.git` |
| Working branch | **`sakib`** (tracks `origin/sakib`) |
| Protected branch | **`main`** — read-only for this work |
| `sakib` position | Identical to `main` at `19858cbc` (0 ahead, 0 behind) |
| GitHub CLI | **Not installed** — use `git` or the web UI |

Verify at any time:

```powershell
git rev-parse --abbrev-ref HEAD      # must print: sakib
git branch -r                         # origin/main, origin/sakib
```

---

## Per-task workflow

Every task follows the same loop. Push only after the test passes.

```
1. git pull origin sakib              # start from current
2. ...make the CSS change...
3. composer smoke                     # must pass
4. compare against the A0 baseline    # must show no regression
5. git add <only the files for this task>
6. git commit -m "A2-3: restyle button family to dense system"
7. git push origin sakib              # never main
8. tick the box in ../STATUS.md
9. git add ../STATUS.md && git commit -m "A2-3: mark complete" && git push origin sakib
```

Steps 6 and 9 are separate commits on purpose: the second one records progress, so reverting a
task never loses the progress record.

---

## Safety checks

**Before every commit** — confirm you are not about to touch `main`:

```powershell
git rev-parse --abbrev-ref HEAD       # must be: sakib
```

**Before every push** — confirm nothing sensitive is staged:

```powershell
git diff --cached --name-only
```

Must contain **no** `.sql` files. `databases/` holds production PII, SMTP credentials, API keys
and resumes; it is gitignored and must stay that way.

**After every push** — confirm `main` did not move:

```powershell
git log --oneline -1 origin/main      # must still be 19858cbc
```

---

## Part B

Part B follows Part A on the same branch. Two options:

| Option | Approach | Use when |
|---|---|---|
| **Continue on `sakib`** (default) | All 74 Part B tasks commit to `sakib` after Part A | Simpler; `sakib` history is the full redesign |
| Cut a branch | `git checkout -b part-b-bs5 sakib`, merge back into `sakib` when done | You want `sakib` to always hold a working Part A |

Either way, **nothing is pushed to `main`**. Default to continuing on `sakib`.

---

## Reverting a task

Every task is one commit with its ID in the message, so any task can be undone alone:

```powershell
git log --oneline sakib                 # find the task commit
git revert <sha>                        # undo it
git push origin sakib
```

Reverting is safe on `sakib` — it is a working branch, not a shared release branch. There is no
force-push step anywhere in this workflow.

---

## Reviewing progress

```powershell
git log --oneline origin/main..sakib    # every redesign commit, in order
```

Because each message starts with its task ID, this doubles as a progress log that matches
[`../STATUS.md`](../STATUS.md).

---

## Optional: install GitHub CLI

Only needed if you want PRs or issue tracking:

```powershell
winget install --id GitHub.cli
gh auth login
```

Not required. Direct `git push` to `sakib` is the whole workflow.
