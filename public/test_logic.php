<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getConnection();
    echo "<h1>TEST RESULTS</h1>";

    // --- TEST 1: EMAIL SYNC ---
    echo "<h2>TEST 1: EMAIL SYNC</h2>";
    try {
        $db->execute("INSERT INTO NGUOI_DUNG (ma_nguoi_dung, ten_dang_nhap, mat_khau, ho_ten, email, vai_tro) VALUES (999, 'test@test.com', '123', 'Test User', 'test@test.com', 'hoi_vien') ON DUPLICATE KEY UPDATE ten_dang_nhap='test@test.com', email='test@test.com'");
    } catch (Exception $e) {
        echo "<p style='color:red'>Setup error: " . $e->getMessage() . "</p>";
    }

    // Logic from MemberController
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

    $finalUsername = $db->prepare("SELECT ten_dang_nhap FROM NGUOI_DUNG WHERE ma_nguoi_dung = 999");
    $finalUsername->execute();
    $username = $finalUsername->fetchColumn();
    echo "<p>Expected Username: newemail@test.com<br>";
    echo "Actual Username: <strong>$username</strong><br>";
    echo ($username === 'newemail@test.com' ? "<span style='color:green'>PASSED</span>" : "<span style='color:red'>FAILED</span>") . "</p>";

    // --- TEST 2: PT BOOKING OVERLAP ---
    echo "<h2>TEST 2: PT BOOKING OVERLAP</h2>";
    $db->execute("INSERT INTO NGUOI_DUNG (ma_nguoi_dung, ten_dang_nhap, vai_tro) VALUES (998, 'trainer_test', 'hlv') ON DUPLICATE KEY UPDATE vai_tro='hlv'");
    $db->execute("INSERT INTO HUAN_LUYEN_VIEN (ma_hlv, ma_nguoi_dung) VALUES (99, 998) ON DUPLICATE KEY UPDATE ma_nguoi_dung=998");
    $db->execute("INSERT INTO HOI_VIEN (ma_hoi_vien, ma_nguoi_dung, so_buoi_pt_con_lai) VALUES (99, 999, 10) ON DUPLICATE KEY UPDATE so_buoi_pt_con_lai=10");

    $db->execute("DELETE FROM LICH_DAT_PT WHERE ma_hoi_vien=99 OR ma_hlv=99");
    
    // Book at 10:00
    $ngayGio1 = '2030-01-01 10:00:00';
    $db->prepare("INSERT INTO LICH_DAT_PT (ma_hoi_vien, ma_hlv, loai_pt, ngay_gio_tap, trang_thai) VALUES (99, 99, 'gym', ?, 'confirmed')")->execute([$ngayGio1]);
    echo "<p>Booked session at $ngayGio1.</p>";

    // Try overlap 10:30
    $ngayGio2 = '2030-01-01 10:30:00';
    $stmtOverlap = $db->prepare("SELECT COUNT(*) FROM LICH_DAT_PT WHERE ma_hlv = ? AND ABS(TIMESTAMPDIFF(MINUTE, ngay_gio_tap, ?)) < 60 AND trang_thai IN ('confirmed')");
    $stmtOverlap->execute([99, $ngayGio2]);
    $overlap30 = $stmtOverlap->fetchColumn();
    
    echo "<p>Checking overlap for $ngayGio2 (30 mins apart)...<br>";
    echo "Expected block: Yes (Count > 0)<br>";
    echo "Actual block count: <strong>$overlap30</strong><br>";
    echo ($overlap30 > 0 ? "<span style='color:green'>PASSED (BLOCKED)</span>" : "<span style='color:red'>FAILED (ALLOWED)</span>") . "</p>";

    // Try valid 11:30
    $ngayGio3 = '2030-01-01 11:30:00';
    $stmtOverlap->execute([99, $ngayGio3]);
    $overlap90 = $stmtOverlap->fetchColumn();
    
    echo "<p>Checking overlap for $ngayGio3 (90 mins apart)...<br>";
    echo "Expected block: No (Count = 0)<br>";
    echo "Actual block count: <strong>$overlap90</strong><br>";
    echo ($overlap90 == 0 ? "<span style='color:green'>PASSED (ALLOWED)</span>" : "<span style='color:red'>FAILED (BLOCKED)</span>") . "</p>";

    // Cleanup
    $db->execute("DELETE FROM LICH_DAT_PT WHERE ma_hoi_vien=99 OR ma_hlv=99");
    $db->execute("DELETE FROM HOI_VIEN WHERE ma_hoi_vien=99");
    $db->execute("DELETE FROM HUAN_LUYEN_VIEN WHERE ma_hlv=99");
    $db->execute("DELETE FROM NGUOI_DUNG WHERE ma_nguoi_dung IN (999, 998)");

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
}
?>
