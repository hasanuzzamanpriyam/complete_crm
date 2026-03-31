const ZKLib = require('zklib-js');
const axios = require('axios');
const cron = require('node-cron');

// Configuration
const config = {
    DEVICE_IP: '192.168.0.169',
    DEVICE_PORT: 4370,
    API_URL: 'http://localhost/tic_crm/api/biometric_attendance/sync',
    API_TOKEN: 'zkteco_sync_token_123',
    SYNC_INTERVAL: '*/30 * * * * *' // Every 30 seconds
};

async function syncLogs() {
    let zk = new ZKLib(config.DEVICE_IP, config.DEVICE_PORT, 10000, 4000);
    
    try {
        console.log(`[${new Date().toLocaleString()}] Connecting to device ${config.DEVICE_IP}...`);
        
        // Create socket and connect
        await zk.createSocket();
        console.log("Connected to device.");

        // Get attendance logs
        const logs = await zk.getAttendances();
        
        if (logs && logs.data && logs.data.length > 0) {
            console.log(`Fetched ${logs.data.length} logs. Sending to ERP...`);
            
            try {
                const response = await axios.post(config.API_URL, {
                    logs: logs.data
                }, {
                    headers: {
                        'X-API-TOKEN': config.API_TOKEN,
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log("ERP Response:", response.data);
            } catch (apiError) {
                console.error("API Error:", apiError.response ? apiError.response.data : apiError.message);
            }
        } else {
            console.log("No new logs found on device.");
        }

        // Optional: clear logs from device if needed (be careful!)
        // await zk.clearAttendanceLog(); 

        await zk.disconnect();
        console.log("Disconnected from device.");
        
    } catch (error) {
        console.error("Error during sync:", error.message);
        if (zk) {
            try { await zk.disconnect(); } catch (e) {}
        }
    }
}

// Schedule the task
console.log(`Starting ZKTeco Sync Service...`);
console.log(`Device: ${config.DEVICE_IP}:${config.DEVICE_PORT}`);
console.log(`API: ${config.API_URL}`);
console.log(`Interval: ${config.SYNC_INTERVAL}`);

cron.schedule(config.SYNC_INTERVAL, () => {
    syncLogs();
});

// Run once on start
syncLogs();
