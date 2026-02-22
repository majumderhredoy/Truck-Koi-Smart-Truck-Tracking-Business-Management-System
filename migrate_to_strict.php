<?php
require_once 'init.php';

echo "<h2>Migrating to Strict 1-to-1 Schema...</h2>";

// 1. Update Drivers Table
$updates_drivers = [
    "ALTER TABLE drivers CHANGE COLUMN name driver_name VARCHAR(100) NOT NULL",
    "ALTER TABLE drivers CHANGE COLUMN phone phone_number VARCHAR(20) NOT NULL",
    "ALTER TABLE drivers CHANGE COLUMN license_no license_number VARCHAR(50) NOT NULL",
    "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS truck_id INT UNIQUE DEFAULT NULL"
];

foreach ($updates_drivers as $sql) {
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>SUCCESS: $sql</p>";
    } else {
        echo "<p style='color:orange;'>INFO/ERROR: " . $conn->error . " (SQL: $sql)</p>";
    }
}

// 2. Update Trucks Table
$updates_trucks = [
    "ALTER TABLE trucks CHANGE COLUMN name truck_name VARCHAR(50) NOT NULL",
    "ALTER TABLE trucks ADD COLUMN IF NOT EXISTS driver_id INT UNIQUE DEFAULT NULL",
    "ALTER TABLE trucks ADD UNIQUE INDEX IF NOT EXISTS idx_plate_unique (plate_number)"
];

foreach ($updates_trucks as $sql) {
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>SUCCESS: $sql</p>";
    } else {
        echo "<p style='color:orange;'>INFO/ERROR: " . $conn->error . " (SQL: $sql)</p>";
    }
}

// 3. Add Constraints
$constraints = [
    "ALTER TABLE trucks ADD CONSTRAINT fk_truck_driver_strict FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL",
    "ALTER TABLE drivers ADD CONSTRAINT fk_driver_truck_strict FOREIGN KEY (truck_id) REFERENCES trucks(id) ON DELETE SET NULL"
];

foreach ($constraints as $sql) {
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>SUCCESS: $sql</p>";
    } else {
        echo "<p style='color:orange;'>INFO/ERROR: " . $conn->error . " (SQL: $sql)</p>";
    }
}

echo "<h3>Migration Finished. Please check your tables.</h3>";
?>
