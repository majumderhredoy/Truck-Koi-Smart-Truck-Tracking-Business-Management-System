<?php
require_once 'init.php';
$user_id = $_SESSION['user_id'] ?? 1;

echo "Current User: $user_id\n";

// Align all journeys to this user
$conn->query("UPDATE journeys SET user_id = $user_id");
echo "Aligned journeys: " . $conn->affected_rows . "\n";

// Align all trucks to this user (so they show up in dashboard/tracking)
$conn->query("UPDATE trucks SET user_id = $user_id");
echo "Aligned trucks: " . $conn->affected_rows . "\n";

// Set some completed data if not already
$conn->query("UPDATE journeys SET 
    status = 'completed', 
    end_time = NOW(), 
    rent_amount = 22000, 
    fuel_cost = 5000, 
    driver_bill = 1500, 
    helper_bill = 1000, 
    net_revenue = 14500 
    WHERE status = 'completed'");

echo "Demo records ready for User $user_id\n";
?>
