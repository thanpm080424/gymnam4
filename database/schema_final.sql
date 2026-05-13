-- ==============================================================================
-- MONKEY GYM - FINAL SCHEMA CONSOLIDATED (v1 to v5)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS monkey_gym;
USE monkey_gym;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. NGUOI_DUNG
CREATE TABLE IF NOT EXISTS NGUOI_DUNG (
    ma_nguoi_dung INT AUTO_INCREMENT PRIMARY KEY,
    ten_dang_nhap VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email hoặc tên đăng nhập',
    mat_khau VARCHAR(255) NOT NULL COMMENT 'Mật khẩu Bcrypt',
    vai_tro ENUM('admin', 'hlv', 'hoi_vien', 'nhanvien') NOT NULL DEFAULT 'hoi_vien',
    ho_ten VARCHAR(150) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    so_dien_thoai VARCHAR(20) DEFAULT NULL,
    trang_thai ENUM('active', 'banned', 'pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. HOI_VIEN
CREATE TABLE IF NOT EXISTS HOI_VIEN (
    ma_hoi_vien INT AUTO_INCREMENT PRIMARY KEY,
    ma_nguoi_dung INT NOT NULL,
    ma_qr VARCHAR(100) NOT NULL UNIQUE,
    chieu_cao FLOAT DEFAULT NULL,
    can_nang FLOAT DEFAULT NULL,
    ngay_het_han_goi DATE DEFAULT NULL,
    bi_ban TINYINT(1) NOT NULL DEFAULT 0,
    so_buoi_pt_con_lai INT NOT NULL DEFAULT 0,
    FOREIGN KEY (ma_nguoi_dung) REFERENCES NGUOI_DUNG(ma_nguoi_dung) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. HUAN_LUYEN_VIEN
CREATE TABLE IF NOT EXISTS HUAN_LUYEN_VIEN (
    ma_hlv INT AUTO_INCREMENT PRIMARY KEY,
    ma_nguoi_dung INT NOT NULL,
    chuyen_mon VARCHAR(255),
    anh_dai_dien VARCHAR(255) DEFAULT NULL,
    nam_kinh_nghiem INT DEFAULT 0,
    mo_ta TEXT DEFAULT NULL,
    gioi_thieu TEXT DEFAULT NULL,
    FOREIGN KEY (ma_nguoi_dung) REFERENCES NGUOI_DUNG(ma_nguoi_dung) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. GOI_TAP
CREATE TABLE IF NOT EXISTS GOI_TAP (
    ma_goi INT AUTO_INCREMENT PRIMARY KEY,
    ten_goi VARCHAR(150) NOT NULL,
    gia_tien DECIMAL(12, 2) NOT NULL,
    thoi_han_thang INT NOT NULL,
    so_buoi_pt INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. DANG_KY_GOI
CREATE TABLE IF NOT EXISTS DANG_KY_GOI (
    ma_dang_ky INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_goi INT NOT NULL,
    ngay_kich_hoat DATE DEFAULT NULL,
    ngay_ket_thuc DATE DEFAULT NULL,
    ngay_bat_dau DATE DEFAULT NULL, -- Alias cho ngay_kich_hoat
    gia_thanh_toan DECIMAL(12,2) DEFAULT NULL,
    trang_thai ENUM('pending', 'active', 'expired', 'cho_thanh_toan', 'dang_hoat_dong') DEFAULT 'pending',
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    FOREIGN KEY (ma_goi) REFERENCES GOI_TAP(ma_goi) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. THANH_TOAN
CREATE TABLE IF NOT EXISTS THANH_TOAN (
    ma_thanh_toan INT AUTO_INCREMENT PRIMARY KEY,
    ma_dang_ky INT DEFAULT NULL,
    ma_yc_thue INT DEFAULT NULL,
    vnp_TxnRef VARCHAR(100) NOT NULL UNIQUE,
    so_tien DECIMAL(12, 2) NOT NULL,
    phuong_thuc ENUM('vnpay', 'cash', 'tien_mat', 'chuyen_khoan', 'the') NOT NULL DEFAULT 'vnpay',
    trang_thai ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    nguoi_thu INT DEFAULT NULL,
    ghi_chu TEXT DEFAULT NULL,
    ngay_thanh_toan DATETIME DEFAULT NULL,
    FOREIGN KEY (ma_dang_ky) REFERENCES DANG_KY_GOI(ma_dang_ky) ON DELETE CASCADE,
    FOREIGN KEY (ma_yc_thue) REFERENCES YEU_CAU_THUE_TU(ma_yc) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. SAN_PHAM
CREATE TABLE IF NOT EXISTS SAN_PHAM (
    ma_sp INT AUTO_INCREMENT PRIMARY KEY,
    ten_sp VARCHAR(255) NOT NULL,
    gia_tien DECIMAL(12, 2) NOT NULL,
    mo_ta TEXT,
    ton_kho INT DEFAULT 0,
    hinh_anh VARCHAR(255),
    gia_diem INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. DON_HANG_SP
CREATE TABLE IF NOT EXISTS DON_HANG_SP (
    ma_dh INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_sp INT NOT NULL,
    so_luong INT DEFAULT 1,
    gia_tien DECIMAL(12, 2) NOT NULL,
    gia_thanh_toan DECIMAL(12, 2) DEFAULT NULL,
    ngay_mua TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    ma_giao_dich VARCHAR(100),
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    FOREIGN KEY (ma_sp) REFERENCES SAN_PHAM(ma_sp) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. MA_GIAM_GIA
CREATE TABLE IF NOT EXISTS MA_GIAM_GIA (
    ma_giam_gia INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    phan_tram_giam INT NOT NULL DEFAULT 0,
    so_tien_giam DECIMAL(12, 2) DEFAULT 0,
    loai_ap_dung ENUM('all', 'package', 'product') NOT NULL DEFAULT 'all',
    so_luong_con INT NOT NULL DEFAULT 0,
    ngay_het_han DATETIME NOT NULL,
    mo_ta TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. LICH_DAT_PT
CREATE TABLE IF NOT EXISTS LICH_DAT_PT (
    ma_lich INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_hlv INT NOT NULL,
    ngay_gio_tap DATETIME NOT NULL,
    loai_pt VARCHAR(100),
    ghi_chu TEXT,
    trang_thai ENUM('pending', 'confirmed', 'attended', 'cancelled', 'cancel_requested', 'cancel_rejected') DEFAULT 'pending',
    ly_do_huy TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    FOREIGN KEY (ma_hlv) REFERENCES HUAN_LUYEN_VIEN(ma_hlv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. DIEM_TICH_LUY
CREATE TABLE IF NOT EXISTS DIEM_TICH_LUY (
    ma_dtl INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL UNIQUE,
    so_diem INT NOT NULL DEFAULT 0,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. LICH_SU_DIEM
CREATE TABLE IF NOT EXISTS LICH_SU_DIEM (
    ma_lsd INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    so_diem_thay_doi INT NOT NULL,
    ly_do VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. DANH_GIA_HLV
CREATE TABLE IF NOT EXISTS DANH_GIA_HLV (
    ma_dg INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_hlv INT NOT NULL,
    so_sao TINYINT NOT NULL DEFAULT 5,
    noi_dung TEXT,
    trang_thai ENUM('pending', 'approved', 'rejected', 'reported') DEFAULT 'pending',
    ghi_chu_tu_choi VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    FOREIGN KEY (ma_hlv) REFERENCES HUAN_LUYEN_VIEN(ma_hlv) ON DELETE CASCADE,
    UNIQUE KEY unique_review (ma_hoi_vien, ma_hlv)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. TU_DO
CREATE TABLE IF NOT EXISTS TU_DO (
    ma_tu INT AUTO_INCREMENT PRIMARY KEY,
    so_tu VARCHAR(20) NOT NULL UNIQUE,
    trang_thai ENUM('trong', 'dang_thue', 'bao_tri') DEFAULT 'trong',
    loai_tu VARCHAR(10) DEFAULT 'M',
    gia_thue_thang DECIMAL(10,2) NOT NULL DEFAULT 100000,
    ghi_chu VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. YEU_CAU_THUE_TU
CREATE TABLE IF NOT EXISTS YEU_CAU_THUE_TU (
    ma_yc INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_tu INT DEFAULT NULL,
    trang_thai ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    so_thang INT DEFAULT 1,
    loai_tu_mong_muon VARCHAR(10) DEFAULT 'M',
    ly_do_tu_choi VARCHAR(255) DEFAULT NULL,
    ngay_bat_dau DATE DEFAULT NULL,
    ngay_ket_thuc DATE DEFAULT NULL,
    ma_giao_dich_thue VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    FOREIGN KEY (ma_tu) REFERENCES TU_DO(ma_tu) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. NHAT_KY_TAP
CREATE TABLE IF NOT EXISTS NHAT_KY_TAP (
    ma_nk INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    tieu_de VARCHAR(255) NOT NULL,
    noi_dung TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. MEDIA_NHAT_KY
CREATE TABLE IF NOT EXISTS MEDIA_NHAT_KY (
    ma_media INT AUTO_INCREMENT PRIMARY KEY,
    ma_nk INT NOT NULL,
    duong_dan VARCHAR(500) NOT NULL,
    loai_media ENUM('image', 'video') NOT NULL,
    FOREIGN KEY (ma_nk) REFERENCES NHAT_KY_TAP(ma_nk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. GHI_CHU_HLV
CREATE TABLE IF NOT EXISTS GHI_CHU_HLV (
    ma_gc INT AUTO_INCREMENT PRIMARY KEY,
    ma_hlv INT NOT NULL,
    ma_hoi_vien INT NOT NULL,
    noi_dung TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hlv) REFERENCES HUAN_LUYEN_VIEN(ma_hlv) ON DELETE CASCADE,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    UNIQUE KEY unique_note (ma_hlv, ma_hoi_vien)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. THONG_BAO_HE_THONG
CREATE TABLE IF NOT EXISTS THONG_BAO_HE_THONG (
    ma_tb INT AUTO_INCREMENT PRIMARY KEY,
    tieu_de VARCHAR(255) NOT NULL,
    noi_dung TEXT NOT NULL,
    hinh_anh VARCHAR(500) DEFAULT NULL,
    trang_thai ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. YEU_CAU_BAN
CREATE TABLE IF NOT EXISTS YEU_CAU_BAN (
    ma_ycb INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_staff INT NOT NULL,
    ly_do TEXT NOT NULL,
    trang_thai ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    ghi_chu_admin VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. phong_ban
CREATE TABLE IF NOT EXISTS `phong_ban` (
    `ma_phong_ban` int NOT NULL AUTO_INCREMENT,
    `ten_phong_ban` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `mo_ta` text COLLATE utf8mb4_unicode_ci,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_phong_ban`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. nhan_vien
CREATE TABLE IF NOT EXISTS `nhan_vien` (
    `ma_nhan_vien` int NOT NULL AUTO_INCREMENT,
    `ma_nguoi_dung` int NOT NULL,
    `ma_phong_ban` int DEFAULT NULL,
    `chuc_vu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Nhân viên',
    `ngay_vao_lam` date DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_nhan_vien`),
    UNIQUE KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
    FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `NGUOI_DUNG` (`ma_nguoi_dung`) ON DELETE CASCADE,
    FOREIGN KEY (`ma_phong_ban`) REFERENCES `phong_ban` (`ma_phong_ban`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. diem_danh
CREATE TABLE IF NOT EXISTS `diem_danh` (
    `ma_diem_danh` int NOT NULL AUTO_INCREMENT,
    `ma_hoi_vien` int NOT NULL,
    `ngay` date NOT NULL,
    `gio_diem_danh` time NOT NULL,
    `phuong_thuc` enum('qr_code','manual') COLLATE utf8mb4_unicode_ci DEFAULT 'qr_code',
    `ghi_chu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_diem_danh`),
    UNIQUE KEY `unique_checkin` (`ma_hoi_vien`, `ngay`),
    FOREIGN KEY (`ma_hoi_vien`) REFERENCES `HOI_VIEN` (`ma_hoi_vien`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. ca_lam_viec
CREATE TABLE IF NOT EXISTS `ca_lam_viec` (
    `ma_ca` int NOT NULL AUTO_INCREMENT,
    `ten_ca` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `gio_bat_dau` time NOT NULL,
    `gio_ket_thuc` time NOT NULL,
    `mo_ta` text COLLATE utf8mb4_unicode_ci,
    `trang_thai` tinyint(1) DEFAULT 1,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_ca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. phan_ca_nhan_vien
CREATE TABLE IF NOT EXISTS `phan_ca_nhan_vien` (
    `ma_phan_ca` int NOT NULL AUTO_INCREMENT,
    `ma_nhan_vien` int NOT NULL,
    `ma_ca` int NOT NULL,
    `ngay` date NOT NULL,
    `ghi_chu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_phan_ca`),
    UNIQUE KEY `unique_shift` (`ma_nhan_vien`, `ngay`, `ma_ca`),
    FOREIGN KEY (`ma_nhan_vien`) REFERENCES `nhan_vien` (`ma_nhan_vien`) ON DELETE CASCADE,
    FOREIGN KEY (`ma_ca`) REFERENCES `ca_lam_viec` (`ma_ca`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. lich_lam_viec_hlv
CREATE TABLE IF NOT EXISTS `lich_lam_viec_hlv` (
    `ma_llv` int NOT NULL AUTO_INCREMENT,
    `ma_hlv` int NOT NULL,
    `ngay_trong_tuan` tinyint NOT NULL COMMENT '0=CN, 1=T2,..., 6=T7',
    `gio_bat_dau` time NOT NULL,
    `gio_ket_thuc` time NOT NULL,
    `muc_tieu` VARCHAR(100) DEFAULT NULL,
    `trang_thai` ENUM('trong', 'da_dat') NOT NULL DEFAULT 'trong',
    PRIMARY KEY (`ma_llv`),
    UNIQUE KEY `unique_hlv_slot` (`ma_hlv`, `ngay_trong_tuan`, `gio_bat_dau`),
    FOREIGN KEY (`ma_hlv`) REFERENCES `HUAN_LUYEN_VIEN` (`ma_hlv`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. log_hoat_dong
CREATE TABLE IF NOT EXISTS `log_hoat_dong` (
    `ma_log` int NOT NULL AUTO_INCREMENT,
    `ma_nguoi_dung` int DEFAULT NULL,
    `hanh_dong` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `chi_tiet` text COLLATE utf8mb4_unicode_ci,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_log`),
    FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `NGUOI_DUNG` (`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. email_queue
CREATE TABLE IF NOT EXISTS `email_queue` (
    `id` int NOT NULL AUTO_INCREMENT,
    `to_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `to_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
    `subject` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
    `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `priority` tinyint DEFAULT 5,
    `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
    `attempts` tinyint DEFAULT 0,
    `max_attempts` tinyint DEFAULT 3,
    `error_message` text COLLATE utf8mb4_unicode_ci,
    `scheduled_at` datetime DEFAULT NULL,
    `sent_at` datetime DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29. LICH_SU_RA_VAO (Legacy / Parallel with diem_danh)
CREATE TABLE IF NOT EXISTS LICH_SU_RA_VAO (
    ma_lich_su INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    thoi_gian_vao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ghi_chu VARCHAR(255) DEFAULT '',
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
