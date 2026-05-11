<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = new Database();
    $db->execute("ALTER TABLE THONG_BAO_HE_THONG ADD COLUMN hinh_anh VARCHAR(500) DEFAULT NULL AFTER noi_dung");
    echo "Successfully added hinh_anh column.";
} catch (Exception $e) {
    echo "Error or column already exists: " . $e->getMessage();
}
?>
