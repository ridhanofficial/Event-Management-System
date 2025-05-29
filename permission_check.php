<?php
/**
 * File Permission Checker and Fixer
 * 
 * This script checks and optionally fixes file permissions for deployment
 * to ensure proper security and functionality on the production server.
 * 
 * Usage:
 * php permission_check.php [fix]
 * 
 * - Running without arguments will check and report issues
 * - Running with 'fix' argument will attempt to fix permissions
 */

// Define color codes for CLI output
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_RED', "\033[31m");
define('COLOR_RESET', "\033[0m");

// Check if running in CLI mode
if (PHP_SAPI !== 'cli') {
    die("This script must be run from the command line.");
}

// Parse arguments
$fixMode = isset($argv[1]) && $argv[1] === 'fix';

echo "EventsPro Permission " . ($fixMode ? "Fixer" : "Checker") . "\n";
echo "====================================\n\n";

// Base directory (current directory)
$baseDir = __DIR__;

// Define permission targets
$dirPermission = 0755;  // rwxr-xr-x
$filePermission = 0644; // rw-r--r--
$executablePermission = 0755; // rwxr-xr-x for scripts

// Define special file types
$executableExtensions = ['sh', 'bash', 'bat', 'cmd'];

// Define directories to exclude (will not be checked/fixed)
$excludeDirs = [
    '.git',
    'node_modules',
    'vendor'
];

// Define special directories that need write permission
$writableDirs = [
    'uploads',
    'public/uploads',
    'public/images'
];

// Count statistics
$stats = [
    'total_dirs' => 0,
    'total_files' => 0,
    'incorrect_dirs' => 0,
    'incorrect_files' => 0,
    'fixed_dirs' => 0,
    'fixed_files' => 0,
    'error_fixes' => 0
];

/**
 * Check or fix permissions recursively
 */
function processPermissions($path, $fixMode, &$stats, $depth = 0) {
    global $dirPermission, $filePermission, $executablePermission, $excludeDirs, $writableDirs, $executableExtensions;
    
    $result = true;
    
    // Check if this directory should be excluded
    $dirName = basename($path);
    if (in_array($dirName, $excludeDirs)) {
        echo str_repeat("  ", $depth) . "Skipping excluded directory: {$path}\n";
        return true;
    }
    
    if (is_dir($path)) {
        $stats['total_dirs']++;
        
        // Check if this is a writable directory
        $isWritableDir = false;
        foreach ($writableDirs as $wDir) {
            if (strpos($path, $wDir) !== false) {
                $isWritableDir = true;
                break;
            }
        }
        
        $targetPermission = $isWritableDir ? 0777 : $dirPermission;
        $currentPermission = fileperms($path) & 0777;
        
        $permissionDisplay = sprintf("%04o", $currentPermission);
        $targetDisplay = sprintf("%04o", $targetPermission);
        
        if ($currentPermission !== $targetPermission) {
            $stats['incorrect_dirs']++;
            
            if ($fixMode) {
                echo str_repeat("  ", $depth) . COLOR_YELLOW . "Fixing dir:  {$path} ({$permissionDisplay} -> {$targetDisplay})" . COLOR_RESET . "\n";
                
                if (chmod($path, $targetPermission)) {
                    $stats['fixed_dirs']++;
                } else {
                    $stats['error_fixes']++;
                    echo str_repeat("  ", $depth) . COLOR_RED . "Failed to change permissions for {$path}" . COLOR_RESET . "\n";
                    $result = false;
                }
            } else {
                echo str_repeat("  ", $depth) . COLOR_YELLOW . "Incorrect dir:  {$path} (Current: {$permissionDisplay}, Should be: {$targetDisplay})" . COLOR_RESET . "\n";
            }
        } else if ($depth <= 3) { // Only show correct permissions for top-level directories
            echo str_repeat("  ", $depth) . COLOR_GREEN . "Correct dir:   {$path} ({$permissionDisplay})" . COLOR_RESET . "\n";
        }
        
        // Process contents recursively
        $handle = opendir($path);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') continue;
            
            $fullPath = $path . '/' . $file;
            processPermissions($fullPath, $fixMode, $stats, $depth + 1);
        }
        closedir($handle);
    } else {
        $stats['total_files']++;
        
        // Determine if this is an executable file
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isExecutable = in_array($extension, $executableExtensions);
        
        $targetPermission = $isExecutable ? $executablePermission : $filePermission;
        $currentPermission = fileperms($path) & 0777;
        
        $permissionDisplay = sprintf("%04o", $currentPermission);
        $targetDisplay = sprintf("%04o", $targetPermission);
        
        if ($currentPermission !== $targetPermission) {
            $stats['incorrect_files']++;
            
            if ($fixMode) {
                echo str_repeat("  ", $depth) . COLOR_YELLOW . "Fixing file: {$path} ({$permissionDisplay} -> {$targetDisplay})" . COLOR_RESET . "\n";
                
                if (chmod($path, $targetPermission)) {
                    $stats['fixed_files']++;
                } else {
                    $stats['error_fixes']++;
                    echo str_repeat("  ", $depth) . COLOR_RED . "Failed to change permissions for {$path}" . COLOR_RESET . "\n";
                    $result = false;
                }
            } else {
                echo str_repeat("  ", $depth) . COLOR_YELLOW . "Incorrect file: {$path} (Current: {$permissionDisplay}, Should be: {$targetDisplay})" . COLOR_RESET . "\n";
            }
        }
    }
    
    return $result;
}

// Start processing from the base directory
$success = processPermissions($baseDir, $fixMode, $stats);

// Print summary
echo "\n====================================\n";
echo "Permission Check Summary\n";
echo "====================================\n";
echo "Total directories checked: {$stats['total_dirs']}\n";
echo "Total files checked: {$stats['total_files']}\n";

if ($fixMode) {
    echo "Directories fixed: {$stats['fixed_dirs']} / {$stats['incorrect_dirs']}\n";
    echo "Files fixed: {$stats['fixed_files']} / {$stats['incorrect_files']}\n";
    
    if ($stats['error_fixes'] > 0) {
        echo COLOR_RED . "Failed fixes: {$stats['error_fixes']}" . COLOR_RESET . "\n";
    } else {
        echo COLOR_GREEN . "All permissions were fixed successfully!" . COLOR_RESET . "\n";
    }
} else {
    echo "Directories with incorrect permissions: {$stats['incorrect_dirs']}\n";
    echo "Files with incorrect permissions: {$stats['incorrect_files']}\n";
    
    if ($stats['incorrect_dirs'] > 0 || $stats['incorrect_files'] > 0) {
        echo COLOR_YELLOW . "\nTo fix permissions, run: php permission_check.php fix" . COLOR_RESET . "\n";
    } else {
        echo COLOR_GREEN . "\nAll permissions are correctly set!" . COLOR_RESET . "\n";
    }
}

echo "\n====================================\n";
echo "Target Permissions:\n";
echo "- Directories: " . sprintf("%04o", $dirPermission) . "\n";
echo "- Files: " . sprintf("%04o", $filePermission) . "\n";
echo "- Executable files: " . sprintf("%04o", $executablePermission) . "\n";
echo "- Writable directories: 0777\n";
echo "====================================\n";

exit($success ? 0 : 1);
