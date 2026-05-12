<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();
$stmt = $db->query("SHOW TABLES");
if ($stmt) {
    $tables = $stmt->fetchAll();
    echo "<pre>";
    print_r($tables);
    echo "</pre>";
}
?>
