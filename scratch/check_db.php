<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();
$stmt = $db->query("DESCRIBE GOI_TAP");
$columns = $stmt->fetchAll();
print_r($columns);
?>
