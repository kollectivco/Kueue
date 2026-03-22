<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title><?php _e( 'Kueue Ticket Scanner', 'kueue-events-core' ); ?></title>
    <!-- HTML5 QRCode Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <!-- Dexie JS for easy IndexedDB management -->
    <script src="https://unpkg.com/dexie/dist/dexie.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #121212; color: #fff; margin: 0; padding: 0; padding-bottom: 20px; }
        .container { max-width: 600px; margin: 0 auto; padding: 15px; }
        .header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #333; }
        .header h2 { font-size: 1.1em; margin: 0; }
        
        .status-badge { font-size: 0.7em; padding: 2px 8px; border-radius: 10px; font-weight: bold; text-transform: uppercase; }
        .status-online { background: #4caf50; color: #fff; }
        .status-offline { background: #f44336; color: #fff; }
        .sync-counter { font-size: 0.75em; color: #ffeb3b; margin-left: 5px; }

        .scanner-section { background: #000; border-radius: 12px; overflow: hidden; margin-top: 10px; position: relative; }
        #reader { width: 100% !important; border: none !important; }

        .settings-bar { display: flex; flex-wrap: wrap; gap: 8px; padding: 10px 0; }
        .setting-item { flex: 1; min-width: 140px; }
        .setting-item label { display: block; font-size: 0.7em; color: #888; margin-bottom: 4px; }
        .setting-item select { background: #222; color: #fff; border: 1px solid #444; padding: 8px; width: 100%; border-radius: 6px; font-size: 0.9em; }

        .result-section { margin-top: 15px; border-radius: 12px; padding: 15px; text-align: center; display: none; }
        .result-valid { background: #1b5e20; border: 2px solid #4caf50; }
        .result-invalid { background: #b71c1c; border: 2px solid #f44336; }
        .result-warning { background: #e65100; border: 2px solid #ff9800; }
        .result-offline { background: #455a64; border: 2px solid #607d8b; }

        .result-meta { text-align: left; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px; font-size: 0.85em; }
        .result-meta div { margin-bottom: 4px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 3px; }
        .result-meta .label { color: rgba(255,255,255,0.6); display: inline-block; width: 80px; }

        .controls { margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        button { padding: 12px; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; font-size: 0.9em; }
        #start-btn { background: #4caf50; color: #fff; grid-column: span 2; }
        #stop-btn { background: #f44336; color: #fff; grid-column: span 2; display: none; }
        #prep-offline-btn { background: #673ab7; color: #fff; }
        #sync-btn { background: #ff9800; color: #fff; }

        .manual-input { margin-top: 15px; display: flex; gap: 8px; }
        .manual-input input { flex: 1; background: #222; color: #fff; border: 1px solid #444; padding: 10px; border-radius: 8px; }
        .manual-input button { background: #0073aa; color: #fff; padding: 0 15px; }

        .overlay-msg { position: absolute; top: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); text-align: center; padding: 5px; font-size: 0.8em; z-index: 10; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h2>Kueue Scanner</h2>
                <div id="connection-status">
                    <span class="status-badge status-online" id="status-text">Online</span>
                    <span class="sync-counter" id="pending-sync-count"></span>
                </div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=kq-events-dashboard'); ?>" style="color: #0073aa; text-decoration: none; font-size: 0.85em;">Exit</a>
        </div>

        <div class="settings-bar">
            <div class="setting-item">
                <label>Mode</label>
                <select id="scan-mode">
                    <option value="auto">Auto Toggle</option>
                    <option value="checkin">Entry Only</option>
                    <option value="checkout">Exit Only</option>
                </select>
            </div>
            <div class="setting-item">
                <label>Filter Event</label>
                <select id="event-select">
                    <?php foreach ($events as $e) : ?>
                        <option value="<?php echo (int) $e->ID; ?>"><?php echo esc_html($e->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="scanner-section">
            <div id="reader"></div>
            <div id="scanner-overlay" class="overlay-msg" style="display:none">Processing...</div>
        </div>
        
        <button id="start-btn">ENABLE CAMERA</button>
        <button id="stop-btn">STOP SCANNER</button>

        <div class="controls">
            <button id="prep-offline-btn" title="Cache current event tickets for offline validation">PREP OFFLINE</button>
            <button id="sync-btn" title="Manually push queued scans">SYNC NOW</button>
        </div>

        <div class="manual-input">
            <input type="text" id="manual-token" placeholder="Manual token input...">
            <button id="manual-btn">OK</button>
        </div>

        <div id="result-box" class="result-section">
            <div id="result-status-text" style="font-size: 1.3em; font-weight: bold;"></div>
            <div id="result-message" style="margin-top: 5px; font-size: 0.9em;"></div>
            
            <div class="result-meta">
                <div><span class="label">Attendee:</span> <span id="res-attendee"></span></div>
                <div><span class="label">Event:</span> <span id="res-event"></span></div>
                <div><span class="label">Type:</span> <span id="res-type"></span></div>
                <div><span class="label">Ticket #:</span> <span id="res-number"></span></div>
            </div>
        </div>
    </div>

    <script>
        // --- 1. Database Setup (Dexie) ---
        const db = new Dexie("KueueScannerDB");
        db.version(1).stores({
            tickets: 'token, event_id, status, attendee_name',
            queue: '++id, token, event_id, mode, offline_at'
        });

        const SCANNER_TOKEN = '<?php echo $scanner_token; ?>';
        const DEVICE_ID = '<?php echo $device_id; ?>';
        const REST_URL = '<?php echo esc_url_raw( rest_url("kq/v1") ); ?>';
        const NONCE = '<?php echo wp_create_nonce("wp_rest"); ?>';

        const html5QrCode = new Html5Qrcode("reader");
        let isProcessing = false;
        let lastScanResult = null;

        // --- 2. Connectivity Management ---
        function updateOnlineStatus() {
            const statusText = document.getElementById('status-text');
            if (navigator.onLine) {
                statusText.innerText = "Online";
                statusText.className = "status-badge status-online";
                autoSync();
            } else {
                statusText.innerText = "Offline";
                statusText.className = "status-badge status-offline";
            }
        }
        window.addEventListener('online',  updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();

        // --- 3. Queue Management ---
        async function updateQueueCounter() {
            const count = await db.queue.count();
            const counter = document.getElementById('pending-sync-count');
            counter.innerText = count > 0 ? `(${count} pending)` : '';
        }
        updateQueueCounter();

        // --- 4. Scanning Logic ---
        const onScanSuccess = (decodedText) => {
            if (isProcessing) return;
            if (decodedText === lastScanResult) return; 
            lastScanResult = decodedText;
            processScan(decodedText);
        }

        async function processScan(token) {
            isProcessing = true;
            document.getElementById('scanner-overlay').innerText = "Validating...";
            document.getElementById('scanner-overlay').style.display = "block";

            const eventId = document.getElementById('event-select').value;
            const mode = document.getElementById('scan-mode').value;

            if (navigator.onLine) {
                await validateOnline(token, eventId, mode);
            } else {
                await validateOffline(token, eventId, mode);
            }

            setTimeout(() => { 
                isProcessing = false; 
                lastScanResult = null;
                document.getElementById('scanner-overlay').style.display = "none";
            }, 1500);
        }

        async function validateOnline(token, eventId, mode) {
            try {
                const response = await fetch(`${REST_URL}/validate-ticket`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': NONCE,
                        'X-Scanner-Token': SCANNER_TOKEN,
                        'X-Device-ID': DEVICE_ID
                    },
                    body: JSON.stringify({ token, event_id: eventId, mode })
                });

                if (response.status === 401) {
                    alert("Scanner Session Expired. Please reload page.");
                    location.reload();
                    return;
                }

                const data = await response.json();
                showResult(data);
                vibrate(data.status === 'valid');
            } catch (err) {
                // Network error? Switch to offline if possible
                validateOffline(token, eventId, mode);
            }
        }

        async function validateOffline(token, eventId, mode) {
            const cleanToken = token.startsWith('kq:t:') ? token.substring(5) : token;
            const cachedTicket = await db.tickets.get(cleanToken);

            if (!cachedTicket) {
                showResult({ 
                    status: 'invalid', 
                    message: 'Offline: Ticket not in local cache. Connect to sync.' 
                });
                vibrate(false);
                return;
            }

            // Simple offline status check
            if (cachedTicket.status === 'checked_in' && mode === 'checkin') {
                showResult({ 
                    ...cachedTicket, 
                    status: 'already_used', 
                    message: 'Offline Alert: Already checked in.' 
                });
                vibrate(false);
                return;
            }

            // Local Duplicate Prevention (Checks queue)
            const alreadyInQueue = await db.queue.where('token').equals(cleanToken).first();
            if (alreadyInQueue && mode === 'checkin') {
                showResult({ 
                    ...cachedTicket, 
                    status: 'already_used', 
                    message: 'Offline Alert: Duplicate scan in queue.' 
                });
                vibrate(false);
                return;
            }

            // Queue the scan
            await db.queue.add({
                token: cleanToken,
                event_id: eventId,
                mode: mode,
                offline_at: new Date().toISOString()
            });

            showResult({
                ...cachedTicket,
                status: 'offline_queued',
                message: 'Offline Entry Queued. Sync required.'
            });
            updateQueueCounter();
            vibrate(true);
        }

        // --- 5. Batch Sync ---
        async function autoSync() {
            const count = await db.queue.count();
            if (count > 0) syncNow();
        }

        async function syncNow() {
            const scans = await db.queue.toArray();
            if (scans.length === 0) return;

            document.getElementById('sync-btn').innerText = "Syncing...";
            
            try {
                const response = await fetch(`${REST_URL}/sync-scans`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': NONCE,
                        'X-Scanner-Token': SCANNER_TOKEN,
                        'X-Device-ID': DEVICE_ID
                    },
                    body: JSON.stringify({ scans })
                });

                if (response.ok) {
                    await db.queue.clear();
                    updateQueueCounter();
                    document.getElementById('last-scan-info').innerText = `Synced ${scans.length} scans.`;
                }
            } catch (err) {
                console.error("Sync failed", err);
            } finally {
                document.getElementById('sync-btn').innerText = "SYNC NOW";
            }
        }

        // --- 6. Prep Offline Data ---
        document.getElementById('prep-offline-btn').addEventListener('click', async () => {
            const eventId = document.getElementById('event-select').value;
            if (!eventId) {
                alert("Please select an event first.");
                return;
            }

            const btn = document.getElementById('prep-offline-btn');
            const originalText = btn.innerText;
            btn.innerText = "Downloading...";
            btn.disabled = true;
            
            try {
                const response = await fetch(`${REST_URL}/export-tickets?event_id=${eventId}`, {
                    headers: {
                        'X-WP-Nonce': NONCE,
                        'X-Scanner-Token': SCANNER_TOKEN,
                        'X-Device-ID': DEVICE_ID
                    }
                });

                if (!response.ok) throw new Error("Server error " + response.status);

                const tickets = await response.json();
                
                // Save to IndexedDB
                await db.tickets.bulkPut(tickets);
                
                alert(`Successfully cached ${tickets.length} tickets for offline use.`);
                document.getElementById('last-scan-info').innerText = `Cache updated: ${tickets.length} tickets.`;
            } catch(e) {
                alert("Cache failed: " + e.message);
            } finally {
                 btn.innerText = originalText;
                 btn.disabled = false;
            }
        });

        function showResult(data) {
            const box = document.getElementById('result-box');
            box.style.display = 'block';
            box.className = 'result-section';

            let statusClass = 'result-invalid';
            if (data.status === 'valid') statusClass = 'result-valid';
            else if (data.status === 'offline_queued') statusClass = 'result-offline';
            else if (data.status === 'already_used' || data.status === 'wrong_event') statusClass = 'result-warning';
            
            box.classList.add(statusClass);
            document.getElementById('result-status-text').innerText = data.status.replace('_', ' ').toUpperCase();
            document.getElementById('result-message').innerText = data.message;
            
            if (data.attendee_name) {
                document.getElementById('res-attendee').innerText = data.attendee_name;
                document.getElementById('res-event').innerText = data.event_name || '-';
                document.getElementById('res-type').innerText = data.ticket_type || '-';
                document.getElementById('res-number').innerText = data.ticket_number || '-';
                document.querySelector('.result-meta').style.display = 'block';
            } else {
                document.querySelector('.result-meta').style.display = 'none';
            }
        }

        function vibrate(success) {
            if (!navigator.vibrate) return;
            if (success) navigator.vibrate(200);
            else navigator.vibrate([100, 50, 100]);
        }

        // UI Event Listeners
        document.getElementById('start-btn').addEventListener('click', () => {
             html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, onScanSuccess)
                .then(() => {
                    document.getElementById('start-btn').style.display = 'none';
                    document.getElementById('stop-btn').style.display = 'block';
                }).catch(err => alert("Camera Error: " + err));
        });
        document.getElementById('stop-btn').addEventListener('click', () => {
            html5QrCode.stop().then(() => {
                document.getElementById('start-btn').style.display = 'block';
                document.getElementById('stop-btn').style.display = 'none';
            });
        });
        document.getElementById('manual-btn').addEventListener('click', () => {
            const val = document.getElementById('manual-token').value;
            if (val) processScan(val);
        });
        document.getElementById('sync-btn').addEventListener('click', syncNow);

    </script>
</body>
</html>
