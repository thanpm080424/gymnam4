<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();
$sql = "CREATE TABLE IF NOT EXISTS EMAIL_BOOKING_LOG (
    ma_log INT AUTO_INCREMENT PRIMARY KEY,
    ma_lich INT,
    email_gui_den VARCHAR(255),
    trang_thai_gui VARCHAR(50) DEFAULT 'sent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$db->query($sql);
echo "Table EMAIL_BOOKING_LOG created successfully.";
