<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getConnection();

echo "=== TEST 1: EMAIL SYNC ===\n";
// Create a dummy user
$db->exec("INSERT INTO NGUOI_DUNG (ma_nguoi_dung, ten_dang_nhap, mat_khau, ho_ten, email, vai_tro) VALUES (999, 'test@test.com', '123', 'Test User', 'test@test.com', 'hoi_vien') ON DUPLICATE KEY UPDATE ten_dang_nhap='test@test.com', email='test@test.com'");

// Simulate updateProfile logic
$userId = 999;
$ho_ten = "Test User Updated";
$email = "newemail@test.com";
$sdt = "0123456789";

$stmtCurr = $db->prepare("SELECT ten_dang_nhap, email FROM NGUOI_DUNG WHERE ma_nguoi_dung = ?");
$stmtCurr->execute([$userId]);
$currUser = $stmtCurr->fetch();

$stmt = $db->prepare("UPDATE NGUOI_DUNG SET ho_ten = ?, email = ?, so_dien_thoai = ? WHERE ma_nguoi_dung = ?");
$stmt->execute([$ho_ten, $email, $sdt, $userId]);

if ($currUser && $currUser['ten_dang_nhap'] === $currUser['email']) {
    $checkDup = $db->prepare("SELECT COUNT(*) FROM NGUOI_DUNG WHERE ten_dang_nhap = ? AND ma_nguoi_dung != ?");
    $checkDup->execute([$email, $userId]);
    if ($checkDup->fetchColumn() == 0) {
        $db->prepare("UPDATE NGUOI_DUNG SET ten_dang_nhap = ? WHERE ma_nguoi_dung = ?")->execute([$email, $userId]);
    }
}

$stmtCheck = $db->prepare("SELECT ten_dang_nhap FROM NGUOI_DUNG WHERE ma_nguoi_dung = ?");
$stmtCheck->execute([$userId]);
$finalUsername = $stmtCheck->fetchColumn();
echo "Username after email update: $finalUsername (Expected: newemail@test.com)\n";


echo "\n=== TEST 2: PT BOOKING OVERLAP ===\n";
// Insert dummy member and trainer
$db->exec("INSERT INTO NGUOI_DUNG (ma_nguoi_dung, ten_dang_nhap, vai_tro) VALUES (998, 'trainer_test', 'hlv') ON DUPLICATE KEY UPDATE vai_tro='hlv'");
$db->exec("INSERT INTO HUAN_LUYEN_VIEN (ma_hlv, ma_nguoi_dung) VALUES (99, 998) ON DUPLICATE KEY UPDATE ma_nguoi_dung=998");

$db->exec("INSERT INTO HOI_VIEN (ma_hoi_vien, ma_nguoi_dung, so_buoi_pt_con_lai) VALUES (99, 999, 10) ON DUPLICATE KEY UPDATE so_buoi_pt_con_lai=10");

// Book a session at 10:00
$db->exec("DELETE FROM LICH_DAT_PT WHERE ma_hoi_vien=99 OR ma_hlv=99");
$ngayGio1 = '2030-01-01 10:00:00';
$db->prepare("INSERT INTO LICH_DAT_PT (ma_hoi_vien, ma_hlv, loai_pt, ngay_gio_tap, trang_thai) VALUES (99, 99, 'gym', ?, 'confirmed')")->execute([$ngayGio1]);
echo "Booked session 1 at: $ngayGio1\n";

// Try to book at 10:30 (should be blocked)
$ngayGio2 = '2030-01-01 10:30:00';
$stmtOverlap = $db->prepare("SELECT COUNT(*) FROM LICH_DAT_PT WHERE ma_hlv = ? AND ABS(TIMESTAMPDIFF(MINUTE, ngay_gio_tap, ?)) < 60 AND trang_thai IN ('confirmed')");
$stmtOverlap->execute([99, $ngayGio2]);
$count = $stmtOverlap->fetchColumn();
echo "Overlap count for $ngayGio2: $count (Expected: > 0, should be blocked)\n";

// Try to book at 11:30 (should pass)
$ngayGio3 = '2030-01-01 11:30:00';
$stmtOverlap->execute([99, $ngayGio3]);
$count2 = $stmtOverlap->fetchColumn();
echo "Overlap count for $ngayGio3: $count2 (Expected: 0, should pass)\n";

// Cleanup
$db->exec("DELETE FROM LICH_DAT_PT WHERE ma_hoi_vien=99 OR ma_hlv=99");
$db->exec("DELETE FROM HOI_VIEN WHERE ma_hoi_vien=99");
$db->exec("DELETE FROM HUAN_LUYEN_VIEN WHERE ma_hlv=99");
$db->exec("DELETE FROM NGUOI_DUNG WHERE ma_nguoi_dung IN (999, 998)");

echo "Tests completed.\n";
?>
