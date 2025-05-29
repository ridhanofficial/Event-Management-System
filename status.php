<?php
/**
 * Quick Environment Check
 * 
 * This file provides a quick status check for the EventsPro platform.
 * It should return HTTP 200 OK with a simple JSON response when the system is working.
 * This can be used for health monitoring and basic system diagnostics.
 */

// Headers
header('Content-Type: application/json');

// Basic environment check
$status = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => defined('APP_ENV') ? APP_ENV : 'unknown',
    'system' => [
        'php_version' => phpversion(),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'domain' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? true : false
    ]
];

// Check database connection if possible
if (file_exists('includes/init.php')) {
    try {
        // Try to include initialization without displaying errors
        ob_start();
        @require_once 'includes/init.php';
        ob_end_clean();
        
        // If database connection is available
        if (isset($pdo)) {
            $status['database'] = 'connected';
        } else {
            $status['database'] = 'not initialized';
        }
    } catch (Exception $e) {
        $status['database'] = 'error';
        $status['database_message'] = 'Could not connect to database';
    }
} else {
    $status['database'] = 'not checked';
}

// Output status
echo json_encode($status, JSON_PRETTY_PRINT);
exit;
