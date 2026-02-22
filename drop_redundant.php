<?php
require_once 'init.php';

// First, check if it exists
$check = $conn->query("SHOW COLUMNS FROM trucks LIKE 'driver_name'");
if ($check->num_rows > 0) {
    if ($conn->query("ALTER TABLE trucks DROP COLUMN driver_name")) {
        echo "SUCCESS: Dropped redundant 'driver_name' column from trucks table.\n";
    } else {
        echo "ERROR dropping column: " . $conn->error . "\n";
    }
} else {
    echo "INFO: 'driver_name' column already removed from trucks.\n";
}

// Also check for 'driver_phone' which might be redundant too
$check_phone = $conn->query("SHOW COLUMNS FROM trucks LIKE 'driver_phone'");
if ($check_phone->num_rows > 0) {
    $conn->query("ALTER TABLE trucks DROP COLUMN driver_phone");
    echo "SUCCESS: Dropped redundant 'driver_phone' column from trucks table.\n";
}
?>
