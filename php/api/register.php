<?php
// API endpoint for user registration
require_once '../../includes/db_config.php';
require_once '../../includes/auth.php';
require_once '../../includes/api_utils.php';

// Set CORS headers
setCorsHeaders();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Get request data
$data = getRequestData();

// Validate required fields
$requiredFields = ['name', 'email', 'password', 'role', 'phone', 'address'];
validateRequiredFields($data, $requiredFields);

// Sanitize inputs
$name = sanitizeInput($data['name']);
$email = sanitizeInput($data['email']);
$password = $data['password']; // Don't sanitize password before hashing
$role = sanitizeInput($data['role']);
$phone = sanitizeInput($data['phone']);
$address = sanitizeInput($data['address']);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError('Invalid email address', 400);
}

// Validate role (only allow customer, vet, or admin)
$allowedRoles = ['customer', 'vet', 'admin'];
if (!in_array($role, $allowedRoles)) {
    sendError('Invalid role. Allowed roles: customer, vet, admin', 400);
}

// Check if email already exists
$conn = connectDB();
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    closeDB($conn);
    sendError('Email already exists', 400);
}

// Register the user
if (registerUser($name, $email, $password, $role, $phone, $address)) {
    // Get user details for JWT
    $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    // Generate JWT token
    require_once '../../includes/jwt_helper.php';
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
    ], 'User registered successfully');
} else {
    closeDB($conn);
    sendError('Failed to register user', 500);
}