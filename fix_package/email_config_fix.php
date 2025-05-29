<?php
/**
 * Email Configuration Fix
 * 
 * This script helps identify and fix email settings for the production environment.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration files
require_once __DIR__ . '/config/app.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Email Configuration Fix</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .failure { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        input[type='text'], input[type='password'], input[type='email'], input[type='number'], select { 
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; 
        }
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { border: 1px solid #ddd; border-radius: 5px; }
        .card-header { background-color: #f8f9fa; padding: 10px 20px; border-bottom: 1px solid #ddd; }
        .card-body { padding: 20px; }
        .mb-3 { margin-bottom: 15px; }
        .custom-control { position: relative; z-index: 1; display: block; min-height: 1.5rem; padding-left: 1.5rem; }
        .custom-control-input { position: absolute; left: 0; z-index: -1; width: 1rem; height: 1rem; }
        .custom-control-label { position: relative; margin-bottom: 0; vertical-align: top; cursor: pointer; }
        .custom-control-label::before { position: absolute; top: 0.25rem; left: -1.5rem; display: block; width: 1rem; height: 1rem; content: ''; background-color: #fff; border: 1px solid #adb5bd; border-radius: 0.25rem; }
        .custom-switch .custom-control-label::after { top: calc(0.25rem + 2px); left: calc(-1.5rem + 2px); width: calc(1rem - 4px); height: calc(1rem - 4px); border-radius: 0.5rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>PIETECH Events Platform - Email Configuration Fix</h1>
        <p>This tool helps identify and fix email configuration issues.</p>";

// Get current email configuration
$emailConfigPath = __DIR__ . '/config/email.php';
$emailConfig = file_exists($emailConfigPath) ? file_get_contents($emailConfigPath) : '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_config'])) {
    
    // Create backup
    if (file_exists($emailConfigPath)) {
        $backup = $emailConfigPath . '.bak.' . date('YmdHis');
        copy($emailConfigPath, $backup);
        echo "<div class='success'>Created backup of email configuration at " . basename($backup) . "</div>";
    }
    
    // Get form values
    $mailEnabled = isset($_POST['mail_enabled']) ? 'true' : 'false';
    $mailHost = $_POST['mail_host'];
    $mailPort = (int)$_POST['mail_port'];
    $mailUsername = $_POST['mail_username'];
    $mailPassword = $_POST['mail_password'];
    $mailEncryption = $_POST['mail_encryption'];
    $mailFromAddress = $_POST['mail_from_address'];
    $mailFromName = $_POST['mail_from_name'];
    $mailDebug = (int)$_POST['mail_debug'];
    
    // Create new config content
    $newConfig = <<<EOT
<?php
/**
 * Email Configuration
 * 
 * This file defines email-related configuration settings.
 */

// Helper function to get environment variables safely
function getEnvOrDefault(\$key, \$default) {
    \$value = getenv(\$key);
    return \$value !== false ? \$value : \$default;
}

// Email server settings
define('MAIL_HOST', getEnvOrDefault('MAIL_HOST', '{$mailHost}'));
define('MAIL_PORT', getEnvOrDefault('MAIL_PORT', {$mailPort}));
define('MAIL_USERNAME', getEnvOrDefault('MAIL_USERNAME', '{$mailUsername}'));
define('MAIL_PASSWORD', getEnvOrDefault('MAIL_PASSWORD', '{$mailPassword}'));
define('MAIL_ENCRYPTION', getEnvOrDefault('MAIL_ENCRYPTION', '{$mailEncryption}'));
define('MAIL_FROM_ADDRESS', getEnvOrDefault('MAIL_FROM_ADDRESS', '{$mailFromAddress}'));
define('MAIL_FROM_NAME', getEnvOrDefault('MAIL_FROM_NAME', '{$mailFromName}'));

// Email debug level (0 = off, 1 = client, 2 = client and server)
define('MAIL_DEBUG', getEnvOrDefault('MAIL_DEBUG', {$mailDebug}));

// SMTP authentication
define('MAIL_AUTH', getEnvOrDefault('MAIL_AUTH', 'true') === 'true');

