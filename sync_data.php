<?php
require_once 'init.php';

$res = $conn->query("UPDATE trucks t JOIN drivers d ON t.id = d.truck_id SET t.driver_id = d.id WHERE (t.driver_id IS NULL OR t.driver_id = 0)");
if ($res) {
    echo "SUCCESS: Synced driver_id from drivers table.\n";
} else {
    echo "ERROR syncing: " . $conn->error . "\n";
}
?>
