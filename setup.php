<?php
/**
 * TaskFlow Setup Script
 * This script helps verify the installation and setup the database
 */

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die("❌ PHP 7.4 or higher is required. Current version: " . PHP_VERSION);
}

// Check required extensions
$required_extensions = ['mysqli', 'session', 'json'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die("❌ Missing PHP extensions: " . implode(', ', $missing_extensions));
}

echo "✅ PHP version and extensions check passed\n";

// Database configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'taskflow'
];

// Test database connection
try {
    $connection = new mysqli($db_config['host'], $db_config['username'], $db_config['password']);
    
    if ($connection->connect_error) {
        throw new Exception("Connection failed: " . $connection->connect_error);
    }
    
    echo "✅ Database connection successful\n";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS `{$db_config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if ($connection->query($sql) === TRUE) {
        echo "✅ Database '{$db_config['database']}' created/verified\n";
    } else {
        throw new Exception("Error creating database: " . $connection->error);
    }
    
    // Select the database
    $connection->select_db($db_config['database']);
    
    // Check if tables exist
    $tables_check = $connection->query("SHOW TABLES LIKE 'users'");
    if ($tables_check->num_rows == 0) {
        echo "⚠️  Database tables not found. Please import tasks.sql manually.\n";
        echo "   You can do this by:\n";
        echo "   1. Opening phpMyAdmin\n";
        echo "   2. Selecting the 'taskflow' database\n";
        echo "   3. Going to Import tab\n";
        echo "   4. Choosing tasks.sql file\n";
        echo "   5. Clicking 'Go'\n";
    } else {
        echo "✅ Database tables found\n";
    }
    
    $connection->close();
    
} catch (Exception $e) {
    die("❌ Database error: " . $e->getMessage());
}

// Check file permissions
$files_to_check = [
    'db_connect.php',
    'Login.php',
    'Dashboard.php',
    'Task.php'
];

foreach ($files_to_check as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File not found: $file\n";
    } else {
        echo "✅ File exists: $file\n";
    }
}

// Check if sessions are working
session_start();
$_SESSION['test'] = 'working';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'working') {
    echo "✅ PHP sessions working\n";
    unset($_SESSION['test']);
} else {
    echo "⚠️  PHP sessions may not be working properly\n";
}

echo "\n🎉 Setup verification complete!\n";
echo "📋 Next steps:\n";
echo "1. Import tasks.sql into your database\n";
echo "2. Access the application via your web browser\n";
echo "3. Login with admin/password or user1/password\n";
echo "4. Change default passwords immediately\n";
echo "\n📖 For detailed instructions, see INSTALLATION_GUIDE.md\n";
?>
