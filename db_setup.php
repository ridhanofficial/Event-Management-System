<?php
/**
 * Database Creation Script for Production
 * 
 * This script creates the database and required tables for the EventsPro platform.
 * Run this script once to set up the database structure on the production server.
 * 
 * IMPORTANT: Delete this file after successful database creation for security reasons.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Production database credentials
$db_host = '127.0.0.1';  // Using IP instead of localhost to force TCP/IP
$db_name = 'mseet_38774389_events';
$db_user = 'mseet_38774389_events';
$db_pass = 'ridhan93';

echo "<h1>EventsPro Database Setup</h1>";
echo "<p>Attempting to create database and tables...</p>";

try {
    // First, connect without specifying a database
    $pdo = new PDO(
        "mysql:host={$db_host}",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<p style='color: green;'>Connected to MySQL server successfully!</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$db_name}'");
    $dbExists = $stmt->rowCount() > 0;
    
    if (!$dbExists) {
        // Create the database
        $pdo->exec("CREATE DATABASE `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color: green;'>Database '{$db_name}' created successfully!</p>";
    } else {
        echo "<p style='color: orange;'>Database '{$db_name}' already exists.</p>";
    }
    
    // Connect to the newly created database
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<p style='color: green;'>Connected to database '{$db_name}' successfully!</p>";
    
    // Define table creation queries
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password` varchar(255) NOT NULL,
            `reg_no` varchar(50) DEFAULT NULL,
            `department` varchar(100) DEFAULT NULL,
            `role` enum('user','admin') NOT NULL DEFAULT 'user',
            `verification_token` varchar(255) DEFAULT NULL,
            `verified` tinyint(1) NOT NULL DEFAULT '0',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `last_login` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'events' => "CREATE TABLE IF NOT EXISTS `events` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text NOT NULL,
            `date` date NOT NULL,
            `time` time NOT NULL,
            `venue` varchar(100) NOT NULL,
            `room_no` varchar(50) DEFAULT NULL,
            `max_participants` int(11) NOT NULL,
            `category` varchar(100) NOT NULL,
            `organizer` varchar(100) NOT NULL,
            `contact_email` varchar(100) NOT NULL,
            `contact_phone` varchar(20) DEFAULT NULL,
            `registration_fee` decimal(10,2) DEFAULT '0.00',
            `registration_deadline` datetime NOT NULL,
            `team_based` tinyint(1) NOT NULL DEFAULT '0',
            `team_size_min` int(11) DEFAULT NULL,
            `team_size_max` int(11) DEFAULT NULL,
            `created_by` int(11) NOT NULL,
            `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `created_by` (`created_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'registrations' => "CREATE TABLE IF NOT EXISTS `registrations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `event_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `team_name` varchar(100) DEFAULT NULL,
            `members` text,
            `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            `check_in` tinyint(1) NOT NULL DEFAULT '0',
            `winner_position` int(11) DEFAULT NULL,
            `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
            `payment_reference` varchar(100) DEFAULT NULL,
            `payment_amount` decimal(10,2) DEFAULT '0.00',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `event_user` (`event_id`,`user_id`),
            KEY `user_id` (`user_id`),
            KEY `event_id` (`event_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'settings' => "CREATE TABLE IF NOT EXISTS `settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `key` varchar(100) NOT NULL,
            `value` text NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `key` (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    // Create tables
    echo "<h2>Creating Tables:</h2>";
    echo "<ul>";
    foreach ($tables as $name => $sql) {
        try {
            $pdo->exec($sql);
            echo "<li style='color: green;'>Table '{$name}' created/verified successfully!</li>";
        } catch (PDOException $e) {
            echo "<li style='color: red;'>Error creating table '{$name}': " . $e->getMessage() . "</li>";
        }
    }
    echo "</ul>";
    
    // Create admin user if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount == 0) {
        // Create default admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, verified)
            VALUES ('Admin User', 'admin@pietech-events.is-best.net', :password, 'admin', 1)
        ");
        
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();
        
        echo "<p style='color: green;'>Default admin user created!</p>";
        echo "<p><strong>Email:</strong> admin@pietech-events.is-best.net</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p style='color: red;'><strong>Important:</strong> Please change this password immediately after logging in!</p>";
    } else {
        echo "<p style='color: blue;'>Admin user already exists. No default admin created.</p>";
    }
    
    // Create foreign key constraints
    $constraints = [
        "ALTER TABLE `events` ADD CONSTRAINT `events_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE",
        "ALTER TABLE `registrations` ADD CONSTRAINT `registrations_event_id_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE",
        "ALTER TABLE `registrations` ADD CONSTRAINT `registrations_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE"
    ];
    
    echo "<h2>Creating Foreign Keys:</h2>";
    echo "<ul>";
    foreach ($constraints as $sql) {
        try {
            $pdo->exec($sql);
            echo "<li style='color: green;'>Foreign key constraint added successfully!</li>";
        } catch (PDOException $e) {
            // Constraint might already exist
            if ($e->getCode() == '42121') {
                echo "<li style='color: blue;'>Foreign key constraint already exists.</li>";
            } else {
                echo "<li style='color: orange;'>Note on foreign key: " . $e->getMessage() . "</li>";
            }
        }
    }
    echo "</ul>";
    
    echo "<h2>Database Setup Complete!</h2>";
    echo "<p>Your EventsPro database has been successfully set up. You can now <a href='/index.php'>access the application</a>.</p>";
    echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this setup script (db_setup.php) from your server for security reasons!</p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Database Setup Failed</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    
    // Provide more helpful information based on error code
    if ($e->getCode() == 1044) {
        echo "<p>It appears your database user doesn't have permission to create databases. Contact your hosting provider to create the database manually or grant CREATE permissions to your user.</p>";
    } else if ($e->getCode() == 1045) {
        echo "<p>Authentication failed. Check your username and password.</p>";
    } else if ($e->getCode() == 2002) {
        echo "<p>Could not connect to the database server. Possible reasons:</p>
              <ul>
                <li>The database server is not running</li>
                <li>The host address is incorrect</li>
                <li>A firewall is blocking the connection</li>
                <li>Try changing the host from '127.0.0.1' to 'localhost' or vice versa</li>
              </ul>";
    }
}
