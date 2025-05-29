<?php
/**
 * Domain-specific Database Connection Test
 * 
 * This script tests the database connection for different domains
 * and helps diagnose deployment issues.
 */

// Set headers for plain text output
header('Content-Type: text/plain');

// Display server information
echo "=== Server Information ===\n";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "\n";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
echo "\n";

// Check if we're on a production domain
$host = $_SERVER['HTTP_HOST'] ?? '';
$isProdDomain = (strpos($host, 'pietech-events.is-best.net') !== false || 
                strpos($host, 'pietech-event.zya.me') !== false);

echo "=== Domain Detection ===\n";
echo "Detected Host: {$host}\n";
echo "Is Production Domain: " . ($isProdDomain ? 'Yes' : 'No') . "\n";
echo "\n";

// Include the database configuration file
require_once 'config/database.php';

// Display database configuration
echo "=== Database Configuration ===\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (DB_PASS ? '[HIDDEN]' : '[EMPTY]') . "\n";
echo "\n";

// Test database connection
echo "=== Connection Test ===\n";
try {
    // We're reusing the $db connection from database.php
    if (isset($db) && $db instanceof PDO) {
        echo "✅ Database connection successful!\n";
        
        // Check if required tables exist
        $tables = ['users', 'events', 'registrations'];
        $missingTables = [];
        
        foreach ($tables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() == 0) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            echo "✅ All required tables exist.\n";
            
            // Count records in tables
            echo "\n=== Database Content ===\n";
            foreach ($tables as $table) {
                $stmt = $db->query("SELECT COUNT(*) as count FROM {$table}");
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "{$table}: {$count} records\n";
            }
        } else {
            echo "❌ Missing tables: " . implode(', ', $missingTables) . "\n";
        }
    } else {
        echo "❌ Database connection object not available.\n";
    }
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Environment Variables ===\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'Not set') . "\n";
echo "APP_URL: " . (getenv('APP_URL') ?: 'Not set') . "\n";

echo "\n=== PHP Information ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";

echo "\n=== Test Complete ===\n";