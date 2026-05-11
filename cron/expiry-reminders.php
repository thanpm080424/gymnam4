<?php
/**
 * Cron Job: Nhắc hội viên sắp hết hạn gói tập
 * Chạy mỗi ngày lúc 9h sáng: 0 9 * * * php /path/to/cron/expiry-reminders.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/EmailSender.php';
require_once __DIR__ . '/../includes/EmailTemplates.php';

$db = new Database();
$emailSender = new EmailSender($db);

// Tìm gói tập sắp hết hạn trong 7 ngày
$sql = "SELECT h.*, n.email, dkg.ngay_ket_thuc, g.ten_goi, g.gia
        FROM hoi_vien h
        JOIN nguoi_dung n ON h.nguoi_dung_id = n.id
        JOIN dang_ky_goi dkg ON h.id = dkg.hoi_vien_id
        JOIN goi_tap g ON dkg.goi_tap_id = g.id
        WHERE dkg.trang_thai = 'dang_hoat_dong'
        AND DATEDIFF(dkg.ngay_ket_thuc, NOW()) = 7
        AND n.email IS NOT NULL AND n.email != ''";

$members = $db->query($sql)->fetchAll();

$sent = 0;
$failed = 0;

foreach ($members as $member) {
    $emailData = [
        'ho_ten' => $member['ho_ten'],
        'ten_goi' => $member['ten_goi'],
        'ngay_het_han' => date('d/m/Y', strtotime($member['ngay_ket_thuc'])),
        'so_ngay_con_lai' => 7,
        'gia_gia_han' => number_format($member['gia'], 0, ',', '.')
    ];
    
    $result = $emailSender->sendNow(
        $member['email'],
        $member['ho_ten'],
        '⚠️ Gói tập của bạn sắp hết hạn - Monkey Gym',
        EmailTemplates::expiryReminder($emailData)
    );
    
    if ($result) {
        $sent++;
    } else {
        $failed++;
    }
    
    sleep(2); // Tránh spam
}

// Log
$logFile = __DIR__ . '/expiry-reminders.log';
$logMessage = date('Y-m-d H:i:s') . " - Checked: " . count($members) . ", Sent: $sent, Failed: $failed\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

echo $logMessage;
