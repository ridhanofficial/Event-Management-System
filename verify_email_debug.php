<?php
/**
 * Email Debug Verification Script
 * 
 * This script verifies that PHPMailer debug output is being securely logged to a file
 * instead of being displayed in the browser.
 */

// Include initialization file
require_once 'includes/init.php';

// Attempt to send a test email with debug enabled
echo "<h1>Email Debug Security Verification</h1>";
echo "<p>Testing secure debug output...</p>";

// Check if logs directory exists
$logsDir = __DIR__ . '/logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0755, true);
    echo "<p>Created logs directory at: {$logsDir}</p>";
}

// Force debug level to 2 temporarily for testing
$originalDebugLevel = defined('MAIL_DEBUG') ? MAIL_DEBUG : 0;
define('MAIL_DEBUG_TESTING', 2);

// Create a test mailer instance
$testMailer = new Mailer();

try {
    // Create a sample email to test debug logging
    $recipient = "test@example.com";
    $name = "Test User";
    $subject = "Debug Test Email";
    $body = "<p>This is a test email for debug verification.</p>";
    
    // Send test email, forcing debug mode for the test
    $reflector = new ReflectionClass('Mailer');
    $property = $reflector->getProperty('mailer');
    $property->setAccessible(true);
    $phpMailer = $property->getValue($testMailer);
    
    if ($phpMailer) {
        // Set debug level high for testing
        $phpMailer->SMTPDebug = MAIL_DEBUG_TESTING;
        
        // Check if we're using the secure debug output function
        if (is_callable($phpMailer->Debugoutput) && 
            !in_array($phpMailer->Debugoutput, ['error_log', 'html', 'echo'])) {
            echo "<p style='color: green;'>✓ PHPMailer is configured to use a secure custom debug output function.</p>";
        } else {
            echo "<p style='color: red;'>✗ Warning! PHPMailer is not using a secure custom debug output function.</p>";
            echo "<p>Current Debugoutput setting: " . (is_callable($phpMailer->Debugoutput) ? "Custom Function" : $phpMailer->Debugoutput) . "</p>";
        }
        
        // Don't actually send the email, we're just testing the debug output
        echo "<p>Not actually sending email - just testing debug configuration.</p>";
    } else {
        echo "<p style='color: red;'>✗ Could not access PHPMailer instance.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Check if debug log file has been created
$logFile = $logsDir . '/smtp_debug.log';
if (file_exists($logFile)) {
    echo "<p style='color: green;'>✓ SMTP debug log file exists at: {$logFile}</p>";
    
    // Show last few lines of the log
    $logContents = file_get_contents($logFile);
    $logLines = explode("\n", $logContents);
    $lastLines = array_slice($logLines, -10);
    
    echo "<h2>Last 10 lines from debug log:</h2>";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    echo "</pre>";
} else {
    echo "<p style='color: orange;'>⚠ Debug log file has not been created yet. Try sending an actual test email.</p>";
}

// Provide instructions for testing in production
echo "<h2>Security Recommendations:</h2>";
echo "<ul>";
echo "<li>Always set MAIL_DEBUG to 0 in production environments.</li>";
echo "<li>Periodically check and rotate the smtp_debug.log file.</li>";
echo "<li>Make sure the logs directory is not publicly accessible.</li>";
echo "<li>Consider adding a .htaccess file to protect the logs directory.</li>";
echo "</ul>";

// Link to test email script
echo "<p><a href='test_email.php'>Go to Email Test Page</a></p>";
?>
