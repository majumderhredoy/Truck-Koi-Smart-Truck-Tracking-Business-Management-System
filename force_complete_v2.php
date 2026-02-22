<?php
require_once 'init.php';

// Force complete ALL existing journeys for demo
$q1 = $conn->query("UPDATE journeys SET status = 'completed', end_time = NOW(), end_location = 'ঢাকা টার্মিনাল' WHERE status = 'active'");
$c1 = $conn->affected_rows;

// Add dummy data
$sql = "UPDATE journeys SET 
        fuel_cost = 4500 + (id * 200), 
        driver_bill = 2000, 
        helper_bill = 1000, 
        rent_amount = 20000 + (id * 1000), 
        net_revenue = (20000 + (id * 1000)) - (4500 + (id * 200) + 2000 + 1000)
        WHERE status = 'completed'";

$q2 = $conn->query($sql);
$c2 = $conn->affected_rows;

echo "Trips marked completed: $c1\n";
echo "Finance data updated: $c2\n";
?>
