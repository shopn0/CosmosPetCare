<?php
// API endpoint for user login
require_once '../../includes/db_config.php';
require_once '../../includes/auth.php';
require_once '../../includes/api_utils.php';
require_once '../../includes/jwt_helper.php';

// Set CORS headers
setCorsHeaders();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Get request data
$data = getRequestData();

// Validate required fields
$requiredFields = ['email', 'password'];
validateRequiredFields($data, $requiredFields);

// Sanitize inputs
$email = sanitizeInput($data['email']);
$password = $data['password']; // Don't sanitize password

// Attempt to login user
if (loginUser($email, $password)) {
    // Get user details for JWT
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    // Generate JWT token
    $token = generateJWTToken($user);
    
    // Return success response with token
    sendSuccess([
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'token' => $token
    ], 'Login successful');
} else {
    sendError('Invalid email or password', 401);
}