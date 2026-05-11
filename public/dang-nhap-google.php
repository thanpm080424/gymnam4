<?php
/**
 * Google OAuth Callback Handler
 * Processes user login via Google OAuth 2.0
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/GoogleOAuth.php';
require_once __DIR__ . '/../includes/EmailService.php';

startSession();

require_once __DIR__ . '/../includes/Database.php';
$db = new Database();
$oauth = new GoogleOAuth($db);

// Check for authorization code
if (empty($_GET['code'])) {
    setFlash('error', 'Không nhận được authorization code từ Google');
    redirect('/login');
}

$code = $_GET['code'];
$state = $_GET['state'] ?? null;

try {
    // Process OAuth login and get user ID
    $userId = $oauth->processLogin($code, $state);
    
    // Get full user info from database
    $user = $db->selectOne(
        "SELECT ma_nguoi_dung, ten_dang_nhap, email, vai_tro FROM NGUOI_DUNG WHERE ma_nguoi_dung = ?",
        [$userId]
    );
    
    if (!$user) {
        setFlash('error', 'Không thể tải thông tin người dùng');
        redirect('/login');
    }
    
    // Set session - match AuthController keys
    $_SESSION['user_id']       = (int)$user['ma_nguoi_dung'];
    $_SESSION['ten_dang_nhap'] = $user['ten_dang_nhap'];
    $_SESSION['email']         = $user['email'] ?? $user['ten_dang_nhap'];
    $_SESSION['ho_ten']        = $user['ho_ten'] ?? $user['ten_dang_nhap'] ?? '';
    $_SESSION['vai_tro']       = $user['vai_tro'];
    $_SESSION['role']          = $user['vai_tro'];
    $_SESSION['user_role']     = $user['vai_tro'];
    $_SESSION['logged_in_via'] = 'google_oauth';
    
    setFlash('success', 'Đăng nhập thành công');
    
    // Redirect based on role
    switch ($user['vai_tro']) {
        case 'admin':
            redirect('/admin/dashboard');
            break;
        case 'hlv':
        case 'trainer':
            redirect('/trainer/dashboard');
            break;
        case 'nhanvien':
        case 'staff':
            redirect('/admin/dashboard');
            break;
        default:
            redirect('/member/dashboard');
    }
    
} catch (Exception $e) {
    error_log("OAuth Login Error: " . $e->getMessage());
    setFlash('error', 'Lỗi đăng nhập: ' . $e->getMessage());
    redirect('/login');
}
?>
