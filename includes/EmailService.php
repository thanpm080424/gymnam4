<?php
/**
 * Email Service - Monkey Gym Management System
 * Supports both PHPMailer (SMTP) and PHP built-in mail()
 * 
 * Updated: 2025 - Enhanced SMTP support
 */

class EmailService {
    private $db;
    private $usePHPMailer = true;
    private $mailProvider = 'smtp'; // 'smtp' or 'mail'
    
    public function __construct($db = null) {
        $this->db = $db;
        $this->checkMailerAvailability();
    }
    
    /**
     * Check if PHPMailer is available
     */
    private function checkMailerAvailability() {
        if (!defined('MAIL_HOST')) {
            $this->usePHPMailer = false;
            $this->mailProvider = 'mail';
            return;
        }
        
        // Check if PHPMailer files exist
        $basePath = __DIR__ . '/PHPMailer/src/';
        if (!file_exists($basePath . 'PHPMailer.php')) {
            $this->usePHPMailer = false;
            $this->mailProvider = 'mail';
        }
    }
    
    /**
     * Send email using SMTP (PHPMailer) or built-in mail()
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $content Email HTML content
     * @param array $options Additional options
     * @return bool
     */
    public function send($to, $subject, $content, $options = []) {
        if ($this->usePHPMailer && $this->mailProvider === 'smtp') {
            return $this->sendViaSMTP($to, $subject, $content, $options);
        } else {
            return $this->sendViaMailFunction($to, $subject, $content, $options);
        }
    }
    
