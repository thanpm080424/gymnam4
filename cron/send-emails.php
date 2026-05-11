<?php
/**
 * Cron Job: Gửi email từ queue
 * Chạy mỗi 5 phút: * /5 * * * * php /path/to/cron/send-emails.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/EmailSender.php';

// Khởi tạo
$db = new Database();
$emailSender = new EmailSender($db);

// Gửi 50 emails mỗi lần chạy
$result = $emailSender->processQueue(50);

// Log kết quả
$logFile = __DIR__ . '/send-emails.log';
$logMessage = date('Y-m-d H:i:s') . " - Sent: {$result['sent']}, Failed: {$result['failed']}\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

echo $logMessage;
