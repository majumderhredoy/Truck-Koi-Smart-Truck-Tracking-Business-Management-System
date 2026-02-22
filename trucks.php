<?php
require_once 'init.php';
requireLogin();

$page_title = 'গাড়ি সমূহ — ' . SITE_NAME;
$page_title_short = 'গাড়ি সমূহ';

$user_id = $_SESSION['user_id'];
$trucks_query = "SELECT t.*, d.driver_name as current_driver 
                 FROM trucks t 
                 LEFT JOIN drivers d ON t.driver_id = d.id 
                 WHERE t.user_id = $user_id 
                 ORDER BY t.created_at DESC";
$trucks = $conn->query($trucks_query);
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
    <style>
        .truck-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }
        .truck-card-pro {
            background: #FFF;
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #EEE;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        .truck-card-pro:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        .truck-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-running { background: rgba(76, 175, 80, 0.1); color: #4CAF50; }
        .badge-idle { background: rgba(255, 152, 0, 0.1); color: #FF9800; }
        .badge-stopped { background: rgba(244, 67, 54, 0.1); color: #F44336; }

        .truck-icon-circle {
            width: 50px;
            height: 50px;
            background: #F5F5F5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary-red);
            margin-bottom: 16px;
        }

        .details-list {
            list-style: none;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #F5F5F5;
        }
        .details-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .details-label { color: var(--text-muted); }
        .details-value { font-weight: 600; }

        .card-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        .btn-action {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #EEE;
            background: #FFF;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-action:hover { background: #F9F9F9; border-color: #DDD; }
        .btn-track { background: var(--primary-red-light); color: var(--primary-red); border: none; }
        .btn-track:hover { background: #fee2e7; }
    </style>
</head>
<body>
    <div class="pro-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="pro-content">
            <?php include 'includes/header_pro.php'; ?>

            <div class="pro-container">
                <div class="truck-list-header" style="margin-bottom: 32px;">
                    <div>
                        <h1 style="font-size: 28px; margin-bottom: 8px;">আপনার গাড়ি সমূহ</h1>
                        <p style="color: var(--text-muted);">আপনার ফ্লিটে মোট <?php echo $trucks->num_rows; ?>টি গাড়ি রয়েছে</p>
                    </div>
                    <a href="dashboard.php" class="btn-pro btn-pro-primary">
                        <i class="fas fa-plus"></i> গাড়ি যোগ করুন
                    </a>
                </div>

                <div class="truck-grid">
                    <?php if ($trucks->num_rows > 0): ?>
                        <?php while($truck = $trucks->fetch_assoc()): ?>
                            <div class="truck-card-pro">
                                <div class="truck-badge badge-<?php echo $truck['status']; ?>">
                                    <?php 
                                        if($truck['status'] == 'running') echo 'চলমান';
                                        elseif($truck['status'] == 'idle') echo 'অলস';
                                        else echo 'বন্ধ';
                                    ?>
                                </div>
                                
                                <div class="truck-icon-circle" style="overflow: hidden; background: #F5F5F5;">
                                    <?php if (!empty($truck['truck_image'])): ?>
                                        <img src="<?php echo $truck['truck_image']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-truck"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 style="font-size: 18px; margin-bottom: 4px;"><?php echo $truck['name']; ?></h3>
                                <p style="color: var(--text-muted); font-size: 14px; font-weight: 600;"><?php echo $truck['plate_number']; ?></p>

                                <ul class="details-list">
                                    <li class="details-item">
                                        <span class="details-label">ব্র্যান্ড</span>
                                        <span class="details-value"><?php echo ($truck['brand'] ?? '') ?: 'অজানা'; ?></span>
                                    </li>
                                    <li class="details-item">
                                        <span class="details-label">চালক</span>
                                        <span class="details-value"><?php echo $truck['current_driver'] ?: 'চালক নেই'; ?></span>
                                    </li>
                                    <li class="details-item">
                                        <span class="details-label">ডিভাইস ID</span>
                                        <span class="details-value"><?php echo ($truck['gps_device_id'] ?? '') ?: 'সংযুক্ত নয়'; ?></span>
                                    </li>
                                    <li class="details-item">
                                        <span class="details-label">অবস্থান</span>
                                        <span class="details-value" style="font-weight: 500; font-size: 12px; max-width: 150px; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo $truck['location'] ?: 'অজানা'; ?>
                                        </span>
                                    </li>
                                </ul>

                                <div class="card-actions">
                                    <a href="index.php?truck=<?php echo $truck['id']; ?>" class="btn-action btn-track">
                                        <i class="fas fa-map-marker-alt"></i> ট্র্যাক
                                    </a>
                                    <?php if(!$truck['driver_id']): ?>
                                        <button onclick="openAssignModal(<?php echo $truck['id']; ?>, '<?php echo $truck['name']; ?>')" class="btn-action" style="color: var(--primary-red); border-color: #FEE;">
                                            <i class="fas fa-user-plus"></i> নিযুক্ত
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="window.location.href='dashboard.php'" class="btn-action">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="pro-card" style="grid-column: 1 / -1; text-align: center; padding: 60px;">
                            <i class="fas fa-truck-loading" style="font-size: 60px; color: #EEE; margin-bottom: 24px;"></i>
                            <h3>কোনো গাড়ি পাওয়া যায়নি</h3>
                            <p style="color: var(--text-muted); margin-bottom: 24px;">আপনার ফ্লিটে কোনো গাড়ি যুক্ত করা নেই।</p>
                            <a href="dashboard.php" class="btn-pro btn-pro-primary">নতুন গাড়ি যোগ করুন</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php include 'includes/footer_pro.php'; ?>
        </main>
    </div>

    <!-- Assignment Modal -->
    <div id="assignModal" class="modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="modal-content pro-card" style="width: 90%; max-width: 400px; padding: 32px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 style="font-size: 20px;">ড্রাইভার নিযুক্ত করুন</h3>
                <span style="cursor: pointer; font-size: 24px; color: #BBB;" onclick="closeAssignModal()">&times;</span>
            </div>
            <p id="truckInfo" style="margin-bottom: 16px; font-weight: 600; color: var(--text-muted);"></p>
            
            <form id="assignForm" onsubmit="handleAssignSubmit(event)">
                <input type="hidden" id="assignTruckId">
                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 14px; margin-bottom: 8px;">ফ্রি ড্রাইভার নির্বাচন করুন</label>
                    <select id="free_driver_id" class="form-control" required style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                        <!-- Loaded via Ajax -->
                    </select>
                </div>
                <button type="submit" class="btn-pro btn-pro-primary" style="width: 100%; justify-content: center; padding: 14px;">
                    সংরক্ষণ করুন
                </button>
            </form>
        </div>
    </div>

    <script>
        async function openAssignModal(truckId, truckName) {
            document.getElementById('assignTruckId').value = truckId;
            document.getElementById('truckInfo').innerText = 'গাড়ি: ' + truckName;
            
            // Fetch free drivers
            const res = await fetch('api/driver_api.php');
            const result = await res.json();
            
            const select = document.getElementById('free_driver_id');
            if(result.success) {
                const freeDrivers = result.data.filter(d => !d.truck_id);
                if(freeDrivers.length === 0) {
                    select.innerHTML = '<option value="">কোথাও কোনো ফ্রি ড্রাইভার নেই</option>';
                } else {
                    select.innerHTML = '<option value="">ড্রাইভার বেছে নিন...</option>' + 
                        freeDrivers.map(d => `<option value="${d.id}">${d.driver_name} (${d.phone_number})</option>`).join('');
                }
            }
            
            document.getElementById('assignModal').style.display = 'flex';
        }

        function closeAssignModal() {
            document.getElementById('assignModal').style.display = 'none';
        }

        async function handleAssignSubmit(e) {
            e.preventDefault();
            const truckId = document.getElementById('assignTruckId').value;
            const driverId = document.getElementById('free_driver_id').value;

            if(!driverId) {
                alert('অনুগ্রহ করে একজন ড্রাইভার নির্বাচন করুন');
                return;
            }

            const res = await fetch('api/assign_driver.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ truck_id: truckId, driver_id: driverId })
            });

            const result = await res.json();
            if(result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        }
    </script>
</body>
</html>
