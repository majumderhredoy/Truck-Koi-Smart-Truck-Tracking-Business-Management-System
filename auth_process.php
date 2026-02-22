<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // LOGIN HANDLING
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $phone = $conn->real_escape_string($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (!empty($phone) && !empty($password)) {
            // Sanitize phone: remove spaces, dashes, etc.
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            $sql = "SELECT id, name, phone, password, profile_image FROM users WHERE phone = '$phone'";
            $result = $conn->query($sql);
            
            if ($result) {
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        // Login Success
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_phone'] = $user['phone'];
                        $_SESSION['user_avatar'] = $user['profile_image'];
                        
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        // Invalid Password
                        header("Location: login.php?error=invalid_password");
                        exit();
                    }
                } else {
                    // User not found
                    header("Location: login.php?error=user_not_found");
                    exit();
                }
            } else {
                // Database error
                header("Location: login.php?error=db_error&detail=" . urlencode($conn->error));
                exit();
            }
        } else {
            header("Location: login.php?error=empty_fields");
            exit();
        }
    }
    
    // REGISTRATION HANDLING
    elseif (isset($_POST['action']) && $_POST['action'] == 'register') {
        $name = $conn->real_escape_string($_POST['name'] ?? '');
        $phone = $conn->real_escape_string($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $email = $conn->real_escape_string($_POST['email'] ?? '');
        
        if (!empty($name) && !empty($phone) && !empty($password)) {
            // Check if passwords match
            if ($password !== $confirm_password) {
                header("Location: register.php?error=password_mismatch");
                exit();
            }
            // Check if phone already exists
            $check = "SELECT id FROM users WHERE phone = '$phone'";
            if ($conn->query($check)->num_rows > 0) {
                header("Location: register.php?error=phone_exists");
                exit();
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert User
            $sql = "INSERT INTO users (name, phone, password, email) VALUES ('$name', '$phone', '$hashed_password', '$email')";
            
            if ($conn->query($sql) === TRUE) {
                $user_id = $conn->insert_id;
                
                // Login immediately
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_phone'] = $phone;
                
                // Seed some sample trucks for this new user
                $truck1 = "INSERT INTO trucks (user_id, name, plate_number, driver_name, speed, fuel, location, lat, lng, status, brand, gps_device_id) 
                          VALUES ($user_id, 'ট্রাক ১', 'ঢাকা · বিএ-১৪৩২', 'রহমান আ.', 42, 68, 'মতিঝিল, ঢাকা', 23.8103, 90.4125, 'running', 'TATA', 'TK-001')";
                
                $truck2 = "INSERT INTO trucks (user_id, name, plate_number, driver_name, speed, fuel, location, lat, lng, status, brand, gps_device_id) 
                          VALUES ($user_id, 'ট্রাক ২', 'ঢাকা · ১১-৫৮৭২', 'করিম এম.', 0, 45, 'মিরপুর, ঢাকা', 23.8223, 90.3654, 'idle', 'HINO', 'TK-002')";
                
                $truck3 = "INSERT INTO trucks (user_id, name, plate_number, driver_name, speed, fuel, location, lat, lng, status, brand, gps_device_id) 
                          VALUES ($user_id, 'ট্রাক ৩', 'চট্টগ্রাম · টিআর-০২৯১', 'হাসান আর.', 61, 15, 'তেজগাঁও, ঢাকা', 23.7639, 90.3889, 'running', 'ISUZU', 'TK-003')";
                          
                $conn->query($truck1);
                $conn->query($truck2);
                $conn->query($truck3);
                
                header("Location: dashboard.php");
                exit();
            } else {
                header("Location: register.php?error=db_error");
                exit();
            }
        } else {
             header("Location: register.php?error=missing_fields");
             exit();
        }
    }
}

// If accessed directly without POST
header("Location: login.php");
exit();
?>
