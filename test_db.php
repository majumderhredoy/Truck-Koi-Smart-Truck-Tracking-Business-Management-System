<?php
require_once 'init.php';

echo "Database Host: " . DB_HOST . "\n";
echo "Database Name: " . DB_NAME . "\n";

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "\n";
} else {
    echo "Connected successfully\n";
    
    $sql = "SELECT COUNT(*) as count FROM users";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "User count: " . $row['count'] . "\n";
        
        $sql = "SELECT id, name, phone FROM users LIMIT 5";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . ", Name: " . $row['name'] . ", Phone: " . $row['phone'] . "\n";
        }
    } else {
        echo "Query failed: " . $conn->error . "\n";
    }
}
?>
