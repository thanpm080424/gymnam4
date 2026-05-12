<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();
$stmt = $db->query("DESCRIBE GOI_TAP");
if ($stmt) {
    $columns = $stmt->fetchAll();
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} else {
    echo "Query failed!";
}
?>
