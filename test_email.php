<?php
/**
 * Email Test Script
 * 
 * This script tests the email functionality of the application.
 */

// Include initialization file
require_once 'includes/init.php';

// Check if form was submitted
$message = null;
$status = null;
$testRecipient = "pietsuvathi@gmail.com"; // Default recipient
$testName = "Test User";

if (isset($_POST['send_test'])) {
    // Get form data
    $testRecipient = sanitizeInput($_POST['recipient_email']);
    $testName = sanitizeInput($_POST['recipient_name']);
    $testSubject = "PIETECH Events Platform - Email Test";
    
    // Create email body
    $testBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4a5568; color: #fff; padding: 10px 20px; text-align: center; }
            .content { padding: 20px; background-color: #f8f9fa; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #6c757d; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>PIETECH Events Platform</h2>
            </div>
            <div class='content'>
                <h1>Email Test</h1>
                <p>This is a test email from the PIETECH Events Platform.</p>
                <p>If you received this email, your email configuration is working correctly.</p>
                <p>Time of test: " . date('Y-m-d H:i:s') . "</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " PIETECH Events Platform. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Try to send the email
    $result = $mailer->send($testRecipient, $testName, $testSubject, $testBody);
    
    if ($result) {
        $message = "Email was sent successfully to {$testRecipient}. Please check the inbox or spam folder.";
        $status = "success";
    } else {
        $message = "Email sending failed. Please check the email configuration and server logs.";
        $status = "danger";
    }
}

// Page HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test - PIETECH Events Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2><i class="fas fa-envelope me-2"></i>Email Configuration Test</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Email Status -->
                        <div class="mb-4">
                            <h4><i class="fas fa-cog me-2"></i>Email Status</h4>
                            <div class="alert <?php echo MAIL_ENABLED ? 'alert-success' : 'alert-warning'; ?>">
                                <strong>Email Functionality:</strong> <?php echo MAIL_ENABLED ? 'Enabled' : 'Disabled'; ?>
                                <?php if (!MAIL_ENABLED): ?>
                                    <p class="mb-0 mt-2">To enable email, set MAIL_ENABLED=true in your .env file.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Test Form -->
                        <form method="post" action="test_email.php" class="mb-4">
                            <h4><i class="fas fa-paper-plane me-2"></i>Send Test Email</h4>
                            <div class="mb-3">
                                <label for="recipient_email" class="form-label">Recipient Email</label>
                                <input type="email" class="form-control" id="recipient_email" name="recipient_email" value="<?php echo $testRecipient; ?>" required>
                                <div class="form-text">Enter the email address where you want to send the test email.</div>
                            </div>
                            <div class="mb-3">
                                <label for="recipient_name" class="form-label">Recipient Name</label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" value="<?php echo $testName; ?>" required>
                            </div>
                            <button type="submit" name="send_test" class="btn btn-primary" <?php echo !MAIL_ENABLED ? 'disabled' : ''; ?>>
                                <i class="fas fa-paper-plane me-2"></i>Send Test Email
                            </button>
                        </form>
                        
                        <!-- Email Configuration -->
                        <h4><i class="fas fa-list-alt me-2"></i>Current Email Configuration</h4>
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width: 30%">MAIL_HOST</th>
                                    <td><?php echo MAIL_HOST; ?></td>
                                </tr>
                                <tr>
                                    <th>MAIL_PORT</th>
                                    <td><?php echo MAIL_PORT; ?></td>
                                </tr>
                                <tr>
                                    <th>MAIL_USERNAME</th>
                                    <td><?php echo MAIL_USERNAME; ?></td>
                                </tr>
                                <tr>
                                    <th>MAIL_PASSWORD</th>
                                    <td><span class="text-muted">[Hidden for security]</span></td>
                                </tr>
                                <tr>
                                    <th>MAIL_ENCRYPTION</th>
                                    <td><?php echo MAIL_ENCRYPTION; ?></td>
                                </tr>
                                <tr>
                                    <th>MAIL_FROM_ADDRESS</th>
                                    <td><?php echo MAIL_FROM_ADDRESS; ?></td>
                                </tr>
                                <tr>
                                    <th>MAIL_FROM_NAME</th>
                                    <td><?php echo MAIL_FROM_NAME; ?></td>
                                </tr>
                                <tr>
                                    <th>MAIL_DEBUG</th>
                                    <td><?php echo MAIL_DEBUG; ?></td>
                                </tr>
                                <tr>
                                    <th>MAIL_AUTH</th>
                                    <td><?php echo MAIL_AUTH ? 'true' : 'false'; ?></td>
                                </tr>
                                <tr>
                                    <th>MAIL_ENABLED</th>
                                    <td><?php echo MAIL_ENABLED ? 'true' : 'false'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 