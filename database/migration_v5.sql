-- ================================================================
-- MONKEY GYM v5.0 - Migration Script
-- Nâng cấp bảng lich_lam_viec_hlv để hỗ trợ Thời Khóa Biểu HLV
-- Chạy file này trên DB monkey_gym đang có (KHÔNG xóa dữ liệu cũ)
-- ================================================================

USE monkey_gym;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------
-- STEP 1: Thêm cột muc_tieu và trang_thai vào lich_lam_viec_hlv
-- ---------------------------------------------------------------

ALTER TABLE `lich_lam_viec_hlv`
    ADD COLUMN IF NOT EXISTS `muc_tieu` VARCHAR(100) DEFAULT NULL COMMENT 'VD: Giảm mỡ, Tăng cơ, Beginner, Yoga nhẹ',
    ADD COLUMN IF NOT EXISTS `trang_thai` ENUM('trong', 'da_dat') NOT NULL DEFAULT 'trong' COMMENT 'Trạng thái slot: trong=chưa có khách, da_dat=đã có khách đăng ký';

-- ---------------------------------------------------------------
-- STEP 2: Thêm UNIQUE constraint để tránh trùng slot
-- (1 HLV không có 2 slot cùng thứ + cùng giờ bắt đầu)
-- ---------------------------------------------------------------

-- Xóa index cũ nếu tồn tại trước khi tạo mới
ALTER TABLE `lich_lam_viec_hlv`
    DROP INDEX IF EXISTS `unique_hlv_slot`;

ALTER TABLE `lich_lam_viec_hlv`
    ADD UNIQUE KEY `unique_hlv_slot` (`ma_hlv`, `ngay_trong_tuan`, `gio_bat_dau`);

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Migration v5.0 hoàn thành! Bảng lich_lam_viec_hlv đã được nâng cấp với cột muc_tieu và trang_thai.' AS result;
