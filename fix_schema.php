<?php
require_once 'init.php';

$columns_to_add = [
    'driver_id' => "INT DEFAULT NULL",
    'gps_device_id' => "VARCHAR(100) DEFAULT NULL",
    'brand' => "VARCHAR(100) DEFAULT NULL",
    'truck_name' => "VARCHAR(50) DEFAULT NULL"
];

foreach ($columns_to_add as $col => $definition) {
    $check = $conn->query("SHOW COLUMNS FROM trucks LIKE '$col'");
    if ($check->num_rows == 0) {
        if ($conn->query("ALTER TABLE trucks ADD COLUMN $col $definition")) {
            echo "SUCCESS: Added $col to trucks\n";
        } else {
            echo "ERROR adding $col: " . $conn->error . "\n";
        }
    } else {
        echo "INFO: $col already exists in trucks\n";
    }
}

// Check if 'name' exists, if so, copy it to 'truck_name' if empty
$check_name = $conn->query("SHOW COLUMNS FROM trucks LIKE 'name'");
if ($check_name->num_rows > 0) {
    $conn->query("UPDATE trucks SET truck_name = name WHERE truck_name IS NULL OR truck_name = ''");
}

echo "Schema fix attempt finished.\n";
?>
