<?php
/**
 * Production Environment Test Script
 * 
 * This script tests connectivity to the production database and displays
 * configuration details for troubleshooting the EventsPro platform on
 * https://pietech-events.is-best.net
 */

// Set error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define helper functions
function printHeader($title) {
    echo "<div style='background-color: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
    echo "<h3>{$title}</h3>";
}

function printFooter() {
    echo "</div>";
}

function displayEnvironmentInfo() {
    printHeader("Environment Information");
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
    echo "<p><strong>Server Protocol:</strong> " . $_SERVER['SERVER_PROTOCOL'] . "</p>";
    echo "<p><strong>Domain:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
    echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
    echo "<p><strong>HTTPS:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'Yes' : 'No') . "</p>";
    printFooter();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventsPro Production Environment Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; padding-bottom: 40px; }
        .connection-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; padding: 10px; border-radius: 4px; }
        .connection-error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; overflow: auto; }
        .test-section { margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>EventsPro Production Environment Test</h1>
                <p class="lead">This page tests the configuration of the production environment for EventsPro.</p>
                
                <div class="alert alert-warning">
                    <strong>Warning!</strong> This script displays sensitive information. Delete it after testing.
                </div>
                
                <?php
                // Display environment information
                displayEnvironmentInfo();
                
                // Test database connection
                printHeader("Database Connection Test");
                
                echo "<h4>Configuration Detection:</h4>";
                echo "<p>Attempting to detect configuration based on domain...</p>";
                
                // Detect domain for configuration
                $host = $_SERVER['HTTP_HOST'] ?? '';
                $isPietech = strpos($host, 'pietech-events.is-best.net') !== false;
                
                echo $isPietech 
                    ? "<p>Detected domain: <strong>pietech-events.is-best.net</strong></p>" 
                    : "<p>Running on local or unrecognized domain: <strong>{$host}</strong></p>";
                
                // Define database credentials based on domain
                if ($isPietech) {
                    $db_host = 'localhost';
                    $db_name = 'mseet_38774389_events';
                    $db_user = 'mseet_38774389_events';
                    $db_pass = 'ridhan93';
                    
                    echo "<pre>Using production database configuration:
Host: {$db_host}
Name: {$db_name}
User: {$db_user}
</pre>";
                } else {
                    // Local environment
                    $db_host = 'localhost';
                    $db_name = 'pietech_events';
                    $db_user = 'root';
                    $db_pass = '';
                    
                    echo "<pre>Using local database configuration:
Host: {$db_host}
Name: {$db_name}
User: {$db_user}
</pre>";
                }
                
                // Attempt connection
                echo "<h4>Connection Test:</h4>";
                
                try {
                    $db = new PDO(
                        "mysql:host={$db_host};charset=utf8mb4",
                        $db_user,
                        $db_pass,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false
                        ]
                    );
                    
                    echo "<div class='connection-success'>
                        <strong>Success!</strong> Connected to MySQL server.
                    </div>";
                    
                    // Check if database exists
                    echo "<h4>Database Existence Check:</h4>";
                    $stmt = $db->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$db_name}'");
                    $dbExists = $stmt->rowCount() > 0;
                    
                    if ($dbExists) {
                        echo "<div class='connection-success'>
                            <strong>Success!</strong> Database '{$db_name}' exists.
                        </div>";
                        
                        // Connect to the specific database
                        $db = new PDO(
                            "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
                            $db_user,
                            $db_pass,
                            [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES => false
                            ]
                        );
                        
                        // Check tables
                        echo "<h4>Table Check:</h4>";
                        $stmt = $db->query("SHOW TABLES");
                        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        if (count($tables) > 0) {
                            echo "<div class='connection-success'>
                                <strong>Success!</strong> Found " . count($tables) . " tables in the database.
                            </div>";
                            
                            echo "<p>Tables found:</p>";
                            echo "<ul>";
                            foreach ($tables as $table) {
                                echo "<li>{$table}</li>";
                            }
                            echo "</ul>";
                            
                            // Check for specific required tables
                            $requiredTables = ['users', 'events', 'registrations'];
                            $missingTables = array_diff($requiredTables, $tables);
                            
                            if (count($missingTables) === 0) {
                                echo "<div class='connection-success'>
                                    <strong>Success!</strong> All required tables exist.
                                </div>";
                                
                                // Check record counts
                                echo "<h4>Record Counts:</h4>";
                                echo "<table class='table table-striped table-bordered'>";
                                echo "<thead><tr><th>Table</th><th>Record Count</th></tr></thead>";
                                echo "<tbody>";
                                
                                foreach ($requiredTables as $table) {
                                    $stmt = $db->query("SELECT COUNT(*) FROM {$table}");
                                    $count = $stmt->fetchColumn();
                                    echo "<tr><td>{$table}</td><td>{$count}</td></tr>";
                                }
                                
                                echo "</tbody></table>";
                            } else {
                                echo "<div class='connection-error'>
                                    <strong>Warning!</strong> Some required tables are missing: " . implode(', ', $missingTables) . "
                                </div>";
                            }
                        } else {
                            echo "<div class='connection-error'>
                                <strong>Warning!</strong> No tables found in the database. Database schema may not be imported.
                            </div>";
                        }
                    } else {
                        echo "<div class='connection-error'>
                            <strong>Error!</strong> Database '{$db_name}' does not exist. You need to create it first.
                        </div>";
                    }
                } catch (PDOException $e) {
                    echo "<div class='connection-error'>
                        <strong>Connection Failed!</strong> " . $e->getMessage() . "
                    </div>";
                }
                printFooter();
                
                // Check file system and permissions
                printHeader("File System Check");
                
                // Check important directories
                $directories = [
                    'public/uploads' => 'Check if upload directory exists and is writable',
                    'app/views' => 'Check if views directory exists',
                    'config' => 'Check if configuration directory exists',
                    'vendor' => 'Check if vendor directory exists'
                ];
                
                echo "<table class='table table-striped table-bordered'>";
                echo "<thead><tr><th>Directory</th><th>Status</th><th>Permissions</th></tr></thead>";
                echo "<tbody>";
                
                foreach ($directories as $dir => $desc) {
                    $path = __DIR__ . '/' . $dir;
                    $exists = file_exists($path);
                    $isDir = is_dir($path);
                    $writable = is_writable($path);
                    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
                    
                    $status = $exists && $isDir 
                        ? ($writable ? 'OK' : 'Not Writable') 
                        : 'Not Found';
                        
                    $statusClass = $exists && $isDir && $writable ? 'success' : 'danger';
                    
                    echo "<tr>";
                    echo "<td>{$dir}<br><small class='text-muted'>{$desc}</small></td>";
                    echo "<td class='text-{$statusClass}'>{$status}</td>";
                    echo "<td>{$perms}</td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";
                printFooter();
                
                // Test TCPDF library
                printHeader("TCPDF Availability Check");
                
                $tcpdfPath = __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
                $tcpdfExists = file_exists($tcpdfPath);
                
                if ($tcpdfExists) {
                    echo "<div class='connection-success'>
                        <strong>Success!</strong> TCPDF library found at {$tcpdfPath}
                    </div>";
                    
                    // Check if we can load it
                    try {
                        require_once $tcpdfPath;
                        if (class_exists('TCPDF')) {
                            echo "<div class='connection-success'>
                                <strong>Success!</strong> TCPDF class is available for use.
                            </div>";
                        } else {
                            echo "<div class='connection-error'>
                                <strong>Error!</strong> TCPDF class not found even though the file exists.
                            </div>";
                        }
                    } catch (Exception $e) {
                        echo "<div class='connection-error'>
                            <strong>Error!</strong> Could not load TCPDF: " . $e->getMessage() . "
                        </div>";
                    }
                } else {
                    echo "<div class='connection-error'>
                        <strong>Warning!</strong> TCPDF library not found at {$tcpdfPath}. 
                        PDF export functionality will not work.
                    </div>";
                    
                    // Check alternative paths
                    $altPaths = [
                        __DIR__ . '/app/libraries/tcpdf/tcpdf.php',
                        __DIR__ . '/tcpdf/tcpdf.php'
                    ];
                    
                    foreach ($altPaths as $path) {
                        if (file_exists($path)) {
                            echo "<div class='connection-success'>
                                <strong>Alternative Found!</strong> TCPDF might be available at {$path}
                            </div>";
                        }
                    }
                }
                printFooter();
                ?>
                
                <div class="mt-4">
                    <div class="alert alert-info">
                        <strong>Next Steps:</strong>
                        <ul>
                            <li>If all tests passed, your EventsPro platform should be working correctly.</li>
                            <li>If there are errors, address them based on the information provided above.</li>
                            <li>Once you've confirmed everything works, delete this test file.</li>
                            <li>Visit your site at <a href="https://pietech-events.is-best.net">https://pietech-events.is-best.net</a></li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-primary">Go to Homepage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="container mt-5">
        <hr>
        <p class="text-muted text-center">EventsPro Production Environment Test — <?php echo date('Y-m-d H:i:s'); ?></p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
