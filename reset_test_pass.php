<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = new Database();
$newPass = password_hash('123456', PASSWORD_BCRYPT);

$targets = ['admin', 'hlv1@gmail.com', 'hoivien5@gmail.com', 'staff'];
foreach ($targets as $user) {
    $db->execute("UPDATE NGUOI_DUNG SET mat_khau = ? WHERE ten_dang_nhap = ?", [$newPass, $user]);
}
echo "Passwords reset to 123456 for: " . implode(', ', $targets);
?>
