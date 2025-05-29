<?php
/**
 * Database Configuration File
 * 
 * This file contains the database connection parameters for the PIETECH Events Platform.
 */

// Load environment variables if not already loaded
if (!getenv('DB_HOST')) {
    // Check if we're on a production domain
    $host = $_SERVER['HTTP_HOST'] ?? '';      if (strpos($host, 'pietech-events.is-best.net') !== false) {
        // Production database credentials
        // Use the verified working MySQL server hostname & username
        define('DB_HOST', 'sql205.hstn.me');  // Verified working hostname
        define('DB_NAME', 'mseet_38774389_events');
        define('DB_USER', 'mseet_38774389');  // Verified working username
        define('DB_PASS', 'ridhan93');
    } else {
        // Local development database credentials (from .env)
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_NAME', getenv('DB_NAME') ?: 'pietech_events');
        define('DB_USER', getenv('DB_USER') ?: 'root');
        define('DB_PASS', getenv('DB_PASS') ?: '');
    }
} else {
    // Environment variables already loaded, use them
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_NAME', getenv('DB_NAME'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
}

// First, connect without specifying a database to check if it exists
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    $dbExists = $stmt->rowCount() > 0;
    
    if (!$dbExists) {
        // Create the database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        
        // Import schema if this is a new database
        if (file_exists(__DIR__ . '/../database/schema.sql')) {
            $pdo->exec("USE `" . DB_NAME . "`");
            $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
            $pdo->exec($sql);
        }
    }
    
    // Now connect to the specific database
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
} catch (PDOException $e) {
    // Log error for debugging
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}