    /**
     * Send via PHPMailer (SMTP)
     */
    private function sendViaSMTP($to, $subject, $content, $options = []) {
        try {
            // Load PHPMailer
            require_once __DIR__ . '/PHPMailer/src/Exception.php';
            require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
            require_once __DIR__ . '/PHPMailer/src/SMTP.php';
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT ?? 587;
            
            // Email Properties
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME ?? 'Monkey Gym');
            $mail->addAddress($to);
            
            // CC, BCC if provided
            if (!empty($options['cc'])) {
                $mail->addCC($options['cc']);
            }
            if (!empty($options['bcc'])) {
                $mail->addBCC($options['bcc']);
            }
            
            // Subject & Content
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body    = $content;
            $mail->AltBody = strip_tags($content);
            $mail->CharSet = 'UTF-8';
            
            // Add attachment if provided
            if (!empty($options['attachments'])) {
                foreach ((array)$options['attachments'] as $file) {
                    if (file_exists($file)) {
                        $mail->addAttachment($file);
                    }
                }
            }
            
            // Send email
            return $mail->send();
            
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log("Email failed to send: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send via PHP built-in mail() function
     */
    private function sendViaMailFunction($to, $subject, $content, $options = []) {
        // Headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . (MAIL_FROM ?? 'noreply@localhost') . "\r\n";
        $headers .= "X-Mailer: PHP " . phpversion() . "\r\n";
        
        if (!empty($options['cc'])) {
            $headers .= "Cc: " . $options['cc'] . "\r\n";
        }
        if (!empty($options['bcc'])) {
            $headers .= "Bcc: " . $options['bcc'] . "\r\n";
        }
        
        // Additional parameters
        $additionalParams = '';
        if (defined('MAIL_FROM')) {
            $additionalParams = '-f ' . MAIL_FROM;
        }
        
        // Send email
        $result = @mail($to, $subject, $content, $headers, $additionalParams);
        
        if (!$result) {
            error_log("Email failed to send via mail() - To: $to, Subject: $subject");
        }
        
        return $result;
    }
    
    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail($email, $name, $username) {
        $subject = "Chào mừng bạn đến với Monkey Gym";
        $content = $this->getWelcomeTemplate($name, $username);
        return $this->send($email, $subject, $content);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $name, $resetToken) {
        $resetLink = SITE_URL . '/public/reset-password.php?token=' . urlencode($resetToken);
        $subject = "Đặt lại mật khẩu Monkey Gym";
        $content = $this->getPasswordResetTemplate($name, $resetLink);
        return $this->send($email, $subject, $content);
    }
    
    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmation($email, $memberName, $trainerName, $bookingDate, $bookingTime) {
        $subject = "Xác nhận đặt lịch PT - Monkey Gym";
        $content = $this->getBookingTemplate($memberName, $trainerName, $bookingDate, $bookingTime);
        return $this->send($email, $subject, $content);
    }
    
    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation($email, $memberName, $amount, $packageName, $transactionId) {
        $subject = "Xác nhận thanh toán - Monkey Gym";
        $content = $this->getPaymentTemplate($memberName, $amount, $packageName, $transactionId);
        return $this->send($email, $subject, $content);
    }
    
    /**
     * Send absence reminder email (e.g. 15 days)
     */
    public function sendAbsenceReminder($email, $memberName, $daysAbsent) {
        $subject = "Monkey Gym nhớ bạn! Đã $daysAbsent ngày rồi bạn chưa đến tập 🏋️‍♂️";
        $content = $this->getAbsenceTemplate($memberName, $daysAbsent);
        return $this->send($email, $subject, $content);
    }

    /**
     * Send package expiration reminder email
     */
    public function sendExpirationReminder($email, $memberName, $daysLeft, $packageName) {
        $subject = "⏰ Gói tập của bạn sẽ hết hạn sau $daysLeft ngày - Gia hạn ngay!";
        $content = $this->getExpirationTemplate($memberName, $daysLeft, $packageName);
        return $this->send($email, $subject, $content);
    }
    
    /**
     * Send locker rental confirmation email
     */
    public function sendLockerConfirmation($email, $memberName, $soTu, $ngayBatDau, $ngayKetThuc, $trangThai) {
        $subject = "Xác nhận thuê tủ đồ - Monkey Gym";
        $content = $this->getLockerTemplate($memberName, $soTu, $ngayBatDau, $ngayKetThuc, $trangThai);
        return $this->send($email, $subject, $content);
    }
    
    /**
     * Send locker expiration reminder email
     */
    public function sendLockerExpirationReminder($email, $memberName, $soTu, $daysLeft) {
        $subject = "⏰ Tủ đồ số $soTu của bạn sẽ hết hạn sau $daysLeft ngày!";
        $content = $this->getLockerExpirationTemplate($memberName, $soTu, $daysLeft);
        return $this->send($email, $subject, $content);
    }
    
    /**
     * Welcome email template
     */
    private function getWelcomeTemplate($name, $username) {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f5f5f5;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center;'>Chào mừng đến với Monkey Gym</h2>
                <p style='font-size: 16px; color: #666;'>
                    Xin chào <strong>$name</strong>,
                </p>
                <p style='font-size: 16px; color: #666; line-height: 1.6;'>
                    Tài khoản của bạn đã được tạo thành công. Bạn có thể đăng nhập bằng tên đăng nhập:
                </p>
                <p style='text-align: center; background-color: #f0f0f0; padding: 10px; border-radius: 5px; font-weight: bold; font-size: 14px;'>
                    $username
                </p>
                <p style='font-size: 16px; color: #666; line-height: 1.6;'>
                    Vui lòng thay đổi mật khẩu ngay sau khi đăng nhập lần đầu.
                </p>
                <p style='font-size: 14px; color: #999; border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;'>
                    © 2024 Monkey Gym. All rights reserved.
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Password reset email template
     */
    private function getPasswordResetTemplate($name, $resetLink) {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f5f5f5;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center;'>Đặt lại mật khẩu</h2>
                <p style='font-size: 16px; color: #666;'>
                    Xin chào <strong>$name</strong>,
                </p>
                <p style='font-size: 16px; color: #666; line-height: 1.6;'>
                    Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản Monkey Gym của mình.
                </p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='$resetLink' style='background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                        Đặt lại mật khẩu
                    </a>
                </p>
                <p style='font-size: 14px; color: #999;'>
                    Liên kết này sẽ hết hạn trong 24 giờ.
                </p>
                <p style='font-size: 14px; color: #999; border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;'>
                    © 2024 Monkey Gym. All rights reserved.
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Booking confirmation template
     */
    private function getBookingTemplate($memberName, $trainerName, $bookingDate, $bookingTime) {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f5f5f5;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center;'>Xác nhận đặt lịch PT</h2>
                <p style='font-size: 16px; color: #666;'>
                    Xin chào <strong>$memberName</strong>,
                </p>
                <div style='background-color: #f9f9f9; padding: 15px; border-left: 4px solid #4CAF50; margin: 20px 0;'>
                    <p><strong>Thông tin buổi tập:</strong></p>
                    <p>Huấn luyện viên: <strong>$trainerName</strong></p>
                    <p>Ngày: <strong>$bookingDate</strong></p>
                    <p>Giờ: <strong>$bookingTime</strong></p>
                </div>
                <p style='font-size: 14px; color: #999; border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;'>
                    © 2024 Monkey Gym. All rights reserved.
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Payment confirmation template
     */
    private function getPaymentTemplate($memberName, $amount, $packageName, $transactionId) {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f5f5f5;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center;'>Xác nhận thanh toán</h2>
                <p style='font-size: 16px; color: #666;'>
                    Xin chào <strong>$memberName</strong>,
                </p>
                <div style='background-color: #f9f9f9; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0;'>
                    <p><strong>Chi tiết thanh toán:</strong></p>
                    <p>Gói tập: <strong>$packageName</strong></p>
                    <p>Số tiền: <strong style='color: #2196F3;'>" . formatCurrency($amount) . "</strong></p>
                    <p>Mã giao dịch: <strong>$transactionId</strong></p>
                </div>
                <p style='font-size: 14px; color: #666;'>
                    Cảm ơn bạn đã tin tưởng Monkey Gym. Hãy tận hưởng những buổi tập luyện tuyệt vời!
                </p>
                <p style='font-size: 14px; color: #999; border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;'>
                    © " . date('Y') . " Monkey Gym. All rights reserved.
                </p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Absence reminder template
     */
    private function getAbsenceTemplate($memberName, $daysAbsent) {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f5f5f5;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <div style='background-color: #FF9800; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                    <h2 style='color: white; margin: 0;'>Chúng tôi rất nhớ bạn! 🥺</h2>
                </div>
                <div style='padding: 20px;'>
                    <p style='font-size: 16px; color: #333;'>Xin chào <strong>$memberName</strong>,</p>
                    <p style='font-size: 16px; color: #555; line-height: 1.6;'>
                        Hệ thống ghi nhận đã <strong>$daysAbsent ngày</strong> rồi bạn chưa ghé thăm Monkey Gym. Cơ bắp của bạn đang 'khóc thét' đòi được vận động đấy!
                    </p>
                    <p style='font-size: 16px; color: #555; line-height: 1.6;'>
                        Hãy quay lại phòng tập sớm để duy trì thói quen và đạt được mục tiêu sức khỏe của mình nhé. Đừng để những ngày tập luyện gian khổ trước đây trở nên lãng phí.
                    </p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . SITE_URL . "/member/booking' style='background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Đặt Lịch Tập Ngay</a>
                    </div>
                    <p style='font-size: 14px; color: #888;'>Nếu bạn đang gặp khó khăn hoặc cần hỗ trợ tư vấn lộ trình, hãy liên hệ ngay với lễ tân hoặc các HLV của chúng tôi.</p>
                </div>
                <p style='font-size: 12px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 15px;'>
                    © " . date('Y') . " Monkey Gym. Cùng nhau khỏe đẹp mỗi ngày.
                </p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Expiration reminder template
     */
    private function getExpirationTemplate($memberName, $daysLeft, $packageName) {
        $color = $daysLeft <= 1 ? '#F44336' : '#FF9800';
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f5f5f5;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <div style='background-color: $color; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                    <h2 style='color: white; margin: 0;'>Thông Báo Hết Hạn Gói Tập ⏰</h2>
                </div>
                <div style='padding: 20px;'>
                    <p style='font-size: 16px; color: #333;'>Xin chào <strong>$memberName</strong>,</p>
                    <p style='font-size: 16px; color: #555; line-height: 1.6;'>
                        Monkey Gym xin thông báo: Gói tập <strong>$packageName</strong> của bạn chỉ còn <strong>$daysLeft ngày</strong> nữa là sẽ hết hạn.
                    </p>
                    <div style='background-color: #FFF3E0; border-left: 4px solid $color; padding: 15px; margin: 20px 0;'>
                        <p style='margin: 0; color: #E65100; font-weight: bold;'>Đừng để quá trình tập luyện bị gián đoạn!</p>
                        <p style='margin: 5px 0 0 0; color: #E65100; font-size: 14px;'>Hãy gia hạn ngay hôm nay để nhận được các ưu đãi đặc biệt dành cho hội viên thân thiết.</p>
                    </div>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . SITE_URL . "/member/store' style='background-color: #2196F3; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Gia Hạn / Mua Gói Mới</a>
                    </div>
                </div>
                <p style='font-size: 12px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 15px;'>
                    © " . date('Y') . " Monkey Gym. Trân trọng cảm ơn bạn đã đồng hành cùng chúng tôi.
                </p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Locker confirmation template
     */
    private function getLockerTemplate($memberName, $soTu, $ngayBatDau, $ngayKetThuc, $trangThai) {
        $statusText = $trangThai === 'approved' ? 'được cấp phát thành công' : 'đang chờ xác nhận';
        $statusColor = $trangThai === 'approved' ? '#4CAF50' : '#FF9800';
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f5f5f5;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <div style='background-color: #2196F3; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                    <h2 style='color: white; margin: 0;'>Xác Nhận Thuê Tủ Đồ 🔐</h2>
                </div>
                <div style='padding: 20px;'>
                    <p style='font-size: 16px; color: #333;'>Xin chào <strong>$memberName</strong>,</p>
                    <p style='font-size: 16px; color: #555; line-height: 1.6;'>
                        Yêu cầu thuê tủ đồ cá nhân của bạn đã <strong>$statusText</strong>.
                    </p>
                    <div style='background-color: #E3F2FD; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0;'>
                        <p style='margin: 5px 0;'>Tủ số: <strong style='color: #1976D2; font-size: 18px;'>" . htmlspecialchars($soTu) . "</strong></p>
                        <p style='margin: 5px 0;'>Ngày bắt đầu: <strong>" . date('d/m/Y', strtotime($ngayBatDau)) . "</strong></p>
                        <p style='margin: 5px 0;'>Ngày kết thúc: <strong>" . date('d/m/Y', strtotime($ngayKetThuc)) . "</strong></p>
                    </div>
                    <p style='font-size: 14px; color: #888;'>Vui lòng bảo quản chìa khóa cẩn thận và không để đồ dùng có giá trị quá lớn trong tủ. Monkey Gym sẽ không chịu trách nhiệm cho các mất mát liên quan đến tài sản cá nhân.</p>
                </div>
                <p style='font-size: 12px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 15px;'>
                    © " . date('Y') . " Monkey Gym. All rights reserved.
                </p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Locker expiration reminder template
     */
    private function getLockerExpirationTemplate($memberName, $soTu, $daysLeft) {
        $color = $daysLeft <= 1 ? '#F44336' : '#FF9800';
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f5f5f5;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <div style='background-color: $color; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                    <h2 style='color: white; margin: 0;'>Thông Báo Hết Hạn Tủ Đồ ⏰</h2>
                </div>
                <div style='padding: 20px;'>
                    <p style='font-size: 16px; color: #333;'>Xin chào <strong>$memberName</strong>,</p>
                    <p style='font-size: 16px; color: #555; line-height: 1.6;'>
                        Monkey Gym xin thông báo: Thời gian thuê tủ đồ số <strong>$soTu</strong> của bạn chỉ còn <strong>$daysLeft ngày</strong> nữa là sẽ hết hạn.
                    </p>
                    <div style='background-color: #FFF3E0; border-left: 4px solid $color; padding: 15px; margin: 20px 0;'>
                        <p style='margin: 0; color: #E65100; font-weight: bold;'>Lưu ý quan trọng!</p>
                        <p style='margin: 5px 0 0 0; color: #E65100; font-size: 14px;'>Vui lòng đến quầy lễ tân để gia hạn nếu bạn muốn tiếp tục sử dụng, hoặc dọn dẹp đồ đạc và trả lại chìa khóa trước ngày hết hạn để tránh phí phạt.</p>
                    </div>
                </div>
                <p style='font-size: 12px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 15px;'>
                    © " . date('Y') . " Monkey Gym. Trân trọng cảm ơn bạn đã đồng hành cùng chúng tôi.
                </p>
            </div>
        </body>
        </html>
        ";
    }
}

/**
 * Helper function to send email easily
 */
if (!function_exists('sendEmail')) {
    function sendEmail($to, $subject, $content, $options = []) {
        $emailService = new EmailService();
        return $emailService->send($to, $subject, $content, $options);
    }
}
