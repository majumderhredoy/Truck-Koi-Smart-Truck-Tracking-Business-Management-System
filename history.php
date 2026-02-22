<?php
require_once 'init.php';
requireLogin();

$page_title = 'রুট হিস্ট্রি — ' . SITE_NAME;
$page_title_short = 'রুট হিস্ট্রি';

$user_id = $_SESSION['user_id'];
$trucks_res = $conn->query("SELECT id, name, plate_number FROM trucks WHERE user_id = $user_id ORDER BY name");
$trucks = [];
while($t = $trucks_res->fetch_assoc()) $trucks[] = $t;
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
    
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        .history-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            height: calc(100vh - var(--header-height));
        }
        #historyMap { height: 100%; width: 100%; border-radius: 0 0 16px 0; }
        
        .history-sidebar {
            background: #FFF;
            border-right: 1px solid #EEE;
            padding: 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .journey-item {
            padding: 16px;
            border: 1px solid #F0F0F0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 12px;
        }
        .journey-item:hover { border-color: var(--primary-red); background: #FFF5F7; }
        .journey-item.active { background: #FFF5F7; border-color: var(--primary-red); box-shadow: var(--shadow-sm); }

        .timeline { border-left: 2px dashed #DDD; margin-left: 10px; padding-left: 20px; position: relative; }
        .timeline-point { position: absolute; left: -7px; width: 12px; height: 12px; border-radius: 50%; background: #DDD; }
        .timeline-start { background: #4CAF50; top: 0; }
        .timeline-end { background: #F44336; bottom: 0; }
    </style>
</head>
<body>
    <div class="pro-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="pro-content">
            <?php include 'includes/header_pro.php'; ?>
            
            <div class="history-layout">
                <div class="history-sidebar">
                    <div class="form-group">
                        <label style="font-weight: 700; display: block; margin-bottom: 8px;">গাড়ি নির্বাচন করুন</label>
                        <select id="truckSelect" onchange="loadJourneys(this.value)" class="form-control" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #DDD;">
                            <option value="">বাছাই করুন...</option>
                            <?php foreach($trucks as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo $t['name']; ?> (<?php echo $t['plate_number']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="journeyList" style="margin-top: 10px;">
                        <!-- JS Loaded -->
                        <p style="text-align: center; color: #BBB; margin-top: 40px;">গাড়ি সিলেক্ট করলে যাত্রার ইতিহাস এখানে দেখাবে</p>
                    </div>
                </div>

                <div id="historyMap"></div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let polyline;
        let markers = [];

        function initMap() {
            map = L.map('historyMap').setView([23.8103, 90.4125], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        }

        async function loadJourneys(truckId) {
            if (!truckId) return;
            const res = await fetch(`api/journey_api.php?action=get_history&truck_id=${truckId}`);
            const result = await res.json();
            
            const list = document.getElementById('journeyList');
            if (result.success) {
                if (result.data.length === 0) {
                    list.innerHTML = '<p style="text-align: center; color: #BBB;">কোনো যাত্রার তথ্য পাওয়া যায়নি</p>';
                    return;
                }
                list.innerHTML = result.data.map(j => `
                    <div class="journey-item" onclick="viewJourney(this, ${j.id})">
                        <div style="font-weight: 700; font-size: 14px; margin-bottom: 8px;">
                            <i class="fas fa-calendar-alt"></i> ${new Date(j.start_time).toLocaleDateString('bn-BD')}
                        </div>
                        <div class="timeline">
                            <div class="timeline-point timeline-start"></div>
                            <div style="font-size: 13px; margin-bottom: 10px;">
                                <strong>শুরু:</strong> ${new Date(j.start_time).toLocaleTimeString('bn-BD')} <br>
                                <span style="color: #666;">${j.start_location || 'Unknown'}</span>
                            </div>
                            <div class="timeline-point timeline-end"></div>
                            <div style="font-size: 13px;">
                                <strong>শেষ:</strong> ${j.end_time ? new Date(j.end_time).toLocaleTimeString('bn-BD') : 'চলমান'} <br>
                                <span style="color: #666;">${j.end_location || 'Unknown'}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        async function viewJourney(el, journeyId) {
            // UI Toggle
            document.querySelectorAll('.journey-item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');

            // Clear Map
            if (polyline) map.removeLayer(polyline);
            markers.forEach(m => map.removeLayer(m));
            markers = [];

            const res = await fetch(`api/journey_api.php?action=get_path&journey_id=${journeyId}`);
            const result = await res.json();
            
            if (result.success && result.data.length > 0) {
                const points = result.data.map(p => [p.lat, p.lng]);
                polyline = L.polyline(points, {color: '#DC143C', weight: 4, opacity: 0.8}).addTo(map);
                
                // Start Marker
                const startPoint = points[0];
                const startMarker = L.circleMarker(startPoint, {radius: 8, color: '#4CAF50', fillOpacity: 1}).addTo(map)
                    .bindPopup('যাত্রার শুরু');
                markers.push(startMarker);

                // End Marker
                const endPoint = points[points.length - 1];
                const endMarker = L.circleMarker(endPoint, {radius: 8, color: '#F44336', fillOpacity: 1}).addTo(map)
                    .bindPopup('যাত্রার সমাপ্তি');
                markers.push(endMarker);

                map.fitBounds(polyline.getBounds(), {padding: [50, 50]});
            } else {
                alert('এই যাত্রার কোনো লোকেশন ডেটা পাওয়া যায়নি।');
            }
        }

        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</body>
</html>
