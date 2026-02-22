<?php
require_once 'init.php';

echo "STARTING FIX...\n";

// 1. Force complete ALL active journeys
$conn->query("UPDATE journeys SET status = 'completed', end_time = NOW(), end_location = 'ঢাকা টার্মিনাল' WHERE status = 'active'");
echo "Completed: " . $conn->affected_rows . "\n";
if ($conn->error) echo "Error Q1: " . $conn->error . "\n";

// 2. Set dummy data for all completed journeys
$conn->query("UPDATE journeys SET 
    rent_amount = 25000, 
    fuel_cost = 6000, 
    driver_bill = 2000, 
    helper_bill = 1000, 
    net_revenue = 16000 
    WHERE status = 'completed'");
echo "Financed: " . $conn->affected_rows . "\n";
if ($conn->error) echo "Error Q2: " . $conn->error . "\n";

// 3. Verify counts
$res = $conn->query("SELECT status, COUNT(*) as count FROM journeys GROUP BY status");
while($row = $res->fetch_assoc()) {
    echo $row['status'] . ": " . $row['count'] . "\n";
}
?>
