<?php
require_once 'init.php';
requireLogin();

$page_title = 'ড্যাশবোর্ড — ' . SITE_NAME;
$page_title_short = 'ড্যাশবোর্ড';

// Initial stats fetch (we will refresh these via AJAX too)
// Initial stats fetch
$user_id = $_SESSION['user_id'];
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running,
    SUM(CASE WHEN status = 'idle' THEN 1 ELSE 0 END) as idle
    FROM trucks WHERE user_id = $user_id";
$stats = $conn->query($stats_query)->fetch_assoc();

// Trip and Finance Stats
$trip_count_sql = "SELECT COUNT(*) as today_trips FROM journeys 
                   WHERE user_id = $user_id AND DATE(start_time) = CURDATE()";
$profit_sql = "SELECT SUM(net_revenue) as today_profit FROM journeys 
                WHERE user_id = $user_id AND status = 'completed' AND DATE(end_time) = CURDATE()";

$today_trips = $conn->query($trip_count_sql)->fetch_assoc()['today_trips'] ?? 0;
$today_profit = $conn->query($profit_sql)->fetch_assoc()['today_profit'] ?? 0;
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
</head>
<body>
    <div class="pro-layout">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="pro-content">
            <!-- Header -->
            <?php include 'includes/header_pro.php'; ?>

            <div class="pro-container">
                <!-- Welcome -->
                <div class="welcome-header" style="margin-bottom: 32px;">
                    <h1 style="font-size: 28px; margin-bottom: 8px;">স্বাগতম, <?php echo $_SESSION['user_name']; ?> 👋</h1>
                    <p style="color: var(--text-muted);">আপনার ফ্লিট ম্যানেজমেন্ট ড্যাশবোর্ড</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid" id="statsGrid">
                    <div class="pro-card stat-card">
                        <div class="stat-icon" style="background: rgba(220, 20, 60, 0.1); color: var(--primary-red);">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 800;" id="statTotal"><?php echo $stats['total']; ?></div>
                            <div style="font-size: 14px; color: var(--text-muted);">মোট গাড়ি</div>
                        </div>
                    </div>
                    
                    <div class="pro-card stat-card">
                        <div class="stat-icon" style="background: rgba(76, 175, 80, 0.1); color: var(--status-running);">
                            <i class="fas fa-play"></i>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 800;" id="statRunning"><?php echo $stats['running']; ?></div>
                            <div style="font-size: 14px; color: var(--text-muted);">চলমান</div>
                        </div>
                    </div>

                    <div class="pro-card stat-card">
                        <div class="stat-icon" style="background: rgba(255, 152, 0, 0.1); color: var(--status-idle);">
                            <i class="fas fa-pause"></i>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 800;" id="statIdle"><?php echo $stats['idle']; ?></div>
                            <div style="font-size: 14px; color: var(--text-muted);">অলস</div>
                        </div>
                    </div>

                    <div class="pro-card stat-card">
                        <div class="stat-icon" style="background: rgba(33, 150, 243, 0.1); color: #2196F3;">
                            <i class="fas fa-route"></i>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 800;"><?php echo $today_trips; ?></div>
                            <div style="font-size: 14px; color: var(--text-muted);">আজকের ট্রিপ</div>
                        </div>
                    </div>

                    <div class="pro-card stat-card">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 800;">৳ <?php echo number_format($today_profit); ?></div>
                            <div style="font-size: 14px; color: var(--text-muted);">আজকের নিট লাভ</div>
                        </div>
                    </div>
                </div>

                <!-- Truck List Section -->
                <div class="pro-card">
                    <div class="truck-list-header">
                        <h2 style="font-size: 20px;">আপনার গাড়ি সমূহ</h2>
                        <button class="btn-pro btn-pro-primary" onclick="openTruckModal()">
                            <i class="fas fa-plus"></i> নতুন গাড়ি যোগ করুন
                        </button>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 2px solid #F5F5F5; color: var(--text-muted); font-size: 13px;">
                                    <th style="padding: 16px;">গাড়ির ফটো</th>
                                    <th>গাড়ির নাম</th>
                                    <th>প্লেট নম্বর</th>
                                    <th>ব্র্যান্ড</th>
                                    <th>চালক</th>
                                    <th>অবস্থান</th>
                                    <th>স্ট্যাটাস</th>
                                    <th style="text-align: right;">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody id="truckTableBody">
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer_pro.php'; ?>
        </main>
    </div>

    <!-- AJAX Modal -->
    <div id="truckModal" class="modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="modal-content pro-card" style="width: 90%; max-width: 500px; padding: 32px; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 id="modalTitle" style="font-size: 22px;">নতুন গাড়ি যোগ করুন</h3>
                <span style="cursor: pointer; font-size: 24px; color: #BBB;" onclick="closeTruckModal()">&times;</span>
            </div>
            
