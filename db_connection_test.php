<?php
/**
 * Database Connection Test Script
 * 
 * This script tests various MySQL connection methods to identify
 * the most reliable way to connect to the production database.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define connection parameters
$connections = [
    'tcp_ip_explicit' => [
        'host' => '127.0.0.1',
        'name' => 'mseet_38774389_events',
        'user' => 'mseet_38774389_events',
        'pass' => 'ridhan93'
    ],
    'localhost' => [
        'host' => 'localhost',
        'name' => 'mseet_38774389_events',
        'user' => 'mseet_38774389_events',
        'pass' => 'ridhan93'
    ],
    'socket_explicit' => [
        'host' => 'localhost',
        'socket' => '/var/run/mysqld/mysqld.sock', // Common location for MySQL socket
        'name' => 'mseet_38774389_events',
        'user' => 'mseet_38774389_events',
        'pass' => 'ridhan93'
    ],
    'port_explicit' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'mseet_38774389_events',
        'user' => 'mseet_38774389_events',
        'pass' => 'ridhan93'
    ],
    'no_database' => [
        'host' => '127.0.0.1',
        'user' => 'mseet_38774389_events',
        'pass' => 'ridhan93'
    ]
];

// HTML header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Connection Tests</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .failure { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Database Connection Tests</h1>
    <p>This script tests various MySQL connection methods to help identify the most reliable connection approach.</p>
";

// Display PHP and MySQL information
echo "<h2>System Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>PDO Drivers:</strong> " . implode(', ', PDO::getAvailableDrivers()) . "</p>";
if (function_exists('mysqli_get_client_info')) {
    echo "<p><strong>MySQL Client Version:</strong> " . mysqli_get_client_info() . "</p>";
}
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Server Domain:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

// Test each connection method
echo "<h2>Connection Tests</h2>";

foreach ($connections as $type => $config) {
    echo "<h3>Testing $type connection</h3>";
    
    try {
        // Build DSN based on connection type
        $dsn = "mysql:host=" . $config['host'];
        
        if (isset($config['port'])) {
            $dsn .= ";port=" . $config['port'];
        }
        
        if (isset($config['socket'])) {
            $dsn .= ";unix_socket=" . $config['socket'];
        }
        
        if (isset($config['name'])) {
            $dsn .= ";dbname=" . $config['name'];
        }
        
        // Display DSN (hiding password)
        echo "<p><strong>Connection string:</strong> $dsn</p>";
        
        // Attempt connection
        $start_time = microtime(true);
        $pdo = new PDO(
            $dsn,
            $config['user'],
            $config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5, // 5 second timeout
            ]
        );
        $end_time = microtime(true);
        $time_taken = round(($end_time - $start_time) * 1000, 2);
        
        // Test a simple query
        $stmt = $pdo->query("SELECT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<div class='success'>";
        echo "<p><strong>Connection successful!</strong></p>";
        echo "<p>Connection time: $time_taken ms</p>";
        
        // Show MySQL version
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>MySQL Version:</strong> " . $version['version'] . "</p>";
        
        // Show connection details
        $stmt = $pdo->query("SHOW VARIABLES LIKE 'socket'");
        $socket = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>MySQL Socket:</strong> " . $socket['Value'] . "</p>";
        
        $stmt = $pdo->query("SHOW VARIABLES LIKE 'port'");
        $port = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>MySQL Port:</strong> " . $port['Value'] . "</p>";
        
        $stmt = $pdo->query("SHOW VARIABLES LIKE 'have_ssl'");
        $ssl = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>MySQL SSL:</strong> " . $ssl['Value'] . "</p>";
        
        // If database was specified, check if we can list tables
        if (isset($config['name'])) {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p><strong>Tables found:</strong> " . count($tables) . "</p>";
            if (count($tables) > 0) {
                echo "<p>Table list: " . implode(", ", array_slice($tables, 0, 10)) . (count($tables) > 10 ? "..." : "") . "</p>";
            }
        }
        
        echo "</div>";
    } catch (PDOException $e) {
        echo "<div class='failure'>";
        echo "<p><strong>Connection failed:</strong> " . $e->getMessage() . "</p>";
        echo "</div>";
    }
}

// Add a check for recommended configuration
echo "<h2>Recommended Configuration</h2>";
echo "<pre>
// Production database configuration
define('DB_HOST', '127.0.0.1'); // Force TCP/IP connection
define('DB_NAME', 'mseet_38774389_events');
define('DB_USER', 'mseet_38774389_events');
define('DB_PASS', 'ridhan93');
</pre>";

echo "<h2>Next Steps</h2>";
echo "<ol>
    <li>Update database.php with the connection method that worked best above</li>
    <li>Run db_setup.php to create tables if they don't exist</li>
    <li>Run production_test.php to verify the entire system is working</li>
    <li>Update permission_check.php to ensure correct file permissions</li>
</ol>";

echo "</body></html>";
