<?php
/**
 * Database Connection Test with Multiple User/Password Combinations
 * 
 * This script tries different database connection credentials
 * to determine the correct combination for your hosting provider.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define potential database credentials
$credentials = [
    // Original credentials
    [
        'host' => 'sql205.hstn.me',
        'dbname' => 'mseet_38774389_events',
        'username' => 'mseet_38774389',
        'password' => 'ridhan93',
        'label' => 'Original credentials'
    ],
    // Alternative 1: Using mseet_38774389 with mseet_38774389_events DB
    [
        'host' => 'sql205.hstn.me',
        'dbname' => 'mseet_38774389_events',
        'username' => 'mseet_38774389_events',
        'password' => 'ridhan93',
        'label' => 'DB-specific username'
    ],
    // Alternative 2: Using standard conventionally paired username/DB
    [
        'host' => 'sql209.hstn.me',
        'dbname' => 'mseet_38774389',
        'username' => 'mseet_38774389',
        'password' => 'ridhan93',
        'label' => 'Username matches DB name'
    ],
    // Alternative 3: Full qualifier
    [
        'host' => 'sql209.hstn.me',
        'dbname' => 'mseet_38774389_events',
        'username' => 'mseet_38774389@192.168.0.5',
        'password' => 'ridhan93',
        'label' => 'Username with host qualifier'
    ]
];

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 15px 0; border-radius: 5px; }
        h1, h2 { color: #333; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; }
        .test-section { margin: 30px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>EventsPro Database Connection Test</h1>
    <p>This script tests multiple database credential combinations to find the one that works with your hosting provider.</p>";

$successfulConnection = false;
$index = 1;

foreach ($credentials as $cred) {
    echo "<div class='test-section'>";
    echo "<h2>Test {$index}: {$cred['label']}</h2>";
    echo "<pre>
Host: {$cred['host']}
Database: {$cred['dbname']}
Username: {$cred['username']}
</pre>";
    
    try {
        // Attempt connection
        $dsn = "mysql:host={$cred['host']};dbname={$cred['dbname']};charset=utf8mb4";
        $pdo = new PDO(
            $dsn,
            $cred['username'],
            $cred['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5 // 5 second timeout
            ]
        );
        
        echo "<div class='success'>";
        echo "<p><strong>✓ SUCCESS! This credential combination works.</strong></p>";
        
        // Check if we can query
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version = $stmt->fetch();
        echo "<p>MySQL Version: " . $version['version'] . "</p>";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p>Found " . count($tables) . " tables in the database:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>{$table}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No tables found in the database.</p>";
        }
        
        echo "<p><strong>✓ USE THESE CREDENTIALS IN YOUR CONFIGURATION FILES:</strong></p>";
        echo "<pre>
    \$host = '{$cred['host']}';
    \$dbname = '{$cred['dbname']}';
    \$username = '{$cred['username']}';
    \$password = '{$cred['password']}';
        </pre>";
        
        $successfulConnection = true;
        echo "</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>";
        echo "<p><strong>✗ Connection failed:</strong> " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    echo "</div>";
    $index++;
}

if (!$successfulConnection) {
    echo "<div class='error'>";
    echo "<h2>No Working Credentials Found</h2>";
    echo "<p>None of the tested credential combinations worked. You may need to:</p>";
    echo "<ol>
        <li>Contact your hosting provider to confirm the correct database credentials</li>
        <li>Check if there are IP restrictions on database access</li>
        <li>Verify if there are special characters in the password that need escaping</li>
    </ol>";
    echo "</div>";
}

echo "<h2>Next Steps</h2>";
echo "<p>Once you have identified the working credentials:</p>";
echo "<ol>
    <li>Update the Database.php file with the correct credentials</li>
    <li>Update the database.php configuration file</li>
    <li>Test your application</li>
</ol>";

echo "</body></html>";
