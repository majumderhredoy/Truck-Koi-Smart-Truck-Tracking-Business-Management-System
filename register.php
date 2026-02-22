<?php
require_once 'init.php';
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নিবন্ধন — ট্রাক কই</title>
    <link rel="stylesheet" href="auth-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Noto+Sans+Bengali:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <a href="index.php" class="auth-logo" style="text-decoration: none;">
                <i class="fas fa-truck-moving"></i>
                <span>ট্রাক কই<sup>PRO</sup></span>
            </a>
            <h1 class="auth-title">অ্যাকাউন্ট তৈরি করুন</h1>
            <p class="auth-subtitle">আপনার গাড়ি ট্র্যাকিং শুরু করতে আপনার তথ্য দিন</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    if($_GET['error'] == 'phone_exists') echo "এই ফোন নম্বরটি আগে ব্যবহার করা হয়েছে!";
                    elseif($_GET['error'] == 'db_error') echo "ডাটাবেস এরর!";
                    elseif($_GET['error'] == 'password_mismatch') echo "পাসওয়ার্ড দুটি মেলেনি!";
                    else echo "সবগুলো ঘর সঠিকভাবে পূরণ করুন।";
                ?>
            </div>
        <?php endif; ?>

        <form action="auth_process.php" method="POST" autocomplete="off">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label>আপনার নাম</label>
                <input type="text" name="name" id="reg_name" class="form-control" placeholder="যেমন: মোঃ সাকিব হাসান" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>ফোন নম্বর</label>
                <input type="tel" name="phone" id="reg_phone" class="form-control" placeholder="০১৭১xxxxxxx" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="reg_pass" class="form-control" placeholder="কমপক্ষে ৬ ক্যারেক্টার" required minlength="6" autocomplete="new-password">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('reg_pass', this)"></i>
                </div>
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড নিশ্চিত করুন</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="reg_confirm_pass" class="form-control" placeholder="আবার পাসওয়ার্ড দিন" required minlength="6" autocomplete="new-password">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('reg_confirm_pass', this)"></i>
                </div>
            </div>
            <button type="submit" class="btn-auth">রেজিস্ট্রেশন করুন</button>
        </form>

        <div class="auth-footer">
            ইতিমধ্যেই অ্যাকাউন্ট আছে? <a href="login.php">লগইন করুন</a>
        </div>
    </div>
    <script src="auth.js"></script>
</body>
</html>