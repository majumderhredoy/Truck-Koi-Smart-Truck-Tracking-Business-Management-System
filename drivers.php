<?php
require_once 'init.php';
requireLogin();

$page_title = 'ড্রাইভার সমূহ — ' . SITE_NAME;
$page_title_short = 'ড্রাইভার সমূহ';

$user_id = $_SESSION['user_id'];
// Fetch trucks for the selection dropdown, including assignment status
$trucks_res = $conn->query("
    SELECT t.id, t.name, t.plate_number, d.id as driver_id 
    FROM trucks t 
    LEFT JOIN drivers d ON t.id = d.truck_id 
    WHERE t.user_id = $user_id 
    ORDER BY t.name
");
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
    <style>
        .driver-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        .driver-card {
            background: #FFF;
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #EEE;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: all 0.2s;
        }
        .driver-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        
        .driver-header { display: flex; align-items: center; gap: 16px; }
        .driver-img { 
            width: 64px; height: 64px; 
            border-radius: 12px; 
            background: #F5F5F5; 
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: var(--primary-red);
        }
        
        .info-group { display: flex; flex-direction: column; gap: 4px; }
        .info-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-size: 14px; font-weight: 600; }
        
        .expiry-badge {
            padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
            display: inline-block;
        }
        .expiry-ok { background: rgba(76, 175, 80, 0.1); color: #4CAF50; }
        .expiry-warn { background: rgba(255, 152, 0, 0.1); color: #FF9800; }
        .expiry-danger { background: rgba(244, 67, 54, 0.1); color: #F44336; }

        .truck-tag {
            background: #F8F9FA;
            border: 1px solid #E9ECEF;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: flex; align-items: center; gap: 8px;
        }
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
                        <h1 style="font-size: 28px; margin-bottom: 8px;">ড্রাইভার সমূহ</h1>
                        <p style="color: var(--text-muted);">আপনার ফ্লিটের ড্রাইভারদের তথ্য এখানে পরিচালনা করুন</p>
                    </div>
                    <button class="btn-pro btn-pro-primary" onclick="openDriverModal()">
                        <i class="fas fa-plus"></i> নতুন ড্রাইভার যোগ করুন
                    </button>
                </div>

                <div class="driver-grid" id="driverGrid">
                    <!-- Loaded via JS -->
                </div>
            </div>

            <?php include 'includes/footer_pro.php'; ?>
        </main>
    </div>

    <!-- Modal -->
    <div id="driverModal" class="modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="modal-content pro-card" style="width: 90%; max-width: 500px; padding: 32px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 id="modalTitle" style="font-size: 22px;">নতুন ড্রাইভার যোগ করুন</h3>
                <span style="cursor: pointer; font-size: 24px; color: #BBB;" onclick="closeModal()">&times;</span>
            </div>
            
            <form id="driverForm" onsubmit="handleSubmit(event)" enctype="multipart/form-data">
                <input type="hidden" id="driverId">
                
                <div class="form-group" style="display: flex; align-items: center; gap: 24px; margin-bottom: 24px;">
                    <div id="driverPreview" style="width: 80px; height: 80px; border-radius: 12px; background: #F5F5F5; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid var(--primary-red-light);">
                        <i class="fas fa-user-tie" style="font-size: 32px; color: var(--primary-red);"></i>
                    </div>
                    <div>
                        <label for="driver_photo" class="btn-pro" style="background: #F5F5F5; color: var(--text-main); font-size: 13px; cursor: pointer;">
                            <i class="fas fa-camera"></i> ছবি পরিবর্তন
                        </label>
                        <input type="file" id="driver_photo" name="driver_photo" style="display: none;" onchange="previewDriver(this)">
                        <p style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">JPG/PNG/WEBP (সর্বোচ্চ ২ মেগাবাইট)</p>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 14px; margin-bottom: 6px;">ড্রাইভারের নাম</label>
                    <input type="text" id="name" class="form-control" required style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 14px; margin-bottom: 6px;">ফোন নম্বর</label>
                    <input type="tel" id="phone" class="form-control" required style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label style="display: block; font-size: 14px; margin-bottom: 6px;">লাইসেন্স নম্বর</label>
                        <input type="text" id="license_no" class="form-control" style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                    </div>
                    <div class="form-group">
                        <label style="display: block; font-size: 14px; margin-bottom: 6px;">লাইসেন্স মেয়াদ</label>
                        <input type="date" id="license_expiry" class="form-control" style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 14px; margin-bottom: 6px;">নিযুক্ত গাড়ি</label>
                    <select id="truck_id" class="form-control" style="width:100%; padding:10px; border:1px solid #DDD; border-radius:8px;">
                        <option value="">কোনো গাড়ি নয়</option>
                        <?php foreach($trucks as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo $t['driver_id'] ? 'data-assigned="true" style="color:#AAA;"' : ''; ?>>
                                <?php echo $t['name']; ?> (<?php echo $t['plate_number']; ?>) 
                                <?php echo $t['driver_id'] ? '[ইতোমধ্যেই নিযুক্ত]' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-pro btn-pro-primary" style="width: 100%; justify-content: center; padding: 14px;">
                    <span id="btnText">সংরক্ষণ করুন</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        async function fetchDrivers() {
            const res = await fetch('api/driver_api.php');
            const result = await res.json();
            if (result.success) renderDrivers(result.data);
        }

        function renderDrivers(drivers) {
            const grid = document.getElementById('driverGrid');
            if (drivers.length === 0) {
                grid.innerHTML = `<div class="pro-card" style="grid-column: 1/-1; text-align: center; padding: 60px;">
                    <i class="fas fa-id-card" style="font-size: 48px; color: #EEE; margin-bottom: 16px;"></i>
                    <h3>কোনো ড্রাইভার পাওয়া যায়নি</h3>
                </div>`;
                return;
            }
            grid.innerHTML = drivers.map(d => `
                <div class="driver-card">
                    <div class="driver-header">
                        <div class="driver-img">
                            ${d.driver_image ? 
                                `<img src="${d.driver_image}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">` : 
                                `<i class="fas fa-user-tie"></i>`
                            }
                        </div>
                        <div>
                            <div class="info-value" style="font-size: 18px;">${d.driver_name}</div>
                            <div style="font-size: 13px; color: var(--text-muted);"><i class="fas fa-phone"></i> ${d.phone_number}</div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="info-group">
                            <div class="info-label">লাইসেন্স</div>
                            <div class="info-value">${d.license_number || '-'}</div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">লাইসেন্স মেয়াদ</div>
                            <div>${renderExpiry(d.license_expiry)}</div>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">বর্তমান দায়িত্ব</div>
                        ${d.truck_name ? 
                            `<div class="truck-tag"><i class="fas fa-truck"></i> ${d.truck_name}</div>` : 
                            `<div style="font-size: 13px; color: var(--text-muted);">অনিযুক্ত</div>`
                        }
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 8px;">
                        <button onclick="editDriver(${d.id})" class="btn-pro" style="flex: 1; justify-content: center; font-size: 13px;"><i class="fas fa-edit"></i> এডিট</button>
                        <button onclick="deleteDriver(${d.id})" class="btn-pro" style="color: var(--status-stopped); border-color: #FEE; font-size: 13px;"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `).join('');
        }

        function renderExpiry(date) {
            if (!date) return '<span class="info-value">-</span>';
            const today = new Date();
            const expiry = new Date(date);
            const diff = (expiry - today) / (1000 * 60 * 60 * 24);
            
            let cls = 'expiry-ok';
            if (diff < 0) cls = 'expiry-danger';
            else if (diff < 30) cls = 'expiry-warn';
            
            return `<span class="expiry-badge ${cls}">${date}</span>`;
        }

        function previewDriver(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('driverPreview');
                    preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openDriverModal(id = null) {
            document.getElementById('driverModal').style.display = 'flex';
            if (!id) {
                document.getElementById('modalTitle').innerText = 'নতুন ড্রাইভার যোগ করুন';
                document.getElementById('btnText').innerText = 'সংরক্ষণ করুন';
                document.getElementById('driverForm').reset();
                document.getElementById('driverId').value = '';
                document.getElementById('driverPreview').innerHTML = '<i class="fas fa-user-tie" style="font-size: 32px; color: var(--primary-red);"></i>';
            }
        }

        function closeModal() { document.getElementById('driverModal').style.display = 'none'; }

        async function editDriver(id) {
            const res = await fetch(`api/driver_api.php?id=${id}`);
            const result = await res.json();
            if (result.success) {
                const d = result.data;
                document.getElementById('driverId').value = d.id;
                document.getElementById('name').value = d.driver_name;
                document.getElementById('phone').value = d.phone_number;
                document.getElementById('license_no').value = d.license_number;
                document.getElementById('license_expiry').value = d.license_expiry;
                document.getElementById('truck_id').value = d.truck_id || '';
                
                if (d.driver_image) {
                    document.getElementById('driverPreview').innerHTML = `<img src="${d.driver_image}" style="width: 100%; height: 100%; object-fit: cover;">`;
                } else {
                    document.getElementById('driverPreview').innerHTML = '<i class="fas fa-user-tie" style="font-size: 32px; color: var(--primary-red);"></i>';
                }

                document.getElementById('modalTitle').innerText = 'ড্রাইভার তথ্য আপডেট';
                document.getElementById('btnText').innerText = 'আপডেট করুন';
                document.getElementById('driverModal').style.display = 'flex';
            }
        }

        async function handleSubmit(e) {
            e.preventDefault();
            const id = document.getElementById('driverId').value;
            const formData = new FormData();
            
            formData.append('action', id ? 'update' : 'add');
            if (id) formData.append('id', id);
            
            formData.append('driver_name', document.getElementById('name').value);
            formData.append('phone_number', document.getElementById('phone').value);
            formData.append('license_number', document.getElementById('license_no').value);
            formData.append('license_expiry', document.getElementById('license_expiry').value);
            formData.append('truck_id', document.getElementById('truck_id').value);
            
            const fileInput = document.getElementById('driver_photo');
            if (fileInput.files[0]) {
                formData.append('driver_photo', fileInput.files[0]);
            }

            const apiUrl = id ? 'api/driver_api.php' : 'api/add_driver_strict.php';

            const res = await fetch(apiUrl, {
                method: 'POST',
                body: formData
            });

            const result = await res.json();
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        }

        async function deleteDriver(id) {
            if (!confirm('আপনি কি এই ড্রাইভারকে মুছে ফেলতে চান?')) return;
            const res = await fetch('api/driver_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: id })
            });
            const result = await res.json();
            if (result.success) fetchDrivers();
        }

        fetchDrivers();
    </script>
</body>
</html>
