<?php
require_once 'init.php';

echo "Hashing plain-text passwords...\n";

$sql = "SELECT id, password FROM users";
$result = $conn->query($sql);

$updated = 0;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $pass = $row['password'];
        
        // Check if it's already hashed (bcrypt hashes start with $2y$)
        if (substr($pass, 0, 4) !== '$2y$') {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $update = "UPDATE users SET password = '$hashed' WHERE id = $id";
            if ($conn->query($update)) {
                echo "Updated user ID $id\n";
                $updated++;
            } else {
                echo "Failed to update user ID $id: " . $conn->error . "\n";
            }
        }
    }
}

echo "Total passwords updated: $updated\n";
?>
