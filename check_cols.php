<?php
require_once 'init.php';
$result = $conn->query("SHOW COLUMNS FROM trucks");
echo "<h3>Trucks Columns:</h3><ul>";
while($row = $result->fetch_assoc()) echo "<li>" . $row['Field'] . "</li>";
echo "</ul>";
?>
