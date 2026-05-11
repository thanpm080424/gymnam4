<?php
require 'includes/Database.php';
$db = new Database();

$sql = "
CREATE TABLE IF NOT EXISTS MA_GIAM_GIA (
    ma_giam_gia INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    phan_tram_giam INT NOT NULL DEFAULT 0,
    loai_ap_dung ENUM('all', 'package', 'product') NOT NULL DEFAULT 'all',
    so_luong_con INT NOT NULL DEFAULT 0,
    ngay_het_han DATETIME NOT NULL,
    mo_ta TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Ensure DON_HANG_SP has gia_thanh_toan
ALTER TABLE DON_HANG_SP ADD COLUMN gia_thanh_toan DECIMAL(12,2) NULL AFTER trang_thai;
";

try {
    $db->execute($sql);
    echo "Migration success!";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage();
}
