<?php
/**
 * Database Connection Test
 * This script tests the database connection configuration
 */

// Include the database connection file
require_once 'includes/Database.php';

// Load environment variables if .env file exists
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse line
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes if present
            if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
                $value = substr($value, 1, -1);
            }
            
            // Set environment variable
            putenv("{$name}={$value}");
        }
    }
}

// Test database connection
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if connection is successful
    if ($conn) {
        echo "✅ Database connection successful!\n";
        echo "Database Host: " . getenv('DB_HOST') . "\n";
        echo "Database Name: " . getenv('DB_NAME') . "\n";
        echo "Database User: " . getenv('DB_USER') . "\n";
        
        // Check if required tables exist
        $tables = ['users', 'events', 'registrations'];
        $missingTables = [];
        
        foreach ($tables as $table) {
            $stmt = $conn->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() == 0) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            echo "✅ All required tables exist.\n";
        } else {
            echo "❌ Missing tables: " . implode(', ', $missingTables) . "\n";
        }
    }
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in the .env file.\n";
}
?> 