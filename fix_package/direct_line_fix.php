<?php
/**
 * Direct Line Fix for Mailer.php
 * 
 * This script specifically targets and fixes line 131 in Mailer.php
 * which is causing the "unexpected token 'and'" syntax error.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Direct Line Fix for Mailer.php</h1>";

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
    exit;
}

echo "<p>Found Mailer.php at: {$mailerPath}</p>";

// Create backup
$backup = $mailerPath . '.bak.' . date('YmdHis');
if (copy($mailerPath, $backup)) {
    echo "<p>Created backup at: " . basename($backup) . "</p>";
} else {
    echo "<p style='color: red;'>Warning: Could not create backup. Proceeding anyway.</p>";
}

// Read file as individual lines
$lines = file($mailerPath);
if (!$lines) {
    echo "<p style='color: red;'>Error: Could not read file.</p>";
    exit;
}

// Check if we have line 131
if (!isset($lines[130])) { // 0-based index
    echo "<p style='color: red;'>Error: File does not have 131 lines.</p>";
    exit;
}

echo "<p>Original line 131: <code>" . htmlspecialchars($lines[130]) . "</code></p>";

// Fix line 131 specifically
$lines[130] = "                    background-color: #2d3748;\n";

echo "<p>New line 131: <code>" . htmlspecialchars($lines[130]) . "</code></p>";

// Write the file back
if (file_put_contents($mailerPath, implode('', $lines))) {
    echo "<p style='color: green;'>Successfully fixed line 131 in Mailer.php!</p>";
    echo "<p>Please try sending an email again to verify the fix worked.</p>";
} else {
    echo "<p style='color: red;'>Error: Could not write to file. Check permissions.</p>";
}

echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
?>
