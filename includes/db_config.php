<?php
// Database connection configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Default XAMPP MySQL username
define('DB_PASS', '');     // Default XAMPP MySQL password is empty
define('DB_NAME', 'vet_management_system');

// Create database connection
function connectDB() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return null;
        }
        
        // Set charset to utf8
        $conn->set_charset("utf8");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Critical database error: " . $e->getMessage());
        return null;
    }
}

// Close database connection
function closeDB($conn) {
    if ($conn) {
        try {
            $conn->close();
        } catch (Exception $e) {
            error_log("Error closing database connection: " . $e->getMessage());
        }
    }
}