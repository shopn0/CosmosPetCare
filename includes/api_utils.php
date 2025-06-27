<?php
// API utilities for handling requests and responses

/**
 * Send JSON response
 * 
 * @param int $statusCode HTTP status code
 * @param array $data Response data
 */
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send success response
 * 
 * @param array $data Response data
 * @param string $message Success message
 */
function sendSuccess($data = [], $message = 'Success') {
    sendResponse(200, [
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Send error response
 * 
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 */
function sendError($message = 'Error', $statusCode = 400) {
    sendResponse($statusCode, [
        'status' => 'error',
        'message' => $message
    ]);
}

/**
 * Get JSON request data
 * 
 * @return array Request data
 */
function getRequestData() {
    // Get JSON input
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);
    
    // If JSON parsing failed, use POST data
    if ($jsonInput && $data === null) {
        sendError('Invalid JSON data', 400);
    }
    
    // If no data was received, use POST data
    if (!$data) {
        $data = $_POST;
    }
    
    return $data;
}

/**
 * Validate required fields
 * 
 * @param array $data Data to validate
 * @param array $requiredFields Required fields
 * @return bool True if all required fields are present, false if not
 */
function validateRequiredFields($data, $requiredFields) {
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            sendError("Field '$field' is required", 400);
            return false;
        }
    }
    
    return true;
}

/**
 * Sanitize input data
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Set CORS headers
 */
function setCorsHeaders() {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
    } else {
        header('Access-Control-Allow-Origin: *');
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        }
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        
        exit(0);
    }
}