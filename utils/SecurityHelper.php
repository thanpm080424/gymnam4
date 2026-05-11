<?php
class SecurityHelper {
    // 1. Mã hóa mật khẩu với Bcrypt
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    // 2. Xác thực mật khẩu
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // 3. Prevent XSS: Lọc Input trước khi lưu hoặc hiển thị
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
            return $data;
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}
