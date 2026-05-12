<?php
require 'database/config.php';
$db = Database::getConnection();
// Xóa các bản ghi trùng lặp (cùng HLV, ngày, giờ) chỉ giữ lại bản ghi có ma_lich nhỏ nhất
$sql = "DELETE t1 FROM LICH_HOC_NHOM t1
        INNER JOIN LICH_HOC_NHOM t2 
        WHERE t1.ma_lich > t2.ma_lich 
        AND t1.ma_hlv = t2.ma_hlv 
        AND t1.ngay_hoc = t2.ngay_hoc 
        AND t1.gio_bat_dau = t2.gio_bat_dau";
$db->query($sql);
echo "Dữ liệu đã được dọn dẹp!";
?>
