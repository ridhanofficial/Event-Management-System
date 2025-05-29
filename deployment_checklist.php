<?php
/**
 * Deployment Checklist
 * 
 * This script helps ensure a successful deployment by checking various
 * system requirements and configuration settings.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the checks to perform
$checks = [
    'php_version' => [
        'name' => 'PHP Version',
        'requirement' => '7.3.0 or higher',
        'status' => version_compare(PHP_VERSION, '7.3.0', '>='),
        'details' => 'Current version: ' . PHP_VERSION
    ],
    'pdo_mysql' => [
        'name' => 'PDO MySQL Extension',
        'requirement' => 'Enabled',
        'status' => extension_loaded('pdo_mysql'),
        'details' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not installed'
    ],
    'mbstring' => [
        'name' => 'Multibyte String Extension',
        'requirement' => 'Enabled',
        'status' => extension_loaded('mbstring'),
        'details' => extension_loaded('mbstring') ? 'Installed' : 'Not installed'
    ],
    'gd' => [
        'name' => 'GD Library',
        'requirement' => 'Enabled',
        'status' => extension_loaded('gd'),
        'details' => extension_loaded('gd') ? 'Installed' : 'Not installed'
    ],
    'file_uploads' => [
        'name' => 'File Uploads',
        'requirement' => 'Enabled',
        'status' => ini_get('file_uploads'),
        'details' => ini_get('file_uploads') ? 'Enabled' : 'Disabled'
    ],
    'upload_max_filesize' => [
        'name' => 'Upload Max Filesize',
        'requirement' => '8M or more',
        'status' => intval(ini_get('upload_max_filesize')) >= 8,
        'details' => 'Current setting: ' . ini_get('upload_max_filesize')
    ],
    'config_exists' => [
        'name' => 'Config Files',
        'requirement' => 'Present',
        'status' => file_exists(__DIR__ . '/config/database.php'),
        'details' => file_exists(__DIR__ . '/config/database.php') ? 'Available' : 'Missing'
    ]
];

// Directory permissions to check
$directory_permissions = [
    '/uploads' => 0755,
    '/uploads/events' => 0755,
    '/uploads/profiles' => 0755,
    '/temp' => 0755,
    '/logs' => 0755
];

// Required database tables
$required_tables = ['users', 'events', 'registrations', 'feedback'];

// HTML header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>EventsPro Deployment Checklist</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 1200px; margin: 0 auto; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .failure { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin: 10px 0; border-radius: 5px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .steps { background-color: #e2e3e5; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .step { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>EventsPro Deployment Checklist</h1>
    <p>This script helps verify that your server meets all requirements for running the EventsPro platform.</p>
";

// 1. System Requirements Check
echo "<h2>1. System Requirements Check</h2>";
echo "<table>
    <tr>
        <th>Requirement</th>
        <th>Expected</th>
        <th>Current</th>
        <th>Status</th>
    </tr>";

$requirements_met = true;
foreach ($checks as $check) {
    echo "<tr>
        <td>{$check['name']}</td>
        <td>{$check['requirement']}</td>
        <td>{$check['details']}</td>
        <td>" . ($check['status'] ? "✅" : "❌") . "</td>
    </tr>";
    
    if (!$check['status']) {
        $requirements_met = false;
    }
}
echo "</table>";

if ($requirements_met) {
    echo "<div class='success'>All system requirements are met!</div>";
} else {
    echo "<div class='failure'>Some system requirements are not met. Please resolve these issues before continuing.</div>";
}

// 2. Directory Permissions
echo "<h2>2. Directory Permissions</h2>";
echo "<table>
    <tr>
        <th>Directory</th>
        <th>Required Permissions</th>
        <th>Current Permissions</th>
        <th>Status</th>
    </tr>";

$all_permissions_correct = true;
foreach ($directory_permissions as $directory => $required_permission) {
    $dir_path = __DIR__ . $directory;
    $exists = is_dir($dir_path);
    
    // Create directory if it doesn't exist
    if (!$exists && isset($_GET['create_dirs'])) {
        mkdir($dir_path, $required_permission, true);
        $exists = is_dir($dir_path);
    }
    
    if ($exists) {
        $current_permission = substr(sprintf('%o', fileperms($dir_path)), -4);
        $permission_ok = is_writable($dir_path);
        
        echo "<tr>
            <td>{$directory}</td>
            <td>" . decoct($required_permission) . "</td>
            <td>{$current_permission}</td>
            <td>" . ($permission_ok ? "✅" : "❌") . "</td>
        </tr>";
        
        if (!$permission_ok) {
            $all_permissions_correct = false;
        }
    } else {
        echo "<tr>
            <td>{$directory}</td>
            <td>" . decoct($required_permission) . "</td>
            <td>Directory not found</td>
            <td>❌</td>
        </tr>";
        $all_permissions_correct = false;
    }
}
echo "</table>";

if ($all_permissions_correct) {
    echo "<div class='success'>All directory permissions are correct!</div>";
} else {
    echo "<div class='warning'>Some directories are missing or have incorrect permissions. <a href='?create_dirs=1'>Create missing directories</a></div>";
    echo "<p>You may need to manually set permissions using FTP or SSH: <code>chmod -R 755 directory_path</code></p>";
}

// 3. Database Connection Check
echo "<h2>3. Database Connection Check</h2>";
try {
    // Include database config
    require_once __DIR__ . '/config/database.php';
    
    // Check if we can connect to the database
    if (DB_HOST && DB_NAME && DB_USER) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            echo "<div class='success'>Successfully connected to the database!</div>";
            
            // Check if all required tables exist
            $existing_tables = [];
            $stmt = $pdo->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $existing_tables[] = $row[0];
            }
            
            $missing_tables = array_diff($required_tables, $existing_tables);
            
            if (empty($missing_tables)) {
                echo "<div class='success'>All required database tables exist!</div>";
            } else {
                echo "<div class='warning'>Missing tables: " . implode(', ', $missing_tables) . ". <a href='db_setup.php'>Run database setup script</a></div>";
            }
        } catch (PDOException $e) {
            echo "<div class='failure'>Database connection failed: " . $e->getMessage() . "</div>";
            echo "<p>You should run the <a href='db_connection_test.php'>database connection test</a> to troubleshoot.</p>";
        }
    } else {
        echo "<div class='failure'>Database configuration is incomplete.</div>";
    }
} catch (Exception $e) {
    echo "<div class='failure'>Error loading database configuration: " . $e->getMessage() . "</div>";
}

// 4. TCPDF Library Check
echo "<h2>4. TCPDF Library Check</h2>";
if (file_exists(__DIR__ . '/app/libraries/tcpdf/tcpdf.php')) {
    echo "<div class='success'>TCPDF library is installed!</div>";
} else {
    echo "<div class='failure'>TCPDF library not found. PDF exports will not work.</div>";
    echo "<p>Install TCPDF with the following steps:</p>";
    echo "<pre>
1. Download TCPDF from https://tcpdf.org/
2. Extract the files to " . __DIR__ . "/app/libraries/tcpdf/
3. Make sure tcpdf.php is directly under this folder
</pre>";
}

// 5. Deployment Steps
echo "<h2>5. Deployment Checklist</h2>";
echo "<div class='steps'>";
echo "<h3>Follow these steps in order:</h3>";

echo "<div class='step'>
    <h4>Step 1: Server Configuration</h4>
    <p>Ensure all system requirements are met (see section 1 above)</p>
</div>";

echo "<div class='step'>
    <h4>Step 2: Directory Setup</h4>
    <p>Create required directories and set proper permissions (see section 2 above)</p>
</div>";

echo "<div class='step'>
    <h4>Step 3: Database Setup</h4>
    <ol>
        <li>Run <a href='db_connection_test.php'>Database Connection Test</a> to find the best connection method</li>
        <li>If needed, update database configuration in config/database.php</li>
        <li>Run <a href='db_setup.php'>Database Setup Script</a> to create tables</li>
        <li>Run <a href='verify_database.php'>Database Verification</a> to verify table structure</li>
    </ol>
</div>";

echo "<div class='step'>
    <h4>Step 4: Final Checks</h4>
    <ol>
        <li>Run <a href='production_test.php'>Production Environment Test</a> to verify the entire system</li>
        <li>Install missing libraries if needed (TCPDF)</li>
        <li>Test login using admin credentials</li>
        <li>Test event creation and attendee registration</li>
        <li>Test attendance recording and export functions</li>
    </ol>
</div>";

echo "<div class='step'>
    <h4>Step 5: Security Cleanup</h4>
    <p>Once everything is working, remove or restrict access to these files:</p>
    <ul>
        <li>db_setup.php</li>
        <li>db_connection_test.php</li>
        <li>production_test.php</li>
        <li>verify_database.php</li>
        <li>permission_check.php</li>
        <li>deployment_checklist.php</li>
    </ul>
</div>";

echo "</div>";

// 6. Quick Links
echo "<h2>6. Useful Links</h2>";
echo "<ul>
    <li><a href='db_connection_test.php'>Database Connection Test</a></li>
    <li><a href='db_setup.php'>Database Setup Script</a></li>
    <li><a href='verify_database.php'>Database Structure Verification</a></li>
    <li><a href='production_test.php'>Production Environment Test</a></li>
    <li><a href='status.php'>System Status</a></li>
    <li><a href='admin/'>Admin Panel</a></li>
</ul>";

echo "</body>
</html>";
