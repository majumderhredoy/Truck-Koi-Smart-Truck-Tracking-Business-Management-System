<?php
require_once '../init.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch trucks for logged in user
$sql = "SELECT * FROM trucks WHERE user_id = $user_id";
$result = $conn->query($sql);

$trucks = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Format for frontend
        $truck_key = 'truck' . $row['id']; // Or just use array
        // We will return a list, but frontend currently expects an object with keys truck1, truck2 etc.
        // Let's adapt to return a list and update frontend, OR map to keys.
        // Let's return a clean list and update frontend to use list.
        $trucks[] = $row;
    }
}

echo json_encode(['trucks' => $trucks]);
?>
