<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();
try {
    // Database class wraps the PDO connection in $db->conn
    $db->conn->exec("TRUNCATE TABLE BANG_LUONG");
    echo "Successfully truncated BANG_LUONG table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
