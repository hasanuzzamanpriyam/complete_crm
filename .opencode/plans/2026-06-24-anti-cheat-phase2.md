# Anti-cheat Phase 2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Detect contextual mismatch — user has high keystrokes while the foreground window is an entertainment/social media site.

**Architecture:** Shared window title from Thread A (app tracking) to Thread B (activity monitoring) via `Arc<Mutex<String>>` on `AppTrackerState`. Window title cloned before `thread::spawn` in start_activity_monitoring, read each tick inside the snapshot lock block. Added to `compute_suspicion()`.

**Tech Stack:** Rust (Tauri)

---

### Task 1: Add shared window title state

**Files:**
- Modify: `src-tauri/src/app_tracker.rs`

- [ ] **Step 1: Add current_window_title to AppTrackerState**

In `AppTrackerState` struct (line 109), after `last_app`:
```rust
pub current_window_title: Arc<Mutex<String>>,
```

- [ ] **Step 2: Initialize in AppTrackerState::new()**

In `new()` (line 129), after `last_app`:
```rust
current_window_title: Arc::new(Mutex::new(String::new())),
```

- [ ] **Step 3: Verify compilation**

Run: `cd C:\Users\CT\Desktop\Tracker\src-tauri && cargo check`
Expected: Clean compilation

---

### Task 2: Write window_title from Thread A

**Files:**
- Modify: `src-tauri/src/app_tracker.rs`

- [ ] **Step 1: Clone Arc before thread::spawn**

In the `start_app_tracking` function (around line 353), find where other Arcs are cloned:

```rust
// Add alongside existing clones
let current_window_title = state.current_window_title.clone();
```

- [ ] **Step 2: Write to shared state**

Inside the `get_active_window()` `Ok` branch (after line 376 `*app = app_name.clone();`), add:
```rust
if let Ok(mut wtitle) = current_window_title.lock() {
    *wtitle = window_title.clone();
}
```

- [ ] **Step 3: Verify compilation**

Run: `cd C:\Users\CT\Desktop\Tracker\src-tauri && cargo check`
Expected: Clean compilation

---

### Task 3: Read window_title in activity monitoring thread

**Files:**
- Modify: `src-tauri/src/app_tracker.rs`

- [ ] **Step 1: Clone Arc before thread::spawn**

In `start_activity_monitoring` function (around line 781-787), add alongside existing clones:
```rust
let current_window_title = state.current_window_title.clone();
```

- [ ] **Step 2: Read in the tick loop**

Inside the `if let Ok(mut snap) = interval_snapshot.lock()` block (line 842), at the top before any other operations:
```rust
// Read current window title from app tracking thread
if let Ok(wtitle) = current_window_title.lock() {
    snap.window_title = wtitle.clone();
}
```

- [ ] **Step 3: Verify compilation**

Run: `cd C:\Users\CT\Desktop\Tracker\src-tauri && cargo check`
Expected: Clean compilation

---

### Task 4: Add window_title to IntervalSnapshot

**Files:**
- Modify: `src-tauri/src/app_tracker.rs`

- [ ] **Step 1: Add field to struct**

In `IntervalSnapshot` (line 30), after `key_history`:
```rust
pub window_title: String,
```

- [ ] **Step 2: Initialize in new()**

In `IntervalSnapshot::new()` (line 43), after `key_history`:
```rust
window_title: String::new(),
```

- [ ] **Step 3: Verify compilation**

Run: `cd C:\Users\CT\Desktop\Tracker\src-tauri && cargo check`
Expected: Clean compilation

---

### Task 5: Add contextual mismatch check in compute_suspicion()

**Files:**
- Modify: `src-tauri/src/app_tracker.rs`

- [ ] **Step 1: Add mismatch detection**

In `compute_suspicion()` (line 69), after the keyboard_cheat calculation (line 90) and before the existing match on (mouse_cheat, keyboard_cheat):

```rust
let entertainment_keywords = [
    "youtube", "netflix", "spotify", "facebook", "twitter",
    "x.com", "instagram", "tiktok", "discord", "twitch",
    "reddit", "game", "play", "steam",
];
let window_lower = self.window_title.to_lowercase();
let is_entertainment = entertainment_keywords.iter().any(|kw| window_lower.contains(kw));

let mismatch_penalty: i64 = if is_entertainment && self.keystroke_count > 5 { 30 } else { 0 };
```

- [ ] **Step 2: Update score calculation**

Replace the existing match on line 92 with:
```rust
self.suspicion_score = match (mouse_cheat, keyboard_cheat) {
    (true, true) => 100.min(100 + mismatch_penalty),
    (true, false) => 50.min(50 + mismatch_penalty),
    (false, true) => 40.min(40 + mismatch_penalty),
    (false, false) => mismatch_penalty,
};
self.is_suspicious = self.suspicion_score >= 50;
```

Actually, since `mismatch_penalty` is only 30 and only applies when `is_entertainment && keystroke_count > 5`, it won't push any value over 100 in the (true, true) case. So the `.min(100)` is redundant but safe. Simplify to:

```rust
self.suspicion_score = match (mouse_cheat, keyboard_cheat) {
    (true, true) => 100 + mismatch_penalty,
    (true, false) => 50 + mismatch_penalty,
    (false, true) => 40 + mismatch_penalty,
    (false, false) => mismatch_penalty,
};
let capped = self.suspicion_score.min(100);
self.suspicion_score = capped;
self.is_suspicious = capped >= 50;
```

- [ ] **Step 3: Verify compilation**

Run: `cd C:\Users\CT\Desktop\Tracker\src-tauri && cargo check`
Expected: Clean compilation (0 errors, 0 warnings)

---

### Verification

- [ ] Run `cd C:\Users\CT\Desktop\Tracker\src-tauri && cargo check` — clean
- [ ] Score logic: YouTube + mashing spacebar → mouse=0 + keyboard=40 + mismatch=30 = 70 (suspicious, >=50)
- [ ] Score logic: Legit IDE typing → mouse=0 + keyboard=0 (filtered) + mismatch=0 = 0 (not suspicious)
- [ ] Score logic: YouTube, idle → mouse=0 + keyboard=0 + mismatch=0 = 0 (not suspicious)
- [ ] Score logic: Same-pixel click on YouTube video → mouse=50 + keyboard=0 + mismatch=30 = 80 (suspicious)
