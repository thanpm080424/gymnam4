-- ================================================================
-- MONKEY GYM v3.0 - Migration Script
-- Merge tính năng từ MonkeyGym_Full vào monkey_gym hiện tại
-- Chạy file này trên DB monkey_gym đang có (KHÔNG xóa dữ liệu cũ)
-- ================================================================

USE monkey_gym;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------
-- STEP 1: Thêm bảng NHAN_VIEN (Quản lý nhân viên)
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `phong_ban` (
    `ma_phong_ban` int NOT NULL AUTO_INCREMENT,
    `ten_phong_ban` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `mo_ta` text COLLATE utf8mb4_unicode_ci,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_phong_ban`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phòng ban nhân viên';

-- Seed dữ liệu phòng ban mặc định
INSERT IGNORE INTO `phong_ban` (`ten_phong_ban`, `mo_ta`) VALUES
('Lễ tân', 'Nhân viên lễ tân, tiếp đón hội viên'),
('Kế toán', 'Nhân viên kế toán, quản lý tài chính'),
('Vệ sinh & Bảo trì', 'Nhân viên vệ sinh và bảo trì thiết bị');

CREATE TABLE IF NOT EXISTS `nhan_vien` (
    `ma_nhan_vien` int NOT NULL AUTO_INCREMENT,
    `ma_nguoi_dung` int NOT NULL,
    `ma_phong_ban` int DEFAULT NULL,
    `chuc_vu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Nhân viên',
    `ngay_vao_lam` date DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_nhan_vien`),
    UNIQUE KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
    KEY `ma_phong_ban` (`ma_phong_ban`),
    CONSTRAINT `nhan_vien_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
    CONSTRAINT `nhan_vien_ibfk_2` FOREIGN KEY (`ma_phong_ban`) REFERENCES `phong_ban` (`ma_phong_ban`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông tin nhân viên';

-- Seed nhân viên từ user có vai_tro = nhanvien
INSERT IGNORE INTO `nhan_vien` (`ma_nguoi_dung`, `chuc_vu`, `ngay_vao_lam`)
SELECT ma_nguoi_dung, 'Nhân viên', CURDATE()
FROM nguoi_dung WHERE vai_tro = 'nhanvien';

-- ---------------------------------------------------------------
-- STEP 2: Thêm bảng DIEM_DANH (Check-in nâng cao)
-- Lưu ý: project1 dùng LICH_SU_RA_VAO, project2 dùng DIEM_DANH
-- Tạo thêm bảng diem_danh để dùng song song với lich_su_ra_vao
-- ---------------------------------------------------------------

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
    CONSTRAINT `diem_danh_ibfk_1` FOREIGN KEY (`ma_hoi_vien`) REFERENCES `hoi_vien` (`ma_hoi_vien`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Điểm danh hội viên hàng ngày';

-- ---------------------------------------------------------------
-- STEP 3: Thêm bảng CA_LAM_VIEC & PHAN_CA_NHAN_VIEN
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ca_lam_viec` (
    `ma_ca` int NOT NULL AUTO_INCREMENT,
    `ten_ca` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `gio_bat_dau` time NOT NULL,
    `gio_ket_thuc` time NOT NULL,
    `mo_ta` text COLLATE utf8mb4_unicode_ci,
    `trang_thai` tinyint(1) DEFAULT 1,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_ca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Các ca làm việc';

INSERT IGNORE INTO `ca_lam_viec` (`ten_ca`, `gio_bat_dau`, `gio_ket_thuc`, `mo_ta`) VALUES
('Ca Sáng', '06:00:00', '14:00:00', 'Ca sáng 6h - 14h'),
('Ca Chiều', '14:00:00', '22:00:00', 'Ca chiều 14h - 22h'),
('Ca Tối', '18:00:00', '02:00:00', 'Ca tối 18h - 2h sáng (gym 24/7)');

CREATE TABLE IF NOT EXISTS `phan_ca_nhan_vien` (
    `ma_phan_ca` int NOT NULL AUTO_INCREMENT,
    `ma_nhan_vien` int NOT NULL,
    `ma_ca` int NOT NULL,
    `ngay` date NOT NULL,
    `ghi_chu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_phan_ca`),
    UNIQUE KEY `unique_shift` (`ma_nhan_vien`, `ngay`, `ma_ca`),
    KEY `ma_ca` (`ma_ca`),
    CONSTRAINT `phan_ca_ibfk_1` FOREIGN KEY (`ma_nhan_vien`) REFERENCES `nhan_vien` (`ma_nhan_vien`) ON DELETE CASCADE,
    CONSTRAINT `phan_ca_ibfk_2` FOREIGN KEY (`ma_ca`) REFERENCES `ca_lam_viec` (`ma_ca`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phân ca nhân viên';

-- ---------------------------------------------------------------
-- STEP 4: Thêm bảng LICH_LAM_VIEC_HLV (Lịch làm việc HLV)
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `lich_lam_viec_hlv` (
    `ma_llv` int NOT NULL AUTO_INCREMENT,
    `ma_hlv` int NOT NULL,
    `ngay_trong_tuan` tinyint NOT NULL COMMENT '0=CN, 1=T2,..., 6=T7',
    `gio_bat_dau` time NOT NULL,
    `gio_ket_thuc` time NOT NULL,
    PRIMARY KEY (`ma_llv`),
    KEY `ma_hlv` (`ma_llv`),
    CONSTRAINT `lich_lam_viec_hlv_ibfk_1` FOREIGN KEY (`ma_hlv`) REFERENCES `huan_luyen_vien` (`ma_hlv`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lịch làm việc định kỳ của HLV';

-- ---------------------------------------------------------------
-- STEP 5: Thêm bảng LOG_HOAT_DONG (Activity log)
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `log_hoat_dong` (
    `ma_log` int NOT NULL AUTO_INCREMENT,
    `ma_nguoi_dung` int DEFAULT NULL,
    `hanh_dong` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `chi_tiet` text COLLATE utf8mb4_unicode_ci,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_log`),
    KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log hoạt động hệ thống';

-- ---------------------------------------------------------------
-- STEP 6: Thêm bảng EMAIL_QUEUE (Hàng đợi gửi email)
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `email_queue` (
    `id` int NOT NULL AUTO_INCREMENT,
    `to_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `to_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
    `subject` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
    `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `priority` tinyint DEFAULT 5 COMMENT '1=cao nhất, 10=thấp nhất',
    `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
    `attempts` tinyint DEFAULT 0,
    `max_attempts` tinyint DEFAULT 3,
    `error_message` text COLLATE utf8mb4_unicode_ci,
    `scheduled_at` datetime DEFAULT NULL,
    `sent_at` datetime DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hàng đợi gửi email';

-- ---------------------------------------------------------------
-- STEP 7: Thêm cột vào bảng THANH_TOAN (hỗ trợ thanh toán trực tiếp bởi staff)
-- ---------------------------------------------------------------

ALTER TABLE `thanh_toan`
    ADD COLUMN IF NOT EXISTS `nguoi_thu` int DEFAULT NULL COMMENT 'ma_nguoi_dung của người thu tiền',
    ADD COLUMN IF NOT EXISTS `ghi_chu` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `ngay_thanh_toan` datetime DEFAULT NULL;

-- Cập nhật ngay_thanh_toan từ created_at cho bản ghi cũ
UPDATE `thanh_toan` SET `ngay_thanh_toan` = `created_at` WHERE `ngay_thanh_toan` IS NULL;

-- Thêm giá trị mới vào ENUM phuong_thuc (tiền mặt trực tiếp tại quầy)
ALTER TABLE `thanh_toan`
    MODIFY COLUMN `phuong_thuc` ENUM('vnpay','cash','tien_mat','chuyen_khoan','the') 
    COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash';

-- ---------------------------------------------------------------
-- STEP 8: Thêm cột vào DANG_KY_GOI (tương thích với thanh toán trực tiếp)
-- ---------------------------------------------------------------

ALTER TABLE `dang_ky_goi`
    ADD COLUMN IF NOT EXISTS `gia_thanh_toan` decimal(12,2) DEFAULT NULL COMMENT 'Giá thực tế đã thanh toán',
    ADD COLUMN IF NOT EXISTS `ngay_bat_dau` date DEFAULT NULL COMMENT 'Alias cho ngay_kich_hoat';

-- Sync ngay_bat_dau từ ngay_kich_hoat
UPDATE `dang_ky_goi` SET `ngay_bat_dau` = `ngay_kich_hoat` WHERE `ngay_bat_dau` IS NULL AND `ngay_kich_hoat` IS NOT NULL;

-- Thêm giá trị mới vào ENUM trang_thai
ALTER TABLE `dang_ky_goi`
    MODIFY COLUMN `trang_thai` ENUM('pending','active','expired','cho_thanh_toan','dang_hoat_dong')
    COLLATE utf8mb4_unicode_ci DEFAULT 'pending';

-- ---------------------------------------------------------------
-- STEP 9: Thêm cột ho_ten, email, so_dien_thoai vào NGUOI_DUNG
-- (project2 dùng ho_ten trực tiếp trong nguoi_dung)
-- ---------------------------------------------------------------

ALTER TABLE `nguoi_dung`
    ADD COLUMN IF NOT EXISTS `ho_ten` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Họ tên đầy đủ',
    ADD COLUMN IF NOT EXISTS `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email liên hệ (có thể khác ten_dang_nhap)',
    ADD COLUMN IF NOT EXISTS `so_dien_thoai` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- Sync email từ ten_dang_nhap nếu ten_dang_nhap là email
UPDATE `nguoi_dung` SET `email` = `ten_dang_nhap` 
WHERE `email` IS NULL AND `ten_dang_nhap` LIKE '%@%';

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Migration v3.0 hoàn thành thành công! Đã thêm: nhan_vien, phong_ban, diem_danh, ca_lam_viec, log_hoat_dong, email_queue' AS result;
