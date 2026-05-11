<?php
/**
 * Middleware - Auth & Role checks
 * WAMP-compatible: redirect dùng SITE_URL
 */
class Middleware {

    private static function baseUrl() {
        // Đọc SITE_URL từ config nếu đã define, fallback về /monkey-gym/public
        if (defined('SITE_URL')) return rtrim(SITE_URL, '/');
        return '';
    }

    private static function go($path) {
        $url = self::baseUrl() . '/' . ltrim($path, '/');
        header("Location: $url");
        exit;
    }

    public static function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            self::go('/login');
        }
    }

    public static function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) self::go('/login');
        $role = $_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '';
        if (!in_array($role, ['admin', 'nhanvien'])) {
            http_response_code(403);
            die("<h1 style='color:red;text-align:center;margin-top:50px'>403 - Không có quyền truy cập</h1>");
        }
    }

    public static function checkOnlyAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) self::go('/login');
        $role = $_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '';
        if ($role !== 'admin') {
            http_response_code(403);
            die("<h1 style='color:red;text-align:center;margin-top:50px'>403 - Chỉ Admin mới có quyền</h1>");
        }
    }

    public static function checkTrainer() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) self::go('/login');
        $role = $_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '';
        if ($role !== 'hlv') self::go('/login');
    }

    public static function checkStaff() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) self::go('/login');
        $role = $_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '';
        if (!in_array($role, ['admin', 'nhanvien'])) self::go('/login');
    }

    public static function checkMember() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) self::go('/login');
        $role = $_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '';
        if (!in_array($role, ['hoi_vien','admin'])) {
            http_response_code(403);
            die("<h2 style='text-align:center;margin-top:80px;color:#C9993F'>403 — Chỉ hội viên mới truy cập được trang này.</h2>");
        }
    }

    public static function checkOnlyNhanVien() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) self::go('/login');
        $role = $_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '';
        if ($role !== 'nhanvien') {
            http_response_code(403);
            die("<h2 style='text-align:center;margin-top:80px;color:#ef4444'>403 — Không có quyền.</h2>");
        }
    }

    public static function redirectToDashboard() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '';
        switch ($role) {
            case 'admin': case 'nhanvien': self::go('/admin/dashboard'); break;
            case 'hlv':                    self::go('/trainer/dashboard'); break;
            default:                       self::go('/member/dashboard');
        }
    }
}