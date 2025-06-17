<?php
include '../db.php';
include '../utils/auth_check.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing product_id"]);
        exit;
    }
    // 1. Find or create cart for user
    $cart_id = null;
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $cart_id = $row['id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, total_price) VALUES (?, 0)");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $cart_id = $conn->insert_id;
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create cart"]);
            exit;
        }
    }
    // 2. Insert or update cart_items
    $stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $cart_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $new_quantity = $row['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $row['id']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
        $stmt->execute();
    }
    echo json_encode(["status" => "success", "message" => "Added to cart"]);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>
