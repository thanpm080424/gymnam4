<?php
require 'config/config.php';
require 'includes/Database.php';
$db = new Database();
$orders = $db->select('SELECT * FROM DON_HANG_SP ORDER BY ngay_mua DESC LIMIT 5');
foreach ($orders as $o) {
    echo "ID: {$o['ma_dh']} | Member: {$o['ma_hoi_vien']} | Product: {$o['ma_sp']} | Status: {$o['trang_thai']} | Date: {$o['ngay_mua']}\n";
}
