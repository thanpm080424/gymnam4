<?php
require_once __DIR__ . '/../database/config.php';
try {
    $db = Database::getConnection();
    
    // 1. Bổ sung cột Lương cứng và Giá mỗi buổi dạy vào bảng HLV (nếu chưa có)
    // Kiểm tra xem cột đã tồn tại chưa trước khi thêm
    $cols = $db->query("SHOW COLUMNS FROM HUAN_LUYEN_VIEN")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('luong_cung', $cols)) {
        $db->exec("ALTER TABLE HUAN_LUYEN_VIEN ADD COLUMN luong_cung DECIMAL(10,2) DEFAULT 5000000");
    }
    if (!in_array('gia_buoi_day', $cols)) {
        $db->exec("ALTER TABLE HUAN_LUYEN_VIEN ADD COLUMN gia_buoi_day DECIMAL(10,2) DEFAULT 150000");
    }

    // 2. Tạo bảng BẢNG LƯƠNG để lưu lịch sử thanh toán hàng tháng
    $sql2 = "CREATE TABLE IF NOT EXISTS BANG_LUONG (
        ma_luong INT AUTO_INCREMENT PRIMARY KEY,
        ma_hlv INT NOT NULL,
        thang_nam VARCHAR(10) NOT NULL, -- Ví dụ: '05/2026'
        luong_cung DECIMAL(10,2) DEFAULT 0,
        so_buoi_day INT DEFAULT 0, 
        thuong_hoa_hong DECIMAL(10,2) DEFAULT 0, 
        tong_luong DECIMAL(10,2) DEFAULT 0,
        trang_thai ENUM('chua_thanh_toan', 'da_thanh_toan') DEFAULT 'chua_thanh_toan',
        ngay_tinh_luong TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ma_hlv) REFERENCES HUAN_LUYEN_VIEN(ma_hlv) ON DELETE CASCADE
    );";
    $db->exec($sql2);

    echo "Payroll database tables and columns created successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
