<?php
header('Content-Type: application/json');
echo json_encode([
    "status" => "success",
    "message" => "Dairy Management Backend API is running."
]);
?>