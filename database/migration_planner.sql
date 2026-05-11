-- Migration: Add Planner Tables
USE monkey_gym;

CREATE TABLE IF NOT EXISTS LICH_BAN_HOI_VIEN (
    ma_lich_ban INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    lich_ban_json TEXT NOT NULL COMMENT 'JSON: { "2": ["sang", "chieu"], "3": [], ... }',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE,
    UNIQUE KEY unique_member_schedule (ma_hoi_vien)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PLAN_CA_NHAN (
    ma_plan INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_vien INT NOT NULL,
    muc_tieu VARCHAR(255) DEFAULT NULL,
    chieu_cao FLOAT DEFAULT NULL,
    can_nang FLOAT DEFAULT NULL,
    bmi FLOAT DEFAULT NULL,
    lich_tap LONGTEXT DEFAULT NULL COMMENT 'JSON workout plan',
    che_do_an LONGTEXT DEFAULT NULL COMMENT 'JSON diet plan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_vien) REFERENCES HOI_VIEN(ma_hoi_vien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
