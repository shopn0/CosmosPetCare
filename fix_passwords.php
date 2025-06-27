<?php
// Quick script to update user passwords
require_once 'includes/db_config.php';

try {
    $conn = connectDB();
    
    // Password we want to use (admin123)
    $plainPassword = 'admin123';
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    
    // Update all demo user passwords
    $stmt = $conn->prepare("UPDATE users SET password = ?");
    $stmt->bind_param("s", $hashedPassword);
    
    if ($stmt->execute()) {
        echo "All user passwords have been updated to 'admin123'.<br>";
        echo "You can now login with any of the demo accounts using this password.<br>";
        echo "<a href='login.html'>Go to login page</a>";
    } else {
        echo "Error updating passwords: " . $stmt->error;
    }
    
    $stmt->close();
    closeDB($conn);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>