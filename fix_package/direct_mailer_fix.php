<?php
/**
 * Direct Mailer Fix
 * 
 * This script fixes the syntax error in Mailer.php line 130
 * by removing the invalid syntax "and QR code"
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Mailer.php Syntax Fix</h1>";

// Try multiple possible paths to find Mailer.php
$possiblePaths = [
    '/home/vol2_8/hstn.me/mseet_38774389/pietech-events.is-best.net/htdocs/includes/Mailer.php', // Production path
    dirname(__DIR__) . '/includes/Mailer.php', // Local XAMPP path
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
    echo "<p style='color: red;'>Error: Could not find Mailer.php in any expected location.</p>";
    echo "<form method='post'>
        <p>Please enter the full path to Mailer.php:</p>
        <input type='text' name='custom_path' style='width: 80%;' required>
        <button type='submit'>Fix File</button>
    </form>";
    
    if (isset($_POST['custom_path']) && !empty($_POST['custom_path'])) {
        $mailerPath = $_POST['custom_path'];
        if (!file_exists($mailerPath)) {
            echo "<p style='color: red;'>Error: File not found at specified path.</p>";
            exit;
        }
    } else {
        exit;
    }
}

echo "<p>Found Mailer.php at: " . htmlspecialchars($mailerPath) . "</p>";

// Create backup
$backup = $mailerPath . '.bak.' . date('YmdHis');
if (copy($mailerPath, $backup)) {
    echo "<p>Created backup at: " . basename($backup) . "</p>";
} else {
    echo "<p style='color: orange;'>Warning: Could not create backup file. Proceeding anyway.</p>";
}

// Read file content
$content = file_get_contents($mailerPath);
if ($content === false) {
    echo "<p style='color: red;'>Error: Could not read file.</p>";
    exit;
}

// Fix line 130 and surrounding lines
// The issue is the invalid syntax "and QR code" that appears multiple times
$fixedContent = preg_replace('/\$includeQrCode = true; and QR code/', '$includeQrCode = true;', $content);

// Write back to file
if (file_put_contents($mailerPath, $fixedContent)) {
    echo "<p style='color: green;'>Successfully fixed syntax error in Mailer.php!</p>";
    
    // Show what was fixed
    echo "<div style='background-color: #f8f8f8; padding: 15px; border: 1px solid #ddd; margin: 20px 0;'>
        <h3>What was fixed:</h3>
        <pre>
// Before:
\$includeQrCode = true; <span style='color: red; font-weight: bold;'>and QR code</span>

// After:
\$includeQrCode = true;
        </pre>
        <p>Multiple instances of this invalid syntax were removed.</p>
    </div>";
    
    echo "<p>You should now be able to send emails properly. Try refreshing the page that was showing the error.</p>";
} else {
    echo "<p style='color: red;'>Error: Could not write to file. Check file permissions.</p>";
}

echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
?>
