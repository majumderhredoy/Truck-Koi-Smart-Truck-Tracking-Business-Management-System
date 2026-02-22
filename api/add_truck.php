<?php
header('Content-Type: application/json');
require_once '../init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read input (handles both JSON and Form Data)
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $truck_name = trim($input['truck_name'] ?? '');
    $truck_number = trim($input['truck_number'] ?? '');
    $driver_name = trim($input['driver_name'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validation
    if (empty($truck_name) || empty($truck_number)) {
        echo json_encode(['status' => 'error', 'message' => 'গাড়ির নাম এবং প্লেট নম্বর প্রদান করুন']);
        exit();
    }

    // Prepared Statement for SQL Injection protection
    $stmt = $conn->prepare("INSERT INTO trucks (user_id, name, plate_number, driver_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $truck_name, $truck_number, $driver_name);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        echo json_encode([
            'status' => 'success', 
            'message' => 'Truck added successfully',
            'data' => [
                'id' => $new_id,
                'name' => $truck_name,
                'plate_number' => $truck_number,
                'driver_name' => $driver_name,
                'status' => 'idle',
                'location' => 'অজানা'
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ডাটাবেস ত্রুটি: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
