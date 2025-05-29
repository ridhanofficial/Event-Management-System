<?php
/**
 * Enhanced Database Connection Test
 * 
 * This script provides a comprehensive test of the database connection 
 * with detailed diagnostics to help troubleshoot issues.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Display a styled heading
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 15px 0; border-radius: 5px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .test-section { margin: 30px 0; }
        h1, h2 { color: #333; }
        h2 { margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; }
        .test-name { font-weight: bold; margin-bottom: 5px; }
        .result { font-weight: bold; }
        .step { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>EventsPro Database Connection Diagnostics</h1>";

// Environment Information
echo "<h2>Environment Information</h2>
<table>
    <tr><th>Setting</th><th>Value</th></tr>
    <tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>
    <tr><td>Server Software</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>
    <tr><td>Domain</td><td>" . $_SERVER['HTTP_HOST'] . "</td></tr>
    <tr><td>Time</td><td>" . date('Y-m-d H:i:s') . "</td></tr>
    <tr><td>Path</td><td>" . __DIR__ . "</td></tr>
</table>";

// Test database connection with explicit credentials
echo "<h2>Testing Direct Database Connection</h2>";
echo "<div class='step'>";
echo "<div class='test-name'>Test 1: Connection to sql209.hstn.me</div>";

try {
    // Production database credentials
    $host = 'sql205.hstn.me';
    $dbname = 'mseet_38774389_events';
    $username = 'mseet_38774389';
    $password = 'ridhan93';
    
    echo "<pre>
Host: $host
Database: $dbname
Username: $username
</pre>";
    
    // Attempt connection
    $start_time = microtime(true);
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
    $end_time = microtime(true);
    $time_taken = round(($end_time - $start_time) * 1000, 2);
    
    echo "<div class='success'>";
    echo "<p class='result'>Connection successful!</p>";
    echo "<p>Connection time: $time_taken ms</p>";
    
    // Check if we can query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "<p>MySQL Version: " . $version['version'] . "</p>";
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<p>Found " . count($tables) . " tables in the database.</p>";
        echo "<p>Tables: " . implode(", ", array_slice($tables, 0, 10)) . (count($tables) > 10 ? "..." : "") . "</p>";
    } else {
        echo "<p class='warning'>No tables found in the database.</p>";
    }
    
    echo "</div>";
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<p class='result'>Connection failed:</p>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials and ensure the database server is accessible.</p>";
    echo "</div>";
}
echo "</div>";

// Test connection using Database.php class
echo "<div class='step'>";
echo "<div class='test-name'>Test 2: Connection using Database class</div>";

// Include Database class if it exists
if (file_exists(__DIR__ . '/includes/Database.php')) {
    try {
        include_once __DIR__ . '/includes/Database.php';
        
        // Create Database instance
        $database = new Database();
        $conn = $database->getConnection();
        
        if ($conn instanceof PDO) {
            echo "<div class='success'>";
            echo "<p class='result'>Database class connection successful!</p>";
            
            // Check if we can query
            $stmt = $conn->query("SELECT VERSION() as version");
            $version = $stmt->fetch();
            echo "<p>MySQL Version: " . $version['version'] . "</p>";
            
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<p class='result'>Database class did not return a PDO object</p>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<p class='result'>Error using Database class:</p>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }
} else {
    echo "<div class='warning'>";
    echo "<p class='result'>Database.php file not found at " . __DIR__ . "/includes/Database.php</p>";
    echo "</div>";
}
echo "</div>";

// Test through config/database.php if it exists
echo "<div class='step'>";
echo "<div class='test-name'>Test 3: Connection through config/database.php</div>";

if (file_exists(__DIR__ . '/config/database.php')) {
    try {
        // Include database config (but capture any output)
        ob_start();
        include_once __DIR__ . '/config/database.php';
        $output = ob_get_clean();
        
        if (isset($db) && $db instanceof PDO) {
            echo "<div class='success'>";
            echo "<p class='result'>Config/database.php connection successful!</p>";
            
            // Check if we can query
            $stmt = $db->query("SELECT VERSION() as version");
            $version = $stmt->fetch();
            echo "<p>MySQL Version: " . $version['version'] . "</p>";
            
            echo "</div>";
        } else {
            echo "<div class='warning'>";
            echo "<p class='result'>Config/database.php did not create a PDO object named \$db</p>";
            if (!empty($output)) {
                echo "<pre>Output: " . htmlspecialchars($output) . "</pre>";
            }
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<p class='result'>Error with config/database.php:</p>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }
} else {
    echo "<div class='warning'>";
    echo "<p class='result'>config/database.php file not found</p>";
    echo "</div>";
}
echo "</div>";

// Next steps
echo "<h2>Next Steps</h2>";
echo "<ul>
    <li>If all tests are successful, the database connection is working properly.</li>
    <li>If any test fails, check the error message and verify your database credentials.</li>
    <li>Make sure all database.php files contain the correct database server: sql209.hstn.me</li>
    <li>Make sure all files use the correct username: mseet_38774389 (not mseet_38774389_events)</li>
    <li>After fixing any issues, remove this file from the production server for security.</li>
</ul>";

echo "<p><a href='index.php'>Return to Homepage</a></p>";
echo "</body></html>";
