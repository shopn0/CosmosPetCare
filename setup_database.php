<?php
// Database setup script
try {
    // Connect to MySQL server (without selecting a database)
    $conn = new mysqli("localhost", "root", "");
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to MySQL server successfully!<br>";
    
    // Read the SQL file
    $sql = file_get_contents('database_schema.sql');
    
    // Execute multi query
    if ($conn->multi_query($sql)) {
        echo "Database and tables created successfully!<br>";
        
        // Need to process all result sets to allow further queries
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        echo "Setup complete. You can now <a href='login.html'>login</a> with the following demo accounts:<br>";
        echo "- Admin: admin@vetcare.bd (password: admin123)<br>";
        echo "- Vet: farida@vetcare.bd (password: admin123)<br>";
        echo "- Customer: nasreen@gmail.com (password: admin123)<br>";
    } else {
        echo "Error creating database: " . $conn->error . "<br>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Setup error: " . $e->getMessage();
}
?>