<?php
// Enhanced dashboard.php with Free Driver filtering
$free_drivers_res = $conn->query("SELECT id, driver_name FROM drivers WHERE user_id = $user_id AND truck_id IS NULL ORDER BY driver_name");
$free_drivers = [];
while($d = $free_drivers_res->fetch_assoc()) $free_drivers[] = $d;
?>
            <form id="truckForm" onsubmit="handleTruckSubmit(event)">
                <input type="hidden" id="truckId" name="id">
                
                <div style="border-bottom: 2px solid #F5F5F5; margin-bottom: 20px; padding-bottom: 20px;">
                    <h4 style="font-size: 14px; color: var(--primary-red); margin-bottom: 12px;"><i class="fas fa-truck"></i> গাড়ির তথ্য</h4>
                    
                    <div class="form-group" style="margin-bottom: 16px; text-align: center;">
                        <div id="truckPreview" style="width: 120px; height: 120px; border: 2px dashed #DDD; border-radius: 12px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #FAFAFA; cursor: pointer;" onclick="document.getElementById('truck_photo').click()">
                            <i class="fas fa-camera" style="font-size: 24px; color: #BBB;"></i>
                        </div>
                        <input type="file" id="truck_photo" name="truck_photo" accept="image/*" style="display: none;" onchange="previewTruck(this)">
                        <label for="truck_photo" class="btn-pro" style="background: #F5F5F5; font-size: 12px; padding: 6px 12px;">ফটো আপলোড করুন</label>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">গাড়ির নাম</label>
                            <input type="text" id="name" required class="form-control" style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                        </div>
                        <div class="form-group">
                            <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">প্লেট নম্বর (UNIQUE)</label>
                            <input type="text" id="plate" required class="form-control" style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">ব্র্যান্ড</label>
                            <input type="text" id="brand" class="form-control" style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                        </div>
                        <div class="form-group">
                            <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">ডিভাইস ID</label>
                            <input type="text" id="device_id" class="form-control" style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">ড্রাইভার নির্বাচন করুন (অবশ্যই ফ্রি হতে হবে)</label>
                        <select id="driver_id" class="form-control" style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                            <option value="">ড্রাইভার নেই (পরে নিযুক্ত করুন)</option>
                            <?php foreach($free_drivers as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo $d['driver_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if(empty($free_drivers)): ?>
                            <p style="font-size: 12px; color: var(--primary-red); margin-top: 8px;"><i class="fas fa-exclamation-triangle"></i> কোনো ফ্রি ড্রাইভার নেই। আগে ড্রাইভার যোগ করুন।</p>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn-pro btn-pro-primary" style="width: 100%; justify-content: center; padding: 14px; font-weight: 700;">
                    <i class="fas fa-check-circle"></i> <span id="btnText">তথ্য সংরক্ষণ করুন</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Frontend Logic -->
    <script>
        async function fetchTrucks() {
            const res = await fetch('api/truck_api.php');
            const result = await res.json();
            if (result.success) {
                renderTrucks(result.data);
                updateStats(result.data);
            }
        }

        function renderTrucks(trucks) {
            const container = document.getElementById('truckTableBody');
            container.innerHTML = trucks.map(truck => `
                <tr style="border-bottom: 1px solid #F5F5F5; font-size: 14px;">
                    <td style="padding: 16px;">
                        <div style="width: 40px; height: 40px; border-radius: 6px; overflow: hidden; background: #F5F5F5; display: flex; align-items: center; justify-content: center;">
                            ${truck.truck_image ? `<img src="${truck.truck_image}" style="width:100%; height:100%; object-fit:cover;">` : `<i class="fas fa-truck" style="color: #DDD;"></i>`}
                        </div>
                    </td>
                    <td style="font-weight: 600;">${truck.name}</td>
                    <td>${truck.plate_number}</td>
                    <td>${truck.brand || '-'}</td>
                    <td>${truck.driver_name || '-'}</td>
                    <td><span style="color: var(--text-muted); font-size: 12px;"><i class="fas fa-map-marker-alt"></i> ${truck.location || 'অজানা'}</span></td>
                    <td>
                        <span style="padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: ${getStatusBg(truck.status)}; color: ${getStatusColor(truck.status)}">
                            ${translateStatus(truck.status)}
                        </span>
                    </td>
                    <td style="text-align: right; padding-right: 16px;">
                        <button onclick="editTruck(${truck.id})" style="border: none; background: none; color: var(--text-muted); cursor: pointer; padding: 8px;"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteTruck(${truck.id})" style="border: none; background: none; color: var(--status-stopped); cursor: pointer; padding: 8px;"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        function updateStats(trucks) {
            document.getElementById('statTotal').innerText = trucks.length;
            document.getElementById('statRunning').innerText = trucks.filter(t => t.status === 'running').length;
            document.getElementById('statIdle').innerText = trucks.filter(t => t.status === 'idle').length;
        }

        function getStatusBg(status) {
            if (status === 'running') return 'rgba(76, 175, 80, 0.1)';
            if (status === 'idle') return 'rgba(255, 152, 0, 0.1)';
            return 'rgba(244, 67, 54, 0.1)';
        }

        function getStatusColor(status) {
            if (status === 'running') return '#4CAF50';
            if (status === 'idle') return '#FF9800';
            return '#F44336';
        }

        function translateStatus(status) {
            if (status === 'running') return 'চলছে';
            if (status === 'idle') return 'অলস';
            return 'বন্ধ';
        }

        function openTruckModal(id = null) {
            const modal = document.getElementById('truckModal');
            modal.style.display = 'flex';
            if (!id) {
                document.getElementById('modalTitle').innerText = 'নতুন গাড়ি যোগ করুন';
                document.getElementById('btnText').innerText = 'সংরক্ষণ করুন';
                document.getElementById('truckForm').reset();
                document.getElementById('truckId').value = '';
                document.getElementById('truckPreview').innerHTML = '<i class="fas fa-camera" style="font-size: 24px; color: #BBB;"></i>';
            }
        }

        function previewTruck(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('truckPreview').innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function closeTruckModal() {
            document.getElementById('truckModal').style.display = 'none';
        }

        async function editTruck(id) {
            const res = await fetch(`api/truck_api.php?id=${id}`);
            const result = await res.json();
            if (result.success) {
                const truck = result.data;
                document.getElementById('truckId').value = truck.id;
                document.getElementById('name').value = truck.name || truck.truck_name;
                document.getElementById('plate').value = truck.plate_number;
                document.getElementById('driver_id').value = truck.driver_id || '';
                document.getElementById('brand').value = truck.brand || '';
                document.getElementById('device_id').value = truck.gps_device_id || '';
                
                if (truck.truck_image) {
                    document.getElementById('truckPreview').innerHTML = `<img src="${truck.truck_image}" style="width:100%; height:100%; object-fit:cover;">`;
                } else {
                    document.getElementById('truckPreview').innerHTML = '<i class="fas fa-camera" style="font-size: 24px; color: #BBB;"></i>';
                }

                document.getElementById('modalTitle').innerText = 'গাড়ি তথ্য আপডেট';
                document.getElementById('btnText').innerText = 'আপডেট করুন';
                document.getElementById('truckModal').style.display = 'flex';
            }
        }

        async function handleTruckSubmit(e) {
            e.preventDefault();
            const id = document.getElementById('truckId').value;
            const isAdd = !id;
            
            const formData = new FormData();
            formData.append('truck_name', document.getElementById('name').value);
            formData.append('plate_number', document.getElementById('plate').value);
            formData.append('driver_id', document.getElementById('driver_id').value);
            formData.append('brand', document.getElementById('brand').value);
            formData.append('device_id', document.getElementById('device_id').value);
            
            const fileInput = document.getElementById('truck_photo');
            if (fileInput.files[0]) {
                formData.append('truck_photo', fileInput.files[0]);
            }

            if (id) {
                formData.append('id', id);
                formData.append('action', 'update');
            } else {
                formData.append('action', 'add');
            }

            const apiUrl = isAdd ? 'api/add_truck_strict.php' : 'api/truck_api.php';

            try {
                const res = await fetch(apiUrl, {
                    method: 'POST',
                    body: formData
                });

                const result = await res.json();
                
                if (result.success) {
                    alert(result.message);
                    location.reload(); // Refresh to update dropdowns and table
                } else {
                    alert(result.message);
                }
            } catch (err) {
                console.error('Error:', err);
                alert('সার্ভারে সমস্যা হয়েছে');
            }
        }

        async function deleteTruck(id) {
            if (!confirm('আপনি কি এই গাড়িটি মুছে ফেলতে চান?')) return;
            const res = await fetch('api/truck_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: id })
            });
            const result = await res.json();
            if (result.success) {
                fetchTrucks();
                alert(result.message);
            } else {
                alert(result.message);
            }
        }

        // Initial Load
        fetchTrucks();
    </script>
</body>
</html>
