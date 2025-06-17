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
// Find user's cart
$stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_id = null;
if ($row = $result->fetch_assoc()) {
    $cart_id = $row['id'];
} else {
    echo json_encode(["status" => "success", "cart" => []]);
    exit;
}
// Get cart items and product info
$stmt = $conn->prepare("SELECT ci.id, ci.product_id, p.name, p.price, ci.quantity FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id = ?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
echo json_encode(["status" => "success", "cart" => $items]);
?>
