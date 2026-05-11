<?php
require_once __DIR__ . '/database/config.php';

try {
    $db = Database::getConnection();
    $sql = "CREATE TABLE IF NOT EXISTS LICH_BAN_HOI_VIEN (
        ma_hoi_vien INT PRIMARY KEY,
        lich_ban_json TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "Thành công: Đã khởi tạo bảng LICH_BAN_HOI_VIEN.";
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>
