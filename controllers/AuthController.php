<?php
/**
 * Authentication Controller - Monkey Gym
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../includes/EmailService.php';

class AuthController {
    private $userModel;

    public function __construct() {
        startSession();
        $this->userModel = new UserModel();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                setFlash('danger', 'Vui lòng nhập email và mật khẩu');
                redirect('/login');
            }

            $user = $this->userModel->getUserByEmail($email);

            if (!$user) {
                setFlash('danger', 'Email không tồn tại trên hệ thống');
                redirect('/login');
            }

            if (!verifyPassword($password, $user['mat_khau'])) {
                setFlash('danger', 'Mật khẩu không chính xác');
                redirect('/login');
            }

            // FIX: trang_thai là ENUM 'active'/'banned', không phải 0/1
            if (($user['trang_thai'] ?? 'active') === 'banned') {
                setFlash('danger', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.');
                redirect('/login');
            }

            // Lấy thêm ho_ten từ cột ho_ten (nếu có) hoặc dùng email
            $hoTen = $user['ho_ten'] ?? $user['ten_dang_nhap'] ?? '';

            // Set session đầy đủ - thống nhất dùng 'role' và 'vai_tro'
            $_SESSION['user_id']       = (int)$user['ma_nguoi_dung'];
            $_SESSION['ten_dang_nhap'] = $user['ten_dang_nhap'];
            $_SESSION['email']         = $user['email'] ?? $user['ten_dang_nhap'];
            $_SESSION['ho_ten']        = $hoTen;
            $_SESSION['vai_tro']       = $user['vai_tro'];
            $_SESSION['role']          = $user['vai_tro'];  // alias cho AdminController cũ
            $_SESSION['user_role']     = $user['vai_tro'];  // alias khác

            setFlash('success', 'Đăng nhập thành công! Chào mừng ' . ($hoTen ?: $email));

            // Redirect đúng route theo vai trò
            switch ($user['vai_tro']) {
                case 'admin':
                case 'nhanvien':
                    redirect('/admin/dashboard');
                    break;
                case 'hlv':
                    redirect('/trainer/dashboard');
                    break;
                default: // hoi_vien
                    redirect('/member/dashboard');
            }

        } else {
            require __DIR__ . '/../views/auth/dang-nhap.php';
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email           = trim($_POST['email'] ?? '');
            $password        = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $hoTen           = trim($_POST['ho_ten'] ?? '');

            if (empty($email) || empty($password)) {
                setFlash('danger', 'Vui lòng nhập email và mật khẩu');
                redirect('/register');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                setFlash('danger', 'Email không hợp lệ');
                redirect('/register');
            }

            if (strlen($password) < (defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8)) {
                setFlash('danger', 'Mật khẩu phải có ít nhất 8 ký tự');
                redirect('/register');
            }

            if ($password !== $passwordConfirm) {
                setFlash('danger', 'Mật khẩu xác nhận không khớp');
                redirect('/register');
            }

            if ($this->userModel->userExists($email)) {
                setFlash('danger', 'Email đã được sử dụng');
                redirect('/register');
            }

            $userId = $this->userModel->createUser($email, $password, $hoTen);
            if ($userId) {
                // Gửi email chào mừng
                try {
                    $emailService = new EmailService();
                    $emailService->sendWelcomeEmail($email, $hoTen ?: $email, $email);
                } catch (Exception $e) {
                    error_log('Failed to send welcome email: ' . $e->getMessage());
                }
                
                setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
                redirect('/login');
            } else {
                setFlash('danger', 'Lỗi đăng ký. Vui lòng thử lại.');
                redirect('/register');
            }

        } else {
            require __DIR__ . '/../views/auth/dang-ky.php';
        }
    }

    public function logout() {
        startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        redirect('/login');
    }
}
