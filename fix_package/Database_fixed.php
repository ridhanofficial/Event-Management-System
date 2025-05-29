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
    private $conn;
    
    public function __construct() {
        // Check if we're on a production domain
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        if (strpos($host, 'pietech-events.is-best.net') !== false) {
            // Production database credentials - using tested working credentials
            $this->host = 'sql205.hstn.me';  // Corrected hostname based on testing
            $this->dbName = 'mseet_38774389_events';
            $this->username = 'mseet_38774389'; // Corrected username based on testing
            $this->password = 'ridhan93';
        } else {
            // Local development database credentials
            $this->host = 'localhost';
            $this->dbName = 'eventspro';
            $this->username = 'root';
            $this->password = '';
        }
        
        $this->connect();
    }
    
    /**
     * Connect to the database
     * 
     * @return void
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Create PDO instance
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // Handle connection error
            
            // Check if this is a production server
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $is_production = (strpos($host, 'pietech-events.is-best.net') !== false);
            
            // Check if this is an AJAX request
            $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
            
            // Different error handling based on environment and request type
            if ($is_production) {
                // Production error handling
                if ($is_ajax) {
                    // For AJAX requests, return JSON error
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Database connection error. Please try again later.']);
                    exit;
                }
                
                // Regular request - show error page
                include_once __DIR__ . '/../maintenance.html';
                exit;
            } else {
                // In development, show detailed error
                $error_message = "Database Connection Failed: " . $e->getMessage();
                $error_message .= " (Host: {$this->host}, Database: {$this->dbName}, User: {$this->username})";
                die($error_message);
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
?>
