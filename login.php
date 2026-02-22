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
    <title>লগইন — ট্রাক কই</title>
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
            <h1 class="auth-title">ফিরে আসা স্বাগতম</h1>
            <p class="auth-subtitle">আপনার প্রোফাইলে লগইন করুন</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    if($_GET['error'] == 'invalid_password') echo "পাসওয়ার্ড ভুল!";
                    elseif($_GET['error'] == 'user_not_found') echo "ব্যবহারকারী পাওয়া যায়নি!";
                    else echo "কিছু সমস্যা হয়েছে, আবার চেষ্টা করুন।";
                ?>
            </div>
        <?php endif; ?>

        <form action="auth_process.php" method="POST" autocomplete="off">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label>ফোন নম্বর</label>
                <input type="tel" name="phone" id="login_phone" class="form-control" placeholder="০১৭১xxxxxxx" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="login_pass" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('login_pass', this)"></i>
                </div>
            </div>
            <button type="submit" class="btn-auth">লগইন করুন</button>
        </form>

        <div class="auth-footer">
            অ্যাকাউন্ট নেই? <a href="register.php">নিবন্ধন করুন</a>
        </div>
    </div>
    <script src="auth.js"></script>
</body>
</html>