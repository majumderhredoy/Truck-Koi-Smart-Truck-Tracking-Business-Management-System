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
$name = trim($input['driver_name'] ?? '');
$phone = trim($input['phone_number'] ?? '');
$license = trim($input['license_number'] ?? '');
$expiry = trim($input['license_expiry'] ?? '');

if (empty($name) || empty($phone) || empty($license)) {
    echo json_encode(['success' => false, 'message' => 'নাম, ফোন এবং লাইসেন্স নম্বর আবশ্যক']);
    exit();
}

// Handle Photo Upload
$driver_image = null;
if (isset($_FILES['driver_photo']) && $_FILES['driver_photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['driver_photo'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed)) {
        $new_name = "driver_" . time() . "_" . $user_id . "." . $ext;
        if (move_uploaded_file($file['tmp_name'], "../uploads/drivers/" . $new_name)) {
            $driver_image = "uploads/drivers/" . $new_name;
        }
    }
}

// Simple Insert for Driver (Initially without truck)
$stmt = $conn->prepare("INSERT INTO drivers (user_id, driver_name, phone_number, license_number, license_expiry, driver_image) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $user_id, $name, $phone, $license, $expiry, $driver_image);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'ড্রাইভার সফলভাবে যুক্ত হয়েছে',
        'id' => $conn->insert_id
    ]);
} else {
    if ($conn->errno === 1062) {
        echo json_encode(['success' => false, 'message' => 'এই লাইসেন্স নম্বরটি ইতিপূর্বেই নিবন্ধিত রয়েছে']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ত্রুটি: ' . $conn->error]);
    }
}
$stmt->close();
?>
