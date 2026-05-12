<?php
require_once __DIR__ . '/../database/config.php';
try {
    $db = Database::getConnection();
    
    // 1. Bảng Danh mục Lớp Học
    $sql1 = "CREATE TABLE IF NOT EXISTS LOP_HOC_NHOM (
        ma_lop INT PRIMARY KEY AUTO_INCREMENT,
        ten_lop VARCHAR(255) NOT NULL,
        mo_ta TEXT,
        hinh_anh VARCHAR(255),
        loai_lop VARCHAR(100),
        trang_thai ENUM('active', 'inactive') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->query($sql1);

    // 2. Bảng Lịch Học (Từng buổi cụ thể)
    $sql2 = "CREATE TABLE IF NOT EXISTS LICH_HOC_NHOM (
        ma_lich INT PRIMARY KEY AUTO_INCREMENT,
        ma_lop INT NOT NULL,
        ma_hlv INT NOT NULL,
        ngay_hoc DATE NOT NULL,
        gio_bat_dau TIME NOT NULL,
        gio_ket_thuc TIME NOT NULL,
        so_luong_toi_da INT DEFAULT 20,
        trang_thai ENUM('sap_dien_ra', 'dang_dien_ra', 'da_ket_thuc', 'da_huy') DEFAULT 'sap_dien_ra',
        FOREIGN KEY (ma_lop) REFERENCES LOP_HOC_NHOM(ma_lop) ON DELETE CASCADE,
        FOREIGN KEY (ma_hlv) REFERENCES HUAN_LUYEN_VIEN(ma_hlv) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->query($sql2);

    // 3. Bảng Hội viên Đặt chỗ
    $sql3 = "CREATE TABLE IF NOT EXISTS DAT_CHO_LOP_HOC (
        ma_dat_cho INT PRIMARY KEY AUTO_INCREMENT,
        ma_lich INT NOT NULL,
        ma_hoi_vien INT NOT NULL,
        thoi_gian_dat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        trang_thai ENUM('thanh_cong', 'da_huy') DEFAULT 'thanh_cong',
        FOREIGN KEY (ma_lich) REFERENCES LICH_HOC_NHOM(ma_lich) ON DELETE CASCADE,
        FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
        UNIQUE KEY (ma_lich, ma_hoi_vien) 
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->query($sql3);

    // Dữ liệu mẫu
    $count = $db->query("SELECT COUNT(*) FROM LOP_HOC_NHOM")->fetchColumn();
    if ($count == 0) {
        $db->query("INSERT INTO LOP_HOC_NHOM (ten_lop, mo_ta, hinh_anh, loai_lop) VALUES 
        ('Yoga Zen Căn Bản', 'Phục hồi cơ thể, cân bằng tâm trí', 'https://images.unsplash.com/photo-1603988363607-e1e4a66962c6?q=80&w=800', 'Yoga'),
        ('HIIT Đốt Mỡ Siêu Tốc', '45 phút tập luyện cường độ cao ngắt quãng', 'https://images.unsplash.com/photo-1599058945522-28d584b6f4ff?q=80&w=800', 'Cardio')");
    }

    echo "Group X tables created successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
