<?php
require_once 'init.php';

// Force complete ALL existing journeys for demo
$conn->query("UPDATE journeys SET status = 'completed', end_time = NOW(), end_location = 'ঢাকা টার্মিনাল' WHERE status = 'active'");

// Add dummy data
$sql = "UPDATE journeys SET 
        fuel_cost = 4500 + (id * 200), 
        driver_bill = 2000, 
        helper_bill = 1000, 
        rent_amount = 20000 + (id * 1000), 
        net_revenue = (20000 + (id * 1000)) - (4500 + (id * 200) + 2000 + 1000)
        WHERE status = 'completed'";

$conn->query($sql);
echo "Successfully completed " . $conn->affected_rows . " trips and added financial data.";
?>
