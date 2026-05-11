<?php
require 'config/config.php';
require 'includes/Database.php';
$db = new Database();
$products = $db->select('SELECT ma_sp, ten_sp, gia_tien, ton_kho FROM SAN_PHAM LIMIT 5');
foreach ($products as $p) {
    echo "ID: {$p['ma_sp']} | Name: {$p['ten_sp']} | Price: {$p['gia_tien']} | Stock: {$p['ton_kho']}\n";
}
