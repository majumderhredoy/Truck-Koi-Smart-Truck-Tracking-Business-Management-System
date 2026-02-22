<?php
require_once 'init.php';
requireLogin();

$page_title = 'লাইভ ট্র্যাকিং — ' . SITE_NAME;
$page_title_short = 'লাইভ ট্র্যাকিং';

$user_id = $_SESSION['user_id'];
$trucks_sql = "SELECT id, name, plate_number FROM trucks WHERE user_id = $user_id ORDER BY created_at DESC";
$trucks_result = $conn->query($trucks_sql);

// Check if a specific truck is requested via URL
$selected_truck_id = isset($_GET['truck']) ? (int)$_GET['truck'] : null;
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="pro-layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <style>
        .story-item {
            position: relative;
            padding-left: 24px;
            padding-bottom: 20px;
            border-left: 2px solid #E0E0E0;
        }
        .story-item:last-child { border-left-color: transparent; }
        .story-dot {
            position: absolute;
            left: -7px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #DDD;
            border: 2px solid #FFF;
        }
        .story-active { background: var(--primary-red); box-shadow: 0 0 0 4px rgba(220, 20, 60, 0.1); }
        .story-time { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
        .story-content { font-size: 13px; font-weight: 500; }
        .story-loc { font-size: 11px; color: var(--text-muted); }

        .tracking-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            height: calc(100vh - var(--header-height));
        }
        
        #map {
            height: 100%;
            width: 100%;
            z-index: 10;
        }

        .tracking-sidebar {
            background: #FFF;
            border-right: 1px solid #EEE;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .truck-select-box {
            background: #F9F9F9;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #EEE;
        }

        .truck-detail-card {
            background: #FFF;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #EEE;
            box-shadow: var(--shadow-sm);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .info-label { color: var(--text-muted); }
        .info-value { font-weight: 600; color: var(--text-main); }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        @media (max-width: 992px) {
            .tracking-layout { grid-template-columns: 1fr; }
            .tracking-sidebar { height: auto; border-right: none; border-bottom: 1px solid #EEE; }
            #map { height: 500px; }
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .toast {
            background: #FFF;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-red);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            animation: slideIn 0.3s ease-out;
            min-width: 300px;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast.success { border-left-color: #10B981; }
        .toast.error { border-left-color: #EF4444; }

        /* Modal specific */
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #475569; }
        .form-input { 
            width: 100%; padding: 12px 16px; border: 1.5px solid #E2E8F0; border-radius: 10px; 
            font-family: inherit; transition: all 0.2s;
        }
        .form-input:focus { border-color: var(--primary-red); outline: none; box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1); }
    </style>
</head>
<body>
    <div class="pro-layout">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="pro-content">
            <!-- Header -->
            <?php include 'includes/header_pro.php'; ?>

            <div class="tracking-layout">
                <!-- Tracking Left Sidebar -->
                <div class="tracking-sidebar">
                    <div class="truck-select-box">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <label style="font-weight: 700;">গাড়ি বাছাই করুন</label>
                            <label style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                <input type="checkbox" id="autoRefresh" checked> লাইভ আপডেট
                            </label>
                        </div>
                        <select id="truckSelector" onchange="loadTruckOnMap(this.value)" style="width: 100%; padding: 12px; border: 1px solid #DDD; border-radius: 8px; font-family: inherit;">
                            <option value="">সকল গাড়ি</option>
                            <?php while($row = $trucks_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo $selected_truck_id == $row['id'] ? 'selected' : ''; ?>>
                                    <?php echo $row['name']; ?> (<?php echo $row['plate_number']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div id="truckDetailsContainer" style="display: none;">
                        <h3 style="font-size: 18px; margin-bottom: 16px;">গাড়ির বিস্তারিত</h3>
                        <div class="truck-detail-card">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                                <div id="statusDot" class="status-dot"></div>
                                <h4 id="detailTruckName" style="font-size: 16px; margin: 0;">-</h4>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">প্লেট নম্বর</span>
                                <span class="info-value" id="detailPlate">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">ব্র্যান্ড</span>
                                <span class="info-value" id="detailBrand">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">চালক</span>
                                <span class="info-value" id="detailDriver">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">গতি</span>
                                <span class="info-value" id="detailSpeed">0 কিমি/ঘণ্টা</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">ফুয়েল</span>
                                <span class="info-value" id="detailFuel">0%</span>
                            </div>
                            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #F5F5F5;">
                                <div class="info-label" style="margin-bottom: 4px;">বর্তমান অবস্থান</div>
                                <div class="info-value" id="detailLocation" style="font-size: 13px;">অজানা</div>
                            </div>

                            <!-- Journey Controls -->
                            <div id="journeyControls" style="margin-top: 20px; display: grid; gap: 10px;">
                                <button id="btnStartJourney" onclick="startJourney()" class="btn-pro btn-pro-primary" style="width: 100%; justify-content: center; background: #4CAF50;">
                                    <i class="fas fa-play"></i> যাত্রা শুরু করুন
                                </button>
                                <button id="btnEndJourney" onclick="endJourney()" class="btn-pro" style="width: 100%; justify-content: center; background: #F44336; color: white; display: none;">
                                    <i class="fas fa-stop"></i> যাত্রা শেষ করুন
                                </button>
                                <div id="activeJourneyBadge" style="display: none; text-align: center; background: #E8F5E9; color: #2E7D32; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">
                                    <i class="fas fa-circle-notch fa-spin"></i> যাত্রা চলমান...
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="noTruckMsg" style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                        <i class="fas fa-truck" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.2;"></i>
                        ম্যাপে দেখার জন্য একটি গাড়ি সিলেক্ট করুন
                    </div>

                    <!-- Root Story Section -->
                    <div id="rootStoryContainer" style="display: none; border-top: 2px solid #F5F5F5; padding-top: 24px;">
                        <h3 style="font-size: 18px; margin-bottom: 16px;">রুট স্টোরি (Root Story)</h3>
                        <div id="storyTimeline" style="display: flex; flex-direction: column; gap: 0;">
                            <!-- JS Loaded Updates -->
                        </div>
                    </div>
                </div>

                <!-- Map Container -->
                <div id="map"></div>
            </div>

            <!-- Toast Container -->
            <div id="toastContainer" class="toast-container"></div>

            <!-- Finance Input Modal (Driver) -->
            <div id="journeyFinanceModal" class="modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                <div class="modal-content pro-card" style="width: 95%; max-width: 450px; padding: 40px; background: white; border-radius: 20px;">
                    <div style="text-align: center; margin-bottom: 24px;">
                        <div style="width: 64px; height: 64px; background: #ECFDF5; color: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 8px;">যাত্রা শেষ হয়েছে!</h3>
                        <p style="color: var(--text-muted); font-size: 14px;">ট্রিপের হিসাব টি দ্রুত যোগ করুন</p>
                    </div>
                    
                    <form id="journeyFinanceForm" onsubmit="saveJourneyFinance(event)">
                        <input type="hidden" id="postJourneyId">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label">ভাড়া (Revenue)</label>
                                <input type="number" id="jf_rent" class="form-input" placeholder="৳ ০" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">জ্বালানি খরচ (Fuel)</label>
                                <input type="number" id="jf_fuel" class="form-input" placeholder="৳ ০" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                            <div class="form-group">
                                <label class="form-label">ড্রাইভার বিল</label>
                                <input type="number" id="jf_driver" class="form-input" placeholder="৳ ০" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">হেল্পার বিল</label>
                                <input type="number" id="jf_helper" class="form-input" placeholder="৳ ০" required>
                            </div>
                        </div>

                        <button type="submit" id="btnSaveFinance" class="btn-pro btn-pro-primary" style="width: 100%; justify-content: center; padding: 16px; font-weight: 700;">
                            <i class="fas fa-save"></i> হিসাব সংরক্ষণ করুন
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        let map;
        let markers = {};
        
        // Initialize Map
        function initMap() {
            map = L.map('map').setView([23.8103, 90.4125], 13); // Dhaka Center
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Load all trucks initially if required, or just wait for selection
            loadAllMarkers();
            
            // If truck ID passed in URL
            const urlTruckId = '<?php echo $selected_truck_id; ?>';
            if (urlTruckId) {
                setTimeout(() => loadTruckOnMap(urlTruckId), 500);
            }
        }

        async function loadAllMarkers() {
            const res = await fetch('api/truck_api.php');
            const result = await res.json();
            if (result.success) {
                result.data.forEach(truck => {
                    const marker = L.marker([truck.lat, truck.lng]).addTo(map);
                    marker.bindPopup(`<b>${truck.name}</b><br>${truck.plate_number}`);
                    markers[truck.id] = marker;
                });
            }
        }

        async function loadTruckOnMap(id) {
            if (!id) {
                document.getElementById('truckDetailsContainer').style.display = 'none';
                document.getElementById('noTruckMsg').style.display = 'block';
                return;
            }

            const res = await fetch(`api/truck_api.php?id=${id}`);
            const result = await res.json();
            
            if (result.success) {
                const truck = result.data;
                
                // Update Sidebar
                document.getElementById('truckDetailsContainer').style.display = 'block';
                document.getElementById('noTruckMsg').style.display = 'none';
                
                document.getElementById('detailTruckName').innerText = truck.name;
                document.getElementById('detailPlate').innerText = truck.plate_number;
                document.getElementById('detailBrand').innerText = truck.brand || '-';
                document.getElementById('detailDriver').innerText = truck.driver_name || '-';
                document.getElementById('detailSpeed').innerText = `${truck.speed} কিমি/ঘণ্টা`;
                document.getElementById('detailFuel').innerText = `${truck.fuel}%`;
                document.getElementById('detailLocation').innerText = truck.location || 'অজানা';
                
                const dot = document.getElementById('statusDot');
                dot.style.background = truck.status === 'running' ? '#4CAF50' : (truck.status === 'idle' ? '#FF9800' : '#F44336');

                // Move Map to Truck
                if (truck.lat && truck.lng) {
                    map.setView([truck.lat, truck.lng], 16);
                    if (markers[truck.id]) {
                        markers[truck.id].setLatLng([truck.lat, truck.lng]);
                        markers[truck.id].openPopup();
                    } else {
                        const marker = L.marker([truck.lat, truck.lng]).addTo(map);
                        marker.bindPopup(`<b>${truck.name}</b><br>${truck.plate_number}`);
                        markers[truck.id] = marker;
                    }
                }

                // Check active journey status
                checkActiveJourney(id);
                loadRootStory(id);
            }
        }

        async function loadRootStory(truckId) {
            const res = await fetch(`api/journey_api.php?action=get_history&truck_id=${truckId}`);
            const result = await res.json();
            const container = document.getElementById('rootStoryContainer');
            const timeline = document.getElementById('storyTimeline');

            if (result.success && result.data.length > 0) {
                container.style.display = 'block';
                // Get the most recent journey
                const latest = result.data[0];
                
                // Get path for this journey
                const pathRes = await fetch(`api/journey_api.php?action=get_path&journey_id=${latest.id}`);
                const pathResult = await pathRes.json();
                
                if (pathResult.success) {
                    // Show last 5 updates
                    const updates = pathResult.data.slice(-5).reverse();
                    timeline.innerHTML = updates.map((u, index) => `
                        <div class="story-item">
                            <div class="story-dot ${index === 0 ? 'story-active' : ''}"></div>
                            <div class="story-time">${new Date(u.created_at).toLocaleTimeString('bn-BD')}</div>
                            <div class="story-content">লোকেশন আপডেট</div>
                            <div class="story-loc">${u.lat.toFixed(4)}, ${u.lng.toFixed(4)}</div>
                        </div>
                    `).join('');
                }
            } else {
                container.style.display = 'none';
            }
        }

        let activeJourneyId = null;
        let updateInterval = null;

        async function checkActiveJourney(truckId) {
            const res = await fetch(`api/journey_api.php?action=check_active&truck_id=${truckId}`);
            const result = await res.json();
            
            const btnStart = document.getElementById('btnStartJourney');
            const btnEnd = document.getElementById('btnEndJourney');
            const badge = document.getElementById('activeJourneyBadge');

            if (result.success && result.active) {
                activeJourneyId = result.journey.id;
                btnStart.style.display = 'none';
                btnEnd.style.display = 'flex';
                badge.style.display = 'block';
                startUpdateLoop();
            } else {
                activeJourneyId = null;
                btnStart.style.display = 'flex';
                btnEnd.style.display = 'none';
                badge.style.display = 'none';
                stopUpdateLoop();
            }
        }

        async function startJourney() {
            const truckId = document.getElementById('truckSelector').value;
            if (!truckId) return;

            const btn = document.getElementById('btnStartJourney');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> শুরু হচ্ছে...';

            const res = await fetch('api/journey_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'start_journey',
                    truck_id: truckId,
                    start_location: document.getElementById('detailLocation').innerText
                })
            });

            const result = await res.json();
            btn.disabled = false;
            btn.innerHTML = originalHtml;

            if (result.success) {
                activeJourneyId = result.journey_id;
                showToast('যাত্রা শুরু হয়েছে!', 'success');
                checkActiveJourney(truckId);
            } else {
                showToast(result.message, 'error');
            }
        }

        async function endJourney() {
            const truckId = document.getElementById('truckSelector').value;
            if (!activeJourneyId || !truckId) return;

            const btn = document.getElementById('btnEndJourney');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> প্রসেস হচ্ছে...';

            const res = await fetch('api/journey_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'end_journey',
                    journey_id: activeJourneyId,
                    truck_id: truckId,
                    end_location: document.getElementById('detailLocation').innerText
                })
            });

            const result = await res.json();
            btn.disabled = false;
            btn.innerHTML = originalHtml;

            if (result.success) {
                showToast('যাত্রা সফলভাবে শেষ হয়েছে!', 'success');
                // Open Finance Modal
                document.getElementById('postJourneyId').value = activeJourneyId;
                document.getElementById('journeyFinanceModal').style.display = 'flex';
                
                checkActiveJourney(truckId);
            } else {
                showToast(result.message, 'error');
            }
        }

        async function saveJourneyFinance(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveFinance');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> সেভ হচ্ছে...';

            const data = {
                action: 'update_finance',
                journey_id: document.getElementById('postJourneyId').value,
                rent_amount: document.getElementById('jf_rent').value,
                fuel_cost: document.getElementById('jf_fuel').value,
                driver_bill: document.getElementById('jf_driver').value,
                helper_bill: document.getElementById('jf_helper').value
            };

            const res = await fetch('api/journey_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> হিসাব সংরক্ষণ করুন';

            if (result.success) {
                showToast('আয়-ব্যয় হিসাব যোগ হয়েছে', 'success');
                document.getElementById('journeyFinanceModal').style.display = 'none';
            } else {
                showToast(result.message, 'error');
            }
        }

        function showToast(msg, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle')}"></i>
                <span>${msg}</span>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function startUpdateLoop() {
            if (updateInterval) return;
            updateInterval = setInterval(simulateMovement, 10000); // Simulate every 10s
        }

        function stopUpdateLoop() {
            if (updateInterval) {
                clearInterval(updateInterval);
                updateInterval = null;
            }
        }

        async function simulateMovement() {
            if (!activeJourneyId) return;
            const truckId = document.getElementById('truckSelector').value;
            
            // Generate minor random offset for simulation
            const offsetLat = (Math.random() - 0.5) * 0.002;
            const offsetLng = (Math.random() - 0.5) * 0.002;
            
            const marker = markers[truckId];
            if (!marker) return;
            
            const pos = marker.getLatLng();
            const newLat = pos.lat + offsetLat;
            const newLng = pos.lng + offsetLng;

            await fetch('api/journey_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_location',
                    journey_id: activeJourneyId,
                    truck_id: truckId,
                    lat: newLat,
                    lng: newLng,
                    speed: Math.floor(Math.random() * 60) + 20,
                    location_name: "লাইভ আপডেট"
                })
            });

            loadTruckOnMap(truckId); // Refresh UI
        }

        // Auto Refresh Logic
        setInterval(() => {
            const isChecked = document.getElementById('autoRefresh').checked;
            const selectedId = document.getElementById('truckSelector').value;
            if (isChecked) {
                if (selectedId) {
                    loadTruckOnMap(selectedId);
                } else {
                    loadAllMarkers();
                }
            }
        }, 5000); // Update every 5 seconds

        // Run map init after page loads
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</body>
</html>
