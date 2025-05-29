<?php
/**
 * Test Cancel Registration Script
 */

// Include initialization file
require_once 'includes/init.php';

// Set error reporting to display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require login
requireLogin();

// Check if registration ID is provided
$registrationId = isset($_GET['id']) ? (int)$_GET['id'] : null;

echo '<h1>Test Cancel Registration</h1>';

if (!$registrationId) {
    echo '<p>Error: No registration ID provided. Add ?id=X to the URL.</p>';
    
    // Show available registrations
    $userId = $_SESSION['user_id'];
    $registrations = $registrationModel->getByUserId($userId);
    
    if ($registrations && count($registrations) > 0) {
        echo '<h2>Your Registrations</h2>';
        echo '<table border="1" cellpadding="5">';
        echo '<tr><th>ID</th><th>Event</th><th>Date</th><th>Status</th><th>Test</th></tr>';
        
        foreach ($registrations as $reg) {
            echo '<tr>';
            echo '<td>' . $reg['id'] . '</td>';
            echo '<td>' . htmlspecialchars($reg['event_title']) . '</td>';
            echo '<td>' . $reg['event_date'] . '</td>';
            echo '<td>' . $reg['status'] . '</td>';
            echo '<td><a href="test_cancel_registration.php?id=' . $reg['id'] . '">Test Cancel</a></td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<p>You have no registrations.</p>';
    }
    
    exit;
}

// Get registration details
$registration = $registrationModel->getById($registrationId);

// Check if registration exists and belongs to the current user
if (!$registration || $registration['user_id'] != $_SESSION['user_id']) {
    echo '<p>Error: Registration not found or you don\'t have permission to cancel it.</p>';
    exit;
}

echo '<h2>Registration Details</h2>';
echo '<p><strong>Event:</strong> ' . htmlspecialchars($registration['event_title']) . '</p>';
echo '<p><strong>Date:</strong> ' . $registration['event_date'] . '</p>';
echo '<p><strong>Time:</strong> ' . $registration['event_time'] . '</p>';
echo '<p><strong>Status:</strong> ' . $registration['status'] . '</p>';

// Check if registration is for a past event
$isPast = isEventPast($registration['event_date'], $registration['event_time']);
if ($isPast) {
    echo '<p style="color: red;">This event is in the past. You cannot cancel registrations for past events.</p>';
    exit;
}

// Create a form to cancel the registration
echo '<h2>Cancel This Registration</h2>';
echo '<form method="POST" action="cancel_registration.php" style="margin-top: 20px;">';
echo '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
echo '<input type="hidden" name="registration_id" value="' . $registrationId . '">';
echo '<p style="color: red;">Warning: This action cannot be undone.</p>';
echo '<button type="submit" style="padding: 10px; background: #dc3545; color: white; border: none; cursor: pointer;">Cancel Registration</button>';
echo '</form>';
?>
