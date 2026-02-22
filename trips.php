<?php
require_once 'init.php';
requireLogin();

$page_title = 'ট্রিপ ও আয়-ব্যয় — ' . SITE_NAME;
$page_title_short = 'ট্রিপ ও আয়-ব্যয়';
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
        .finance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        .finance-card {
            padding: 24px;
            border-radius: 20px;
            background: #FFF;
            box-shadow: var(--shadow-sm);
            border: 1px solid #F0F0F0;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .finance-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .profit-pos { color: #10B981; background: #ECFDF5; }
        .profit-neg { color: #EF4444; background: #FEF2F2; }
        .expense-theme { color: #F59E0B; background: #FFFBEB; }
        
        .trip-table th { font-weight: 700; color: var(--text-muted); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .trip-table td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #F8FAFC; }
        
        .rev-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }
        .rev-plus { background: #ECFDF5; color: #059669; }
        .rev-zero { background: #F1F5F9; color: #64748B; }

        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #475569; }
        .form-input { 
            width: 100%; padding: 12px 16px; border: 1.5px solid #E2E8F0; border-radius: 10px; 
            font-family: inherit; transition: all 0.2s;
        }
        .form-input:focus { border-color: var(--primary-red); outline: none; box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1); }

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
    </style>
</head>
<body>
    <div class="pro-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="pro-content">
            <?php include 'includes/header_pro.php'; ?>

            <div class="pro-container">
                <div class="welcome-header" style="margin-bottom: 32px;">
                    <h1 style="font-size: 28px; margin-bottom: 8px;">ট্রিপ ও আয়-ব্যয় ট্র্যাকার</h1>
                    <p style="color: var(--text-muted);">আপনার ব্যবসার প্রতিটি ট্রিপের খরচ ও আয় পরিচালনা করুন</p>
                </div>

                <!-- Finance Stats -->
                <div class="finance-grid">
                    <div class="finance-card">
                        <div class="finance-icon profit-pos"><i class="fas fa-calendar-day"></i></div>
                        <div>
                            <div style="font-size: 13px; color: var(--text-muted); font-weight: 600;">আজকের নিট লাভ</div>
                            <div style="font-size: 24px; font-weight: 800;" id="todayProfit">৳ ০</div>
                        </div>
                    </div>
                    <div class="finance-card">
                        <div class="finance-icon profit-pos" style="background: #E0F2FE; color: #0284C7;"><i class="fas fa-chart-line"></i></div>
                        <div>
                            <div style="font-size: 13px; color: var(--text-muted); font-weight: 600;">শেষ ৩০ দিনের লাভ</div>
                            <div style="font-size: 24px; font-weight: 800;" id="monthProfit">৳ ০</div>
                        </div>
                    </div>
                    <div class="finance-card">
                        <div class="finance-icon expense-theme"><i class="fas fa-gas-pump"></i></div>
                        <div>
                            <div style="font-size: 13px; color: var(--text-muted); font-weight: 600;">৩০ দিনের মোট খরচ</div>
                            <div style="font-size: 24px; font-weight: 800;" id="monthExpense">৳ ০</div>
                        </div>
                    </div>
                </div>

                <!-- Trips List -->
                <div class="pro-card">
                    <div class="truck-list-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <div>
                            <h2 style="font-size: 20px;">সাম্প্রতিক ট্রিপ সমূহ</h2>
                            <div style="font-size: 13px; color: var(--text-muted);"><i class="fas fa-info-circle"></i> ট্রিপ শেষ হওয়ার পর হিসাব যোগ করুন</div>
                        </div>
                        <button onclick="loadFinanceData()" class="btn-pro" style="background: #F8FAFC; color: var(--text-main); border: 1px solid #E2E8F0;">
                            <i class="fas fa-sync-alt"></i> রিফ্রেশ
                        </button>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="trip-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 2px solid #F1F5F9;">
                                    <th style="padding: 16px;">ট্রাক ও তারিখ</th>
                                    <th>রুট (যাত্রা - গন্তব্য)</th>
                                    <th>ভাড়া (Rent)</th>
                                    <th>মোট খরচ</th>
                                    <th>নিট লাভ</th>
                                    <th style="text-align: right;">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody id="tripTableBody">
                                <!-- JS Loaded -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Toast Container -->
            <div id="toastContainer" class="toast-container"></div>

            <?php include 'includes/footer_pro.php'; ?>
        </main>
    </div>

    <!-- Finance Edit Modal -->
    <div id="financeModal" class="modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="modal-content pro-card" style="width: 90%; max-width: 480px; padding: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 style="font-size: 22px; font-weight: 800;">ট্রিপ আয়-ব্যয় হিসাব</h3>
                <span style="cursor: pointer; font-size: 24px; color: #BBB;" onclick="closeFinanceModal()">&times;</span>
            </div>
            
            <form id="financeForm" onsubmit="handleFinanceSubmit(event)">
                <input type="hidden" id="modalJourneyId">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label class="form-label">ভাড়া (Revenue)</label>
                        <input type="number" id="f_rent" class="form-input" placeholder="৳ ০.০০" required step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">জ্বালানি খরচ (Fuel)</label>
                        <input type="number" id="f_fuel" class="form-input" placeholder="৳ ০.০০" required step="0.01">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 32px;">
                    <div class="form-group">
                        <label class="form-label">ড্রাইভার বিল</label>
                        <input type="number" id="f_driver" class="form-input" placeholder="৳ ০.০০" required step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">হেল্পার বিল</label>
                        <input type="number" id="f_helper" class="form-input" placeholder="৳ ০.০০" required step="0.01">
                    </div>
                </div>

                <button type="submit" class="btn-pro btn-pro-primary" style="width: 100%; justify-content: center; padding: 16px; font-weight: 700; font-size: 16px;">
                    <i class="fas fa-save"></i> হিসাব সংরক্ষণ করুন
                </button>
            </form>
        </div>
    </div>

    <script>
        async function loadFinanceData() {
            // Stats
            const statsRes = await fetch('api/journey_api.php?action=get_finance_summary');
            const stats = await statsRes.json();
            if(stats.success) {
                document.getElementById('todayProfit').innerText = '৳ ' + stats.today_profit.toLocaleString();
                document.getElementById('monthProfit').innerText = '৳ ' + stats.month_profit.toLocaleString();
                document.getElementById('monthExpense').innerText = '৳ ' + stats.month_expense.toLocaleString();
            }

            // Table
            const tripsRes = await fetch('api/journey_api.php?action=get_all_trips');
            const trips = await tripsRes.json();
            if(trips.success) {
                renderTrips(trips.data);
            }
        }

        function renderTrips(trips) {
            const body = document.getElementById('tripTableBody');
            body.innerHTML = trips.map(trip => {
                const totalCost = parseFloat(trip.fuel_cost) + parseFloat(trip.driver_bill) + parseFloat(trip.helper_bill);
                const net = parseFloat(trip.net_revenue);
                const date = new Date(trip.start_time).toLocaleDateString('bn-BD', { month: 'short', day: 'numeric' });
                
                return `
                    <tr>
                        <td style="padding: 16px;">
                            <div style="font-weight: 700; color: var(--text-main);">${trip.truck_name}</div>
                            <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;">${date} • ${trip.plate_number}</div>
                        </td>
                        <td>
                            <div style="font-size: 13px; font-weight: 600;">${trip.start_location}</div>
                            <div style="font-size: 12px; color: var(--text-muted);"><i class="fas fa-arrow-down" style="font-size: 10px; margin: 4px 0;"></i></div>
                            <div style="font-size: 13px; font-weight: 600;">${trip.end_location || 'চলমান'}</div>
                        </td>
                        <td><span style="font-weight: 700;">৳ ${parseFloat(trip.rent_amount).toLocaleString()}</span></td>
                        <td><span style="color: #64748B;">৳ ${totalCost.toLocaleString()}</span></td>
                        <td>
                            <span class="rev-badge ${net > 0 ? 'rev-plus' : 'rev-zero'}">
                                ৳ ${net.toLocaleString()}
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button onclick="openFinanceModal(${JSON.stringify(trip).replace(/"/g, '&quot;')})" 
                                    class="btn-action" style="background: #F8FAFC; color: var(--text-main); border: 1px solid #E2E8F0; margin-right: 8px;">
                                <i class="fas fa-calculator"></i> হিসাব
                            </button>
                            <button onclick="deleteTrip(${trip.id})" 
                                    class="btn-action" style="background: #FFF1F2; color: #E11D48; border: 1px solid #FECACA;">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function deleteTrip(id) {
            if(!confirm('আপনি কি নিশ্চিত যে আপনি এই ট্রিপটি মুছে ফেলতে চান? এর সাথে সম্পর্কিত সকল ডাটা মুছে যাবে।')) return;

            const res = await fetch('api/journey_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_trip', journey_id: id })
            });

            const result = await res.json();
            if(result.success) {
                showToast('ট্রিপটি সফলভাবে মুছে ফেলা হয়েছে', 'success');
                loadFinanceData();
            } else {
                showToast(result.message, 'error');
            }
        }

        function openFinanceModal(trip) {
            document.getElementById('modalJourneyId').value = trip.id;
            document.getElementById('f_rent').value = trip.rent_amount;
            document.getElementById('f_fuel').value = trip.fuel_cost;
            document.getElementById('f_driver').value = trip.driver_bill;
            document.getElementById('f_helper').value = trip.helper_bill;
            document.getElementById('financeModal').style.display = 'flex';
        }

        function closeFinanceModal() {
            document.getElementById('financeModal').style.display = 'none';
        }

        async function handleFinanceSubmit(e) {
            e.preventDefault();
            const data = {
                action: 'update_finance',
                journey_id: document.getElementById('modalJourneyId').value,
                rent_amount: document.getElementById('f_rent').value,
                fuel_cost: document.getElementById('f_fuel').value,
                driver_bill: document.getElementById('f_driver').value,
                helper_bill: document.getElementById('f_helper').value
            };

            const res = await fetch('api/journey_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            if(result.success) {
                showToast('আয়-ব্যয় সফলভাবে আপডেট করা হয়েছে', 'success');
                closeFinanceModal();
                loadFinanceData();
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

        loadFinanceData();
    </script>
</body>
</html>
