<?php
/**
 * Enhanced Database Connection Class
 * Monkey Gym Management System
 * Uses PDO for better security with prepared statements
 */

class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $charset;
    
    public $conn;
    public $error;
    
    // Static instance for backward compatibility
    private static $staticInstance = null;
    
    public function __construct() {
        $this->host     = defined('DB_HOST') ? DB_HOST : 'localhost';
        $this->user     = defined('DB_USER') ? DB_USER : 'root';
        $this->pass     = defined('DB_PASS') ? DB_PASS : '';
        $this->dbname   = defined('DB_NAME') ? DB_NAME : 'monkey_gym';
        $this->charset  = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
        
        $this->connect();
    }
    
    /**
     * Static method for backward compatibility
     * Usage: $db = Database::getConnection();
     */
    public static function getConnection() {
        if (self::$staticInstance === null) {
            self::$staticInstance = new self();
        }
        return self::$staticInstance;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES     => false,
            ];
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database connection failed: " . $this->error);
            
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
                die("Lỗi kết nối hệ thống. Vui lòng liên hệ quản trị viên.");
            } else {
                die("Connection failed: " . $this->error);
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Execute a query with prepared statement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Query failed: " . $this->error . " | SQL: " . $sql);
            return false;
        }
    }
    
    /**
     * Select multiple rows
     */
    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return false;
    }
    
    /**
     * Select single row
     */
    public function selectOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return false;
    }
    
    /**
     * Insert data and return last insert ID
     */
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    /**
     * Insert from array (automatic SQL generation)
     * @param string $table Table name
     * @param array $data Key-value pairs: ['field' => 'value']
     */
    public function insertArray($table, $data = []) {
        if (empty($data)) return false;
        
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO " . $table . " (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        
        return $this->insert($sql, $values);
    }
    
    /**
     * Update data from array
     * @param string $table Table name
     * @param array $data Fields to update
     * @param string $where WHERE clause (e.g., "id = ?")
     * @param array $whereParams Parameters for WHERE clause
     */
    public function updateArray($table, $data = [], $where = '', $whereParams = []) {
        if (empty($data) || empty($where)) return false;
        
        $fields = array_keys($data);
        $values = array_values($data);
        
        $updates = [];
        foreach ($fields as $field) {
            $updates[] = "$field = ?";
        }
        
        $params = array_merge($values, $whereParams);
        $sql = "UPDATE " . $table . " SET " . implode(', ', $updates) . " WHERE " . $where;
        
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }
    
    /**
     * Update query
     */
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }
    
    /**
     * Delete data
     */
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }
    
    /**
     * Delete from array
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters
     */
    public function deleteArray($table, $where = '', $params = []) {
        if (empty($where)) return false;
        
        $sql = "DELETE FROM " . $table . " WHERE " . $where;
        return $this->delete($sql, $params);
    }
    
    /**
     * Count records
     */
    public function count($sql, $params = []) {
        $result = $this->selectOne($sql, $params);
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Check if record exists
     */
    public function exists($sql, $params = []) {
        $result = $this->selectOne($sql, $params);
        return !empty($result);
    }
    
    /**
     * Get last error
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Close connection
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Alias: execute() = query() - dùng cho MonkeyGym_Full compatibility
     */
    public function execute($sql, $params = []) {
        return $this->query($sql, $params);
    }

    /**
     * Alias: lastInsertId() - dùng cho MonkeyGym_Full compatibility
     */
    public function lastInsertId() {
        return $this->conn ? $this->conn->lastInsertId() : false;
    }

    /**
     * beginTransaction / commit / rollBack wrappers
     */
    public function beginTransaction() {
        return $this->conn ? $this->conn->beginTransaction() : false;
    }
    public function commit() {
        return $this->conn ? $this->conn->commit() : false;
    }
    public function rollBack() {
        if ($this->conn && $this->conn->inTransaction()) {
            return $this->conn->rollBack();
        }
        return false;
    }

    public function inTransaction() {
        return $this->conn ? $this->conn->inTransaction() : false;
    }



    /**
     * prepare() - expose PDO prepare cho legacy controllers
     */
    public function prepare($sql) {
        try {
            return $this->conn->prepare($sql);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("[DB prepare] " . $e->getMessage());
            return false;
        }
    }

}