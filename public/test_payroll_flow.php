<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();

echo "--- TEST: CALCULATE PAYROLL ---\n";
// 1. Giả lập một lịch dạy cho HLV 1
$db->query("INSERT INTO LICH_HOC_NHOM (ma_lop, ma_hlv, ngay_hoc, gio_bat_dau, gio_ket_thuc) VALUES (1, 1, CURDATE(), '08:00', '09:00')");

// 2. Chạy tính lương
$month = date('m/Y');
$dbMonth = date('Y-m');
$db->query("DELETE FROM BANG_LUONG WHERE thang_nam = ? AND trang_thai = 'chua_thanh_toan'", [$month]);
$trainers = $db->query("SELECT * FROM HUAN_LUYEN_VIEN WHERE ma_hlv = 1")->fetchAll();
foreach ($trainers as $t) {
    $ma_hlv = $t['ma_hlv'];
    $luong_cung = $t['luong_cung'] ?? 5000000;
    $gia_buoi = $t['gia_buoi_day'] ?? 150000;
    $so_buoi = $db->query("SELECT COUNT(*) FROM LICH_HOC_NHOM WHERE ma_hlv = ? AND DATE_FORMAT(ngay_hoc, '%Y-%m') = ?", [$ma_hlv, $dbMonth])->fetchColumn();
    $tong = $luong_cung + ($so_buoi * $gia_buoi);
    $db->query("INSERT INTO BANG_LUONG (ma_hlv, thang_nam, luong_cung, so_buoi_day, thuong_hoa_hong, tong_luong) VALUES (?, ?, ?, ?, ?, ?)", 
        [$ma_hlv, $month, $luong_cung, $so_buoi, $so_buoi * $gia_buoi, $tong]);
}
echo "Calculation Done.\n";

// 3. Kiểm tra kết quả
$res = $db->query("SELECT * FROM BANG_LUONG WHERE ma_hlv = 1 ORDER BY ma_luong DESC LIMIT 1")->fetch();
print_r($res);

echo "\n--- TEST: PAY SALARY ---\n";
$ma_luong = $res['ma_luong'];
$db->query("UPDATE BANG_LUONG SET trang_thai = 'da_thanh_toan' WHERE ma_luong = ?", [$ma_luong]);
$res_after = $db->query("SELECT trang_thai FROM BANG_LUONG WHERE ma_luong = ?", [$ma_luong])->fetch();
echo "Status after pay: " . $res_after['trang_thai'] . "\n";
?>
