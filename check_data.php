<?php
require_once 'init.php';
$sql = "SELECT t.id, t.truck_name, t.driver_name as truck_table_driver, d.driver_name as drivers_table_driver 
        FROM trucks t 
        LEFT JOIN drivers d ON t.driver_id = d.id 
        LIMIT 5";
$result = $conn->query($sql);
echo "<table border='1'><tr><th>ID</th><th>Truck</th><th>In Truck Table</th><th>In Driver Table (Joined)</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['truck_name']}</td><td>{$row['truck_table_driver']}</td><td>{$row['drivers_table_driver']}</td></tr>";
}
echo "</table>";
?>
