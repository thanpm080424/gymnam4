<?php
/**
 * Helper Functions - Monkey Gym Management System
 * Session, Auth, Security, Validation, Utilities
 */

// ============================================================================
// SESSION & AUTH HELPERS
// ============================================================================

if (!function_exists('startSession')) {
    function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            require_once __DIR__ . '/../config/config.php';
            session_start();
        }
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        startSession();
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isLoggedIn()) {
            redirect('/login');
        }
    }
}

if (!function_exists('requireRole')) {
    function requireRole($roles) {
        requireLogin();
        if (!hasRole($roles)) {
            die("Bạn không có quyền truy cập trang này.");
        }
    }
}

if (!function_exists('hasRole')) {
    function hasRole($roles) {
        startSession();
        if (!isLoggedIn()) return false;
        
        $userRole = $_SESSION['user_role'] ?? $_SESSION['vai_tro'] ?? '';
        
        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }
        return $userRole === $roles;
    }
}

if (!function_exists('getUserInfo')) {
    function getUserInfo() {
        startSession();
        if (!isLoggedIn()) return null;
        
        return [
            'id'         => (int)($_SESSION['user_id'] ?? 0),
            'username'   => $_SESSION['ten_dang_nhap'] ?? $_SESSION['user_name'] ?? '',
            'email'      => $_SESSION['email'] ?? '',
            'role'       => $_SESSION['user_role'] ?? $_SESSION['vai_tro'] ?? '',
            'vai_tro'    => $_SESSION['user_role'] ?? $_SESSION['vai_tro'] ?? '',
            'avatar'     => $_SESSION['avatar'] ?? null,
            'ho_ten'     => $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? '',
        ];
    }
}

if (!function_exists('logout')) {
    function logout() {
        startSession();
        $_SESSION = [];
        session_destroy();
        redirect('/login');
    }
}

// ============================================================================
// REDIRECT & RESPONSE HELPERS
// ============================================================================

