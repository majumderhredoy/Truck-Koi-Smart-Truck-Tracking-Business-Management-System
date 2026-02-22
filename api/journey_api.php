<?php
header('Content-Type: application/json');
require_once '../init.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    // Check for active journey
    if ($action === 'check_active') {
        $truck_id = (int)$_GET['truck_id'];
        $sql = "SELECT id, start_time FROM journeys WHERE truck_id = $truck_id AND user_id = $user_id AND status = 'active' LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            echo json_encode(['success' => true, 'active' => true, 'journey' => $result->fetch_assoc()]);
        } else {
            echo json_encode(['success' => true, 'active' => false]);
        }
    }
    
    // Get history list
    elseif ($action === 'get_history') {
        $truck_id = (int)$_GET['truck_id'];
        $sql = "SELECT * FROM journeys WHERE truck_id = $truck_id AND user_id = $user_id ORDER BY start_time DESC";
        $result = $conn->query($sql);
        $data = [];
        while($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
    }

    // Get specific journey path
    elseif ($action === 'get_path') {
        $journey_id = (int)$_GET['journey_id'];
        $sql = "SELECT lat, lng, created_at, speed FROM location_history WHERE journey_id = $journey_id ORDER BY created_at ASC";
        $result = $conn->query($sql);
        $data = [];
        while($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
    }

    // New: Get Financial Summary
    elseif ($action === 'get_finance_summary') {
        $today_sql = "SELECT SUM(net_revenue) as today_profit FROM journeys 
                      WHERE user_id = $user_id AND status = 'completed' AND DATE(end_time) = CURDATE()";
        $month_sql = "SELECT SUM(net_revenue) as month_profit, SUM(fuel_cost + driver_bill + helper_bill) as month_expense 
                      FROM journeys 
                      WHERE user_id = $user_id AND status = 'completed' AND end_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $today = $conn->query($today_sql)->fetch_assoc();
        $month = $conn->query($month_sql)->fetch_assoc();
        
        echo json_encode([
            'success' => true, 
            'today_profit' => (float)($today['today_profit'] ?? 0),
            'month_profit' => (float)($month['month_profit'] ?? 0),
            'month_expense' => (float)($month['month_expense'] ?? 0)
        ]);
    }

    // New: Get All Trips with Finance
    elseif ($action === 'get_all_trips') {
        $sql = "SELECT j.*, t.name as truck_name, t.plate_number 
                FROM journeys j 
                JOIN trucks t ON j.truck_id = t.id 
                WHERE j.user_id = $user_id 
                ORDER BY j.start_time DESC LIMIT 50";
        $result = $conn->query($sql);
        $data = [];
        while($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
    }
    exit();
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;
    $action = $input['action'] ?? '';

    if ($action === 'start_journey') {
        $truck_id = (int)$input['truck_id'];
        $start_loc = $conn->real_escape_string($input['start_location'] ?? 'Unknown');
        
        // Ensure no other active journey for this truck
        $conn->query("UPDATE journeys SET status = 'completed', end_time = NOW() WHERE truck_id = $truck_id AND user_id = $user_id AND status = 'active'");
        
        $sql = "INSERT INTO journeys (truck_id, user_id, status, start_location) VALUES ($truck_id, $user_id, 'active', '$start_loc')";
        if ($conn->query($sql)) {
            $journey_id = $conn->insert_id;
            // Update truck status
            $conn->query("UPDATE trucks SET status = 'running' WHERE id = $truck_id AND user_id = $user_id");
            echo json_encode(['success' => true, 'message' => 'যাত্রা শুরু হয়েছে', 'journey_id' => $journey_id]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
    }

    elseif ($action === 'end_journey') {
        $journey_id = (int)$input['journey_id'];
        $truck_id = (int)$input['truck_id'];
        $end_loc = $conn->real_escape_string($input['end_location'] ?? 'Unknown');
        
        $sql = "UPDATE journeys SET status = 'completed', end_time = NOW(), end_location = '$end_loc' WHERE id = $journey_id AND user_id = $user_id";
        if ($conn->query($sql)) {
            $conn->query("UPDATE trucks SET status = 'idle' WHERE id = $truck_id AND user_id = $user_id");
            echo json_encode(['success' => true, 'message' => 'যাত্রা শেষ হয়েছে']);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
    }

    elseif ($action === 'update_finance') {
        $journey_id = (int)$input['journey_id'];
        $fuel = (float)($input['fuel_cost'] ?? 0);
        $driver = (float)($input['driver_bill'] ?? 0);
        $helper = (float)($input['helper_bill'] ?? 0);
        $rent = (float)($input['rent_amount'] ?? 0);
        
        // Calculate Net Revenue
        $net = $rent - ($fuel + $driver + $helper);
        
        $sql = "UPDATE journeys SET 
                fuel_cost = $fuel, 
                driver_bill = $driver, 
                helper_bill = $helper, 
                rent_amount = $rent, 
                net_revenue = $net 
                WHERE id = $journey_id AND user_id = $user_id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'আয়-ব্যয় সফলভাবে আপডেট করা হয়েছে']);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
    }

    elseif ($action === 'update_location') {
        $journey_id = (int)$input['journey_id'];
        $truck_id = (int)$input['truck_id'];
        $lat = (float)$input['lat'];
        $lng = (float)$input['lng'];
        $speed = (int)($input['speed'] ?? 0);
        $loc_name = $conn->real_escape_string($input['location_name'] ?? '');

        $sql = "INSERT INTO location_history (journey_id, truck_id, lat, lng, location_name, speed, status) 
                VALUES ($journey_id, $truck_id, $lat, $lng, '$loc_name', $speed, 'running')";
        
        // Also update the current truck position for live view
        $conn->query("UPDATE trucks SET lat = $lat, lng = $lng, speed = $speed, location = '$loc_name' WHERE id = $truck_id AND user_id = $user_id");
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
    }

    elseif ($action === 'delete_trip') {
        $journey_id = (int)$input['journey_id'];
        
        // Transactional delete: remove location history first
        $conn->begin_transaction();
        try {
            $conn->query("DELETE FROM location_history WHERE journey_id = $journey_id");
            $conn->query("DELETE FROM journeys WHERE id = $journey_id AND user_id = $user_id");
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'ট্রিপটি সফলভাবে মুছে ফেলা হয়েছে']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'মুছে ফেলা সম্ভব হয়নি']);
        }
    }
    exit();
}
