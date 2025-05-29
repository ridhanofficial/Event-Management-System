<?php
/**
 * Initialization File
 * 
 * This file initializes the application by loading required files,
 * setting up global variables, and starting sessions.
 */

// Start session
session_start();

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse line
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

// Set timezone
date_default_timezone_set(getenv('TIMEZONE') ?: 'UTC');

// Include configuration files first
require_once __DIR__ . '/../config/app.php';

// Include required files
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Mailer.php';

// Include models
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Event.php';
require_once __DIR__ . '/../app/models/Registration.php';
require_once __DIR__ . '/../app/models/EventRegistration.php';

// Initialize database connection
$db = new Database();
$dbConnection = $db->getConnection();

// Initialize models
$userModel = new User($dbConnection);
$eventModel = new Event($dbConnection);
$registrationModel = new Registration($dbConnection);
$eventRegistrationModel = new EventRegistration($dbConnection);

// Initialize mailer
$mailer = new Mailer();