<?php
// Debug endpoint to check API connectivity
// Save this file as debug.php in your php/api directory

// Set headers to allow CORS and prevent caching
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

// Return basic test data to verify the API is working
echo json_encode([
    'status' => 'success',
    'message' => 'API test endpoint is working',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
        'request_headers' => getallheaders()
    ]
]);