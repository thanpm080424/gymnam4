<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/EmailService.php';

$emailService = new EmailService();
$to = 'thanpm080424@gmail.com'; // User's email from the system settings
$subject = "Test Email Monkey Gym";
$content = "<h1>Email Test Thành Công!</h1><p>Hệ thống PHPMailer và SMTP của Monkey Gym đang hoạt động hoàn hảo.</p><p>Thời gian: " . date('Y-m-d H:i:s') . "</p>";

echo "Đang thử gửi email tới $to qua SMTP...<br>";
$result = $emailService->send($to, $subject, $content);

if ($result) {
    echo "✅ SUCCESS: Gửi email thành công!";
} else {
    echo "❌ ERROR: Gửi email thất bại. Vui lòng kiểm tra log lỗi PHP.";
}
?>
