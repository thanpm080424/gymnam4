<?php
/**
 * Configuration File - Monkey Gym Management System
 * Updated: 2025 - Enhanced with OAuth & Payment Support
 */

// ============================================================================
// DATABASE CONFIGURATION
// ============================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'monkey_gym');
define('DB_CHARSET', 'utf8mb4');

// ============================================================================
// SITE SETTINGS
// ============================================================================
// Auto-detect SITE_URL dựa theo vị trí thực tế trên server
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Lấy thư mục project (2 cấp lên từ public/)
    $scriptDir = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')));
    $scriptDir = rtrim($scriptDir, '/');
    define('SITE_URL', $protocol . '://' . $host . $scriptDir);
}
// URL trỏ vào thư mục public/ (dùng cho CSS, JS, ảnh)
if (!defined('ASSET_URL')) {
    define('ASSET_URL', SITE_URL . '/public');
}
define('SITE_NAME', 'Monkey Gym');
define('ENVIRONMENT', 'development');

// ============================================================================
// SESSION SECURITY SETTINGS
// ============================================================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 3600);
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// ============================================================================
// ERROR REPORTING & TIMEZONE
// ============================================================================
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

// ============================================================================
// EMAIL CONFIGURATION (Gmail SMTP)
// ============================================================================
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'thanpm080424@gmail.com');
define('MAIL_PASS', 'vwzbvkggxuoxvgvi');
define('MAIL_FROM', 'thanpm080424@gmail.com');
define('MAIL_FROM_NAME', 'Monkey Gym System');

// ============================================================================
// GOOGLE OAUTH CONFIGURATION
// ============================================================================
define('GOOGLE_CLIENT_ID', '1064464500270-3fi33i9mkbobfmvragt95n5pm5fr92mv.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_CALLBACK_URL', SITE_URL . '/dang-nhap-google');

// ============================================================================
// VNPAY PAYMENT CONFIGURATION
// ============================================================================
define('VNPAY_MERCHANT_ID', 'WNQFH8OD');                // Terminal ID từ VNPay
define('VNPAY_SECRET_KEY', 'X5ZQUOI10XGRK2C9WQU8VLXV4T5RNW6C'); // Hash Secret từ VNPay
define('VNPAY_API_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'); // Môi trường TEST
define('VNPAY_RETURN_URL', SITE_URL . '/vnpay-return');
define('VNPAY_NOTIFY_URL', SITE_URL . '/vnpay-notify');

// ============================================================================
// QR CODE & FILE UPLOAD
// ============================================================================
define('UPLOAD_PATH', __DIR__ . '/../public/uploads');
define('QR_UPLOAD_PATH', UPLOAD_PATH . '/qr');
define('AVATAR_UPLOAD_PATH', UPLOAD_PATH . '/avatars');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// ============================================================================
// AI & EXTERNAL SERVICES
// ============================================================================
define('GEMINI_API_KEY', 'AIzaSyDh1jxn02x2bNRudpbt2ETxf7lMJ_O4MT0'); // Chú ý: Nên chuyển sang biến môi trường nếu có thể

// ============================================================================
// SECURITY
// ============================================================================
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);

// Ensure log directory exists
if (!defined('LOG_DIR')) {
    define('LOG_DIR', __DIR__ . '/../logs');
    if (!is_dir(LOG_DIR)) {
        mkdir(LOG_DIR, 0755, true);
    }
}

// ============================================================================
// GYM TIME SLOTS (Khung giờ hoạt động)
// ============================================================================
if (!defined('GYM_TIME_SLOTS')) {
    define('GYM_TIME_SLOTS', [
        ['bat_dau' => '06:00:00', 'ket_thuc' => '07:00:00', 'is_break' => false],
        ['bat_dau' => '07:00:00', 'ket_thuc' => '08:00:00', 'is_break' => false],
        ['bat_dau' => '08:00:00', 'ket_thuc' => '09:00:00', 'is_break' => false],
        ['bat_dau' => '09:00:00', 'ket_thuc' => '10:00:00', 'is_break' => false],
        ['bat_dau' => '10:00:00', 'ket_thuc' => '11:00:00', 'is_break' => false],
        ['bat_dau' => '11:00:00', 'ket_thuc' => '12:00:00', 'is_break' => false],
        ['bat_dau' => '12:00:00', 'ket_thuc' => '13:00:00', 'is_break' => true],
        ['bat_dau' => '13:00:00', 'ket_thuc' => '14:00:00', 'is_break' => false],
        ['bat_dau' => '14:00:00', 'ket_thuc' => '15:00:00', 'is_break' => false],
        ['bat_dau' => '15:00:00', 'ket_thuc' => '16:00:00', 'is_break' => false],
        ['bat_dau' => '16:00:00', 'ket_thuc' => '17:00:00', 'is_break' => false],
        ['bat_dau' => '17:00:00', 'ket_thuc' => '18:00:00', 'is_break' => false],
        ['bat_dau' => '18:00:00', 'ket_thuc' => '19:00:00', 'is_break' => false],
        ['bat_dau' => '19:00:00', 'ket_thuc' => '20:00:00', 'is_break' => false],
        ['bat_dau' => '20:00:00', 'ket_thuc' => '21:00:00', 'is_break' => false],
    ]);
}

