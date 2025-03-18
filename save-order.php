<?php
// save-order.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the JSON data from the request
$json_data = file_get_contents('php://input');
$order_data = json_decode($json_data, true);

if (!$order_data || !isset($order_data['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

// Create directory with absolute path and proper permissions
$order_dir = __DIR__ . '/megrendelesek';
if (!file_exists($order_dir)) {
    if (!mkdir($order_dir, 0777, true)) {
        error_log("Failed to create directory: " . $order_dir . " - " . error_get_last()['message']);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create directory']);
        exit;
    }
    chmod($order_dir, 0777); // Ensure directory is writable
}

// Generate filename
$order_id = $order_data['order_id'];
$json_filename = $order_dir . '/order_' . $order_id . '_' . date('Ymd_His') . '.json';

// Save the data with proper error handling
try {
    $result = file_put_contents($json_filename, json_encode($order_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception("Failed to write file: " . error_get_last()['message']);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order saved successfully',
        'file' => $json_filename,
        'bytes' => $result
    ]);
    
} catch (Exception $e) {
    error_log("Error saving order JSON: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>