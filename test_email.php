<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/EmailService.php';

$emailService = new EmailService();
$to = 'thanpm080424@gmail.com'; // User's email
$subject = "Test Email Monkey Gym";
$content = "<h1>Email Test</h1><p>Đây là email kiểm tra hệ thống.</p>";

echo "Đang gửi email tới $to...<br>";
$result = $emailService->send($to, $subject, $content);

if ($result) {
    echo "✅ Gửi email thành công!";
} else {
    echo "❌ Gửi email thất bại. Vui lòng kiểm tra error_log.";
}
