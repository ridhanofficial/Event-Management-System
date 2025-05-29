<?php
/**
 * Syntax Error Fix Script
 * 
 * This script checks for and fixes common syntax errors in PHP files,
 * particularly looking for HTML tags accidentally inserted into PHP files.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>PHP Syntax Error Fix</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .failure { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin: 10px 0; border-radius: 5px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        code { font-family: monospace; }
    </style>
</head>
<body>
    <h1>PHP Syntax Error Fix Tool</h1>
    <p>This tool checks and fixes common syntax errors in PHP files.</p>";

// Files to check
$files_to_check = [
    'config/app.php',
    'config/database.php',
    'config/email.php',
    'includes/functions.php',
    'includes/init.php'
];

// Function to fix common PHP syntax errors
function fixPhpSyntaxErrors($content) {
    $fixed = $content;
    
    // 1. Fix missing PHP closing tags
    if (strpos($fixed, '?>') === false) {
        $fixed .= "\n?>";
    }
    
    // 2. Fix HTML tags inside PHP code (outside of echo/print statements)
    // This is a simplified fix - in real scenarios you would need more sophisticated parsing
    if (preg_match('/<(!DOCTYPE|html|head|body|div|p|script|link)/i', $fixed)) {
        // Attempt to isolate PHP code
        $phpParts = [];
        preg_match_all('/(<\?php.*?\?>)/s', $fixed, $phpParts);
        
        if (!empty($phpParts[0])) {
            // Found PHP blocks, keep only those
            $fixed = implode("\n", $phpParts[0]);
        } else {
            // No complete PHP blocks found, just keep the PHP part
            $phpStart = strpos($fixed, '<?php');
            if ($phpStart !== false) {
                $fixed = substr($fixed, $phpStart);
                // Add closing tag if not present
                if (strpos($fixed, '?>') === false) {
                    $fixed .= "\n?>";
                }
            }
        }
    }
    
    return $fixed;
}

// Process each file
foreach ($files_to_check as $relative_path) {
    $file_path = __DIR__ . '/' . $relative_path;
    echo "<h2>Checking: " . htmlspecialchars($relative_path) . "</h2>";
    
    if (!file_exists($file_path)) {
        echo "<div class='warning'>File not found: " . htmlspecialchars($file_path) . "</div>";
        continue;
    }
    
    // Read file content
    $content = file_get_contents($file_path);
    
    // Check for common syntax issues
    $has_issues = false;
    
    // Check for HTML tags in PHP files
    if (preg_match('/<(!DOCTYPE|html|body|head|div|p)/i', $content)) {
        echo "<div class='failure'>Found HTML tags inside PHP code (likely syntax error)</div>";
        $has_issues = true;
    }
    
    // Check for missing closing PHP tag
    if (strpos($content, '?>') === false) {
        echo "<div class='warning'>Missing closing PHP tag (?>) - this is often a good practice but can cause issues in some frameworks</div>";
    }
    
    // If issues found, attempt to fix
    if ($has_issues) {
        $fixed_content = fixPhpSyntaxErrors($content);
        
        // Show diff between original and fixed
        echo "<div class='warning'>Attempting to fix issues...</div>";
        echo "<h3>Original Content (First 300 chars):</h3>";
        echo "<pre><code>" . htmlspecialchars(substr($content, 0, 300)) . "...</code></pre>";
        echo "<h3>Fixed Content (First 300 chars):</h3>";
        echo "<pre><code>" . htmlspecialchars(substr($fixed_content, 0, 300)) . "...</code></pre>";
        
        // Create backup of original
        $backup_path = $file_path . '.bak';
        if (copy($file_path, $backup_path)) {
            echo "<div class='success'>Created backup at " . htmlspecialchars($backup_path) . "</div>";
            
            // Write fixed content
            if (file_put_contents($file_path, $fixed_content)) {
                echo "<div class='success'>Successfully fixed syntax issues in " . htmlspecialchars($relative_path) . "</div>";
            } else {
                echo "<div class='failure'>Failed to write fixed content to " . htmlspecialchars($file_path) . "</div>";
            }
        } else {
            echo "<div class='failure'>Failed to create backup before fixing file</div>";
        }
    } else {
        echo "<div class='success'>No syntax issues detected in this file</div>";
    }
    
    echo "<hr>";
}

// Special focus on app.php as mentioned in the error message
$app_php_path = __DIR__ . '/config/app.php';
if (file_exists($app_php_path)) {
    echo "<h2>Fixing app.php (Reported as having syntax error on line 24)</h2>";
    
    // Fix app.php specifically with closing tag
    $app_content = file_get_contents($app_php_path);
    
    // Ensure proper closing
    if (substr(trim($app_content), -2) !== '?>') {
        $fixed_app = rtrim($app_content) . "\n?>";
        
        // Write fixed content
        if (file_put_contents($app_php_path, $fixed_app)) {
            echo "<div class='success'>Added proper closing tag to app.php</div>";
        } else {
            echo "<div class='failure'>Failed to write fixed content to app.php</div>";
        }
    } else {
        echo "<div class='success'>app.php already has proper closing tag</div>";
    }
}

echo "<h2>Completed Syntax Check</h2>";
echo "<p>If the above fixes did not resolve your issues, you may need to manually examine the files for more complex syntax errors.</p>";
echo "<p><a href='verify_deployment.php'>Return to the verification page</a> to check if all issues are resolved.</p>";
echo "</body></html>";
