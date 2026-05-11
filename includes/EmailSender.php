<?php
/**
 * Email Sender Class - PRODUCTION READY
 * Gửi email thực tế qua Gmail SMTP
 * 
 * MonkeyGym Email System
 * Author: Claude AI
 * Date: 2025-12-11
 * Version: 1.0
 */

// require_once __DIR__ . '/../vendor/autoload.php'; // Uncomment khi cài PHPMailer via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $db;
    private $config;
    
    public function __construct($database) {
        $this->db = $database;
        
        // ============================================================
        // ⭐ SMTP CONFIGURATION - ĐỔI THÔNG TIN NÀY
        // ============================================================
        
        $this->config = [
            // Gmail SMTP
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_secure' => 'tls',  // 'tls' cho port 587, 'ssl' cho port 465
            
            // ⭐ QUAN TRỌNG: Đổi email và App Password của bạn
            'smtp_username' => 'your-email@gmail.com',    // Email Gmail của bạn
            'smtp_password' => 'xxxx xxxx xxxx xxxx',     // App Password (16 ký tự)
            
            // From email (có thể khác với smtp_username)
            'from_email' => 'noreply@monkeygym.com',
            'from_name' => 'Monkey Gym',
            
            // Debug mode (chỉ dùng khi test)
            'debug' => false  // true = show debug info, false = production mode
        ];
        
        // ============================================================
        // HƯỚNG DẪN TẠO APP PASSWORD:
        // ============================================================
        // 1. Truy cập: https://myaccount.google.com/apppasswords
        // 2. Đăng nhập Gmail
        // 3. Tạo password mới cho "MonkeyGym Email"
        // 4. Copy password 16 ký tự (có thể có dấu cách)
        // 5. Paste vào 'smtp_password' ở trên
        // 
        // LƯU Ý: Phải bật 2-Step Verification trước
        // ============================================================
    }
    
    /**
     * Gửi email ngay lập tức
     * 
     * @param string $toEmail - Email người nhận
     * @param string $toName - Tên người nhận
     * @param string $subject - Tiêu đề email
     * @param string $body - Nội dung HTML
     * @param array $attachments - File đính kèm (optional)
     * @return array ['success' => true/false, 'message' => '...']
     */
    public function sendNow($toEmail, $toName, $subject, $body, $attachments = []) {
        try {
            $mail = new PHPMailer(true);
            
            // ============================================================
            // SMTP CONFIGURATION
            // ============================================================
            
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_secure'];
            $mail->Port = $this->config['smtp_port'];
            $mail->CharSet = 'UTF-8';
            
            // Debug mode
            if ($this->config['debug']) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug: $str");
                };
            }
            
            // ============================================================
            // SENDER & RECIPIENT
            // ============================================================
            
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->config['from_email'], $this->config['from_name']);
            
            // ============================================================
            // CONTENT
            // ============================================================
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body); // Plain text version
            
            // ============================================================
            // ATTACHMENTS
            // ============================================================
            
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    if (isset($file['path']) && file_exists($file['path'])) {
                        $mail->addAttachment($file['path'], $file['name'] ?? '');
                    }
                }
            }
            
            // ============================================================
            // SEND
            // ============================================================
            
            $mail->send();
            
            // Log success
            $this->logEmail($toEmail, $subject, 'sent', null);
            
            return [
                'success' => true,
                'message' => 'Email đã được gửi thành công!'
            ];
            
        } catch (Exception $e) {
            $errorMessage = "Email gửi thất bại: {$mail->ErrorInfo}";
            error_log($errorMessage);
            $this->logEmail($toEmail, $subject, 'failed', $errorMessage);
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'error' => $mail->ErrorInfo
            ];
        }
    }
    
    /**
     * Thêm email vào queue (gửi sau bằng cron job)
     * 
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $body
     * @param int $priority - 1=highest, 5=normal, 10=lowest
     * @param string|null $scheduledAt - Y-m-d H:i:s format
     * @return int|false - Queue ID hoặc false nếu lỗi
     */
    public function queueEmail($toEmail, $toName, $subject, $body, $priority = 5, $scheduledAt = null) {
        $sql = "INSERT INTO email_queue 
                (to_email, to_name, subject, body, priority, scheduled_at, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        
        return $this->db->insert($sql, [
            $toEmail,
            $toName,
            $subject,
            $body,
            $priority,
            $scheduledAt
        ]);
    }
    
    /**
     * Xử lý email queue - gọi từ cron job
     * 
     * @param int $limit - Số email tối đa mỗi lần
     * @return array - ['total', 'sent', 'failed']
     */
    public function processQueue($limit = 10) {
        // Lấy emails cần gửi
        $sql = "SELECT * FROM email_queue 
                WHERE status = 'pending' 
                AND attempts < max_attempts
                AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                ORDER BY priority ASC, created_at ASC 
                LIMIT ?";
        
        $emails = $this->db->select($sql, [$limit]);
        
        $sent = 0;
        $failed = 0;
        
        foreach ($emails as $email) {
            $result = $this->sendNow(
                $email['to_email'],
                $email['to_name'],
                $email['subject'],
                $email['body']
            );
            
            if ($result['success']) {
                // Update status = sent
                $this->db->update(
                    "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?",
                    [$email['id']]
                );
                $sent++;
            } else {
                // Tăng attempts
                $this->db->update(
                    "UPDATE email_queue 
                     SET attempts = attempts + 1, 
                         error_message = ?,
                         status = IF(attempts + 1 >= max_attempts, 'failed', 'pending')
                     WHERE id = ?",
                    [$result['message'], $email['id']]
                );
                $failed++;
            }
            
            // Sleep 1 giây giữa các email để tránh spam
            sleep(1);
        }
        
        return [
            'total' => count($emails),
            'sent' => $sent,
            'failed' => $failed
        ];
    }
    
    /**
     * Gửi email hàng loạt (broadcast)
     * 
     * @param array $recipients - [['email' => '', 'name' => ''], ...]
     * @param string $subject
     * @param string $body
     * @param bool $useQueue - true = add to queue, false = send now
     * @return array
     */
    public function sendBulk($recipients, $subject, $body, $useQueue = true) {
        $success = 0;
        $failed = 0;
        
        foreach ($recipients as $recipient) {
            if ($useQueue) {
                $result = $this->queueEmail(
                    $recipient['email'],
                    $recipient['name'],
                    $subject,
                    $body,
                    5  // normal priority
                );
                
                if ($result) {
                    $success++;
                } else {
                    $failed++;
                }
            } else {
                $result = $this->sendNow(
                    $recipient['email'],
                    $recipient['name'],
                    $subject,
                    $body
                );
                
                if ($result['success']) {
                    $success++;
                } else {
                    $failed++;
                }
                
                // Sleep giữa các email
                sleep(2);
            }
        }
        
        return [
            'total' => count($recipients),
            'success' => $success,
            'failed' => $failed
        ];
    }
    
    /**
     * Test SMTP connection
     * 
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection() {
        try {
            $mail = new PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_secure'];
            $mail->Port = $this->config['smtp_port'];
            $mail->SMTPDebug = 0;
            
            // Try to connect
            $mail->smtpConnect();
            $mail->smtpClose();
            
            return [
                'success' => true,
                'message' => 'SMTP connection successful!'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Connection failed: {$e->getMessage()}"
            ];
        }
    }
    
    /**
     * Log email activity vào audit_log
     * 
     * @param string $toEmail
     * @param string $subject
     * @param string $status - 'sent' hoặc 'failed'
     * @param string|null $error
     */
    private function logEmail($toEmail, $subject, $status, $error = null) {
        try {
            $sql = "INSERT INTO audit_log 
                    (action, description, ip_address) 
                    VALUES ('SEND_EMAIL', ?, ?)";
            
            $description = "To: $toEmail | Subject: $subject | Status: $status";
            if ($error) {
                $description .= " | Error: $error";
            }
            
            $this->db->query($sql, [
                $description,
                $_SERVER['REMOTE_ADDR'] ?? 'system'
            ]);
        } catch (Exception $e) {
            // Ignore log errors
            error_log("Failed to log email: " . $e->getMessage());
        }
    }
    
    /**
     * Get email statistics
     * 
     * @return array
     */
    public function getStats() {
        $stats = [
            'pending' => $this->db->count('email_queue', ['status' => 'pending']),
            'sent' => $this->db->count('email_queue', ['status' => 'sent']),
            'failed' => $this->db->count('email_queue', ['status' => 'failed']),
        ];
        
        $stats['total'] = $stats['pending'] + $stats['sent'] + $stats['failed'];
        
        return $stats;
    }
}
