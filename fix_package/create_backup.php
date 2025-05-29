<?php
/**
 * Backup Script
 * 
 * This script creates backups of all files that will be modified
 * by the deployment fix. Run this script BEFORE applying any fixes.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$backup_dir = __DIR__ . '/backups_' . date('Y-m-d_His');
$files_to_backup = [
    __DIR__ . '/config/database.php',
    __DIR__ . '/includes/Database.php',
    __DIR__ . '/config/app.php',
    __DIR__ . '/includes/functions.php',
    __DIR__ . '/includes/init.php',
    __DIR__ . '/config/email.php',
    __DIR__ . '/images/logo.png'
];

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>EventsPro Backup</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .failure { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>PIETECH Events Platform - Backup Tool</h1>
    <p>This tool will create backups of all files that will be modified by the deployment fix.</p>";

// Create backup directory
if (!file_exists($backup_dir)) {
    if (mkdir($backup_dir, 0755, true)) {
        echo "<div class='success'>Created backup directory: " . htmlspecialchars($backup_dir) . "</div>";
    } else {
        echo "<div class='failure'>Failed to create backup directory: " . htmlspecialchars($backup_dir) . "</div>";
        exit(1);
    }
}

// Backup each file
foreach ($files_to_backup as $file) {
    if (file_exists($file)) {
        $filename = basename($file);
        $directory = dirname($file);
        $relative_dir = str_replace(__DIR__, '', $directory);
        
        // Create subdirectories in backup folder if needed
        $target_dir = $backup_dir . $relative_dir;
        if (!file_exists($target_dir) && !mkdir($target_dir, 0755, true)) {
            echo "<div class='failure'>Failed to create directory structure: " . htmlspecialchars($target_dir) . "</div>";
            continue;
        }
        
        $target_file = $target_dir . '/' . $filename;
        
        if (copy($file, $target_file)) {
            echo "<div class='success'>✓ Backed up: " . htmlspecialchars($file) . " → " . htmlspecialchars($target_file) . "</div>";
        } else {
            echo "<div class='failure'>✗ Failed to backup: " . htmlspecialchars($file) . "</div>";
        }
    } else {
        echo "<div class='failure'>⚠️ File not found, cannot backup: " . htmlspecialchars($file) . "</div>";
    }
}

echo "<h2>Backup Complete</h2>";
echo "<div class='success'>All available files have been backed up to: " . htmlspecialchars($backup_dir) . "</div>";
echo "<p>Now you can safely deploy the fixes. If something goes wrong, you can restore these backup files.</p>";
echo "</body></html>";
