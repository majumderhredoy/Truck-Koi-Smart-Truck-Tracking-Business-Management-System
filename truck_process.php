<?php
require_once 'init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];

    if ($action == 'add') {
        $name = $conn->real_escape_string($_POST['name'] ?? '');
        $plate = $conn->real_escape_string($_POST['plate'] ?? '');
        $driver = $conn->real_escape_string($_POST['driver'] ?? '');
        $phone = $conn->real_escape_string($_POST['phone'] ?? '');
        $brand = $conn->real_escape_string($_POST['brand'] ?? '');

        if (!empty($name) && !empty($plate)) {
            $sql = "INSERT INTO trucks (user_id, name, plate_number, driver_name, driver_phone, brand) 
                    VALUES ($user_id, '$name', '$plate', '$driver', '$phone', '$brand')";
            if ($conn->query($sql)) {
                header("Location: dashboard.php?success=truck_added");
            } else {
                header("Location: dashboard.php?error=db_error");
            }
        } else {
            header("Location: dashboard.php?error=missing_fields");
        }
        exit();
    }

    if ($action == 'edit') {
        $truck_id = (int)$_POST['truck_id'];
        $name = $conn->real_escape_string($_POST['name'] ?? '');
        $plate = $conn->real_escape_string($_POST['plate'] ?? '');
        $driver = $conn->real_escape_string($_POST['driver'] ?? '');
        $phone = $conn->real_escape_string($_POST['phone'] ?? '');
        $brand = $conn->real_escape_string($_POST['brand'] ?? '');

        if (!empty($name) && !empty($plate)) {
            $sql = "UPDATE trucks SET name = '$name', plate_number = '$plate', driver_name = '$driver', driver_phone = '$phone', brand = '$brand' 
                    WHERE id = $truck_id AND user_id = $user_id";
            if ($conn->query($sql)) {
                header("Location: dashboard.php?success=truck_updated");
            } else {
                header("Location: dashboard.php?error=db_error");
            }
        } else {
            header("Location: dashboard.php?error=missing_fields");
        }
        exit();
    }

    if ($action == 'delete') {
        $truck_id = (int)$_POST['truck_id'];
        $sql = "DELETE FROM trucks WHERE id = $truck_id AND user_id = $user_id";
        if ($conn->query($sql)) {
            header("Location: dashboard.php?success=truck_deleted");
        } else {
            header("Location: dashboard.php?error=db_error");
        }
        exit();
    }
}

header("Location: dashboard.php");
exit();
?>
