<?php
/**
 * VNPay Payment Gateway Integration
 * Monkey Gym Management System
 * 
 * Updated: 2025 - Enhanced VNPay support
 */

class VNPayGateway {
    private $merchantId;
    private $secretKey;
    private $apiUrl;
    private $returnUrl;
    private $notifyUrl;
    private $db;
    
    public function __construct($db = null) {
        $this->merchantId = VNPAY_MERCHANT_ID ?? '';
        $this->secretKey  = VNPAY_SECRET_KEY ?? '';
        $this->apiUrl     = VNPAY_API_URL ?? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        $this->returnUrl  = VNPAY_RETURN_URL ?? '';
        $this->notifyUrl  = VNPAY_NOTIFY_URL ?? '';
        $this->db         = $db;
    }
    
    /**
     * Create payment URL for VNPay
     * 
     * @param int $orderId Order/Transaction ID
     * @param int $amount Amount in VND (must be integer, no decimal)
     * @param string $orderInfo Order description
     * @param string $refCode Reference code
     * @return string Payment URL
     */
    public function createPaymentUrl($orderId, $amount, $orderInfo = '', $refCode = '') {
        if (empty($this->merchantId) || empty($this->secretKey)) {
            throw new Exception("VNPay credentials not configured");
        }
        
        // Build request data - KHÔNG đưa vnp_BankCode rỗng vào
        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $this->merchantId,
            "vnp_Amount"     => (int)($amount * 100),
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $this->getClientIP(),
            "vnp_Locale"     => "vn",
            "vnp_OrderInfo"  => !empty($orderInfo) ? $this->removeVietnameseAccents($orderInfo) : "Thanh toan don hang",
            "vnp_OrderType"  => "billpayment",
            "vnp_ReturnUrl"  => $this->returnUrl,
            "vnp_TxnRef"     => (string)$orderId, // Dùng trực tiếp, không ghép timestamp
            "vnp_ExpireDate" => date('YmdHis', strtotime('+15 minutes')),
        ];
        
        // Sắp xếp theo key
        ksort($inputData);
        
        // Build hashData và queryString theo đúng chuẩn VNPay chính thức
        // Cả key và value đều phải urlencode
        $hashData = "";
        $queryPart = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $queryPart .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        $queryPart = rtrim($queryPart, '&');
        
        // Tạo chữ ký HMAC-SHA512
        $vnpSecureHash = hash_hmac('sha512', $hashData, $this->secretKey);
        
        // Ghép URL cuối cùng
        $paymentUrl = $this->apiUrl . '?' . $queryPart . '&vnp_SecureHash=' . $vnpSecureHash;
        
        return $paymentUrl;
    }
    
    /**
     * Verify return data from VNPay
     */
    public function verifyReturnData($returnData) {
        if (empty($returnData['vnp_SecureHash'])) {
            return false;
        }
        
        $securehashInput = $returnData['vnp_SecureHash'];
        
        // Xóa hash và hash type ra khỏi mảng trước khi tính lại
        $data = $returnData;
        unset($data['vnp_SecureHash']);
        unset($data['vnp_SecureHashType']);
        
        ksort($data);
        
        // Build hashData đúng chuẩn VNPay
        $hashData = "";
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $this->secretKey);
        
        return hash_equals(strtolower($secureHash), strtolower($securehashInput));
    }

    
    /**
     * Process payment return
     * 
     * @param array $returnData Data from VNPay
     * @return array Result with status and message
     */
    public function processPaymentReturn($returnData) {
        // Verify signature
        if (!$this->verifyReturnData($returnData)) {
            return [
                'success' => false,
                'message' => 'Signature verification failed'
            ];
        }
        
        // Check response code
        $responseCode = $returnData['vnp_ResponseCode'] ?? '99';
        
        if ($responseCode !== '00') {
            return [
                'success' => false,
                'message' => 'Payment failed with code: ' . $responseCode
            ];
        }
        
        // Extract order info
        $transactionId = $returnData['vnp_TransactionNo'] ?? '';
        $orderId = $returnData['vnp_TxnRef'] ?? '';
        $amount = (int)($returnData['vnp_Amount'] ?? 0) / 100; // Convert back from hundredths
        
        return [
            'success'        => true,
            'message'        => 'Payment successful',
            'transaction_id' => $transactionId,
            'order_id'       => $orderId,
            'amount'         => $amount,
            'response_code'  => $responseCode
        ];
    }
    
    /**
     * Record payment in database
     */
    public function recordPayment($memberId, $packageId, $amount, $transactionId, $status = 'completed') {
        if (!$this->db) {
            return false;
        }
        
        try {
            // Record payment
            $paymentData = [
                'ma_hoi_vien'        => $memberId,
                'loai_thanh_toan'    => 'vnpay',
                'so_tien'            => $amount,
                'ma_giao_dich'       => $transactionId,
                'trang_thai'         => $status,
                'ngay_tao'           => date('Y-m-d H:i:s')
            ];
            
            $paymentId = $this->db->insertArray('thanh_toan', $paymentData);
            
            if (!$paymentId) {
                return false;
            }
            
            // Update package registration if payment successful
            if ($status === 'completed') {
                $packageData = [
                    'ma_hoi_vien'        => $memberId,
                    'ma_goi'             => $packageId,
                    'trang_thai'         => 'da_thanh_toan',
                    'ngay_bat_dau'       => date('Y-m-d'),
                    'ngay_ket_thuc'      => date('Y-m-d', strtotime('+1 month')),
                    'ma_thanh_toan'      => $paymentId,
                    'ngay_tao'           => date('Y-m-d H:i:s')
                ];
                
                $this->db->insertArray('dang_ky_goi', $packageData);
            }
            
            return $paymentId;
        } catch (Exception $e) {
            error_log("Payment recording error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
        return trim((string)$ip);
    }
    
    /**
     * Remove Vietnamese accents for VNPay OrderInfo to prevent signature mismatch
     */
    private function removeVietnameseAccents($str) {
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/', 'A', $str);
        $str = preg_replace('/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/', 'E', $str);
        $str = preg_replace('/(Ì|Í|Ị|Ỉ|Ĩ)/', 'I', $str);
        $str = preg_replace('/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/', 'O', $str);
        $str = preg_replace('/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/', 'U', $str);
        $str = preg_replace('/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/', 'Y', $str);
        $str = preg_replace('/(Đ)/', 'D', $str);
        // Remove other non-ASCII characters to be safe
        $str = preg_replace('/[^a-zA-Z0-9\s:_-]/', '', $str);
        return $str;
    }
    
    /**
     * Create order in database
     */
    public function createOrder($memberId, $packageId, $amount, $description = '') {
        if (!$this->db) {
            throw new Exception("Database not initialized");
        }
        
        try {
            $orderData = [
                'ma_hoi_vien'        => $memberId,
                'ma_goi'             => $packageId,
                'so_tien'            => $amount,
                'mo_ta'              => $description,
                'trang_thai'         => 'cho_thanh_toan',
                'ngay_tao'           => date('Y-m-d H:i:s'),
                'ngay_het_han'       => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ];
            
            return $this->db->insertArray('don_hang', $orderData);
        } catch (Exception $e) {
            error_log("Order creation error: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Helper function for VNPay
 */
if (!function_exists('createVNPaymentUrl')) {
    function createVNPaymentUrl($orderId, $amount, $orderInfo = '') {
        require_once __DIR__ . '/../config/config.php';
        
        $vnpay = new VNPayGateway();
        return $vnpay->createPaymentUrl($orderId, $amount, $orderInfo);
    }
}
