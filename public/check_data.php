<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();
$stmt = $db->query("SELECT * FROM LOP_HOC_NHOM");
if ($stmt) {
    $rows = $stmt->fetchAll();
    echo "Found " . count($rows) . " rows in LOP_HOC_NHOM.";
    print_r($rows);
} else {
    echo "Query failed!";
}
?>
