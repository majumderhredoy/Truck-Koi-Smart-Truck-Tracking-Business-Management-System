<?php
header('Content-Type: application/json');
require_once '../init.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$user_id = $_SESSION['user_id'];
$t_name = trim($input['truck_name'] ?? '');
$t_plate = trim($input['plate_number'] ?? '');
$d_id = !empty($input['driver_id']) ? (int)$input['driver_id'] : null;

if (empty($t_name) || empty($t_plate)) {
    echo json_encode(['success' => false, 'message' => 'গাড়ির নাম এবং প্লেট নম্বর প্রদান করুন']);
    exit();
}

// Check if driver is free
$check = $conn->query("SELECT id FROM drivers WHERE id = $d_id AND user_id = $user_id AND truck_id IS NULL");
if ($check->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'নির্বাচিত ড্রাইভারটি ইতিপূর্বেই অন্য কোনো গাড়িতে নিযুক্ত আছেন অথবা অকার্যকর']);
    exit();
}

// Transaction
$conn->begin_transaction();

try {
    $truck_image = null;
    if (isset($_FILES['truck_photo']) && $_FILES['truck_photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['truck_photo']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('truck_') . '.' . $ext;
        $upload_path = '../uploads/trucks/' . $new_name;
        if (move_uploaded_file($_FILES['truck_photo']['tmp_name'], $upload_path)) {
            $truck_image = 'uploads/trucks/' . $new_name;
        }
    }

    // 1. Insert Truck
    if ($d_id) {
        $stmt1 = $conn->prepare("INSERT INTO trucks (user_id, driver_id, name, truck_name, plate_number, truck_image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("iissss", $user_id, $d_id, $t_name, $t_name, $t_plate, $truck_image);
    } else {
        $stmt1 = $conn->prepare("INSERT INTO trucks (user_id, name, truck_name, plate_number, truck_image) VALUES (?, ?, ?, ?, ?)");
        $stmt1->bind_param("issss", $user_id, $t_name, $t_name, $t_plate, $truck_image);
    }
    
    if (!$stmt1->execute()) throw new Exception("গাড়ি সংরক্ষণে সমস্যা: " . $conn->error);
    
    $truck_id = $conn->insert_id;

    // 2. Update Driver mapping (only if driver provided)
    if ($d_id) {
        $stmt2 = $conn->prepare("UPDATE drivers SET truck_id = ? WHERE id = ?");
        $stmt2->bind_param("ii", $truck_id, $d_id);
        if (!$stmt2->execute()) throw new Exception("ড্রাইভার ম্যাপিং আপডেট করতে সমস্যা: " . $conn->error);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'গাড়ি সফলভাবে যুক্ত হয়েছে' . ($d_id ? ' এবং ড্রাইভার নিযুক্ত হয়েছে!' : '!')]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
