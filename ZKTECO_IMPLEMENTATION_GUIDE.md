# ZKTeco F28 Biometric Attendance Integration - Implementation Guide

This document outlines the complete implementation details for the ZKTeco F28 biometric device integration with ZiscoERP.

## 🏗️ System Architecture

```text
[ZKTeco F28 Device] (IP: 192.168.1.201)
       ↓
[Node.js Middleware] (Fetches logs via node-zklib every 2 mins)
       ↓
[ZiscoERP API Endpoint] (/api/biometric_attendance/sync)
       ↓
[MySQL Database] (Logs & Attendance processing)
```

---

## 🗄️ 1. Database Schema Changes

Two new tables were added to manage raw logs and employee mapping:

1. **`biometric_attendance_logs`**: Stores raw, unfiltered logs from the device. Ensures no duplicate syncs via a `UNIQUE KEY (device_user_id, timestamp)`.
2. **`biometric_employee_mapping`**: Maps the `device_user_id` (from the biometric scanner) to the `user_id` in ZiscoERP.

---

## 🌐 2. API Endpoint

**File:** `application/controllers/api/Biometric_attendance.php`

- **Endpoint:** `POST /api/biometric_attendance/sync`
- **Security:** Requires header `X-API-TOKEN: zkteco_sync_token_123`
- **Logic:** 
  1. Receives JSON payload with biometric logs.
  2. Saves them to `biometric_attendance_logs`.
  3. Checks `biometric_employee_mapping`. If a map exists, it processes the log.
  4. Automatically handles Clock In / Clock Out by updating ZiscoERP's native `tbl_attendance` and `tbl_clock` tables.

---

## 🖥️ 3. Admin UI (Biometric Mapping)

A new interface was added to the ERP to allow administrators to map users.

- **Controller Updates:** Added `biometric_mapping`, `save_biometric_mapping`, and `delete_biometric_mapping` methods to `application/controllers/admin/Attendance.php`.
- **View:** Created `application/views/admin/attendance/biometric_mapping.php`.
- **Menu:** Added "Biometric Mapping" under the main Attendance menu in `tbl_menu`.
- **Language:** Added `$lang['biometric_mapping']` to `main_lang.php`.

---

## ⚙️ 4. Node.js Middleware (Sync Service)

A dedicated Node.js service was created to act as a bridge between the device (TCP) and the ERP (HTTP).

**Directory:** `biometric-sync/`
**Files:** 
- `package.json` (Dependencies: `node-zklib`, `axios`, `node-cron`)
- `sync.js` (The main script)

### Configuration (`sync.js`)
If your device IP or ERP URL changes, update these variables at the top of `biometric-sync/sync.js`:
```javascript
const config = {
    DEVICE_IP: '192.168.1.201',
    DEVICE_PORT: 4370,
    API_URL: 'http://localhost/tic_crm/api/biometric_attendance/sync', // Update to production URL
    API_TOKEN: 'zkteco_sync_token_123',
    SYNC_INTERVAL: '*/2 * * * *' // Runs every 2 minutes
};
```

---

## 🚀 Deployment & Next Steps

Follow these exact steps to get the system running on your live server:

### Step 1: Device Network Setup
1. Ensure the ZKTeco F28 is connected to the same local network as the server running the Node.js middleware.
2. Disable DHCP on the device and set a static IP (e.g., `192.168.1.201`).
3. Set the Comm Key to `0`.

### Step 2: Install Middleware Dependencies
Open your terminal, navigate to the sync folder, and install the required Node.js packages:
```bash
cd path/to/tic_crm/biometric-sync
npm install
```

### Step 3: Map Employees in ERP
1. Log in to ZiscoERP as an Administrator.
2. Go to **Attendance > Biometric Mapping**.
3. Add mappings by entering the Device User ID (from the scanner) and selecting the corresponding employee.

### Step 4: Start the Sync Service (Production)
For a production environment, you should run the middleware in the background using PM2 so it survives server restarts.

1. Install PM2 globally:
```bash
npm install -g pm2
```

2. Start the service:
```bash
pm2 start sync.js --name zkteco-sync
```

3. Save the PM2 process list so it restarts on boot:
```bash
pm2 save
pm2 startup
```

### Step 5: Testing
1. Have an employee (who is mapped in Step 3) punch in on the device.
2. Check the Node.js console logs (`pm2 logs zkteco-sync`). You should see it fetch the log and send it to the ERP.
3. Check **Attendance > Time History** in ZiscoERP. The punch should appear as a standard Clock In/Out.






