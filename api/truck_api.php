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
        $sql = "SELECT t.*, d.driver_name 
                FROM trucks t 
                LEFT JOIN drivers d ON t.driver_id = d.id 
                WHERE t.id = $id AND t.user_id = $user_id";
    } else {
        $sql = "SELECT t.*, d.driver_name 
                FROM trucks t 
                LEFT JOIN drivers d ON t.driver_id = d.id 
                WHERE t.user_id = $user_id 
                ORDER BY t.created_at DESC";
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
    // Read JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST; // Fallback to form data

    $action = $input['action'] ?? '';

    // ADD NEW TRUCK
    if ($action === 'add') {
        $name = $conn->real_escape_string($input['name'] ?? $input['truck_name'] ?? '');
        $plate = $conn->real_escape_string($input['plate_number'] ?? $input['plate'] ?? '');
        $driver_id = !empty($input['driver_id']) ? (int)$input['driver_id'] : null;
        $device_id = $conn->real_escape_string($input['device_id'] ?? '');
        $brand = $conn->real_escape_string($input['brand'] ?? '');

        if (empty($name) || empty($plate)) {
            echo json_encode(['success' => false, 'message' => 'গাড়ির নাম এবং প্লেট নম্বর প্রয়োজন']);
            exit();
        }

        $truck_image = null;
        if (isset($_FILES['truck_photo']) && $_FILES['truck_photo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['truck_photo']['name'], PATHINFO_EXTENSION);
            $new_name = uniqid('truck_') . '.' . $ext;
            $upload_path = '../uploads/trucks/' . $new_name;
            if (move_uploaded_file($_FILES['truck_photo']['tmp_name'], $upload_path)) {
                $truck_image = 'uploads/trucks/' . $new_name;
            }
        }

        $d_val = $driver_id ? $driver_id : "NULL";
        $img_val = $truck_image ? "'$truck_image'" : "NULL";
        
        $sql = "INSERT INTO trucks (user_id, name, truck_name, plate_number, driver_id, gps_device_id, brand, truck_image) 
                VALUES ($user_id, '$name', '$name', '$plate', $d_val, '$device_id', '$brand', $img_val)";
                
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'ট্রাক সফলভাবে যুক্ত হয়েছে', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'তৈরি করতে সমস্যা হয়েছে: ' . $conn->error]);
        }
    }

    // UPDATE TRUCK
    elseif ($action === 'update') {
        $id = (int)$input['id'];
        $name = $conn->real_escape_string($input['name'] ?? $input['truck_name'] ?? '');
        $plate = $conn->real_escape_string($input['plate_number'] ?? $input['plate'] ?? '');
        $driver_id = !empty($input['driver_id']) ? (int)$input['driver_id'] : null;
        $device_id = $conn->real_escape_string($input['device_id'] ?? '');
        $brand = $conn->real_escape_string($input['brand'] ?? '');

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID missing']);
            exit();
        }

        $truck_image = null;
        if (isset($_FILES['truck_photo']) && $_FILES['truck_photo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['truck_photo']['name'], PATHINFO_EXTENSION);
            $new_name = uniqid('truck_') . '.' . $ext;
            $upload_path = '../uploads/trucks/' . $new_name;
            if (move_uploaded_file($_FILES['truck_photo']['tmp_name'], $upload_path)) {
                $truck_image = 'uploads/trucks/' . $new_name;
            }
        }

        $d_val = $driver_id ? $driver_id : "NULL";
        $img_sql = $truck_image ? ", truck_image = '$truck_image'" : "";
        
        $sql = "UPDATE trucks SET 
                name = '$name', 
                truck_name = '$name', 
                plate_number = '$plate', 
                driver_id = $d_val, 
                gps_device_id = '$device_id', 
                brand = '$brand' 
                $img_sql
                WHERE id = $id AND user_id = $user_id";
                
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'তথ্য আপডেট করা হয়েছে']);
        } else {
            echo json_encode(['success' => false, 'message' => 'আপডেট করতে সমস্যা হয়েছে: ' . $conn->error]);
        }
    }

    // DELETE TRUCK
    elseif ($action === 'delete') {
        $id = (int)$input['id'];
        
        // Check if a driver is assigned to this truck
        $driver_check = $conn->query("SELECT id FROM drivers WHERE truck_id = $id");
        if ($driver_check->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'এই গাড়িটির সাথে একজন ড্রাইভার নিযুক্ত আছে। প্রথমে ড্রাইভারের ম্যাপিং পরিবর্তন করুন।']);
            exit();
        }

        $sql = "DELETE FROM trucks WHERE id = $id AND user_id = $user_id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'ট্রাক মুছে ফেলা হয়েছে']);
        } else {
            echo json_encode(['success' => false, 'message' => 'মুছে ফেলতে সমস্যা হয়েছে: ' . $conn->error]);
        }
    }
    exit();
}
?>
