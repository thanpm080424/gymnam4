<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();
$users = $db->query("SELECT ten_dang_nhap, vai_tro FROM NGUOI_DUNG LIMIT 20")->fetchAll();
echo "<pre>";
print_r($users);
echo "</pre>";
?>
