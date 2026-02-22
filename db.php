<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tracker');

// Establish database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for Bangla support
$conn->set_charset("utf8mb4");

// Site constants
define('SITE_NAME', 'ট্রাক কই');
define('SITE_URL', 'http://localhost/tracker');

// Timezone
date_default_timezone_set('Asia/Dhaka');
?>
