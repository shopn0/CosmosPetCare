<?php
// API endpoint for managing users (for admins)
require_once '../../includes/db_config.php';
require_once '../../includes/auth.php';
require_once '../../includes/api_utils.php';
require_once '../../includes/jwt_helper.php';

// Set CORS headers
setCorsHeaders();

// Authenticate user
$userData = authenticateUser();
if (!$userData) {
    sendError('Unauthorized', 401);
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // List users
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $role = isset($_GET['role']) ? sanitizeInput($_GET['role']) : null;
        
        // Special case: Allow any authenticated user to get vets list
        if ($role === 'vet' && checkUserRole('customer')) {
            // Get vets list for booking appointments
            $conn = connectDB();
            $stmt = $conn->prepare("SELECT id, name, role FROM users WHERE role = 'vet' ORDER BY id");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $users = [];
            while ($user = $result->fetch_assoc()) {
                $users[] = $user;
            }
            
            $stmt->close();
            closeDB($conn);
            
            sendSuccess(['users' => $users], 'Vets retrieved successfully');
            exit;
        }
        
        // For all other user management operations, require admin role
        if (!checkUserRole('admin')) {
            sendError('Access denied. Only admins can manage users', 403);
        }
        
        if ($userId) {
            // Get single user
            $user = getUserDetails($userId);
            
            if ($user) {
                sendSuccess(['user' => $user], 'User details retrieved successfully');
            } else {
                sendError('User not found', 404);
            }
        } else {
            // Get all users or filter by role
            $conn = connectDB();
            
            if ($role) {
                $stmt = $conn->prepare("SELECT id, name, email, role, phone, address, created_at 
                                       FROM users WHERE role = ? ORDER BY id");
                $stmt->bind_param("s", $role);
            } else {
                $stmt = $conn->prepare("SELECT id, name, email, role, phone, address, created_at 
                                       FROM users ORDER BY id");
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $users = [];
            while ($user = $result->fetch_assoc()) {
                $users[] = $user;
            }
            
            $stmt->close();
            closeDB($conn);
            
            sendSuccess(['users' => $users], 'Users retrieved successfully');
        }
        break;
        
    case 'POST':
        // Create user
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
        
        // Validate role
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
            // Get user details
            $stmt = $conn->prepare("SELECT id, name, email, role, phone, address, created_at 
                                   FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            $stmt->close();
            closeDB($conn);
            
            sendSuccess(['user' => $user], 'User created successfully');
        } else {
            closeDB($conn);
            sendError('Failed to create user', 500);
        }
        break;
        
    case 'PUT':
        // Update user
        $data = getRequestData();
        
        // Validate required fields
        $requiredFields = ['id', 'name', 'email', 'role', 'phone', 'address'];
        validateRequiredFields($data, $requiredFields);
        
        // Sanitize inputs
        $id = (int)$data['id'];
        $name = sanitizeInput($data['name']);
        $email = sanitizeInput($data['email']);
        $role = sanitizeInput($data['role']);
        $phone = sanitizeInput($data['phone']);
        $address = sanitizeInput($data['address']);
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError('Invalid email address', 400);
        }
        
        // Validate role
        $allowedRoles = ['customer', 'vet', 'admin'];
        if (!in_array($role, $allowedRoles)) {
            sendError('Invalid role. Allowed roles: customer, vet, admin', 400);
        }
        
        // Check if user exists
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            closeDB($conn);
            sendError('User not found', 404);
        }
        
        // Check if email already exists for another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            closeDB($conn);
            sendError('Email already exists for another user', 400);
        }
        
        // Update user
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, phone = ?, address = ? 
                               WHERE id = ?");
        $stmt->bind_param("sssssi", $name, $email, $role, $phone, $address, $id);
        $result = $stmt->execute();
        
        if ($result) {
            // Get updated user details
            $stmt = $conn->prepare("SELECT id, name, email, role, phone, address, created_at 
                                   FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            $stmt->close();
            closeDB($conn);
            
            sendSuccess(['user' => $user], 'User updated successfully');
        } else {
            $stmt->close();
            closeDB($conn);
            sendError('Failed to update user', 500);
        }
        break;
        
    case 'DELETE':
        // Delete user
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$id) {
            sendError('User ID is required', 400);
        }
        
        // Check if user exists
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            closeDB($conn);
            sendError('User not found', 404);
        }
        
        // Don't allow deleting the current user
        if ($id === $userData['id']) {
            $stmt->close();
            closeDB($conn);
            sendError('You cannot delete your own account', 400);
        }
        
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        
        $stmt->close();
        closeDB($conn);
        
        if ($result) {
            sendSuccess([], 'User deleted successfully');
        } else {
            sendError('Failed to delete user', 500);
        }
        break;
        
    default:
        sendError('Method not allowed', 405);
}