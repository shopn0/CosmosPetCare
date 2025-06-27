<?php
// Install Firebase JWT library
echo "Checking if Firebase JWT library is installed...<br>";

if (!file_exists('vendor/firebase/php-jwt')) {
    echo "Firebase JWT library not found. Attempting to install it...<br>";
    
    // Check if composer is installed
    $composerOutput = shell_exec('composer --version 2>&1');
    if (strpos($composerOutput, 'Composer version') === false) {
        echo "Error: Composer is not installed or not in the system path.<br>";
        echo "Please install Composer first: <a href='https://getcomposer.org/download/' target='_blank'>https://getcomposer.org/download/</a><br>";
        echo "Then run: <code>composer require firebase/php-jwt</code> in the project directory.";
        exit;
    }
    
    // Try to install using composer
    echo "Running: composer require firebase/php-jwt<br>";
    $output = shell_exec('composer require firebase/php-jwt 2>&1');
    echo "<pre>$output</pre>";
    
    if (file_exists('vendor/firebase/php-jwt')) {
        echo "Firebase JWT library installed successfully!<br>";
    } else {
        echo "Error: Failed to install Firebase JWT library.<br>";
        echo "Please run: <code>composer require firebase/php-jwt</code> manually in the project directory.";
    }
} else {
    echo "Firebase JWT library is already installed.<br>";
}

echo "<br><a href='fix_passwords.php'>Continue to fix passwords</a>";
?>