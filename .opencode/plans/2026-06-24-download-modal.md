# Download Modal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a "Download Installer (Manual)" button to the Update Available dialog that opens the installer URL in the user's default browser.

**Architecture:** CI3 serves the installer via a new download route that reads `latest.json` and redirects to the platform URL. Frontend constructs the URL from `baseUrl` and calls `open()` via `@tauri-apps/plugin-shell`. No Rust changes needed.

**Tech Stack:** PHP (CI3), TypeScript (React), @tauri-apps/plugin-shell

---

### Task 1: CI3 — Add download route and serve binaries

**Files:**
- Modify: `application/controllers/api/Updates.php`
- Modify: `application/config/routes.php`

- [ ] **Step 1: Add download method to Updates.php**

Add new method after `latest()`:

```php
public function download() {
    $manifest_path = FCPATH . 'downloads/latest.json';
    if (!file_exists($manifest_path)) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode(['success' => false, 'message' => 'No release available']))
            ->_display();
        exit;
    }
    $manifest = json_decode(file_get_contents($manifest_path), true);
    $platform_key = 'windows-x86_64';
    $url = $manifest['platforms'][$platform_key]['url'] ?? '';
    if (empty($url)) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode(['success' => false, 'message' => 'No download URL for this platform']))
            ->_display();
        exit;
    }
    redirect($url);
}
```

- [ ] **Step 2: Add route**

In `application/config/routes.php`, add:
```php
$route['api/updates/timesync/download'] = 'api/updates/download';
```

- [ ] **Step 3: Verify**

Run: `php -l application/controllers/api/Updates.php`
Expected: "No syntax errors detected"

Run: `php -l application/config/routes.php`
Expected: "No syntax errors detected"

---

### Task 2: React — Add shell plugin and Download Installer button

**Files:**
- Modify: `src-tauri/Cargo.toml`
- Modify: `src-tauri/capabilities/default.json`
- Modify: `src-tauri/src/lib.rs` (plugin registration)
- Modify: `package.json`
- Modify: `src/features/updater/components/UpdateSettings.tsx`

- [ ] **Step 1: Add tauri-plugin-shell to Cargo.toml**

Add to `src-tauri/Cargo.toml` under `[dependencies]`:
```toml
tauri-plugin-shell = "2"
```

- [ ] **Step 2: Register shell plugin in lib.rs**

In `src-tauri/src/lib.rs`, find the existing plugin registrations in the `run()` function and add:
```rust
.plugin(tauri_plugin_shell::init())
```

- [ ] **Step 3: Add shell capability**

In `src-tauri/capabilities/default.json`, add to the `permissions` array:
```json
"shell:default",
"shell:allow-open"
```

- [ ] **Step 4: Add @tauri-apps/plugin-shell npm package**

Run:
```bash
cd C:\Users\CT\Desktop\Tracker
npm install @tauri-apps/plugin-shell@^2.0.0
```

- [ ] **Step 5: Add download button to UpdateSettings.tsx**

At the top, add the import:
```tsx
import { open } from "@tauri-apps/plugin-shell";
import { secureStorage } from "@/services/storage/secure";
```

Inside the component, add the handler before the return:
```tsx
const handleDownloadInstaller = async () => {
  try {
    const baseUrl = await secureStorage.get("erp_base_url");
    if (typeof baseUrl !== "string" || !baseUrl) {
      toast.error("ERP URL not configured");
      return;
    }
    const url = `${baseUrl.replace(/\/+$/, "")}/api/updates/timesync/download`;
    await open(url);
  } catch (err) {
    toast.error("Failed to open download", {
      description: err instanceof Error ? err.message : "Could not open download link.",
    });
  }
};
```

In the AlertDialogFooter, add a third button between Later and Download & Install:
```tsx
<AlertDialogFooter>
  <AlertDialogCancel>Later</AlertDialogCancel>
  <Button variant="outline" onClick={handleDownloadInstaller}>
    <Download className="mr-2 h-4 w-4" />
    Download Installer
  </Button>
  <AlertDialogAction onClick={handleInstall}>
    <Download className="mr-2 h-4 w-4" />
    Download & Install
  </AlertDialogAction>
</AlertDialogFooter>
```

Add the `Button` import if not already present (it is, from the check button above line 63).

- [ ] **Step 6: Verify**

Run:
```bash
cd C:\Users\CT\Desktop\Tracker
npx tsc --noEmit
```
Expected: No errors

Run:
```bash
cd C:\Users\CT\Desktop\Tracker\src-tauri
cargo check
```
Expected: Clean compilation

---

### Verification

- [ ] Run `cd C:\Users\CT\Desktop\Tracker && npx tsc --noEmit` — passes
- [ ] Run `cd C:\Users\CT\Desktop\Tracker\src-tauri && cargo check` — passes
- [ ] Run `php -l application/controllers/api/Updates.php` — passes
- [ ] Run `php -l application/config/routes.php` — passes
- [ ] Manual: Check Settings → Updates → "Check for Updates" → dialog shows "Download Installer" button
- [ ] Manual: Clicking "Download Installer" opens the installer URL in default browser