// Enable/disable email sending
define('MAIL_ENABLED', getEnvOrDefault('MAIL_ENABLED', '{$mailEnabled}') === 'true');
?>
EOT;

    // Write to file
    if (file_put_contents($emailConfigPath, $newConfig)) {
        echo "<div class='success'>Email configuration has been successfully updated!</div>";
        
        // Test the configuration if requested
        if (isset($_POST['test_email'])) {
            echo "<div class='warning'>Sending test email to {$mailUsername}...</div>";
            echo "<div class='warning'>Please wait a moment while we test the configuration...</div>";
            
            // Include the new configuration
            require_once $emailConfigPath;
            
            // Include required files
            require_once __DIR__ . '/includes/init.php';
            
            // Test email
            $testSubject = "PIETECH Events Platform - Email Test";
            $testBody = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4a5568; color: #fff; padding: 10px 20px; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Email Configuration Test</h2>
                    </div>
                    <p>Hello,</p>
                    <p>This is a test email sent from the PIETECH Events Platform.</p>
                    <p>If you received this email, it means your email configuration is working correctly!</p>
                    <p>Configuration details:</p>
                    <ul>
                        <li>Host: {$mailHost}</li>
                        <li>Port: {$mailPort}</li>
                        <li>Username: {$mailUsername}</li>
                        <li>Encryption: {$mailEncryption}</li>
                    </ul>
                    <p>You can now go back and use the platform. Verification emails should work properly.</p>
                    <p>Best regards,<br>PIETECH Events Platform</p>
                </div>
            </body>
            </html>";
            
            try {
                require_once __DIR__ . '/includes/Mailer.php';
                $mailer = new Mailer();
                $sendResult = $mailer->send($mailUsername, $mailFromName, $testSubject, $testBody);
                
                if ($sendResult) {
                    echo "<div class='success'>Test email sent successfully to {$mailUsername}!</div>";
                } else {
                    echo "<div class='failure'>Failed to send test email. Please check your configuration.</div>";
                    echo "<div class='warning'>Make sure the email password is correct and the email service allows SMTP access.</div>";
                }
            } catch (Exception $e) {
                echo "<div class='failure'>Error sending test email: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    } else {
        echo "<div class='failure'>Failed to update email configuration. Check file permissions.</div>";
    }
    
    echo "<a href='email_config_fix.php' class='btn btn-primary'>Refresh</a>";
} else {
    // Extract current configuration values from the file
    $currentMailHost = preg_match("/MAIL_HOST', getEnvOrDefault\('MAIL_HOST', '([^']+)'\)/", $emailConfig, $matches) ? $matches[1] : 'smtp.gmail.com';
    $currentMailPort = preg_match("/MAIL_PORT', getEnvOrDefault\('MAIL_PORT', (\d+)\)/", $emailConfig, $matches) ? $matches[1] : 587;
    $currentMailUsername = preg_match("/MAIL_USERNAME', getEnvOrDefault\('MAIL_USERNAME', '([^']+)'\)/", $emailConfig, $matches) ? $matches[1] : '';
    $currentMailPassword = preg_match("/MAIL_PASSWORD', getEnvOrDefault\('MAIL_PASSWORD', '([^']+)'\)/", $emailConfig, $matches) ? $matches[1] : '';
    $currentMailEncryption = preg_match("/MAIL_ENCRYPTION', getEnvOrDefault\('MAIL_ENCRYPTION', '([^']+)'\)/", $emailConfig, $matches) ? $matches[1] : 'tls';
    $currentMailFromAddress = preg_match("/MAIL_FROM_ADDRESS', getEnvOrDefault\('MAIL_FROM_ADDRESS', '([^']+)'\)/", $emailConfig, $matches) ? $matches[1] : 'noreply@example.com';
    $currentMailFromName = preg_match("/MAIL_FROM_NAME', getEnvOrDefault\('MAIL_FROM_NAME', '([^']+)'\)/", $emailConfig, $matches) ? $matches[1] : 'PIETECH Events Platform';
    $currentMailDebug = preg_match("/MAIL_DEBUG', getEnvOrDefault\('MAIL_DEBUG', (\d+)\)/", $emailConfig, $matches) ? $matches[1] : 0;
    $currentMailEnabled = preg_match("/MAIL_ENABLED', getEnvOrDefault\('MAIL_ENABLED', '([^']+)'\)/", $emailConfig, $matches) ? $matches[1] === 'true' : false;
    
    // Show current email issues
    echo "<div class='card mb-3'>
        <div class='card-header'>
            <h2 class='mb-0'>Current Email Status</h2>
        </div>
        <div class='card-body'>";
    
    if (!$currentMailEnabled) {
        echo "<div class='failure'>Email functionality is currently <strong>DISABLED</strong></div>";
    } else {
        echo "<div class='success'>Email functionality is currently <strong>ENABLED</strong></div>";
    }
    
    if ($currentMailPassword === 'your-app-password' || $currentMailPassword === '') {
        echo "<div class='failure'>Email password is not set or is using the default placeholder value</div>";
    }
    
    if ($currentMailHost === 'smtp.gmail.com' && strpos($currentMailUsername, '@gmail.com') === false) {
        echo "<div class='warning'>Gmail SMTP server is configured but the username doesn't look like a Gmail address</div>";
    }
    
    if ($currentMailFromAddress === 'noreply@example.com') {
        echo "<div class='warning'>Default 'From' email address is being used which might cause emails to be marked as spam</div>";
    }
    
    echo "</div>
    </div>";
    
    // Configuration form
    echo "<div class='card'>
        <div class='card-header'>
            <h2 class='mb-0'>Update Email Configuration</h2>
        </div>
        <div class='card-body'>
            <form method='post' action=''>
                <div class='form-group custom-control custom-switch mb-3'>
                    <input type='checkbox' class='custom-control-input' id='mail_enabled' name='mail_enabled' " . ($currentMailEnabled ? 'checked' : '') . ">
                    <label class='custom-control-label' for='mail_enabled'>Enable Email Functionality</label>
                    <small class='form-text text-muted'>When enabled, the system will send emails for account verification and event registrations.</small>
                </div>
                
                <h4>SMTP Server Configuration</h4>
                
                <div class='grid-2 mb-3'>
                    <div class='form-group'>
                        <label for='mail_host'>SMTP Host</label>
                        <input type='text' class='form-control' id='mail_host' name='mail_host' value='" . htmlspecialchars($currentMailHost) . "' required>
                        <small class='form-text text-muted'>e.g. smtp.gmail.com, smtp.mailgun.org, etc.</small>
                    </div>
                    
                    <div class='form-group'>
                        <label for='mail_port'>SMTP Port</label>
                        <input type='number' class='form-control' id='mail_port' name='mail_port' value='" . htmlspecialchars($currentMailPort) . "' required>
                        <small class='form-text text-muted'>Common ports: 587 (TLS), 465 (SSL), 25 (unsecured)</small>
                    </div>
                </div>
                
                <div class='grid-2 mb-3'>
                    <div class='form-group'>
                        <label for='mail_username'>SMTP Username</label>
                        <input type='email' class='form-control' id='mail_username' name='mail_username' value='" . htmlspecialchars($currentMailUsername) . "' required>
                        <small class='form-text text-muted'>Usually your email address</small>
                    </div>
                    
                    <div class='form-group'>
                        <label for='mail_password'>SMTP Password</label>
                        <input type='password' class='form-control' id='mail_password' name='mail_password' value='" . htmlspecialchars($currentMailPassword) . "' required>
                        <small class='form-text text-muted'>For Gmail, use an <a href='https://support.google.com/accounts/answer/185833' target='_blank'>App Password</a></small>
                    </div>
                </div>
                
                <div class='form-group mb-3'>
                    <label for='mail_encryption'>Encryption</label>
                    <select class='form-control' id='mail_encryption' name='mail_encryption' required>
                        <option value='tls' " . ($currentMailEncryption === 'tls' ? 'selected' : '') . ">TLS</option>
                        <option value='ssl' " . ($currentMailEncryption === 'ssl' ? 'selected' : '') . ">SSL</option>
                        <option value='none' " . ($currentMailEncryption === 'none' ? 'selected' : '') . ">None</option>
                    </select>
                    <small class='form-text text-muted'>TLS is recommended for most servers</small>
                </div>
                
                <h4>Sender Information</h4>
                
                <div class='grid-2 mb-3'>
                    <div class='form-group'>
                        <label for='mail_from_address'>From Email Address</label>
                        <input type='email' class='form-control' id='mail_from_address' name='mail_from_address' value='" . htmlspecialchars($currentMailFromAddress) . "' required>
                        <small class='form-text text-muted'>This should be the same as your SMTP username or a verified sender</small>
                    </div>
                    
                    <div class='form-group'>
                        <label for='mail_from_name'>From Name</label>
                        <input type='text' class='form-control' id='mail_from_name' name='mail_from_name' value='" . htmlspecialchars($currentMailFromName) . "' required>
                        <small class='form-text text-muted'>Name that will appear in the 'From' field</small>
                    </div>
                </div>
                
                <div class='form-group mb-3'>
                    <label for='mail_debug'>Debug Level</label>
                    <select class='form-control' id='mail_debug' name='mail_debug'>
                        <option value='0' " . ($currentMailDebug === 0 ? 'selected' : '') . ">Off (0)</option>
                        <option value='1' " . ($currentMailDebug === 1 ? 'selected' : '') . ">Client Messages (1)</option>
                        <option value='2' " . ($currentMailDebug === 2 ? 'selected' : '') . ">Client & Server Messages (2)</option>
                    </select>
                    <small class='form-text text-muted'>Set to 1 or 2 for troubleshooting, 0 for production</small>
                </div>
                
                <div class='form-group custom-control custom-switch mb-3'>
                    <input type='checkbox' class='custom-control-input' id='test_email' name='test_email' checked>
                    <label class='custom-control-label' for='test_email'>Send Test Email After Saving</label>
                    <small class='form-text text-muted'>A test email will be sent to the SMTP username address</small>
                </div>
                
                <div class='mb-3'>
                    <button type='submit' name='update_config' class='btn btn-primary'>Save Configuration</button>
                </div>
            </form>
        </div>
    </div>";
}

echo "<div class='mb-3 mt-3'>
    <a href='index.php' class='btn btn-secondary'>Return to Home</a>
</div>";

echo "</div>
</body>
</html>";
