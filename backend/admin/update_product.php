<?php
include '../db.php';
include '../utils/auth_check.php';
header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access denied."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $category = $_POST['category'] ?? '';
    $ratings = $_POST['ratings'] ?? '';
    $expiry = $_POST['expiry_date'] ?? '';
    $image = $_POST['image'] ?? '';

    if (!$id || !$name || !$desc || !$price || !$stock || !$category || !$expiry) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing required fields."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category=?, ratings=?, image=?, expiry_date=? WHERE id=?");
    $stmt->bind_param("ssdissssi", $name, $desc, $price, $stock, $category, $ratings, $image, $expiry, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Product updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
}
?>
