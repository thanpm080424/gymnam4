<?php
require_once __DIR__ . '/../database/config.php';
try {
    $db = Database::getConnection();
    echo "Current DB: " . $db->query("SELECT DATABASE()")->fetchColumn() . "<br>";

    // 1. Tables for Group X
    $db->query("CREATE TABLE IF NOT EXISTS LOP_HOC_NHOM (
        ma_lop INT AUTO_INCREMENT PRIMARY KEY,
        ten_lop VARCHAR(255) NOT NULL,
        mo_ta TEXT,
        loai_lop VARCHAR(50),
        hinh_anh VARCHAR(255),
        trang_thai ENUM('active', 'inactive') DEFAULT 'active'
    )");

    $db->query("CREATE TABLE IF NOT EXISTS LICH_HOC_NHOM (
        ma_lich INT AUTO_INCREMENT PRIMARY KEY,
        ma_lop INT NOT NULL,
        ma_hlv INT NOT NULL,
        ngay_hoc DATE NOT NULL,
        gio_bat_dau TIME NOT NULL,
        gio_ket_thuc TIME NOT NULL,
        so_luong_toi_da INT DEFAULT 20,
        FOREIGN KEY (ma_lop) REFERENCES LOP_HOC_NHOM(ma_lop) ON DELETE CASCADE,
        FOREIGN KEY (ma_hlv) REFERENCES HUAN_LUYEN_VIEN(ma_hlv) ON DELETE CASCADE
    )");

    $db->query("CREATE TABLE IF NOT EXISTS DAT_CHO_LOP_HOC (
        ma_dat_cho INT AUTO_INCREMENT PRIMARY KEY,
        ma_lich INT NOT NULL,
        ma_hoi_vien INT NOT NULL,
        thoi_gian_dat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        trang_thai ENUM('thanh_cong', 'da_huy') DEFAULT 'thanh_cong',
        UNIQUE KEY unique_booking (ma_lich, ma_hoi_vien),
        FOREIGN KEY (ma_lich) REFERENCES LICH_HOC_NHOM(ma_lich) ON DELETE CASCADE,
        FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
    )");

    // 2. Tables for Payroll
    $db->query("CREATE TABLE IF NOT EXISTS BANG_LUONG (
        ma_luong INT AUTO_INCREMENT PRIMARY KEY,
        ma_hlv INT NOT NULL,
        thang_nam VARCHAR(10) NOT NULL,
        luong_cung DECIMAL(10,2) DEFAULT 5000000,
        so_buoi_day INT DEFAULT 0,
        thuong_hoa_hong DECIMAL(10,2) DEFAULT 0,
        tong_luong DECIMAL(10,2) DEFAULT 0,
        trang_thai ENUM('chua_thanh_toan', 'da_thanh_toan') DEFAULT 'chua_thanh_toan',
        ngay_tinh_luong TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ma_hlv) REFERENCES HUAN_LUYEN_VIEN(ma_hlv) ON DELETE CASCADE
    )");

    // 3. Add columns to HUAN_LUYEN_VIEN
    $cols = $db->query("SHOW COLUMNS FROM HUAN_LUYEN_VIEN")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('luong_cung', $cols)) {
        $db->query("ALTER TABLE HUAN_LUYEN_VIEN ADD COLUMN luong_cung DECIMAL(10,2) DEFAULT 5000000");
    }
    if (!in_array('gia_buoi_day', $cols)) {
        $db->query("ALTER TABLE HUAN_LUYEN_VIEN ADD COLUMN gia_buoi_day DECIMAL(10,2) DEFAULT 150000");
    }

    echo "All tables and columns created successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
