<?php
require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/utils/SecurityHelper.php';

try {
    $db = Database::getConnection();

    // 1. Wipe data (Tùy chọn, để hỗ trợ chạy seed nhiều lần)
    // MySQL 5.7+ yêu cầu tắt kiểm tra FOREIGN KEY trước khi TRUNCATE
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE LICH_SU_RA_VAO");
    $db->exec("TRUNCATE TABLE THANH_TOAN");
    $db->exec("TRUNCATE TABLE DANG_KY_GOI");
    $db->exec("TRUNCATE TABLE GOI_TAP");
    $db->exec("TRUNCATE TABLE KHUYEN_MAI");
    $db->exec("TRUNCATE TABLE HOI_VIEN");
    $db->exec("TRUNCATE TABLE NGUOI_DUNG");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Da lam sach du lieu cu...\n";

    // 2. Chèn người dùng mẫu
    $hashedPassword = SecurityHelper::hashPassword('123');
    
    // -- Admin
    $stmtUser = $db->prepare("INSERT INTO NGUOI_DUNG (ten_dang_nhap, mat_khau, vai_tro) VALUES (:email, :pass, :role)");
    $stmtUser->execute(['email' => 'admin', 'pass' => $hashedPassword, 'role' => 'admin']);
    echo "Tao tai khoan Admin (admin/123)...\n";
    
    // -- Member (hội viên)
    $stmtUser->execute(['email' => 'hoivien@gmail.com', 'pass' => $hashedPassword, 'role' => 'hoi_vien']);
    $memberId = $db->lastInsertId();
    echo "Tao tai khoan Hoi vien (hoivien@gmail.com/123)...\n";

    // 3. Chèn HOI_VIEN
    $stmtHoiVien = $db->prepare("INSERT INTO HOI_VIEN (ma_nguoi_dung, ma_qr, chieu_cao, can_nang) VALUES (:user_id, :qr, :height, :weight)");
    $qrCode = "MEMBER_" . $memberId . "_" . time();
    $stmtHoiVien->execute([
        'user_id' => $memberId, 
        'qr' => $qrCode,
        'height' => 175,
        'weight' => 70
    ]);
    $maHoiVien = $db->lastInsertId();

    // 4. Chèn Gói tập mẫu (GOI_TAP)
    $stmtPackage = $db->prepare("INSERT INTO GOI_TAP (ten_goi, gia_tien, thoi_han_thang, so_buoi_pt) VALUES (:name, :price, :months, :pt)");
    $packages = [
        ['name' => 'Goi Co Ban (1 thang)', 'price' => 500000, 'months' => 1, 'pt' => 0],
        ['name' => 'Goi Tap VIP (3 thang)', 'price' => 1200000, 'months' => 3, 'pt' => 3],
        ['name' => 'Gói Platinum + PT', 'price' => 3000000, 'months' => 6, 'pt' => 10],
    ];
    
    foreach ($packages as $pkg) {
        $stmtPackage->execute($pkg);
    }
    $vipPackageId = $db->lastInsertId(); // Trỏ vào mục cuối hoặc mục tương ứng (ID=2/3)

    // 5. Đăng ký cho thẻ Hội viên 1 gói mẫu (DANG_KY_GOI)
    $stmtRegister = $db->prepare("INSERT INTO DANG_KY_GOI (ma_hoi_vien, ma_goi, ngay_kich_hoat, ngay_ket_thuc, trang_thai) VALUES (:member, :pkg, :start, :end, :status)");
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+3 months'));
    $stmtRegister->execute([
        'member' => $maHoiVien,
        'pkg' => $vipPackageId - 1, // Dùng ID 2 (VIP 3 tháng)
        'start' => $startDate,
        'end' => $endDate,
        'status' => 'active'
    ]);
    
    echo "Seed du lieu mau thanh cong!\n";

} catch (PDOException $e) {
    echo "Loi Seed Du lieu: " . $e->getMessage();
}
