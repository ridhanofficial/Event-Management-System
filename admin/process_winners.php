<?php
// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['eventId']) || !isset($_POST['position'])) {
    header('Location: select_winners.php?error=invalid_submission');
    exit();
}

// Database and models are already available from init.php
// $db, $dbConnection, $eventModel, $mailer, etc.

// Get submitted event ID
$eventId = filter_input(INPUT_POST, 'eventId', FILTER_SANITIZE_NUMBER_INT);

// Get event details
$eventStmt = $dbConnection->prepare("SELECT title FROM events WHERE id = ?");
$eventStmt->execute([$eventId]);
$eventName = $eventStmt->fetchColumn();

if (!$eventName) {
    header('Location: select_winners.php?error=event_not_found');
    exit();
}

// $mailer is already available from init.php

// Counter for successful notifications
$notificationsSent = 0;

// For each user with a selected position
foreach ($_POST['position'] as $userId => $position) {
    // Skip empty positions
    if (empty($position)) {
        continue;
    }
    
    // Sanitize the user ID
    $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
    
    // Validate position value
    $validPositions = ['1st', '2nd', '3rd'];
    if (!in_array($position, $validPositions)) {
        continue;
    }
      // Update registrations table -> set winner_position
    $updateStmt = $dbConnection->prepare("
        UPDATE registrations 
        SET winner_position = ? 
        WHERE user_id = ? AND event_id = ?
    ");
    $updateStmt->execute([$position, $userId, $eventId]);
    
    // Fetch user details
    $userStmt = $dbConnection->prepare("
        SELECT name, email 
        FROM users 
        WHERE id = ?
    ");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        continue;
    }
      // Get today's date
    $currentDate = date("F d, Y");
    
    // Get color based on position
    $positionColor = '';
    $positionIcon = '';
    $positionClass = '';
    
    switch($position) {
        case '1st':
            $positionColor = '#FFD700'; // Gold
            $positionIcon = '🏆';
            $positionClass = 'first-place';
            break;
        case '2nd':
            $positionColor = '#C0C0C0'; // Silver
            $positionIcon = '🥈';
            $positionClass = 'second-place';
            break;
        case '3rd':
            $positionColor = '#CD7F32'; // Bronze
            $positionIcon = '🥉';
            $positionClass = 'third-place';
            break;
    }
    
    // Prepare email content
    $subject = "Congratulations - " . $eventName . " " . $positionIcon;
    
    // HTML Certificate
    $body = "
    <div style='font-family: Arial, sans-serif;'>
        <p>Dear " . $user['name'] . ",</p>
        
        <p>We are delighted to inform you that you have secured the <strong>" . $position . " place</strong> in <strong>" . $eventName . "</strong>. Congratulations on your outstanding achievement!</p>
        
        <div style='max-width: 650px; margin: 30px auto; border: 20px solid #083D77; padding: 0;'>
            <!-- Certificate Header -->
            <div style='background-color: #083D77; color: white; padding: 10px; text-align: center;'>
                <h2 style='margin: 0; font-size: 24px;'>CERTIFICATE OF ACHIEVEMENT</h2>
            </div>
            
            <!-- Certificate Body -->
            <div style='background-color: #F5F5F5; padding: 20px; text-align: center;'>
                <div style='border: 2px solid #ccc; padding: 20px; background-color: white;'>
                    <div style='font-size: 18px; color: #555;'>This certificate is proudly presented to</div>
                    <div style='font-size: 28px; font-weight: bold; color: #333; margin: 20px 0; font-family: \"Times New Roman\", serif;'>" . $user['name'] . "</div>
                    
                    <div style='font-size: 18px; margin: 15px 0;'>
                        For achieving <span style='font-weight: bold; color: " . $positionColor . ";' class='" . $positionClass . "'>" . $position . " Place</span>
                    </div>
                    
                    <div style='font-size: 22px; margin-top: 10px; font-weight: bold;'>" . $eventName . "</div>
                    <div style='margin: 30px 0 10px; font-size: 16px;'>Awarded on " . $currentDate . "</div>
                    
                    <div style='margin: 40px auto 20px; border-top: 2px solid #ccc; width: 60%; padding-top: 10px; font-family: cursive;'>
                        <div style='font-size: 18px;'>PIETECH Events Team</div>
                        <div style='font-size: 14px; color: #777;'>Authorized Signature</div>
                    </div>
                </div>
            </div>
            
            <!-- Certificate Footer -->
            <div style='background-color: #083D77; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                <p style='margin: 0;'>PIETECH Events Platform - Celebrating Excellence</p>
            </div>
        </div>
        
        <p>We appreciate your participation and hope to see you at future events. This certificate serves as recognition of your outstanding performance.</p>
        
        <p>Best regards,<br>
        PIETECH Events Team</p>
    </div>
    ";
    
    // Plain text version for email clients that don't support HTML
    $plainBody = "
    Dear " . $user['name'] . ",
    
    CERTIFICATE OF ACHIEVEMENT
    
    This certificate is proudly presented to
    " . $user['name'] . "
    
    For achieving " . $position . " Place in " . $eventName . "
    
    Awarded on " . $currentDate . "
    
    We appreciate your participation and hope to see you at future events.
    
    Best regards,
    PIETECH Events Team
    ";
      // Add email header for better compatibility
    $emailHeader = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Achievement Certificate</title>
        <style>
            @media print {
                body {
                    width: 21cm;
                    height: 29.7cm;
                    margin: 0;
                    padding: 0;
                }
            }
            .first-place { color: #FFD700 !important; }
            .second-place { color: #C0C0C0 !important; }
            .third-place { color: #CD7F32 !important; }
        </style>
    </head>
    <body>
    ';
    
    $emailFooter = '
    </body>
    </html>
    ';
    
    // Complete HTML email
    $completeHtmlEmail = $emailHeader . $body . $emailFooter;
    
    // Send email notification
    $emailSent = $mailer->send($user['email'], $user['name'], $subject, $completeHtmlEmail, $plainBody);
    
    if ($emailSent) {
        $notificationsSent++;
        
        // Log successful certificate sending
        error_log("Certificate sent to " . $user['name'] . " (" . $user['email'] . ") for " . $position . " place in " . $eventName);
    } else {
        // Log failure
        error_log("Failed to send certificate to " . $user['name'] . " (" . $user['email'] . ")");
    }
}

// Redirect to winner selection page with success message
header('Location: select_winners.php?success=true&event=' . $eventId . '&notifications=' . $notificationsSent);
exit();
?>
