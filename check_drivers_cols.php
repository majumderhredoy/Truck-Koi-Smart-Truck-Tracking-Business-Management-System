<?php
require_once 'init.php';
$res = $conn->query("SHOW COLUMNS FROM drivers");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
