<?php
session_start();
include '../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password_input = $_POST['password'] ?? '';

    if (!$email || !$password_input) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Email and password required."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password_input, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];
            echo json_encode(["status" => "success", "message" => "Login successful.", "name" => $row['name'], "role" => $row['role']]);
        } else {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Invalid password."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
}
?>
