<?php
// API endpoint for updating user profile
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

// Authenticate user with JWT
$userData = authenticateUser();
if (!$userData) {
    sendError('Unauthorized', 401);
}

// Get request data
$data = getRequestData();

// Validate required fields
$requiredFields = ['id', 'name', 'email', 'phone', 'address'];
validateRequiredFields($data, $requiredFields);

// Sanitize inputs
$id = (int)$data['id'];
$name = sanitizeInput($data['name']);
$email = sanitizeInput($data['email']);
$phone = sanitizeInput($data['phone']);
$address = sanitizeInput($data['address']);

// Security check - users can only update their own profile unless they're admin
if ($userData['role'] !== 'admin' && $userData['id'] != $id) {
    sendError('Unauthorized access', 403);
}

// Connect to database
$conn = connectDB();

// Check if user exists
$stmt = $conn->prepare("SELECT id, password FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDB($conn);
    sendError('User not found', 404);
}

$user = $result->fetch_assoc();
$stmt->close();

// If password change is requested
if (isset($data['currentPassword']) && isset($data['newPassword']) && !empty($data['currentPassword']) && !empty($data['newPassword'])) {
    // Verify current password
    if (!password_verify($data['currentPassword'], $user['password'])) {
        closeDB($conn);
        sendError('Current password is incorrect', 400);
    }
    
    // Hash new password
    $hashedPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT);
    
    // Update user with new password
    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $phone, $address, $hashedPassword, $id);
} else {
    // Update user without changing password
    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $phone, $address, $id);
}

if ($stmt->execute()) {
    $stmt->close();
    
    // Get updated user details
    $stmt = $conn->prepare("SELECT id, name, email, role, phone, address FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $updatedUser = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    // Generate new JWT token with updated user data
    $token = generateJWTToken($updatedUser);
    
    // Return success response with updated user and token
    sendSuccess([
        'user' => [
            'id' => $updatedUser['id'],
            'name' => $updatedUser['name'],
            'email' => $updatedUser['email'],
            'role' => $updatedUser['role'],
            'phone' => $updatedUser['phone'],
            'address' => $updatedUser['address']
        ],
        'token' => $token
    ], 'Profile updated successfully');
} else {
    $stmt->close();
    closeDB($conn);
    sendError('Failed to update profile', 500);
}