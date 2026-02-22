<?php
require_once 'init.php';
requireLogin();

$page_title = 'সেটিংস — ' . SITE_NAME;
$page_title_short = 'সেটিংস';

// Get current user info
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user = $conn->query($user_sql)->fetch_assoc();
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
        .settings-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 32px;
        }
        .settings-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .nav-link {
            padding: 12px 16px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .nav-link:hover { background: #EEE; }
        .nav-link.active { background: var(--primary-red-light); color: var(--primary-red); }
        
        .form-section { display: none; }
        .form-section.active { display: block; }
        
        .settings-form { max-width: 600px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-muted); }
        .form-control { width: 100%; padding: 12px; border: 1px solid #DDD; border-radius: 8px; font-family: inherit; font-size: 15px; }
        .form-control:focus { border-color: var(--primary-red); outline: none; }
        
        @media (max-width: 900px) {
            .settings-grid { grid-template-columns: 1fr; }
        }

        /* Password Toggle Support for Settings */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper .form-control {
            padding-right: 48px;
        }
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.2s;
        }
        .password-toggle:hover {
            color: var(--primary-red);
        }
    </style>
</head>
<body>
    <div class="pro-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="pro-content">
            <?php include 'includes/header_pro.php'; ?>

            <div class="pro-container">
                <h1 style="font-size: 28px; margin-bottom: 32px;">অ্যাকাউন্ট সেটিংস</h1>

                <div class="settings-grid">
                    <!-- Settings Sidebar -->
                    <div class="settings-nav">
                        <a href="#" class="nav-link active" onclick="showSection('profile', this)">
                            <i class="far fa-user"></i> প্রোফাইল এডিট
                        </a>
                        <a href="#" class="nav-link" onclick="showSection('security', this)">
                            <i class="fas fa-shield-alt"></i> নিরাপত্তা
                        </a>
                    </div>

                    <!-- Settings Forms -->
                    <div class="pro-card">
                        <!-- Profile Section -->
                        <div id="profileSection" class="form-section active">
                            <h3 style="margin-bottom: 24px;">প্রোফাইল তথ্য</h3>
                            <form id="profileForm" onsubmit="updateProfile(event)" class="settings-form" enctype="multipart/form-data">
                                <div class="form-group" style="display: flex; align-items: center; gap: 24px; margin-bottom: 32px;">
                                    <div id="avatarPreview" style="width: 100px; height: 100px; border-radius: 50%; background: #F5F5F5; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 3px solid var(--primary-red-light);">
                                        <?php if (!empty($user['profile_image'])): ?>
                                            <img src="<?php echo $user['profile_image']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <span style="font-size: 32px; font-weight: 700; color: var(--primary-red);"><?php echo mb_substr($user['name'], 0, 1, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <label for="profile_photo" class="btn-pro" style="background: #F5F5F5; color: var(--text-main); font-size: 13px; cursor: pointer;">
                                            <i class="fas fa-camera"></i> ছবি পরিবর্তন করুন
                                        </label>
                                        <input type="file" id="profile_photo" name="profile_photo" style="display: none;" onchange="previewAvatar(this)">
                                        <p style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">JPG, PNG অথবা WEBP (সর্বোচ্চ ২ মেগাবাইট)</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>আপনার নাম</label>
                                    <input type="text" id="name" class="form-control" value="<?php echo $user['name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>ফোন নম্বর</label>
                                    <input type="tel" id="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>ইমেইল এড্রেস</label>
                                    <input type="email" id="email" class="form-control" value="<?php echo $user['email']; ?>">
                                </div>
                                <button type="submit" class="btn-pro btn-pro-primary">পরিবর্তন সংরক্ষণ করুন</button>
                            </form>
                        </div>

                        <!-- Security Section -->
                        <div id="securitySection" class="form-section">
                            <h3 style="margin-bottom: 24px;">পাসওয়ার্ড পরিবর্তন করুন</h3>
                            <form onsubmit="updatePassword(event)" class="settings-form">
                                <div class="form-group">
                                    <label>বর্তমান পাসওয়ার্ড</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="current_pass" class="form-control" required autocomplete="off">
                                        <i class="fas fa-eye password-toggle" onclick="togglePassword('current_pass', this)"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>নতুন পাসওয়ার্ড</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="new_pass" class="form-control" required minlength="6" autocomplete="new-password">
                                        <i class="fas fa-eye password-toggle" onclick="togglePassword('new_pass', this)"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="confirm_pass" class="form-control" required minlength="6" autocomplete="new-password">
                                        <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_pass', this)"></i>
                                    </div>
                                </div>
                                <button type="submit" class="btn-pro btn-pro-primary">পাসওয়ার্ড আপডেট করুন</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'includes/footer_pro.php'; ?>
        </main>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword(inputId, iconEl) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                iconEl.classList.remove('fa-eye');
                iconEl.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                iconEl.classList.remove('fa-eye-slash');
                iconEl.classList.add('fa-eye');
            }
        }

        // Clear forms on load
        window.onload = function() {
            // Only clear password fields in settings to keep profile info visible
            const passInputs = document.querySelectorAll('input[type="password"]');
            passInputs.forEach(input => {
                input.value = '';
            });
        };

        function showSection(section, el) {
            document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            
            document.getElementById(section + 'Section').classList.add('active');
            el.classList.add('active');
        }

        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        async function updateProfile(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData();
            
            formData.append('action', 'update_profile');
            formData.append('name', document.getElementById('name').value);
            formData.append('phone', document.getElementById('phone').value);
            formData.append('email', document.getElementById('email').value);
            
            const fileInput = document.getElementById('profile_photo');
            if (fileInput.files[0]) {
                formData.append('profile_photo', fileInput.files[0]);
            }

            const res = await fetch('api/user_api.php', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();
            alert(result.message);
            if (result.success) location.reload();
        }

        async function updatePassword(e) {
            e.preventDefault();
            const newPass = document.getElementById('new_pass').value;
            const confirmPass = document.getElementById('confirm_pass').value;

            if (newPass !== confirmPass) {
                alert('নতুন পাসওয়ার্ড মিলছে না!');
                return;
            }

            const data = {
                action: 'update_password',
                current_password: document.getElementById('current_pass').value,
                new_password: newPass
            };

            const res = await fetch('api/user_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            alert(result.message);
            if (result.success) e.target.reset();
        }
    </script>
</body>
</html>
