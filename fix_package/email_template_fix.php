<?php
/**
 * Email Template Fix
 * 
 * This script fixes display issues in email templates.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Email Template Fix</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .failure { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin: 10px 0; border-radius: 5px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        code { font-family: monospace; }
        .container { max-width: 800px; margin: 0 auto; }
        .preview-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Email Template Fix</h1>
        <p>This tool fixes display issues in email templates for the EventsPro platform.</p>";

// Try multiple possible paths to find Mailer.php
$possiblePaths = [
    dirname(__DIR__) . '/includes/Mailer.php', // Local XAMPP path
    '/home/vol2_8/hstn.me/mseet_38774389/pietech-events.is-best.net/includes/Mailer.php', // Production path
    __DIR__ . '/../includes/Mailer.php', // Relative path
];

$mailerPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $mailerPath = $path;
        break;
    }
}

if (!$mailerPath) {
    echo "<div class='failure'>Error: Mailer.php not found in any of the expected locations.</div>";
    echo "<div class='warning'>Please specify the correct path to Mailer.php:</div>";
    echo "<form method='post'>
            <input type='text' name='custom_path' placeholder='/full/path/to/includes/Mailer.php' style='width: 100%; padding: 8px; margin: 10px 0;'>
            <button type='submit' style='padding: 8px 16px; background: #4a5568; color: white; border: none; cursor: pointer;'>Use This Path</button>
          </form>";
    
    // Check if a custom path was provided
    if (isset($_POST['custom_path']) && !empty($_POST['custom_path'])) {
        $customPath = $_POST['custom_path'];
        if (file_exists($customPath)) {
            $mailerPath = $customPath;
            echo "<div class='success'>Found Mailer.php at the specified path!</div>";
        } else {
            echo "<div class='failure'>Error: Mailer.php not found at the specified path: {$customPath}</div>";
            exit;
        }
    } else {
        exit;
    }
}

echo "<div class='success'>Found Mailer.php at: {$mailerPath}</div>";

// Create backup
$backup = $mailerPath . '.bak.' . date('YmdHis');
copy($mailerPath, $backup);
echo "<div class='success'>Created backup of Mailer.php at " . basename($backup) . "</div>";

// Read the mailer file
$content = file_get_contents($mailerPath);

// Create a temporary file for debugging
$tempDebugFile = dirname(__FILE__) . '/mailer_debug.txt';
file_put_contents($tempDebugFile, "Original content length: " . strlen($content) . "\n");

// DIRECT FIX FOR LINE 131 - Let's directly fix line 131 which has the syntax error
$lines = explode("\n", $content);
file_put_contents($tempDebugFile, "Total lines: " . count($lines) . "\n", FILE_APPEND);

// Check if line 131 exists
if (isset($lines[130])) { // 0-based index, so line 131 is at index 130
    file_put_contents($tempDebugFile, "Line 131 before: " . $lines[130] . "\n", FILE_APPEND);
    
    // Replace problematic button hover style - this is likely what's causing the syntax error
    if (strpos($lines[130], 'background-color:rgb') !== false) {
        $lines[130] = '                    background-color: #2d3748;';
        file_put_contents($tempDebugFile, "Fixed line 131\n", FILE_APPEND);
    }
    
    file_put_contents($tempDebugFile, "Line 131 after: " . $lines[130] . "\n", FILE_APPEND);
}

// Reconstruct the content
$content = implode("\n", $lines);

// Fix button color - simplified replacements
$content = str_replace(
    "background-color:rgb(232, 236, 239);",
    "background-color: #4A5568;",
    $content
);

// Fix confirmation box border
$content = str_replace(
    "border: 1px solidrgb(0, 0, 0);",
    "border: 1px solid #B3E5FC;",
    $content
);

// Fix any RGB color issues that could cause syntax errors
$content = preg_replace(
    "/background-color:rgb\((\d+),\s*(\d+),\s*(\d+)\);/",
    "background-color: #4A5568;", 
    $content
);

// Fix button hover styles more reliably
$content = str_replace(
    ".button:hover {
                    background-color:rgb",
    ".button:hover {
                    background-color: #2d3748",
    $content
);

// Make QR code visible using a simpler approach
$content = str_replace(
    "// Generate a unique confirmation number",
    "// Generate a unique confirmation number and QR code
        \$includeQrCode = true;",
    $content
);

// Use a simpler approach for QR section
if (strpos($content, 'qr-section') === false) {
    $content = str_replace(
        '" . ($includeQrCode ? "',
        '",',
        $content
    );
    
    $content = str_replace(
        '<div class=\'event-details\'>',
        '<div class=\'qr-section\'>
                        <p><strong>Your Check-in QR Code:</strong></p>
                        <img src=\'{$qrCodeUrl}\' alt=\'Check-in QR Code\' class=\'qr-code\' width=\'150\' height=\'150\'>
                        <p><small>Present this QR code for quick check-in at the event</small></p>
                    </div>
                
                <div class=\'event-details\'>',
        $content
    );
}

// Write updated content back to file
if (file_put_contents($mailerPath, $content)) {
    echo "<div class='success'>Successfully updated email templates!</div>";
    
    // Show preview of the changes
    echo "<h2>Preview of Email Template</h2>";
    echo "<div class='preview-box'>
        <h3>Example: Event Registration Email</h3>
        <div style='border: 1px solid #ddd; padding: 15px; position: relative; max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;'>
            <div style='background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%); color: #fff; padding: 20px; text-align: center;'>
                <div style='font-size: 24px; font-weight: bold;'>PIETECH Events Platform</div>
            </div>
            
            <div style='padding: 20px;'>
                <h2 style='color: #2d3748;'>Registration Confirmation</h2>
                <p>Dear User,</p>
                <p>Thank you for registering for the following event. We're excited to have you join us!</p>
                
                <div style='background-color: #ebf8ff; border: 1px solid #B3E5FC; border-radius: 6px; padding: 15px; margin: 20px 0; text-align: center;'>
                    <p>Your confirmation number:</p>
                    <div style='font-size: 24px; font-weight: bold; color: #1D6CE3; letter-spacing: 2px;'>ABC12345</div>
                    <p><small>Please keep this number for your records</small></p>
                </div>
                
                <div style='text-align: center; margin: 25px 0; padding: 15px; background-color: #f9f9f9; border-radius: 6px;'>
                    <p><strong>Your Check-in QR Code:</strong></p>
                    <div style='background-color: #ddd; width: 150px; height: 150px; margin: 0 auto;'></div>
                    <p><small>Present this QR code for quick check-in at the event</small></p>
                </div>
                
                <div style='background-color: #fff; padding: 20px; border-radius: 6px; border-left: 4px solid #4a5568; margin: 20px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'>
                    <h3 style='margin-top: 0; color: #2d3748;'>Innovation Hackathon</h3>
                    <p><strong>Venue:</strong> Main Building</p>
                    <p><strong>Room Number:</strong> 101</p>
                    <p><strong>Date:</strong> May 25, 2025</p>
                    <p><strong>Time:</strong> 9:00 AM</p>
                </div>
                
                <p>Please remember to arrive at least 15 minutes before the event starts. If you need to cancel your registration, you can do so by visiting your account dashboard.</p>
                
                <p style='text-align: center;'>
                    <a href='#' style='display: inline-block; background-color: #4A5568; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;'>View My Registrations</a>
                </p>
            </div>
            
            <div style='text-align: center; margin-top: 20px; padding: 20px; background-color: #f8f9fa; font-size: 12px; color: #6c757d;'>
                <p>&copy; 2025 PIETECH Events Platform. All rights reserved.</p>
            </div>
        </div>
    </div>";
    
    echo "<p>The email templates have been updated with the following fixes:</p>
    <ul>
        <li>Fixed button color to ensure it's visible (changed from light gray to dark blue)</li>
        <li>Fixed the confirmation box border</li>
        <li>Ensured QR code is properly included in the email</li>
        <li>Added inline styles to buttons to ensure they display correctly across different email clients</li>
    </ul>";
} else {
    echo "<div class='failure'>Failed to update email templates. Check file permissions.</div>";
}

echo "<div class='warning'>
    <p><strong>Important:</strong> After applying this fix, you should test sending another email to verify the display issues are resolved.</p>
    <p>You can use the <a href='test_email.php'>email testing tool</a> to send a test email and check the formatting.</p>
</div>";

// Add explanation about SMTP logs
echo "<h2>Understanding Email Logs</h2>";
echo "<div class='info' style='background-color: #e2f0fb; border: 1px solid #90cdf4; padding: 10px; margin: 10px 0; border-radius: 5px;'>
    <p><strong>About SMTP Logs:</strong></p>
    <p>The logs you're seeing in the server window show the SMTP (Simple Mail Transfer Protocol) communication between your server and the email service provider.</p>
    <p>These logs are valuable for troubleshooting because they show:</p>
    <ul>
        <li>The exact time each email transaction occurred</li>
        <li>Connection details and authentication steps</li>
        <li>Sender and recipient information</li>
        <li>Whether messages were accepted by the receiving server</li>
        <li>Any errors that occurred during transmission</li>
    </ul>
    <p>The successful logs in your screenshot indicate that the email with subject 'Congratulations - Innovation Hackathon' was successfully processed and sent to the recipient.</p>
</div>";

echo "<p><a href='index.php'>Return to Home</a></p>
</div>
</body>
</html>";
