<?php
require_once 'init.php';
header('Content-Type: text/plain');

echo "--- JOURNEYS SCHEMA ---\n";
$res = $conn->query("DESCRIBE journeys");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n--- TRUCKS COUNT ---\n";
$res = $conn->query("SELECT COUNT(*) as count FROM trucks");
echo "Total Trucks: " . $res->fetch_assoc()['count'] . "\n";

echo "\n--- JOURNEYS COUNT ---\n";
$res = $conn->query("SELECT COUNT(*) as count FROM journeys");
echo "Total Journeys: " . $res->fetch_assoc()['count'] . "\n";

echo "\n--- ACTIVE JOURNEYS ---\n";
$res = $conn->query("SELECT * FROM journeys WHERE status = 'active'");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
