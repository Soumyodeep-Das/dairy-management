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
    $address = $_POST['address'] ?? null;
    $payment_info = $_POST['paymentinfo'] ?? 'Paid';
    if (!$address) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing address"]);
        exit;
    }
    // Find user's cart
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_id = null;
    if ($row = $result->fetch_assoc()) {
        $cart_id = $row['id'];
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Cart is empty"]);
        exit;
    }
    // Calculate total from cart_items and products
    $stmt = $conn->prepare("SELECT SUM(ci.quantity * p.price) as total FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'] ?? 0;
    if ($total <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Cart is empty"]);
        exit;
    }
    // Insert shipping info (minimal, just address)
    $stmt = $conn->prepare("INSERT INTO shipping_info (address, city, state, country, pincode, phone) VALUES (?, '', '', '', '', '')");
    $stmt->bind_param("s", $address);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to save shipping info"]);
        exit;
    }
    $shipping_info_id = $conn->insert_id;
    // Insert order
    $order_status = 'Pending';
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, order_status, payment_info, shipping_info_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idssi", $user_id, $total, $order_status, $payment_info, $shipping_info_id);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to place order"]);
        exit;
    }
    $order_id = $conn->insert_id;
    // Move cart_items to order_items
    $stmt = $conn->prepare("SELECT product_id, quantity FROM cart_items WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Get price for each product
        $pid = $row['product_id'];
        $qty = $row['quantity'];
        $pstmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $pstmt->bind_param("i", $pid);
        $pstmt->execute();
        $pres = $pstmt->get_result();
        $prow = $pres->fetch_assoc();
        $price = $prow['price'] ?? 0;
        $ostmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $ostmt->bind_param("iiid", $order_id, $pid, $qty, $price);
        $ostmt->execute();
    }
    // Clear cart_items and cart
    $conn->query("DELETE FROM cart_items WHERE cart_id = $cart_id");
    $conn->query("DELETE FROM cart WHERE id = $cart_id");
    echo json_encode(["status" => "success", "message" => "Order placed successfully!"]);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>
