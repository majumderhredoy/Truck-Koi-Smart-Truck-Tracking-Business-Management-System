<?php
header('Content-Type: application/json');
require_once '../init.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit();
}

// Read input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$user_id = $_SESSION['user_id'];
$t_name = trim($input['truck_name'] ?? '');
$t_number = trim($input['truck_number'] ?? '');
$d_name = trim($input['driver_name'] ?? '');
$d_phone = trim($input['driver_phone'] ?? '');

// Validation
if (empty($t_name) || empty($t_number) || empty($d_name) || empty($d_phone)) {
    echo json_encode(['success' => false, 'message' => 'সকল তথ্য প্রদান করা আবশ্যক']);
    exit();
}

// Start Transaction
$conn->begin_transaction();

try {
    // 1. Insert Truck
    $stmt1 = $conn->prepare("INSERT INTO trucks (user_id, name, truck_name, plate_number) VALUES (?, ?, ?, ?)");
    $stmt1->bind_param("isss", $user_id, $t_name, $t_name, $t_number);
    
    if (!$stmt1->execute()) {
        if ($conn->errno === 1062) {
            throw new Exception("এই প্লেট নম্বরটি ( $t_number ) ইতিপূর্বেই নিবন্ধিত রয়েছে");
        }
        throw new Exception("গাড়ি সংরক্ষণে সমস্যা হয়েছে: " . $conn->error);
    }
    
    $truck_id = $conn->insert_id;
    $stmt1->close();

    // 2. Insert Driver (Mapped to Truck)
    $stmt2 = $conn->prepare("INSERT INTO drivers (user_id, truck_id, driver_name, phone_number) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("iiss", $user_id, $truck_id, $d_name, $d_phone);
    
    if (!$stmt2->execute()) {
        throw new Exception("ড্রাইভার সংরক্ষণে সমস্যা হয়েছে: " . $conn->error);
    }
    $driver_id = $conn->insert_id;
    $stmt2->close();

    // 3. Update Truck with Driver ID (Circular Mapping)
    $conn->query("UPDATE trucks SET driver_id = $driver_id WHERE id = $truck_id");

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'গাড়ি এবং ড্রাইভার সফলভাবে যুক্ত হয়েছে',
        'data' => [
            'truck' => [
                'id' => $truck_id,
                'name' => $t_name,
                'plate_number' => $t_number,
                'driver_name' => $d_name,
                'location' => 'অজানা',
                'status' => 'idle'
            ],
            'driver' => [
                'id' => $driver_id,
                'name' => $d_name,
                'phone' => $d_phone,
                'truck_name' => $t_name
            ]
        ]
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
