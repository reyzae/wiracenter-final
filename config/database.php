<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private $port;

    public function __construct() {
        // Load environment variables if not already loaded
        $this->loadEnvironmentVariables();
        
        // Use environment variables only - no hardcoded fallbacks for security
        $this->host = $_ENV['DB_HOST'] ?? null;
        $this->db_name = $_ENV['DB_NAME'] ?? null;
        $this->username = $_ENV['DB_USER'] ?? null;
        $this->password = $_ENV['DB_PASS'] ?? null;
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        
        // Validate required environment variables
        if (!$this->host || !$this->db_name || !$this->username || !$this->password) {
            error_log("Database configuration error: Missing required environment variables");
            throw new Exception("Database configuration incomplete. Please check your .env file.");
        }
        
        // Log configuration for debugging (only in debug mode)
        if (isset($_ENV['DEBUG_MODE']) && $_ENV['DEBUG_MODE'] == '1') {
            error_log("Database Config - Host: {$this->host}, DB: {$this->db_name}, User: {$this->username}");
        }
    }

    private function loadEnvironmentVariables() {
        // If environment variables are not loaded, try to load them
        if (empty($_ENV)) {
            $env_file = __DIR__ . '/../.env';
            if (file_exists($env_file)) {
                try {
                    // Load .env file manually with better parsing
                    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lines as $line) {
                        // Skip comments and empty lines
                        $line = trim($line);
                        if (empty($line) || strpos($line, '#') === 0) {
                            continue;
                        }
                        
                        // Only process lines with = sign
                        if (strpos($line, '=') !== false) {
                            $parts = explode('=', $line, 2);
                            if (count($parts) == 2) {
                                $key = trim($parts[0]);
                                $value = trim($parts[1]);
                                
                                // Remove quotes if present
                                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                                    $value = substr($value, 1, -1);
                                }
                                
                                // Only set if key is not empty
                                if (!empty($key)) {
                                    $_ENV[$key] = $value;
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error loading .env file: " . $e->getMessage());
                }
            }
        }
    }

    public function connect(): ?PDO {
        $this->conn = null;
        
        // Try multiple connection methods for different environments
        $connection_methods = [
            // Method 1: Direct connection with current host
            function() {
                return $this->tryConnection($this->host, $this->db_name, $this->username, $this->password);
            },
            // Method 2: Try localhost if current host is not localhost
            function() {
                if ($this->host !== 'localhost') {
                    return $this->tryConnection('localhost', $this->db_name, $this->username, $this->password);
                }
                return null;
            },
            // Method 3: Try 127.0.0.1
            function() {
                return $this->tryConnection('127.0.0.1', $this->db_name, $this->username, $this->password);
            },
            // Method 4: Try without database name (connect to server first)
            function() {
                return $this->tryConnection($this->host, null, $this->username, $this->password);
            }
        ];

        foreach ($connection_methods as $method) {
            try {
                $conn = $method();
                if ($conn) {
                    $this->conn = $conn;
                    
                    // If we connected without database, try to create/select it
                    if (!$this->db_name && $this->conn) {
                        $this->createDatabaseIfNotExists();
                    }
                    
                    if (isset($_ENV['DEBUG_MODE']) && $_ENV['DEBUG_MODE'] == '1') {
                        error_log("Database connection successful to {$this->host}");
                    }
                    
                    return $this->conn;
                }
            } catch (Exception $e) {
                error_log("Connection attempt failed: " . $e->getMessage());
                continue;
            }
        }

        // If all methods failed, log detailed error
        error_log("All database connection methods failed");
        error_log("Final connection details - Host: {$this->host}, DB: {$this->db_name}, User: {$this->username}");
        return null;
    }

    private function tryConnection($host, $dbname, $username, $password) {
        try {
            if ($dbname) {
                $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            } else {
                $dsn = "mysql:host={$host};charset=utf8mb4";
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log("Connection failed for {$host}: " . $e->getMessage());
            return null;
        }
    }

    private function createDatabaseIfNotExists() {
        try {
            $this->conn->exec("CREATE DATABASE IF NOT EXISTS `{$this->db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->conn->exec("USE `{$this->db_name}`");
            error_log("Database '{$this->db_name}' created/selected successfully");
        } catch (Exception $e) {
            error_log("Error creating database: " . $e->getMessage());
        }
    }

    public function getConnection() {
        if (!$this->conn) {
            return $this->connect();
        }
        return $this->conn;
    }

    public function close() {
        $this->conn = null;
    }

    // Static method for backward compatibility
    public static function getInstance() {
        return new self();
    }
    
    // Method to test connection
    public function testConnection() {
        try {
            $conn = $this->connect();
            if ($conn) {
                $stmt = $conn->query("SELECT 1");
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Connection test failed: " . $e->getMessage());
            return false;
        }
    }
}
?>