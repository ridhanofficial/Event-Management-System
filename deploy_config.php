<?php
/**
 * Deployment Configuration
 * 
 * This file contains configuration settings for deploying the EventsPro platform
 * to the production server at https://pietech-events.is-best.net
 */

// Define environment type
define('ENVIRONMENT', 'production');

// Database credentials for production
$production_db = [
    'host'     => 'sql209.hstn.me', // External MySQL server for this hosting provider
    'name'     => 'mseet_38774389_events',
    'user'     => 'mseet_38774389',
    'password' => 'ridhan93',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

// Base URL for production
define('PRODUCTION_BASE_URL', 'https://pietech-events.is-best.net');

// Email configuration for production
$production_email = [
    'host'       => 'your_smtp_host.com', // Replace with your host's SMTP server
    'username'   => 'notifications@pietech-events.is-best.net', // Replace with your email
    'password'   => 'your_email_password', // Replace with your email password
    'port'       => 587,
    'from_email' => 'notifications@pietech-events.is-best.net',
    'from_name'  => 'PIETECH Events Platform'
];

/**
 * Deployment checklist:
 * 
 * 1. Update database config in config/database.php
 * 2. Update base URL in config/app.php
 * 3. Update email settings in config/email.php
 * 4. Check file permissions (755 for directories, 644 for files)
 * 5. Import database schema if not already done
 * 6. Make sure vendor dependencies are uploaded
 * 7. Create .htaccess file for URL rewriting and security
 */
