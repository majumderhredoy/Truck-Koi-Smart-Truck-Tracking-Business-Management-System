<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="pro-sidebar">
    <a href="dashboard.php" class="sidebar-logo">
        <svg viewBox="0 0 48 48" width="32" height="32" fill="none">
            <rect x="6" y="20" width="14" height="16" rx="2" fill="currentColor"/>
            <rect x="20" y="16" width="22" height="20" rx="2" fill="currentColor"/>
            <circle cx="12" cy="38" r="4" fill="#2a2a2a"/>
            <circle cx="30" cy="38" r="4" fill="#2a2a2a"/>
            <circle cx="38" cy="38" r="4" fill="#2a2a2a"/>
        </svg>
        <span>ট্রাক কই<sup>PRO</sup></span>
    </a>

    <div class="sidebar-menu">
        <div class="menu-label">মেইন মেনু</div>
        
        <a href="dashboard.php" class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i>
            <span>ড্যাশবোর্ড</span>
        </a>

        <a href="index.php" class="menu-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-map-marked-alt"></i>
            <span>লাইভ ট্র্যাকিং</span>
        </a>

        <a href="history.php" class="menu-item <?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>
            <span>রুট হিস্ট্রি</span>
        </a>

        <a href="trips.php" class="menu-item <?php echo $current_page == 'trips.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>ট্রিপ ও আয়-ব্যয়</span>
        </a>

        <div class="menu-label" style="margin-top: 24px;">ব্যবস্থাপনা</div>

        <a href="trucks.php" class="menu-item <?php echo $current_page == 'trucks.php' ? 'active' : ''; ?>">
            <i class="fas fa-truck"></i>
            <span>গাড়ি সমূহ</span>
        </a>

        <a href="drivers.php" class="menu-item <?php echo $current_page == 'drivers.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>ড্রাইভার সমূহ</span>
        </a>

        <a href="settings.php" class="menu-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>সেটিংস</span>
        </a>
    </div>

    <div class="sidebar-footer" style="padding: 20px; border-top: 1px solid #EEE;">
        <a href="logout.php" class="menu-item" style="color: var(--status-stopped);">
            <i class="fas fa-sign-out-alt"></i>
            <span>লগআউট</span>
        </a>
    </div>
</aside>
