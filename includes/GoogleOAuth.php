<?php
/**
 * OAuth Integration - Monkey Gym Management System
 * Supports Google OAuth 2.0
 * 
 * Updated: 2025 - Google OAuth Support
 */

class GoogleOAuth {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $db;
    
    public function __construct($db = null) {
        $this->clientId     = GOOGLE_CLIENT_ID ?? '';
        $this->clientSecret = GOOGLE_CLIENT_SECRET ?? '';
        $this->redirectUri  = GOOGLE_CALLBACK_URL ?? '';
        $this->db           = $db;
    }
    
    /**
     * Get authorization URL for user to click
     */
    public function getAuthorizationUrl($state = null) {
        if (empty($this->clientId) || empty($this->redirectUri)) {
            throw new Exception("Google OAuth credentials not configured");
        }
        
        if (!$state) {
            $state = bin2hex(random_bytes(16));
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['oauth_state'] = $state;
        }
        
        $params = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online'
        ];
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken($code) {
        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->redirectUri)) {
            throw new Exception("Google OAuth credentials not configured");
        }
        
        $postData = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code'
        ];
        
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST          => true,
            CURLOPT_POSTFIELDS    => http_build_query($postData),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT       => 30
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Get user info from access token
     */
    public function getUserInfo($accessToken) {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER    => [
                'Authorization: Bearer ' . $accessToken
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT       => 30
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Process OAuth login/registration
     * Returns user ID if successful
     */
    public function processLogin($code, $state = null) {
        // Verify state token
        if ($state) {
            startSession();
            if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
                throw new Exception("Invalid state token");
            }
            unset($_SESSION['oauth_state']);
        }
        
        // Get access token
        $tokenData = $this->getAccessToken($code);
        if (isset($tokenData['error'])) {
            throw new Exception("OAuth error: " . $tokenData['error_description']);
        }
        
        // Get user info
        $userInfo = $this->getUserInfo($tokenData['access_token']);
        if (!isset($userInfo['email'])) {
            throw new Exception("Unable to get user email from Google");
        }
        
        // Check or create user in database
        if ($this->db) {
            return $this->findOrCreateUser($userInfo);
        }
        
        return $userInfo;
    }
    
    /**
     * Find existing user or create new one
     */
    private function findOrCreateUser($googleUserInfo) {
        $email  = $googleUserInfo['email'] ?? '';
        $hoTen  = trim(($googleUserInfo['given_name'] ?? '') . ' ' . ($googleUserInfo['family_name'] ?? ''));
        if (empty($hoTen)) $hoTen = $googleUserInfo['name'] ?? $email;
        $isNewUser = false;

        // Check if user already exists (by email or ten_dang_nhap)
        $user = $this->db->selectOne(
            "SELECT ma_nguoi_dung FROM NGUOI_DUNG WHERE ten_dang_nhap = ? OR email = ?",
            [$email, $email]
        );

        if ($user) {
            // Update ho_ten from Google if not set
            $this->db->query(
                "UPDATE NGUOI_DUNG SET ho_ten = COALESCE(NULLIF(ho_ten,''), ?), email = ? WHERE ma_nguoi_dung = ?",
                [$hoTen, $email, $user['ma_nguoi_dung']]
            );
            return $user['ma_nguoi_dung'];
        }

        // Create new user
        $randomPass = bin2hex(random_bytes(16));
        // Use hashPassword helper if available, otherwise use PASSWORD_BCRYPT
        $hashedPassword = function_exists('hashPassword') ? hashPassword($randomPass) : password_hash($randomPass, PASSWORD_DEFAULT);

        $userId = $this->db->insert(
            "INSERT INTO NGUOI_DUNG (ten_dang_nhap, email, ho_ten, mat_khau, vai_tro, trang_thai) VALUES (?, ?, ?, ?, 'hoi_vien', 'active')",
            [$email, $email, $hoTen, $hashedPassword]
        );

        if (!$userId) {
            throw new Exception('Không thể tạo tài khoản mới.');
        }

        // Create HOI_VIEN record
        $maQR = 'MEMBER_' . $userId . '_' . time();
        $this->db->query(
            "INSERT INTO HOI_VIEN (ma_nguoi_dung, ma_qr) VALUES (?, ?)",
            [$userId, $maQR]
        );

        // Create reward points entry if table exists
        try {
            $hvRow = $this->db->selectOne("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung=?", [$userId]);
            if ($hvRow) {
                $this->db->query("INSERT IGNORE INTO DIEM_TICH_LUY (ma_hoi_vien, so_diem) VALUES (?, 0)", [$hvRow['ma_hoi_vien']]);
            }
        } catch (Exception $e) {}

        // Send welcome email to new user
        try {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailService = new EmailService();
                $emailService->sendWelcomeEmail($email, $hoTen, $email);
            }
        } catch (Exception $e) {
            error_log('Google OAuth welcome email failed: ' . $e->getMessage());
        }

        return $userId;
    }
}

/**
 * Helper function for OAuth
 */
if (!function_exists('getGoogleOAuthUrl')) {
    function getGoogleOAuthUrl() {
        $oauth = new GoogleOAuth();
        return $oauth->getAuthorizationUrl();
    }
}
