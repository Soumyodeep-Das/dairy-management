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
        // Get all cart items for user
        $stmt = $conn->prepare("SELECT c.id, c.product_id, p.name, p.price, c.quantity FROM cart c JOIN product p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        echo json_encode(["status" => "success", "cart" => $items]);
        break;
    case 'POST':
        // Add item to cart
        $data = $_POST;
        if (!isset($data['product_id']) || !isset($data['quantity'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing product_id or quantity"]);
            exit;
        }
        $product_id = $data['product_id'];
        $quantity = $data['quantity'];
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Added to cart"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;
    case 'DELETE':
        // Remove item from cart
        parse_str(file_get_contents("php://input"), $data);
        if (!isset($data['cart_id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing cart_id"]);
            exit;
        }
        $cart_id = $data['cart_id'];
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Removed from cart"]);
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
