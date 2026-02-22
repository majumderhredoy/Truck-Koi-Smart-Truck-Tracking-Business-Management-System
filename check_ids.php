<?php
require_once 'init.php';
$user_id = $_SESSION['user_id'] ?? 1;

echo "--- SESSION USER ID ---\n";
echo "User ID: " . $user_id . "\n";

echo "\n--- TRUCKS ---\n";
$res = $conn->query("SELECT id, name FROM trucks");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - " . $row['name'] . "\n";
}

echo "\n--- RECENT JOURNEYS (LEFT JOIN to see mismatches) ---\n";
$sql = "SELECT j.id as j_id, j.truck_id as jt_id, t.id as t_id, t.name 
        FROM journeys j 
        LEFT JOIN trucks t ON j.truck_id = t.id 
        WHERE j.user_id = $user_id";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
