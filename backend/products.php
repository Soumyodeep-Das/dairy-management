<?php
// This file is deprecated. Use backend/api/products.php instead.
http_response_code(410);
echo json_encode(["status" => "error", "message" => "This endpoint is deprecated. Use /backend/api/products.php."]);
?>