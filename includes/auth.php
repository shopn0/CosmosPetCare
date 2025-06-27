<?php
session_start();
require_once 'db_config.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user's role
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Function to register a new user
function registerUser($name, $email, $password, $role, $phone, $address) {
    $conn = connectDB();
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $role, $phone, $address);
    
    $result = $stmt->execute();
    
    $stmt->close();
    closeDB($conn);
    
    return $result;
}

// Function to authenticate user
function loginUser($email, $password) {
    $conn = connectDB();
    
    // Prepare statement
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            $stmt->close();
            closeDB($conn);
            return true;
        }
    }
    
    $stmt->close();
    closeDB($conn);
    return false;
}

// Function to logout user
function logoutUser() {
    session_unset();
    session_destroy();
    return true;
}

// Function to get user details
function getUserDetails($userId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT id, name, email, role, phone, address, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    return $user;
}

// Function to check if the user has appropriate access
function checkAccess($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = getUserRole();
    
    // Admin has access to everything
    if ($userRole === 'admin') {
        return true;
    }
    
    // Check if user role matches required role
    return $userRole === $requiredRole;
}