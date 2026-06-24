const ZKLib = require('zklib-js');
const axios = require('axios');
const cron = require('node-cron');
const fs = require('fs');
const path = require('path');

/**
 * Format a Date object as a local datetime string: "YYYY-MM-DD HH:MM:SS"
 * This avoids the UTC ISO serialization bug where JS Date.toJSON()
 * outputs UTC time, making all Bangladesh times appear 6 hours earlier.
 */
function formatLocalDateTime(date) {
    const d = new Date(date);
    const year  = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day   = String(d.getDate()).padStart(2, '0');
    const hh    = String(d.getHours()).padStart(2, '0');
    const mm    = String(d.getMinutes()).padStart(2, '0');
    const ss    = String(d.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day} ${hh}:${mm}:${ss}`;
}

// Configuration
const config = {
    DEVICE_IP: '192.168.0.169',
    DEVICE_PORT: 4370,
    API_URL: 'http://localhost/tic_crm/api/biometric_attendance/sync',
    API_TOKEN: 'zkteco_sync_token_123',
    SYNC_INTERVAL: '*/30 * * * * *', // Every 30 seconds
    STATE_FILE: path.join(__dirname, 'sync_state.json')
};

function getLastSyncTime() {
    try {
        if (fs.existsSync(config.STATE_FILE)) {
            const data = fs.readFileSync(config.STATE_FILE, 'utf8');
            return new Date(JSON.parse(data).lastSyncTime);
        }
    } catch (err) {
        console.error("Could not read state file, defaulting to Epoch.");
    }
    return new Date(0);
}

function updateLastSyncTime(time) {
    fs.writeFileSync(config.STATE_FILE, JSON.stringify({ lastSyncTime: time.toISOString() }));
}

let syncing = false;

async function syncLogs() {
    if (syncing) {
        console.log("Sync already in progress, skipping...");
        return;
    }
    syncing = true;
    let zk = new ZKLib(config.DEVICE_IP, config.DEVICE_PORT, 10000, 4000);
    
    try {
        console.log(`[${new Date().toLocaleString()}] Connecting to device ${config.DEVICE_IP}...`);
        
        await zk.createSocket();
        console.log("Connected to device.");

        const logs = await zk.getAttendances();
        
        if (logs && logs.data && logs.data.length > 0) {
            const lastSyncTime = getLastSyncTime();
            let maxLogTime = lastSyncTime;

            const newLogs = logs.data.filter(log => {
                const logTime = new Date(log.recordTime);
                if (logTime > lastSyncTime) {
                    if (logTime > maxLogTime) maxLogTime = logTime;
                    return true;
                }
                return false;
            });
            
            if (newLogs.length > 0) {
                console.log(`Fetched ${newLogs.length} new logs since last state. Sending to ERP...`);

                // Convert recordTime to local datetime string to avoid UTC serialization bug.
                // Without this, JS Date objects stringify as UTC ISO (e.g. "03:30Z" for 09:30 local),
                // causing PHP to store UTC times and all Bangladesh times to show as AM.
                const logsToSend = newLogs.map(log => ({
                    ...log,
                    recordTime: formatLocalDateTime(log.recordTime)
                }));

                try {
                    const response = await axios.post(config.API_URL, {
                        logs: logsToSend
                    }, {
                        headers: {
                            'X-API-TOKEN': config.API_TOKEN,
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    console.log("ERP Response:", response.data);
                    
                    if (response.data.status === 'success') {
                        updateLastSyncTime(maxLogTime);
                    }
                } catch (apiError) {
                    console.error("API Error:", apiError.response ? apiError.response.data : apiError.message);
                }
            } else {
                console.log("No new logs found on device since last sync pointer.");
            }
        } else {
            console.log("No logs found on device.");
        }

        await zk.disconnect();
        console.log("Disconnected from device.");
        
    } catch (error) {
        console.error("Error during sync:", error.message);
        if (zk) {
            try { await zk.disconnect(); } catch (e) {}
        }
    } finally {
        syncing = false;
    }
}

// Schedule the task
console.log(`Starting Event-Driven ZKTeco Sync Service...`);
console.log(`Device: ${config.DEVICE_IP}:${config.DEVICE_PORT}`);
console.log(`API: ${config.API_URL}`);
console.log(`Interval: ${config.SYNC_INTERVAL}`);

cron.schedule(config.SYNC_INTERVAL, () => {
    syncLogs();
});

// Run once on start
syncLogs();
