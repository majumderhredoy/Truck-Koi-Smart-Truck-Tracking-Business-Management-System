<?php
header('Content-Type: application/json');
require_once '../init.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Handle GET (Read)
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $sql = "SELECT d.*, t.truck_name 
                FROM drivers d 
                LEFT JOIN trucks t ON d.truck_id = t.id 
                WHERE d.id = $id AND d.user_id = $user_id";
    } else {
        $sql = "SELECT d.*, t.truck_name 
                FROM drivers d 
                LEFT JOIN trucks t ON d.truck_id = t.id 
                WHERE d.user_id = $user_id 
                ORDER BY d.created_at DESC";
    }
    
    $result = $conn->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => isset($_GET['id']) ? ($data[0] ?? null) : $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    exit();
}

// Handle Write Actions (POST)
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $action = $input['action'] ?? '';

    // ADD NEW DRIVER
    if ($action === 'add') {
        $name = $conn->real_escape_string($input['driver_name'] ?? $input['name'] ?? '');
        $phone = $conn->real_escape_string($input['phone_number'] ?? $input['phone'] ?? '');
        $license = $conn->real_escape_string($input['license_number'] ?? $input['license_no'] ?? '');
        $expiry = $conn->real_escape_string($input['license_expiry'] ?? '');
        $truck_id = !empty($input['truck_id']) ? (int)$input['truck_id'] : null;
        $driver_image = null;

        if (empty($name) || empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'নাম এবং ফোন নম্বর প্রয়োজন']);
            exit();
        }

        // Handle Photo Upload
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

        $truck_val = $truck_id ? $truck_id : "NULL";
        $img_val = $driver_image ? "'$driver_image'" : "NULL";
        
        $sql = "INSERT INTO drivers (user_id, truck_id, driver_name, phone_number, license_number, license_expiry, driver_image) 
                VALUES ($user_id, $truck_val, '$name', '$phone', '$license', '$expiry', $img_val)";
                
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'ড্রাইভার সফলভাবে যুক্ত হয়েছে', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'তৈরি করতে সমস্যা হয়েছে: ' . $conn->error]);
        }
    }

    // UPDATE DRIVER
    elseif ($action === 'update') {
        $id = (int)$input['id'];
        $name = $conn->real_escape_string($input['driver_name'] ?? $input['name'] ?? '');
        $phone = $conn->real_escape_string($input['phone_number'] ?? $input['phone'] ?? '');
        $license = $conn->real_escape_string($input['license_number'] ?? $input['license_no'] ?? '');
        $expiry = $conn->real_escape_string($input['license_expiry'] ?? '');
        $truck_id = !empty($input['truck_id']) ? (int)$input['truck_id'] : null;
        $driver_image = null;

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID missing']);
            exit();
        }

        // Handle Photo Upload
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

        $truck_val = $truck_id ? $truck_id : "NULL";
        $img_sql = $driver_image ? ", driver_image = '$driver_image'" : "";
        
        $sql = "UPDATE drivers SET 
                driver_name = '$name', 
                phone_number = '$phone', 
                license_number = '$license', 
                license_expiry = '$expiry', 
                truck_id = $truck_val 
                $img_sql
                WHERE id = $id AND user_id = $user_id";
                
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'তথ্য আপডেট করা হয়েছে']);
        } else {
            echo json_encode(['success' => false, 'message' => 'আপডেট করতে সমস্যা হয়েছে: ' . $conn->error]);
        }
    }

    // DELETE DRIVER
    elseif ($action === 'delete') {
        $id = (int)$input['id'];
        $sql = "DELETE FROM drivers WHERE id = $id AND user_id = $user_id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'ড্রাইভার মুছে ফেলা হয়েছে']);
        } else {
            echo json_encode(['success' => false, 'message' => 'মুছে ফেলতে সমস্যা হয়েছে: ' . $conn->error]);
        }
    }
    exit();
}
