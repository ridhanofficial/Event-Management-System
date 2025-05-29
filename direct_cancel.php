<?php
/**
 * Direct Registration Cancellation Test Tool
 * 
 * This script provides a direct way to test registration cancellation without using the modal.
 */

// Include initialization file
require_once 'includes/init.php';

// Require login
requireLogin();

// Set error reporting to display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if registration ID is provided
$registrationId = isset($_GET['id']) ? (int)$_GET['id'] : null;

echo '<h1>Direct Registration Cancellation Test</h1>';

if (!$registrationId) {
    echo '<p>No registration ID provided. Add ?id=X to the URL.</p>';
    
    // List all registrations for the current user
    $userId = $_SESSION['user_id'];
    $registrations = $registrationModel->getByUserId($userId);
    
    if ($registrations && count($registrations) > 0) {
        echo '<h2>Your Registrations</h2>';
        echo '<table border="1" cellpadding="5">';
        echo '<tr><th>ID</th><th>Event</th><th>Date</th><th>Status</th><th>Test Link</th></tr>';
        
        foreach ($registrations as $reg) {
            echo '<tr>';
            echo '<td>' . $reg['id'] . '</td>';
            echo '<td>' . htmlspecialchars($reg['event_title']) . '</td>';
            echo '<td>' . $reg['event_date'] . '</td>';
            echo '<td>' . $reg['status'] . '</td>';
            echo '<td><a href="direct_cancel.php?id=' . $reg['id'] . '">Run Direct Cancel</a></td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<p>You have no registrations to cancel.</p>';
    }
    
    exit;
}

// Get registration details
$registration = $registrationModel->getById($registrationId);

// Verify registration exists and belongs to current user
if (!$registration) {
    echo '<p style="color:red">Error: Registration not found.</p>';
    exit;
}

if ($registration['user_id'] != $_SESSION['user_id']) {
    echo '<p style="color:red">Error: You do not have permission to cancel this registration.</p>';
    exit;
}

// Verify if the event is in the past
if (isEventPast($registration['event_date'], $registration['event_time'])) {
    echo '<p style="color:red">Error: This event is in the past. Cannot cancel past events.</p>';
    exit;
}

// Display registration details
echo '<h2>Registration Details</h2>';
echo '<p><strong>Event:</strong> ' . htmlspecialchars($registration['event_title']) . '</p>';
echo '<p><strong>Date/Time:</strong> ' . $registration['event_date'] . ' at ' . $registration['event_time'] . '</p>';
echo '<p><strong>Status:</strong> ' . $registration['status'] . '</p>';

// If a direct cancellation is requested
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    echo '<h2>Processing Cancellation...</h2>';
    
    try {
        // Generate a CSRF token for security
        $csrfToken = generateCsrfToken();
        
        // Directly call the delete function
        $result = $registrationModel->delete($registrationId);
        
        if ($result) {
            echo '<p style="color:green">Success! Registration has been cancelled.</p>';
            echo '<p>SQL DELETE command executed successfully and removed the registration.</p>';
        } else {
            echo '<p style="color:red">Error: Delete operation failed. No rows affected.</p>';
            echo '<p>The database operation completed but no registrations were deleted.</p>';
        }
    } catch (Exception $e) {
        echo '<p style="color:red">Exception occurred: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    
    echo '<p><a href="my_registrations.php">Return to My Registrations</a></p>';
    exit;
}

// Confirmation form
echo '<h2>Confirm Cancellation</h2>';
echo '<p style="color:red">Warning: This action cannot be undone.</p>';
echo '<p>Are you sure you want to cancel your registration for ' . htmlspecialchars($registration['event_title']) . '?</p>';

echo '<div style="margin: 20px 0;">';
echo '<a href="direct_cancel.php?id=' . $registrationId . '&confirm=yes" style="background-color: #dc3545; color: white; padding: 10px 15px; text-decoration: none; margin-right: 10px;">Yes, Cancel Registration</a>';
echo '<a href="my_registrations.php" style="background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none;">No, Go Back</a>';
echo '</div>';

// Display the debugging information
echo '<h2>Technical Information (for debugging)</h2>';
echo '<p>The following database query will be executed:</p>';
echo '<pre style="background-color: #f8f9fa; padding: 10px; border: 1px solid #ddd;">DELETE FROM registrations WHERE id = ' . $registrationId . '</pre>';

echo '<p>User ID: ' . $_SESSION['user_id'] . '</p>';
echo '<p>Registration ID: ' . $registrationId . '</p>';
echo '<p>Current Time: ' . date('Y-m-d H:i:s') . '</p>';
echo '<p>Event Time: ' . $registration['event_date'] . ' ' . $registration['event_time'] . '</p>';
echo '<p>Is Event Past: ' . (isEventPast($registration['event_date'], $registration['event_time']) ? 'Yes' : 'No') . '</p>';
?>
