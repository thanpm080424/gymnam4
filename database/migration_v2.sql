-- ================================================================
-- MONKEY GYM v2.0 - Migration Script
-- Chay file nay tren DB monkey_gym hien co (KHONG xoa du lieu cu)
-- ================================================================

USE monkey_gym;

-- ---------------------------------------------------------------
-- STEP 1: Alter cac bang hien co
-- ---------------------------------------------------------------

-- 1a. NGUOI_DUNG: Them 'nhanvien' vao ENUM vai_tro
ALTER TABLE NGUOI_DUNG 
  MODIFY COLUMN vai_tro ENUM('admin', 'hlv', 'hoi_vien', 'nhanvien') NOT NULL DEFAULT 'hoi_vien';

-- 1b. HOI_VIEN: Them cot bi_ban
ALTER TABLE HOI_VIEN 
  ADD COLUMN IF NOT EXISTS bi_ban TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = bi cam dang nhap',
  ADD COLUMN IF NOT EXISTS so_buoi_pt_con_lai INT NOT NULL DEFAULT 0 COMMENT 'So buoi PT con lai';

-- 1c. HUAN_LUYEN_VIEN: Them thong tin ca nhan public
ALTER TABLE HUAN_LUYEN_VIEN 
  ADD COLUMN IF NOT EXISTS anh_dai_dien VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS gioi_thieu TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS nam_kinh_nghiem INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS mo_ta TEXT DEFAULT NULL;

-- 1d. SAN_PHAM: Them gia bang diem
ALTER TABLE SAN_PHAM 
  ADD COLUMN IF NOT EXISTS gia_diem INT DEFAULT NULL COMMENT 'NULL = khong ban bang diem, so = gia tinh bang diem';

-- ---------------------------------------------------------------
-- STEP 2: Tao cac bang moi
-- ---------------------------------------------------------------

-- 2a. DIEM_TICH_LUY: Diem hien tai cua hoi vien
CREATE TABLE IF NOT EXISTS DIEM_TICH_LUY (
    ma_dtl INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL UNIQUE,
    so_diem INT NOT NULL DEFAULT 0,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Khi tao, seed diem 0 cho tat ca hoi vien hien co
INSERT IGNORE INTO DIEM_TICH_LUY (ma_hoi_vien, so_diem)
SELECT ma_hoi_vien, 0 FROM HOI_VIEN;

-- 2b. LICH_SU_DIEM: Log giao dich diem
CREATE TABLE IF NOT EXISTS LICH_SU_DIEM (
    ma_lsd INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    so_diem_thay_doi INT NOT NULL COMMENT 'Duong = cong, Am = tru',
    ly_do VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2c. DANH_GIA_HLV: Danh gia sao + nhan xet
CREATE TABLE IF NOT EXISTS DANH_GIA_HLV (
    ma_dg INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_hlv INT NOT NULL,
    so_sao TINYINT NOT NULL DEFAULT 5 COMMENT '1-5 sao',
    noi_dung TEXT,
    trang_thai ENUM('pending', 'approved', 'rejected', 'reported') DEFAULT 'pending',
    ghi_chu_tu_choi VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    FOREIGN KEY (ma_hlv) REFERENCES HUAN_LUYEN_VIEN(ma_hlv) ON DELETE CASCADE,
    UNIQUE KEY unique_review (ma_hoi_vien, ma_hlv)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2d. TU_DO: Danh sach tu do (Admin them)
CREATE TABLE IF NOT EXISTS TU_DO (
    ma_tu INT AUTO_INCREMENT PRIMARY KEY,
    so_tu VARCHAR(20) NOT NULL UNIQUE COMMENT 'VD: A01, B12',
    trang_thai ENUM('trong', 'dang_thue', 'bao_tri') DEFAULT 'trong',
    gia_thue_thang DECIMAL(10,2) NOT NULL DEFAULT 100000,
    ghi_chu VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2e. YEU_CAU_THUE_TU: Yeu cau thue tu cua hoi vien
CREATE TABLE IF NOT EXISTS YEU_CAU_THUE_TU (
    ma_yc INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_tu INT DEFAULT NULL COMMENT 'Staff se gan tu sau khi duyet',
    trang_thai ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    ly_do_tu_choi VARCHAR(255) DEFAULT NULL,
    ngay_bat_dau DATE DEFAULT NULL,
    ngay_ket_thuc DATE DEFAULT NULL COMMENT 'Ngay het han thue tu (1 thang)',
    ma_giao_dich_thue VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    FOREIGN KEY (ma_tu) REFERENCES TU_DO(ma_tu) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2f. NHAT_KY_TAP: Entry nhat ky tap luyen
CREATE TABLE IF NOT EXISTS NHAT_KY_TAP (
    ma_nk INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    tieu_de VARCHAR(255) NOT NULL,
    noi_dung TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2g. MEDIA_NHAT_KY: File anh/video di kem nhat ky
CREATE TABLE IF NOT EXISTS MEDIA_NHAT_KY (
    ma_media INT AUTO_INCREMENT PRIMARY KEY,
    ma_nk INT NOT NULL,
    duong_dan VARCHAR(500) NOT NULL,
    loai_media ENUM('image', 'video') NOT NULL,
    FOREIGN KEY (ma_nk) REFERENCES NHAT_KY_TAP(ma_nk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2h. GHI_CHU_HLV: Ghi chu rieng cua HLV ve hoc vien
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

-- 2i. THONG_BAO_HE_THONG: Thong bao dialogcua Admin
CREATE TABLE IF NOT EXISTS THONG_BAO_HE_THONG (
    ma_tb INT AUTO_INCREMENT PRIMARY KEY,
    tieu_de VARCHAR(255) NOT NULL,
    noi_dung TEXT NOT NULL,
    trang_thai ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2j. YEU_CAU_BAN: Yeu cau ban hoi vien tu Staff
CREATE TABLE IF NOT EXISTS YEU_CAU_BAN (
    ma_ycb INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_staff INT NOT NULL COMMENT 'ma_nguoi_dung cua staff gui yeu cau',
    ly_do TEXT NOT NULL,
    trang_thai ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    ghi_chu_admin VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration v2.0 hoàn thành thành công!' AS result;
