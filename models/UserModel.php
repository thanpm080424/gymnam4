<?php
/**
 * User Model - Monkey Gym
 * Handles user/member data operations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/helpers.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get user by email/username
     */
    public function getUserByEmail($email) {
        return $this->db->selectOne(
            "SELECT * FROM NGUOI_DUNG WHERE ten_dang_nhap = ? OR email = ? LIMIT 1",
            [$email, $email]
        );
    }

    /**
     * Create new user
     */
    public function createUser($email, $password, $hoTen = '') {
        $hashed = hashPassword($password);
        
        $userData = [
            'ten_dang_nhap' => $email,
            'email'         => $email,
            'ho_ten'        => $hoTen ?: $email,
            'mat_khau'      => $hashed,
            'vai_tro'       => 'hoi_vien',
            'trang_thai'    => 'active'
        ];
        
        $userId = $this->db->insertArray('NGUOI_DUNG', $userData);
        
        if ($userId) {
            // Create member record
            $memberData = [
                'ma_nguoi_dung' => $userId,
                'ma_qr'         => 'MEMBER_' . $userId . '_' . time()
            ];
            
            $this->db->insertArray('HOI_VIEN', $memberData);
            return $userId;
        }
        
        return false;
    }

    /**
     * Check if user exists
     */
    public function userExists($email) {
        return $this->db->exists(
            "SELECT 1 FROM NGUOI_DUNG WHERE ten_dang_nhap = ? OR email = ?",
            [$email, $email]
        );
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        return verifyPassword($password, $hash);
    }

    /**
     * Update user profile
     */
    public function updateUser($userId, $data) {
        return $this->db->updateArray(
            'NGUOI_DUNG',
            $data,
            'ma_nguoi_dung = ?',
            [$userId]
        );
    }

    /**
     * Get member info
     */
    public function getMemberInfo($userId) {
        return $this->db->selectOne(
            "SELECT m.*, u.ten_dang_nhap, u.email 
             FROM HOI_VIEN m 
             JOIN NGUOI_DUNG u ON m.ma_nguoi_dung = u.ma_nguoi_dung 
             WHERE m.ma_nguoi_dung = ?",
            [$userId]
        );
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        return $this->db->selectOne(
            "SELECT * FROM NGUOI_DUNG WHERE ma_nguoi_dung = ?",
            [$userId]
        );
    }
}
