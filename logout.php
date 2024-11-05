<?php
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}
// Check if the user is logged in
if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];

    // Remove token from database
    $stmt = $conn->prepare("UPDATE users SET token = NULL WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    // Delete the cookie
    setcookie('auth_token', '', time() - 3600, '/', '', true, true);
}

// Redirect to the page where logout occurred
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: $redirect");
exit();