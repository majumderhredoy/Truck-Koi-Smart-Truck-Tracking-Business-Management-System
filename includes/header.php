<?php
// Determine current page for active nav link
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : SITE_NAME; ?></title>
    
    <!-- CSS Dependencies -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="tracker-main.css">
</head>
<body>
    <!-- Header with Navigation -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="6" y="20" width="14" height="16" rx="2" fill="#DC143C" stroke="#B71C1C" stroke-width="1.5"/>
                        <rect x="20" y="16" width="22" height="20" rx="2" fill="#DC143C" stroke="#B71C1C" stroke-width="1.5"/>
                        <rect x="8" y="22" width="8" height="6" rx="1" fill="#FFFFFF" opacity="0.9"/>
                        <line x1="26" y1="18" x2="26" y2="34" stroke="#FFFFFF" stroke-width="1" opacity="0.3"/>
                        <line x1="32" y1="18" x2="32" y2="34" stroke="#FFFFFF" stroke-width="1" opacity="0.3"/>
                        <line x1="38" y1="18" x2="38" y2="34" stroke="#FFFFFF" stroke-width="1" opacity="0.3"/>
                        <circle cx="12" cy="38" r="4" fill="#2a2a2a" stroke="#DC143C" stroke-width="2"/>
                        <circle cx="12" cy="38" r="2" fill="#DC143C"/>
                        <circle cx="30" cy="38" r="4" fill="#2a2a2a" stroke="#DC143C" stroke-width="2"/>
                        <circle cx="30" cy="38" r="2" fill="#DC143C"/>
                        <circle cx="38" cy="38" r="4" fill="#2a2a2a" stroke="#DC143C" stroke-width="2"/>
                        <circle cx="38" cy="38" r="2" fill="#DC143C"/>
                        <rect x="4" y="26" width="2" height="3" rx="0.5" fill="#FFD700"/>
                    </svg>
                    <span class="logo-text">ট্রাক কই</span>
                </a>

                <?php if (isLoggedIn()): ?>
                <nav class="nav-menu">
                    <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">ড্যাশবোর্ড</a>
                    <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">লাইভ ট্র্যাকিং</a>
                    <a href="#" class="nav-link">রিপোর্ট</a>
                </nav>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                <div class="header-actions">
                    <div class="user-menu">
                        <button class="user-button" id="userMenuBtn">
                            <div class="user-avatar" id="userAvatar">
                                <?php if (!empty($_SESSION['user_avatar'])): ?>
                                    <img src="<?php echo $_SESSION['user_avatar']; ?>" alt="User" id="userAvatarImg">
                                <?php else: ?>
                                    <span class="avatar-placeholder"><?php echo mb_substr($_SESSION['user_name'], 0, 1, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="user-name"><?php echo $_SESSION['user_name']; ?></span>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="#" class="dropdown-item" id="uploadPhotoBtn">📷 ফটো আপলোড করুন</a>
                            <input type="file" id="photoUpload" accept="image/*" style="display: none;">
                            <a href="#" class="dropdown-item">প্রোফাইল</a>
                            <a href="#" class="dropdown-item">সেটিংস</a>
                            <hr class="dropdown-divider">
                            <a href="logout.php" class="dropdown-item">লগআউট</a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="header-actions">
                    <a href="login.php" class="auth-link">লগইন</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
