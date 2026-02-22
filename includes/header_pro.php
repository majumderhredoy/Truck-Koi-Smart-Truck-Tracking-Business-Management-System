<header class="pro-header">
    <div class="header-left">
        <div class="breadcrumb" style="color: var(--text-muted); font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <span>ট্রাক কই</span>
            <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
            <span style="color: var(--text-main); font-weight: 600;">
                <?php echo isset($page_title_short) ? $page_title_short : 'ড্যাশবোর্ড'; ?>
            </span>
        </div>
    </div>

    <div class="header-right">
        <div class="header-actions" style="display: flex; align-items: center; gap: 20px;">
            <button class="notif-btn" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer;">
                <i class="far fa-bell"></i>
            </button>

            <div class="header-user" id="userDropdownBtn">
                <div class="user-avatar">
                    <?php if (!empty($_SESSION['user_avatar'])): ?>
                        <img src="<?php echo $_SESSION['user_avatar']; ?>" alt="Avatar">
                    <?php else: ?>
                        <span><?php echo mb_substr($_SESSION['user_name'], 0, 1, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="user-info" style="line-height: 1.2;">
                    <div style="font-weight: 600; font-size: 14px;"><?php echo $_SESSION['user_name']; ?></div>
                    <div style="font-size: 12px; color: var(--text-muted);">মালিক</div>
                </div>
                <i class="fas fa-chevron-down" style="font-size: 10px; color: var(--text-muted); margin-left: 4px;"></i>
            </div>
        </div>

        <!-- Dropdown Menu -->
        <div class="user-dropdown" id="userDropdown" style="display: none; position: absolute; top: var(--header-height); right: 32px; background: white; width: 200px; border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid #EEE; padding: 12px; overflow: hidden;">
            <a href="settings.php" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: var(--text-main); border-radius: 6px; font-size: 14px;">
                <i class="far fa-user"></i> প্রোফাইল
            </a>
            <a href="settings.php" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: var(--text-main); border-radius: 6px; font-size: 14px;">
                <i class="fas fa-cog"></i> সেটিংস
            </a>
            <div style="height: 1px; background: #EEE; margin: 8px 0;"></div>
            <a href="logout.php" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: var(--status-stopped); border-radius: 6px; font-size: 14px;">
                <i class="fas fa-sign-out-alt"></i> লগআউট
            </a>
        </div>
    </div>
</header>

<script>
    const dropdownBtn = document.getElementById('userDropdownBtn');
    const dropdownMenu = document.getElementById('userDropdown');

    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', function() {
            dropdownMenu.style.display = 'none';
        });
    }
</script>
