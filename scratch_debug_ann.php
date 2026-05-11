<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = new Database();
$ann = $db->selectOne("SELECT * FROM THONG_BAO_HE_THONG LIMIT 1");
echo "Columns: " . implode(', ', array_keys($ann));
?>
