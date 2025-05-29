<?php
/**
 * Cancel Registration Script
 * 
 * This script handles the cancellation of a user's registration for an event.
 */

// Include initialization file
require_once 'includes/init.php';

// Require login
requireLogin();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('Invalid request method.', 'danger');
    redirect('my_registrations.php');
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    setFlashMessage('Invalid request. Please try again.', 'danger');
    redirect('my_registrations.php');
}

// Check if registration ID is provided
if (!isset($_POST['registration_id']) || !is_numeric($_POST['registration_id'])) {
    setFlashMessage('Invalid registration ID.', 'danger');
    redirect('my_registrations.php');
}

$registrationId = $_POST['registration_id'];

// Get registration details
$registration = $registrationModel->getById($registrationId);

// Check if registration exists and belongs to the current user
if (!$registration || $registration['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('Registration not found or you don\'t have permission to cancel it.', 'danger');
    redirect('my_registrations.php');
}

// Check if registration is for a past event
if (isEventPast($registration['event_date'], $registration['event_time'])) {
    setFlashMessage('You cannot cancel registrations for past events.', 'warning');
    redirect('my_registrations.php');
}

try {
    // Log the cancellation attempt
    error_log("Attempting to cancel registration ID: $registrationId for user ID: {$_SESSION['user_id']}");
    
    // Delete the registration
    $result = $registrationModel->delete($registrationId);
    
    if ($result) {
        // Log successful cancellation
        error_log("Successfully cancelled registration ID: $registrationId");
        setFlashMessage('Your registration has been successfully cancelled.', 'success');
    } else {
        // Log failed cancellation
        error_log("Failed to cancel registration ID: $registrationId");
        setFlashMessage('Failed to cancel registration. Please try again.', 'danger');
    }
} catch (Exception $e) {
    // Log any exceptions
    error_log("Exception when cancelling registration: " . $e->getMessage());
    setFlashMessage('An error occurred while processing your request.', 'danger');
}

// Redirect back to registrations page
redirect('my_registrations.php');