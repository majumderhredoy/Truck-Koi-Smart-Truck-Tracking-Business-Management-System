<?php
require_once 'init.php';
echo "Current Session User ID: " . ($_SESSION['user_id'] ?? 'NONE') . "\n";
$res = $conn->query("SELECT id, name, username FROM users");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