if (!function_exists('redirect')) {
    function redirect($url) {
        $base = '';
        if (defined('SITE_URL')) $base = rtrim(SITE_URL, '/');
        
        $target = (strpos($url, 'http') === 0)
            ? $url
            : ($base ? ($base . '/' . ltrim($url, '/')) : $url);
        
        header("Location: " . $target);
        exit;
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('setFlash')) {
    function setFlash($type, $message) {
        startSession();
        // Standardized format: ['type' => ..., 'message' => ...]
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('getFlash')) {
    function getFlash($type = null) {
        startSession();
        if (!isset($_SESSION['flash'])) return null;
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        // Support both old format (['flash'][$type]) and new format (['flash']['type'])
        if ($type !== null && is_array($flash) && isset($flash[$type])) {
            return $flash[$type];
        }
        return $flash;
    }
}

// ============================================================================
// SANITIZATION & VALIDATION
// ============================================================================

if (!function_exists('sanitize')) {
    function sanitize($data, $context = 'html') {
        if (is_array($data)) {
            return array_map(function($item) use ($context) {
                return sanitize($item, $context);
            }, $data);
        }
        
        $data = trim((string)($data ?? ''));
        $data = stripslashes($data);
        
        switch ($context) {
            case 'html':
                return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            case 'url':
                return urlencode($data);
            case 'sql':
                return addslashes($data);
            case 'json':
                return json_encode($data, JSON_UNESCAPED_UNICODE);
            default:
                return $data;
        }
    }
}

if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('validatePhone')) {
    function validatePhone($phone) {
        return preg_match('/^(\+84|0)[0-9]{9}$/', $phone) === 1;
    }
}

if (!function_exists('isValidDate')) {
    function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

// ============================================================================
// PASSWORD & SECURITY
// ============================================================================

if (!function_exists('hashPassword')) {
    function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

if (!function_exists('generateToken')) {
    function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = generateToken();
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        startSession();
        return !empty($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return number_format($amount, 0, ',', '.') . ' đ';
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd/m/Y H:i') {
        try {
            return date($format, strtotime($date));
        } catch (Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('truncateText')) {
    function truncateText($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('generateRandomCode')) {
    function generateRandomCode($length = 6, $type = 'alphanum') {
        $chars = '';
        if (in_array($type, ['alpha', 'alphanum'])) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if (in_array($type, ['num', 'alphanum'])) {
            $chars .= '0123456789';
        }
        
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }
}

if (!function_exists('getClientIP')) {
    function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        return trim($ip);
    }
}

// ============================================================================
// PERMISSION CHECKS
// ============================================================================

if (!function_exists('checkVoucher')) {
    function checkVoucher($code) {
        if (!defined('DATABASE_LOADED')) {
            require_once __DIR__ . '/../config/config.php';
            require_once __DIR__ . '/Database.php';
            define('DATABASE_LOADED', true);
        }
        
        $db = new Database();
        $voucher = $db->selectOne(
            "SELECT * FROM khuyen_mai WHERE ma_khuyen_mai = ? AND trang_thai = 1 AND (ngay_het IS NULL OR ngay_het >= NOW())",
            [$code]
        );
        
        return $voucher;
    }
}

// ============================================================================
// ALIAS & COMPATIBILITY FUNCTIONS (Merged từ MonkeyGym_Full)
// ============================================================================

if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        return generateCSRFToken();
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return verifyCSRFToken($token);
    }
}

if (!function_exists('setFlash')) {
    function setFlash($type, $message) {
        startSession();
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('getFlash')) {
    function getFlash() {
        startSession();
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data, $context = 'html') {
        if (is_array($data)) return array_map(fn($v) => sanitize($v, $context), $data);
        $data = trim(stripslashes($data));
        switch ($context) {
            case 'url':      return urlencode($data);
            case 'js':       return json_encode($data);
            case 'filename': return preg_replace('/[^a-zA-Z0-9_.-]/', '', $data);
            default:         return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
    }
}

if (!function_exists('logActivity')) {
    function logActivity($userId, $action, $details = '') {
        try {
            if (!class_exists('Database')) return false;
            $db = new Database();
            $db->execute(
                "INSERT IGNORE INTO log_hoat_dong (ma_nguoi_dung, hanh_dong, chi_tiet, ip_address) VALUES (?,?,?,?)",
                [$userId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? '']
            );
            return true;
        } catch (Exception $e) {
            error_log("logActivity failed: " . $e->getMessage());
            return false;
        }
    }
}

// ============================================================
// QR ĐỘNG: HMAC-SHA256, đổi mỗi 60 giây
// ============================================================
if (!function_exists('generateDynamicQR')) {
    function generateDynamicQR(int $maHoiVien): string {
        $secret  = defined('APP_KEY') ? APP_KEY : 'monkey_gym_secret_2026';
        $window  = (int)(time() / 60); // đổi mỗi 60 giây
        $hmac    = hash_hmac('sha256', "MBR_{$maHoiVien}_{$window}", $secret);
        return "DQRC_{$maHoiVien}_{$window}_" . substr($hmac, 0, 16);
    }
    function verifyDynamicQR(string $qr, int &$memberId): bool {
        $secret = defined('APP_KEY') ? APP_KEY : 'monkey_gym_secret_2026';
        $parts  = explode('_', $qr);
        if (count($parts) < 4 || $parts[0] !== 'DQRC') return false;
        $mid    = (int)$parts[1];
        $win    = (int)$parts[2];
        $hash   = $parts[3];
        // Cho phép sai 1 window (~1 phút dung sai)
        foreach ([$win, $win - 1] as $w) {
            $expected = substr(hash_hmac('sha256', "MBR_{$mid}_{$w}", $secret), 0, 16);
            if (hash_equals($expected, $hash)) {
                $memberId = $mid;
                return true;
            }
        }
        return false;
    }
}

// ============================================================
// CSRF per-action (chống replay)
// ============================================================
if (!function_exists('csrfField')) {
    function csrfField(string $action = 'default'): string {
        $token = generateCSRFToken();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token) . '">'
             . '<input type="hidden" name="_action" value="' . htmlspecialchars($action) . '">';
    }
    function verifyCsrf(string $action = 'default'): bool {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return verifyCSRFToken($token);
    }
}

// ============================================================
// ĐIỂM TÍCH LUỸ: thêm điểm + ghi lịch sử
// ============================================================
if (!function_exists('addPoints')) {
    function addPoints($db, int $maHoiVien, int $diem, string $lyDo): void {
        try {
            // Database::getConnection() hoặc object thường
            $exec = function($sql, $params) use ($db) {
                if (method_exists($db, 'execute')) {
                    $db->execute($sql, $params);
                } else {
                    $s = $db->prepare($sql); $s->execute($params);
                }
            };
            $exec(
                "INSERT INTO DIEM_TICH_LUY (ma_hoi_vien, so_diem)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE so_diem = so_diem + ?",
                [$maHoiVien, $diem, $diem]
            );
            $exec(
                "INSERT INTO LICH_SU_DIEM (ma_hoi_vien, so_diem_thay_doi, ly_do) VALUES (?,?,?)",
                [$maHoiVien, $diem, $lyDo]
            );
        } catch (Exception $e) {
            error_log("addPoints error: " . $e->getMessage());
        }
    }
}