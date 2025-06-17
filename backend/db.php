<?php
$host = "localhost";
$user = "root"; // default for XAMPP
$password = ""; // default for XAMPP
$db = "dairy_management";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
