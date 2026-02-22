<?php
require_once 'init.php';

// Mark some journeys as completed if they are old
$conn->query("UPDATE journeys SET status = 'completed', end_time = NOW(), end_location = 'ঢাকা টার্মিনাল' 
              WHERE status = 'active' AND id < 5");

// Add dummy data to completed journeys
$sql = "UPDATE journeys SET 
        fuel_cost = 5000 + (id * 100), 
        driver_bill = 1500, 
        helper_bill = 800, 
        rent_amount = 15000 + (id * 500), 
        net_revenue = (15000 + (id * 500)) - (5000 + (id * 100) + 1500 + 800)
        WHERE status = 'completed'";

if ($conn->query($sql)) {
    echo "Dummy data updated successfully. Total rows affected: " . $conn->affected_rows;
} else {
    echo "Error: " . $conn->error;
}
?>
