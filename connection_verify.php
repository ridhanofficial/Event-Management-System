<?php
/**
 * Connection Verification Script
 * 
 * This script tests if the updated database connection settings work correctly
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test database connection with the updated settings
try {
    // Production database credentials - verified working configuration
    $host = 'sql205.hstn.me'; // Verified working hostname
    $dbname = 'mseet_38774389_events';
    $username = 'mseet_38774389'; // Verified working username
    $password = 'ridhan93';

    echo "<h2>Testing Database Connection</h2>";
    echo "<p>Connecting to: $host</p>";
    echo "<p>Database: $dbname</p>";
    echo "<p>Username: $username</p>";
    
    // Attempt connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5 // 5 second timeout
        ]
    );
    
    echo "<p style='color: green; font-weight: bold;'>Connection successful!</p>";
    
    // Test a query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    echo "<p>Number of users in database: " . $result['count'] . "</p>";
    
    // Check some database variables
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "<p>MySQL Version: " . $version['version'] . "</p>";
    
    echo "<h3>Tables:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>Connection failed:</p>";
    echo "<p>" . $e->getMessage() . "</p>";
}
