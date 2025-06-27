<?php
// Database connectivity test script
require_once 'includes/db_config.php';

try {
    $conn = connectDB();
    echo "Database connection successful!";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "<br>Users table exists!";
    } else {
        echo "<br>Users table does not exist. Database might not be fully set up.";
    }
    
    closeDB($conn);
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage();
}
?>