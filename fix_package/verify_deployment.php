<?php
/**
 * Deployment Verification Script
 * 
 * This script checks if all fixes have been correctly applied
 * to the production server at https://pietech-events.is-best.net
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Deployment Verification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .failure { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin: 10px 0; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        h2 { margin-top: 25px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h1>PIETECH Events Platform - Deployment Verification</h1>
    <p>This script checks if all fixes have been correctly applied.</p>";

// Track verification results
$all_checks_passed = true;

// 1. Check if app.php is configured correctly
echo "<h2>1. Application URL Configuration</h2>";
if (file_exists(__DIR__ . '/config/app.php')) {
    $app_config = file_get_contents(__DIR__ . '/config/app.php');
    
    // Check for HTML tags in PHP file (common error)
    if (preg_match('/<(!DOCTYPE|html|body|head|div|p)/i', $app_config)) {
        echo "<div class='failure'>✗ app.php contains HTML tags which is causing a syntax error</div>";
        $all_checks_passed = false;
    }
    // Check for proper closing tag
    elseif (strpos($app_config, '?>') === false) {
        echo "<div class='warning'>⚠️ app.php is missing the closing PHP tag ?></div>";
    }
    
    if (strpos($app_config, "define('APP_URL', 'https://pietech-events.is-best.net')") !== false) {
        echo "<div class='success'>✓ APP_URL is correctly configured without trailing slash</div>";
    } else {
        echo "<div class='failure'>✗ APP_URL is not correctly configured in app.php</div>";
        $all_checks_passed = false;
    }
} else {
    echo "<div class='failure'>✗ app.php file not found!</div>";
    $all_checks_passed = false;
}

// 2. Check Database Connection
echo "<h2>2. Database Connection</h2>";
try {
    // Include database config
    if (file_exists(__DIR__ . '/config/database.php')) {
        include_once __DIR__ . '/config/database.php';
    } else {
        throw new Exception("Database configuration file not found!");
    }
    
    // Production database credentials - verified working configuration
    $host = defined('DB_HOST') ? DB_HOST : 'sql205.hstn.me';
    $dbname = defined('DB_NAME') ? DB_NAME : 'mseet_38774389_events';
    $username = defined('DB_USER') ? DB_USER : 'mseet_38774389';
    $password = defined('DB_PASS') ? DB_PASS : 'ridhan93';
    
    // Attempt connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    echo "<div class='success'>✓ Database connection successful! Found {$result['count']} users.</div>";
} catch (PDOException $e) {
    echo "<div class='failure'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    $all_checks_passed = false;
} catch (Exception $e) {
    echo "<div class='failure'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $all_checks_passed = false;
}

// 3. Check Logo Visibility
echo "<h2>3. Logo Visibility</h2>";
$logo_path = __DIR__ . '/images/logo.png';
if (file_exists($logo_path)) {
    echo "<div class='success'>✓ Logo file exists at correct path</div>";
    echo "<div class='success'><img src='images/logo.png' alt='Site Logo' style='max-width: 200px;'><br>If you can see the logo above, it's working correctly!</div>";
} else {
    echo "<div class='failure'>✗ Logo file not found at " . htmlspecialchars($logo_path) . "</div>";
    $all_checks_passed = false;
}

// 4. Check Functions.php
echo "<h2>4. URL Handling Functions</h2>";
if (file_exists(__DIR__ . '/includes/functions.php')) {
    $functions_content = file_get_contents(__DIR__ . '/includes/functions.php');
    if (strpos($functions_content, 'return rtrim($baseUrl, \'/\');') !== false) {
        echo "<div class='success'>✓ getBaseUrl() function correctly removes trailing slashes</div>";
    } else {
        echo "<div class='failure'>✗ getBaseUrl() function may not be correctly updated</div>";
        $all_checks_passed = false;
    }
} else {
    echo "<div class='failure'>✗ functions.php file not found!</div>";
    $all_checks_passed = false;
}

// 5. Check Init.php
echo "<h2>5. Initialization Loading Order</h2>";
if (file_exists(__DIR__ . '/includes/init.php')) {
    $init_content = file_get_contents(__DIR__ . '/includes/init.php');
    if (strpos($init_content, "require_once __DIR__ . '/../config/app.php';") !== false) {
        echo "<div class='success'>✓ init.php correctly loads app.php first</div>";
    } else {
        echo "<div class='failure'>✗ init.php may not load app.php in the correct order</div>";
        $all_checks_passed = false;
    }
} else {
    echo "<div class='failure'>✗ init.php file not found!</div>";
    $all_checks_passed = false;
}

// 6. Test Email Configuration
echo "<h2>6. Email Configuration</h2>";
if (file_exists(__DIR__ . '/config/email.php')) {
    $email_config = file_get_contents(__DIR__ . '/config/email.php');
    if (strpos($email_config, '?>') !== false) {
        echo "<div class='success'>✓ email.php has proper closing PHP tag</div>";
    } else {
        echo "<div class='warning'>⚠️ email.php might be missing closing PHP tag</div>";
    }
} else {
    echo "<div class='failure'>✗ email.php file not found!</div>";
    $all_checks_passed = false;
}

// Final verdict
echo "<h2>Overall Deployment Status</h2>";
if ($all_checks_passed) {
    echo "<div class='success'><strong>✓ All fixes have been successfully deployed!</strong> The platform should now work correctly.</div>";
} else {
    echo "<div class='failure'><strong>✗ Some fixes have not been correctly applied.</strong> Please review the issues above and redeploy the missing or incorrect fixes.</div>";
}

echo "</body></html>";
