<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();
$res = $db->query("DESCRIBE LICH_HOC_NHOM")->fetchAll();
echo "<pre>"; print_r($res); echo "</pre>";
?>
