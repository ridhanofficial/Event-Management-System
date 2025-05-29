<?php
/**
 * Logo Fix Script
 * 
 * This script checks if the logo file exists in the correct location
 * and fixes any path issues.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Logo Fix</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .failure { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>PIETECH Events Platform Logo Fix</h1>";

// Check if logo file exists in images directory
$localLogoPath = __DIR__ . '/images/logo.png';
if (file_exists($localLogoPath)) {
    echo "<div class='success'>";
    echo "<p>✅ Logo file found at /images/logo.png</p>";
    echo "<p>File size: " . filesize($localLogoPath) . " bytes</p>";
    echo "<p>Last modified: " . date("Y-m-d H:i:s", filemtime($localLogoPath)) . "</p>";
    echo "</div>";
    
    echo "<div>";
    echo "<p>Here's how your logo looks:</p>";
    echo "<img src='images/logo.png' alt='PIETECH Logo' height='40'>";
    echo "</div>";
} else {
    echo "<div class='failure'>";
    echo "<p>❌ Logo file NOT found at /images/logo.png</p>";
    
    // Check if logo exists in other directories
    $possibleLocations = [
        __DIR__ . '/public/images/logo.png',
        __DIR__ . '/public/img/logo.png',
        __DIR__ . '/assets/images/logo.png',
        __DIR__ . '/img/logo.png'
    ];
    
    $logoFound = false;
    foreach ($possibleLocations as $location) {
        if (file_exists($location)) {
            $logoFound = true;
            // Get relative path
            $relativePath = str_replace(__DIR__ . '/', '', $location);
            echo "<p>✅ Logo found at: $relativePath</p>";
            
            // Copy logo to the correct location
            if (!is_dir(__DIR__ . '/images')) {
                mkdir(__DIR__ . '/images', 0755, true);
            }
            
            copy($location, $localLogoPath);
            echo "<p>✅ Logo copied to /images/logo.png</p>";
            break;
        }
    }
    
    if (!$logoFound) {
        echo "<p>Could not find logo in any expected location.</p>";
        echo "<p>Please upload your logo to /images/logo.png</p>";
    }
    
    echo "</div>";
}

// Check if init.php is loading app.php
$initFile = file_get_contents(__DIR__ . '/includes/init.php');
if (strpos($initFile, "require_once __DIR__ . '/../config/app.php'") !== false) {
    echo "<div class='success'>";
    echo "<p>✅ init.php is correctly loading app.php</p>";
    echo "</div>";
} else {
    echo "<div class='failure'>";
    echo "<p>❌ init.php is NOT loading app.php</p>";
    echo "<p>Please update init.php to include app.php before other files</p>";
    echo "</div>";
}

// Check getBaseUrl function
$functionsFile = file_get_contents(__DIR__ . '/includes/functions.php');
if (strpos($functionsFile, "rtrim") !== false && strpos($functionsFile, "getBaseUrl") !== false) {
    echo "<div class='success'>";
    echo "<p>✅ getBaseUrl function is correctly implemented to remove trailing slashes</p>";
    echo "</div>";
} else {
    echo "<div class='failure'>";
    echo "<p>❌ getBaseUrl function may not be handling URLs correctly</p>";
    echo "</div>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Ensure app.php is loaded in init.php</li>";
echo "<li>Check that the logo file exists in the /images directory</li>";
echo "<li>Make sure APP_URL in app.php is correctly set without trailing slash</li>";
echo "<li>Verify getBaseUrl function removes trailing slashes from URLs</li>";
echo "</ol>";

echo "<p><a href='index.php'>Return to Homepage</a></p>";

echo "</body></html>";
