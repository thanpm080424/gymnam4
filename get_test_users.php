<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = new Database();
$users = $db->select("SELECT ten_dang_nhap, vai_tro FROM NGUOI_DUNG ORDER BY vai_tro");
print_r($users);
?>
