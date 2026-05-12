<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();
$stmt = $db->query("SHOW TABLES LIKE 'LOP_HOC_NHOM'");
if ($stmt && $stmt->fetch()) {
    echo "Table LOP_HOC_NHOM exists.";
} else {
    echo "Table LOP_HOC_NHOM DOES NOT exist.";
}
?>
