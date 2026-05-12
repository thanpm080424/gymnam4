<?php
require_once __DIR__ . '/../database/config.php';
try {
    $db = Database::getConnection();
    $sql = "CREATE TABLE IF NOT EXISTS DANG_KY_TAP_THU (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ho_ten VARCHAR(100) NOT NULL,
        so_dien_thoai VARCHAR(20) NOT NULL,
        trang_thai ENUM('chua_goi', 'da_goi', 'dang_cho', 'da_chot', 'that_bai') DEFAULT 'chua_goi',
        ghi_chu TEXT,
        ngay_dang_ky TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $db->conn->exec($sql);
    echo "Table DANG_KY_TAP_THU created successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
