<?php
/**
 * Database Connection Class
 * 
 * This class provides a PDO database connection for the application.
 */

class Database {
    private $host;
    private $dbName;
    private $username;
    private $password;
    private $conn;      public function __construct() {
        // Check if we're on a production domain
        $host = $_SERVER['HTTP_HOST'] ?? '';        if (strpos($host, 'pietech-events.is-best.net') !== false) {
            // Production database credentials - using tested working credentials
            $this->host = 'sql205.hstn.me';  // Corrected hostname based on testing
            $this->dbName = 'mseet_38774389_events';
            $this->username = 'mseet_38774389'; // Corrected username based on testing
            $this->password = 'ridhan93';
        } else {
            // Read database configuration from environment variables
            $this->host = getenv('DB_HOST') ?: 'localhost';
            $this->dbName = getenv('DB_NAME') ?: 'pietech_events';
            $this->username = getenv('DB_USER') ?: 'root';
            $this->password = getenv('DB_PASS') ?: '';
        }
        
        $this->connect();
    }
    
    /**
     * Connect to the database
     * 
     * @return void
     */    private function connect() {
        try {
            // First try connecting with dbname in the DSN
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5, // 5 second timeout
                ];
                
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $dbException) {
                // If connection with dbname fails, try connecting without dbname
                // This helps in scenarios where the database needs to be created
                $dsn = "mysql:host={$this->host};charset=utf8mb4";
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                
                // Check if database exists
                $stmt = $this->conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->dbName}'");
                $dbExists = $stmt->rowCount() > 0;
                
                if (!$dbExists) {
                    // Create the database if it doesn't exist
                    $this->conn->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    error_log("Database {$this->dbName} created successfully.");
                }
                
                // Now connect with the database name
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            }
            
        } catch (PDOException $e) {
            // Log detailed error information
            $errorMessage = "Database connection failed: " . $e->getMessage();
            $errorDetails = [
                'host' => $this->host,
                'dbname' => $this->dbName,
                'username' => $this->username,
                'error_code' => $e->getCode(),
                'error_info' => $e->errorInfo ?? 'Not available'
            ];
            
            error_log($errorMessage);
            error_log("Connection details: " . json_encode($errorDetails));
            
            // In production, show a user-friendly error
            if ($_SERVER['HTTP_HOST'] ?? '' == 'pietech-events.is-best.net') {
                // Check if this is an AJAX request
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Database connection failed. Please try again later.']);
                    exit;
                }
                
                // Regular request - show error page
                include_once __DIR__ . '/../maintenance.html';
                exit;
            } else {
                // In development, show detailed error
                die("<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px;border-radius:5px;'>
                    <h3>Database Connection Failed</h3>
                    <p>{$e->getMessage()}</p>
                    <p>Host: {$this->host}</p>
                    <p>Database: {$this->dbName}</p>
                    <p>User: {$this->username}</p>
                </div>");
            }
        }
    }
    
    /**
     * Get the database connection
     * 
     * @return PDO The database connection
     */
    public function getConnection() {
        return $this->conn;
    }
} 