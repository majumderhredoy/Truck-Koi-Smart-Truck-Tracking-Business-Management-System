<?php
header('Content-Type: application/json');
require_once '../init.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Handle POST (Update Profile)
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? '';

    if ($action === 'update_profile') {
        $name = $conn->real_escape_string($input['name'] ?? '');
        $email = $conn->real_escape_string($input['email'] ?? '');
        $phone = $conn->real_escape_string($input['phone'] ?? '');
        $profile_image = null;

        if (empty($name) || empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'নাম এবং ফোন নম্বর প্রয়োজন']);
            exit();
        }

        // Handle File Upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_photo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {
                $new_name = "profile_" . $user_id . "_" . time() . "." . $ext;
                $upload_path = "../uploads/profile/" . $new_name;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $profile_image = "uploads/profile/" . $new_name;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'শুধুমাত্র JPG, PNG এবং WEBP ফরম্যাট অ্যালাউড']);
                exit();
            }
        }

        $image_sql = $profile_image ? ", profile_image = '$profile_image'" : "";
        $sql = "UPDATE users SET name = '$name', email = '$email', phone = '$phone' $image_sql WHERE id = $user_id";
        
        if ($conn->query($sql)) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_phone'] = $phone;
            if ($profile_image) $_SESSION['user_avatar'] = $profile_image;
            
            echo json_encode(['success' => true, 'message' => 'প্রোফাইল সফলভাবে আপডেট করা হয়েছে']);
        } else {
            echo json_encode(['success' => false, 'message' => 'আপডেট করতে সমস্যা হয়েছে: ' . $conn->error]);
        }
    }

    elseif ($action === 'update_password') {
        $current_pass = $input['current_password'] ?? '';
        $new_pass = $input['new_password'] ?? '';

        if (empty($current_pass) || empty($new_pass)) {
            echo json_encode(['success' => false, 'message' => 'সবগুলো ঘর পূরণ করুন']);
            exit();
        }

        // Verify current password
        $sql = "SELECT password FROM users WHERE id = $user_id";
        $user = $conn->query($sql)->fetch_assoc();

        if (password_verify($current_pass, $user['password'])) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = '$hashed_pass' WHERE id = $user_id";
            if ($conn->query($update_sql)) {
                echo json_encode(['success' => true, 'message' => 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে']);
            } else {
                echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড পরিবর্তনে সমস্যা হয়েছে']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'বর্তমান পাসওয়ার্ডটি ভুল']);
        }
    }
    exit();
}
?>
