CREATE DATABASE IF NOT EXISTS monkey_gym;
USE monkey_gym;

-- ==============================================================================
-- 1. NGUOI_DUNG (Quản lý tài khoản đăng nhập)
-- ==============================================================================
CREATE TABLE NGUOI_DUNG (
    ma_nguoi_dung INT AUTO_INCREMENT PRIMARY KEY,
    ten_dang_nhap VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email (để đăng nhập theo chuẩn)',
    mat_khau VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã mã hóa Bcrypt',
    vai_tro ENUM('admin', 'hlv', 'hoi_vien') NOT NULL DEFAULT 'hoi_vien',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 2. HOI_VIEN (Thông tin chi tiết của hội viên)
-- ==============================================================================
CREATE TABLE HOI_VIEN (
    ma_hoi_vien INT AUTO_INCREMENT PRIMARY KEY,
    ma_nguoi_dung INT NOT NULL,
    ma_qr VARCHAR(100) NOT NULL UNIQUE COMMENT 'Chuỗi render QR, vd: MEMBER_58_1766459078',
    chieu_cao FLOAT DEFAULT NULL COMMENT 'Đơn vị: cm (để tính BMI)',
    can_nang FLOAT DEFAULT NULL COMMENT 'Đơn vị: kg (để tính BMI)',
    ngay_het_han_goi DATE DEFAULT NULL COMMENT 'Ngày hết hạn gói cao nhất hiện tại',
    FOREIGN KEY (ma_nguoi_dung) REFERENCES NGUOI_DUNG(ma_nguoi_dung) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 3. GOI_TAP (Danh mục các gói tập Gym/PT)
-- ==============================================================================
CREATE TABLE GOI_TAP (
    ma_goi INT AUTO_INCREMENT PRIMARY KEY,
    ten_goi VARCHAR(150) NOT NULL,
    gia_tien DECIMAL(12, 2) NOT NULL COMMENT 'Giá gốc',
    thoi_han_thang INT NOT NULL COMMENT 'Số tháng áp dụng',
    so_buoi_pt INT DEFAULT 0 COMMENT 'Số buổi huấn luyện viên kèm',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 4. DANG_KY_GOI (Thông tin hội viên mua gói tập)
-- ==============================================================================
CREATE TABLE DANG_KY_GOI (
    ma_dang_ky INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    ma_goi INT NOT NULL,
    ngay_kich_hoat DATE DEFAULT NULL COMMENT 'Ngày bắt đầu tính gói',
    ngay_ket_thuc DATE DEFAULT NULL COMMENT 'Ngày hết hạn gói tập này',
    trang_thai ENUM('pending', 'active', 'expired') DEFAULT 'pending',
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    FOREIGN KEY (ma_goi) REFERENCES GOI_TAP(ma_goi) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 5. THANH_TOAN (Giao dịch tài chính: VNPay/Cash)
-- ==============================================================================
CREATE TABLE THANH_TOAN (
    ma_thanh_toan INT AUTO_INCREMENT PRIMARY KEY,
    ma_dang_ky INT NOT NULL,
    vnp_TxnRef VARCHAR(100) NOT NULL UNIQUE COMMENT 'Mã giao dịch VNPay',
    so_tien DECIMAL(12, 2) NOT NULL,
    phuong_thuc ENUM('vnpay', 'cash') NOT NULL DEFAULT 'vnpay',
    trang_thai ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    FOREIGN KEY (ma_dang_ky) REFERENCES DANG_KY_GOI(ma_dang_ky) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- CÁC BẢNG BỔ SUNG (Dựa trên yêu cầu Module 2 và Module 4)
-- ==============================================================================

-- 6. KHUYEN_MAI (Quản lý Voucher cho Module 2)
CREATE TABLE KHUYEN_MAI (
    ma_khuyen_mai INT AUTO_INCREMENT PRIMARY KEY,
    ma_code VARCHAR(50) NOT NULL UNIQUE,
    phan_tram_giam DECIMAL(5, 2) NOT NULL COMMENT 'Ví dụ: 10.00 cho giảm 10%',
    ngay_bat_dau DATE,
    ngay_ket_thuc DATE,
    trang_thai ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. LICH_SU_RA_VAO (Quản lý Lịch sử quét QR điểm danh cho Module 4)
CREATE TABLE LICH_SU_RA_VAO (
    ma_lich_su INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    thoi_gian_vao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ghi_chu VARCHAR(255) DEFAULT '',
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
