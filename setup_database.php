<?php
require_once 'init.php';

$message = '';

if (isset($_POST['setup'])) {
    // SQL to create Users table
    $users_sql = "CREATE TABLE IF NOT EXISTS `users` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL,
      `phone` VARCHAR(20) NOT NULL UNIQUE,
      `email` VARCHAR(100) DEFAULT NULL,
      `password` VARCHAR(255) NOT NULL,
      `profile_image` VARCHAR(255) DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    // SQL to create Trucks table
    $trucks_sql = "CREATE TABLE IF NOT EXISTS `trucks` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `name` VARCHAR(50) NOT NULL,
      `plate_number` VARCHAR(50) NOT NULL,
      `driver_name` VARCHAR(100) NOT NULL,
      `driver_phone` VARCHAR(20) DEFAULT NULL,
      `gps_device_id` VARCHAR(50) DEFAULT NULL,
      `brand` VARCHAR(50) DEFAULT NULL,
      `speed` INT DEFAULT 0,
      `fuel` INT DEFAULT 100,
      `location` VARCHAR(255) DEFAULT NULL,
      `lat` DECIMAL(10, 8) DEFAULT 23.8103,
      `lng` DECIMAL(11, 8) DEFAULT 90.4125,
      `status` ENUM('running', 'idle', 'stopped') DEFAULT 'idle',
      `logo_image` VARCHAR(255) DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX `idx_user` (`user_id`),
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if ($conn->query($users_sql) === TRUE) {
        $message .= "<div class='success'>✅ 'users' টেবিল সফলভাবে তৈরি হয়েছে।</div>";
    } else {
        $message .= "<div class='error'>❌ 'users' টেবিল তৈরি করতে সমস্যা: " . $conn->error . "</div>";
    }

    if ($conn->query($trucks_sql) === TRUE) {
        $message .= "<div class='success'>✅ 'trucks' টেবিল সফলভাবে তৈরি হয়েছে।</div>";
    } else {
        $message .= "<div class='error'>❌ 'trucks' টেবিল তৈরি করতে সমস্যা: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ডাটাবেস সেটআপ — ট্রাক কই</title>
    <style>
        body { font-family: 'Hind Siliguri', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 500px; text-align: center; }
        h2 { color: #333; margin-top: 0; }
        p { color: #666; margin-bottom: 2rem; }
        button { padding: 1rem 2rem; background: #DC143C; color: white; border: none; border-radius: 6px; font-size: 1.1rem; cursor: pointer; font-family: inherit; font-weight: 600; }
        button:hover { background: #b71c1c; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 10px; text-align: left; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 10px; text-align: left; }
        a { color: #DC143C; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>ডাটাবেস সেটআপ উইজার্ড</h2>
        <p>এই টুলটি আপনার 'tracker' ডাটাবেসে প্রয়োজনীয় টেবিলগুলো (users, trucks) অটোমেটিক তৈরি করে দিবে।</p>
        
        <?php echo $message; ?>
        
        <?php if (empty($message)): ?>
            <form method="POST">
                <button type="submit" name="setup">ডাটাবেস সেটআপ করুন</button>
            </form>
        <?php else: ?>
            <p style="margin-top: 20px;">
                এখন আপনি <a href="register.php">রেজিস্ট্রেশন</a> বা <a href="login.php">লগইন</a> করতে পারবেন।
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
