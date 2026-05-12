<?php
require 'database/config.php';
$db = Database::getConnection();
$res = $db->query("
    SELECT lh.ma_lich, l.ten_lop, u.ten_dang_nhap as hlv, lh.ngay_hoc, lh.gio_bat_dau 
    FROM LICH_HOC_NHOM lh
    JOIN LOP_HOC_NHOM l ON lh.ma_lop = l.ma_lop
    JOIN HUAN_LUYEN_VIEN h ON lh.ma_hlv = h.ma_hlv
    JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
")->fetchAll();
foreach($res as $r) echo implode(' | ', $r) . "\n";
?>
