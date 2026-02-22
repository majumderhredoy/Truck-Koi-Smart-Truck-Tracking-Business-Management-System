<?php
require_once 'init.php';
$table = 'trucks';
$result = $conn->query("SHOW COLUMNS FROM $table");
echo "<h3>Columns in '$table' table:</h3>";
echo "<ul>";
while($row = $result->fetch_assoc()) {
    echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
}
echo "</ul>";

$table2 = 'drivers';
$result2 = $conn->query("SHOW COLUMNS FROM $table2");
echo "<h3>Columns in '$table2' table:</h3>";
echo "<ul>";
while($row = $result2->fetch_assoc()) {
    echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
}
echo "</ul>";
?>
