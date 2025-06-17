<?php
include '../db.php';
include '../utils/auth_check.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get all orders for user
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        echo json_encode(["status" => "success", "orders" => $orders]);
        break;
    case 'POST':
        // Place a new order (expects total, address, etc.)
        $data = $_POST;
        if (!isset($data['total']) || !isset($data['address'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing total or address"]);
            exit;
        }
        $total = $data['total'];
        $address = $data['address'];
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, address) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $user_id, $total, $address);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Order placed"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>
