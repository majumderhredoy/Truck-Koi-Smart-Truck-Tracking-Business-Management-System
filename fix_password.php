<?php
require_once 'init.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $conn->real_escape_string($_POST['phone']);
    $new_password = $_POST['new_password'];
    
    // Check if user exists
    $check_sql = "SELECT id FROM users WHERE phone = '$phone'";
    $result = $conn->query($check_sql);
    
    if ($result && $result->num_rows > 0) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update database
        $update_sql = "UPDATE users SET password = '$hashed_password' WHERE phone = '$phone'";
        
        if ($conn->query($update_sql)) {
            $message = "<div class='success'>পাসওয়ার্ড সফলভাবে রিসেট করা হয়েছে! এখন <a href='login.php'>লগইন করুন</a>।</div>";
        } else {
            $message = "<div class='error'>ডাটাবেস এরর: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='error'>এই ফোন নম্বরের কোনো ব্যবহারকারী পাওয়া যায়নি।</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পাসওয়ার্ড ফিক্স — ট্রাক কই</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; margin-top: 0; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #666; }
        input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background: #DC143C; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #b71c1c; }
        .success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; text-align: center; }
        a { color: #DC143C; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>পাসওয়ার্ড রিসেট</h2>
        <?php echo $message; ?>
        <form method="POST">
            <div class="form-group">
                <label>ফোন নম্বর</label>
                <input type="text" name="phone" placeholder="০১৭১xxxxxxx" required>
            </div>
            <div class="form-group">
                <label>নতুন পাসওয়ার্ড</label>
                <input type="text" name="new_password" placeholder="নতুন পাসওয়ার্ড দিন" required>
            </div>
            <button type="submit">পাসওয়ার্ড সেট করুন</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;"><a href="login.php">লগইন পেজে ফিরে যান</a></p>
    </div>
</body>
</html>
