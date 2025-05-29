<?php
/**
 * Asset Checker
 * 
 * This script checks if all necessary assets are available and properly configured.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Asset Check</title>
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
    <h1>PIETECH Events Platform Asset Check</h1>
    <p>This tool checks all necessary files and configurations needed for proper display.</p>";

// Check for logo
echo "<h2>Logo Check</h2>";
$logoPath = __DIR__ . '/images/logo.png';
if (file_exists($logoPath)) {
    echo "<div class='success'>";
    echo "<p>✅ Logo found at /images/logo.png</p>";
    echo "<img src='images/logo.png' alt='PIETECH Logo' height='40'>";
    echo "</div>";
} else {
    echo "<div class='failure'>";
    echo "<p>❌ Logo not found at /images/logo.png</p>";
    echo "</div>";
    
    // Check if we should create image directory
    if (!is_dir(__DIR__ . '/images')) {
        if (mkdir(__DIR__ . '/images', 0755, true)) {
            echo "<div class='success'><p>Created images directory</p></div>";
        } else {
            echo "<div class='failure'><p>Failed to create images directory</p></div>";
        }
    }
    
    // Check for logo in the deployment directory
    $deploymentLogoPath = __DIR__ . '/deploy_files/logo.png';
    if (file_exists($deploymentLogoPath)) {
        if (copy($deploymentLogoPath, $logoPath)) {
            echo "<div class='success'>";
            echo "<p>✅ Copied logo from deployment files to /images/logo.png</p>";
            echo "</div>";
        } else {
            echo "<div class='failure'>";
            echo "<p>❌ Failed to copy logo from deployment files</p>";
            echo "</div>";
        }
    }
}

// Check URL configuration
echo "<h2>URL Configuration</h2>";

// Check if APP_URL is defined in app.php
$appConfig = file_get_contents(__DIR__ . '/config/app.php');
if (preg_match("/define\\('APP_URL',\\s*'([^']+)'\\)/", $appConfig, $matches)) {
    $appUrl = $matches[1];
    echo "<div class='success'>";
    echo "<p>✅ APP_URL is defined as: $appUrl</p>";
    
    // Check for trailing slash
    if (substr($appUrl, -1) === '/') {
        echo "<p>⚠️ APP_URL has a trailing slash which may cause issues with some paths</p>";
    }
    echo "</div>";
} else {
    echo "<div class='failure'>";
    echo "<p>❌ APP_URL not properly defined in app.php</p>";
    echo "</div>";
}

// Check CSS and JS files
echo "<h2>CSS and JS Files</h2>";
$cssPath = __DIR__ . '/public/css/style.css';
$jsPath = __DIR__ . '/public/js/scripts.js';

echo "<table>";
echo "<tr><th>File</th><th>Status</th><th>Size</th><th>Last Modified</th></tr>";

if (file_exists($cssPath)) {
    echo "<tr><td>/public/css/style.css</td><td>✅ Found</td><td>" . filesize($cssPath) . " bytes</td><td>" . date("Y-m-d H:i:s", filemtime($cssPath)) . "</td></tr>";
} else {
    echo "<tr><td>/public/css/style.css</td><td>❌ Missing</td><td>-</td><td>-</td></tr>";
}

if (file_exists($jsPath)) {
    echo "<tr><td>/public/js/scripts.js</td><td>✅ Found</td><td>" . filesize($jsPath) . " bytes</td><td>" . date("Y-m-d H:i:s", filemtime($jsPath)) . "</td></tr>";
} else {
    echo "<tr><td>/public/js/scripts.js</td><td>❌ Missing</td><td>-</td><td>-</td></tr>";
}
echo "</table>";

// Check redirect function implementation
echo "<h2>Redirect Function Check</h2>";
$functionsFile = file_get_contents(__DIR__ . '/includes/functions.php');
if (strpos($functionsFile, 'function redirect') !== false) {
    echo "<div class='success'>";
    echo "<p>✅ Redirect function found in functions.php</p>";
    echo "</div>";
    
    // Check getBaseUrl implementation
    if (strpos($functionsFile, 'function getBaseUrl') !== false) {
        echo "<div class='success'>";
        echo "<p>✅ getBaseUrl function found in functions.php</p>";
        
        // Check if getBaseUrl uses rtrim to handle trailing slashes
        if (strpos($functionsFile, 'rtrim') !== false && strpos($functionsFile, 'getBaseUrl') !== false) {
            echo "<p>✅ getBaseUrl function appears to handle trailing slashes properly</p>";
        } else {
            echo "<p>⚠️ getBaseUrl function might not be handling trailing slashes properly</p>";
        }
        echo "</div>";
    } else {
        echo "<div class='failure'>";
        echo "<p>❌ getBaseUrl function not found in functions.php</p>";
        echo "</div>";
    }
} else {
    echo "<div class='failure'>";
    echo "<p>❌ Redirect function not found in functions.php</p>";
    echo "</div>";
}

echo "<h2>Recommendations</h2>";
echo "<ol>";
echo "<li>Make sure logo.png is in the images directory</li>";
echo "<li>Ensure APP_URL in app.php is properly set to 'https://pietech-events.is-best.net' without trailing slash</li>";
echo "<li>Update the getBaseUrl function to handle URLs properly</li>";
echo "<li>Make sure init.php loads app.php before other files</li>";
echo "</ol>";

echo "<p><a href='index.php'>Return to Homepage</a></p>";

echo "</body></html>";
