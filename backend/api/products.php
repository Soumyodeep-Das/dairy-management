<?php
include '../db.php';
header('Content-Type: application/json');
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result === false) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "SQL Error: " . $conn->error
    ]);
    exit;
}

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
echo json_encode([
    "status" => "success",
    "products" => $products
]);
?>
