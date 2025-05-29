<?php
/**
 * Database Initialization Script
 * 
 * This script creates the necessary database and tables for the PIETECH Events Platform.
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Function to create the database if it doesn't exist
function createDatabase() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "Database created successfully or already exists.\n";
        
        return true;
    } catch (PDOException $e) {
        echo "Database creation failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Function to import SQL schema
function importSchema() {
    global $db;
    
    try {
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        $db->exec($sql);
        echo "Database schema imported successfully.\n";
        return true;
    } catch (PDOException $e) {
        echo "Schema import failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Initialize the database
if (createDatabase()) {
    if (importSchema()) {
        echo "Database initialization completed successfully!\n";
    }
} 