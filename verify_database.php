<?php
/**
 * Database Structure Verification Script
 * 
 * This script checks if all required tables exist in the database
 * and creates missing tables as needed for the EventsPro platform.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$db_host = 'sql209.hstn.me'; // External MySQL server hostname
$db_name = 'mseet_38774389_events';
$db_user = 'mseet_38774389';
$db_pass = 'ridhan93';

// Required tables and their schema definitions
$required_tables = [
    'users' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'name' => 'VARCHAR(100) NOT NULL',
        'email' => 'VARCHAR(100) NOT NULL UNIQUE',
        'password' => 'VARCHAR(255) NOT NULL',
        'role' => 'ENUM("admin", "user") DEFAULT "user"',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'events' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'title' => 'VARCHAR(200) NOT NULL',
        'description' => 'TEXT',
        'venue' => 'VARCHAR(200)',
        'event_date' => 'DATE NOT NULL',
        'start_time' => 'TIME',
        'end_time' => 'TIME',
        'capacity' => 'INT DEFAULT 0',
        'registration_deadline' => 'DATETIME',
        'created_by' => 'INT',
        'status' => 'ENUM("draft", "published", "cancelled", "completed") DEFAULT "draft"',
        'banner_image' => 'VARCHAR(255)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'registrations' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'event_id' => 'INT NOT NULL',
        'user_id' => 'INT NOT NULL',
        'registration_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'status' => 'ENUM("pending", "approved", "rejected") DEFAULT "pending"',
        'check_in' => 'DATETIME NULL',
        'department' => 'VARCHAR(100)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'feedback' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'event_id' => 'INT NOT NULL',
        'user_id' => 'INT NOT NULL',
        'rating' => 'INT',
        'comment' => 'TEXT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ]
];

// Foreign key relationships
$foreign_keys = [
    'events' => [
        'created_by' => [
            'references' => 'users(id)',
            'on_delete' => 'SET NULL'
        ]
    ],
    'registrations' => [
        'event_id' => [
            'references' => 'events(id)',
            'on_delete' => 'CASCADE'
        ],
        'user_id' => [
            'references' => 'users(id)',
            'on_delete' => 'CASCADE'
        ]
    ],
    'feedback' => [
        'event_id' => [
            'references' => 'events(id)',
            'on_delete' => 'CASCADE'
        ],
        'user_id' => [
            'references' => 'users(id)',
            'on_delete' => 'CASCADE'
        ]
    ]
];

// HTML header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Verification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .failure { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin: 10px 0; border-radius: 5px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>EventsPro Database Verification</h1>
";

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<div class='success'>Successfully connected to the database!</div>";
    
    // Get existing tables
    $stmt = $pdo->query("SHOW TABLES");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Database Tables</h2>";
    echo "<table>
        <tr>
            <th>Table Name</th>
            <th>Status</th>
            <th>Action</th>
        </tr>";
    
    foreach ($required_tables as $table_name => $columns) {
        if (in_array($table_name, $existing_tables)) {
            // Table exists, check if it has all required columns
            $stmt = $pdo->query("DESCRIBE {$table_name}");
            $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $missing_columns = array_diff(array_keys($columns), $existing_columns);
            
            if (empty($missing_columns)) {
                echo "<tr>
                    <td>{$table_name}</td>
                    <td class='success'>OK</td>
                    <td>No action needed</td>
                </tr>";
            } else {
                echo "<tr>
                    <td>{$table_name}</td>
                    <td class='warning'>Missing columns: " . implode(', ', $missing_columns) . "</td>
                    <td><a href='?add_columns=1&table={$table_name}'>Add missing columns</a></td>
                </tr>";
                
                // Add missing columns if requested
                if (isset($_GET['add_columns']) && $_GET['add_columns'] == 1 && $_GET['table'] == $table_name) {
                    try {
                        foreach ($missing_columns as $column) {
                            $pdo->exec("ALTER TABLE {$table_name} ADD COLUMN {$column} {$columns[$column]}");
                        }
                        echo "<div class='success'>Successfully added missing columns to {$table_name}!</div>";
                    } catch (PDOException $e) {
                        echo "<div class='failure'>Error adding columns: " . $e->getMessage() . "</div>";
                    }
                }
            }
        } else {
            echo "<tr>
                <td>{$table_name}</td>
                <td class='failure'>Missing</td>
                <td><a href='?create_table=1&table={$table_name}'>Create table</a></td>
            </tr>";
            
            // Create missing table if requested
            if (isset($_GET['create_table']) && $_GET['create_table'] == 1 && $_GET['table'] == $table_name) {
                try {
                    $create_sql = "CREATE TABLE {$table_name} (";
                    foreach ($columns as $column => $definition) {
                        $create_sql .= "{$column} {$definition}, ";
                    }
                    
                    // Add foreign keys if defined
                    if (isset($foreign_keys[$table_name])) {
                        foreach ($foreign_keys[$table_name] as $column => $fk) {
                            $create_sql .= "FOREIGN KEY ({$column}) REFERENCES {$fk['references']} ON DELETE {$fk['on_delete']}, ";
                        }
                    }
                    
                    // Remove trailing comma and close parenthesis
                    $create_sql = rtrim($create_sql, ', ') . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                    
                    $pdo->exec($create_sql);
                    echo "<div class='success'>Successfully created table {$table_name}!</div>";
                } catch (PDOException $e) {
                    echo "<div class='failure'>Error creating table: " . $e->getMessage() . "</div>";
                }
            }
        }
    }
    
    echo "</table>";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admin_count = $stmt->fetchColumn();
    
    echo "<h2>Admin User Check</h2>";
    
    if ($admin_count > 0) {
        echo "<div class='success'>Admin user exists ({$admin_count} found).</div>";
    } else {
        echo "<div class='warning'>No admin user found. <a href='?create_admin=1'>Create default admin user</a></div>";
        
        // Create admin user if requested
        if (isset($_GET['create_admin']) && $_GET['create_admin'] == 1) {
            try {
                $password_hash = password_hash("Admin@123", PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@pietech-events.com', :password, 'admin')");
                $stmt->bindParam(':password', $password_hash);
                $stmt->execute();
                echo "<div class='success'>Admin user created successfully! Use email: admin@pietech-events.com and password: Admin@123 to login.</div>";
            } catch (PDOException $e) {
                echo "<div class='failure'>Error creating admin user: " . $e->getMessage() . "</div>";
            }
        }
    }
    
    // Database summary
    echo "<h2>Database Summary</h2>";
    echo "<ul>";
    echo "<li><strong>Total required tables:</strong> " . count($required_tables) . "</li>";
    echo "<li><strong>Existing tables:</strong> " . count($existing_tables) . "</li>";
    echo "<li><strong>Missing tables:</strong> " . (count($required_tables) - count(array_intersect($existing_tables, array_keys($required_tables)))) . "</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<div class='failure'><strong>Database connection failed:</strong> " . $e->getMessage() . "</div>";
    
    // Provide troubleshooting steps
    echo "<h2>Troubleshooting Steps</h2>";
    echo "<ol>
        <li>Verify that database credentials are correct</li>
        <li>Make sure the database exists on the server</li>
        <li>Check if the database user has appropriate permissions</li>
        <li>Try using TCP/IP connection (127.0.0.1) instead of 'localhost'</li>
        <li>Verify that MySQL service is running on the server</li>
        <li>Check server logs for more information</li>
    </ol>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>
    <li><a href='production_test.php'>Run full production environment test</a></li>
    <li><a href='db_connection_test.php'>Test different database connection methods</a></li>
    <li><a href='status.php'>Check system status</a></li>
    <li><a href='admin/'>Go to admin panel</a></li>
</ol>";

echo "</body></html>";
