<?php
/**
 * Log Redirect Script for Registration Cancellations
 * 
 * This script is used as a redirect endpoint for registration cancellations
 * to provide additional logging and troubleshooting.
 */

// Include initialization file
require_once 'includes/init.php';

// Log the request
$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/registration_cancellations.log';

// Log details
$timestamp = date('Y-m-d H:i:s');
$userID = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not logged in';
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Unknown';
$regID = isset($_POST['registration_id']) ? $_POST['registration_id'] : 'Not provided';
$hasCSRF = isset($_POST['csrf_token']) ? 'Yes' : 'No';
$method = $_SERVER['REQUEST_METHOD'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct access';

// Create log entry
$logEntry = "[{$timestamp}] User: {$userID} ({$userName}) | Registration ID: {$regID} | " .
            "CSRF Token: {$hasCSRF} | Method: {$method} | " .
            "Referer: {$referer} | User Agent: {$userAgent}\n";

// Write to log file
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Process the cancellation
if ($method === 'POST' && isset($_POST['registration_id']) && is_numeric($_POST['registration_id'])) {
    // Require login
    if (!isLoggedIn()) {
        file_put_contents($logFile, "[{$timestamp}] ERROR: User not logged in\n", FILE_APPEND);
        setFlashMessage('You must be logged in to cancel registrations.', 'danger');
        redirect('login.php');
    }
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        file_put_contents($logFile, "[{$timestamp}] ERROR: Invalid CSRF token\n", FILE_APPEND);
        setFlashMessage('Invalid request. Please try again.', 'danger');
        redirect('my_registrations.php');
    }
    
    $registrationId = $_POST['registration_id'];
    
    // Get registration details
    $registration = $registrationModel->getById($registrationId);
    
    // Check if registration exists and belongs to the current user
    if (!$registration) {
        file_put_contents($logFile, "[{$timestamp}] ERROR: Registration not found: {$registrationId}\n", FILE_APPEND);
        setFlashMessage('Registration not found.', 'danger');
        redirect('my_registrations.php');
    }
    
    if ($registration['user_id'] != $_SESSION['user_id']) {
        file_put_contents($logFile, "[{$timestamp}] ERROR: User {$userID} attempted to cancel registration {$registrationId} belonging to user {$registration['user_id']}\n", FILE_APPEND);
        setFlashMessage('You don\'t have permission to cancel this registration.', 'danger');
        redirect('my_registrations.php');
    }
    
    // Check if registration is for a past event
    if (isEventPast($registration['event_date'], $registration['event_time'])) {
        file_put_contents($logFile, "[{$timestamp}] ERROR: Attempted to cancel past event: Event date: {$registration['event_date']} {$registration['event_time']}\n", FILE_APPEND);
        setFlashMessage('You cannot cancel registrations for past events.', 'warning');
        redirect('my_registrations.php');
    }
    
    // All checks passed, proceed with cancellation
    file_put_contents($logFile, "[{$timestamp}] INFO: All validation passed, proceeding with cancellation\n", FILE_APPEND);
    
    try {
        // Delete the registration
        $result = $registrationModel->delete($registrationId);
        
        if ($result) {
            file_put_contents($logFile, "[{$timestamp}] SUCCESS: Registration {$registrationId} canceled successfully\n", FILE_APPEND);
            setFlashMessage('Your registration has been successfully cancelled.', 'success');
        } else {
            file_put_contents($logFile, "[{$timestamp}] ERROR: Database operation failed to cancel registration {$registrationId}\n", FILE_APPEND);
            setFlashMessage('Failed to cancel registration. Please try again.', 'danger');
        }
    } catch (Exception $e) {
        file_put_contents($logFile, "[{$timestamp}] EXCEPTION: {$e->getMessage()}\n", FILE_APPEND);
        setFlashMessage('An error occurred while processing your request.', 'danger');
    }
    
    // Redirect back to registrations page
    redirect('my_registrations.php');
} else {
    // Invalid request
    file_put_contents($logFile, "[{$timestamp}] ERROR: Invalid request method or missing registration ID\n", FILE_APPEND);
    setFlashMessage('Invalid request. Please use the cancel button on the registrations page.', 'danger');
    redirect('my_registrations.php');
}
?>
