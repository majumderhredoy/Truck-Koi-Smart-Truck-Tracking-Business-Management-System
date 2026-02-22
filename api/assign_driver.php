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

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$user_id = $_SESSION['user_id'];
$truck_id = !empty($input['truck_id']) ? (int)$input['truck_id'] : null;
$driver_id = !empty($input['driver_id']) ? (int)$input['driver_id'] : null;

if (empty($truck_id) || empty($driver_id)) {
    echo json_encode(['success' => false, 'message' => 'গাড়ি এবং ড্রাইভার উভয়ই নির্বাচন করুন']);
    exit();
}

// 1. Check if driver is free
$check_driver = $conn->query("SELECT id FROM drivers WHERE id = $driver_id AND user_id = $user_id AND truck_id IS NULL");
if ($check_driver->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'নির্বাচিত ড্রাইভারটি ইতোমধ্যেই অন্য কোনো গাড়িতে নিযুক্ত বা অকার্যকর']);
    exit();
}

// 2. Check if truck exists and is free (has no driver assigned in trucks table)
$check_truck = $conn->query("SELECT id FROM trucks WHERE id = $truck_id AND user_id = $user_id AND (driver_id IS NULL OR driver_id = 0)");
if ($check_truck->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'এই গাড়িটিতে ইতোমধ্যেই একজন ড্রাইভার নিযুক্ত রয়েছে']);
    exit();
}

// Transaction
$conn->begin_transaction();

try {
    // 1. Update Truck to point to Driver
    $stmt1 = $conn->prepare("UPDATE trucks SET driver_id = ? WHERE id = ?");
    $stmt1->bind_param("ii", $driver_id, $truck_id);
    if (!$stmt1->execute()) throw new Exception("গাড়ি আপডেট করতে সমস্যা: " . $conn->error);

    // 2. Update Driver to point to Truck
    $stmt2 = $conn->prepare("UPDATE drivers SET truck_id = ? WHERE id = ?");
    $stmt2->bind_param("ii", $truck_id, $driver_id);
    if (!$stmt2->execute()) throw new Exception("ড্রাইভার আপডেট করতে সমস্যা: " . $conn->error);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'ড্রাইভার সফলভাবে নিযুক্ত হয়েছে!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
