<?php
/**
 * API: Check Voucher/Promotion Code
 * Returns discount information
 */

require_once '../../config/config.php';
require_once '../../includes/Database.php';
require_once '../../includes/helpers.php';

startSession();
requireLogin();

header('Content-Type: application/json; charset=UTF-8');

try {
    if (!isset($_POST['code'])) {
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => 'Missing voucher code']);
    }
    
    $code = trim($_POST['code']);
    $db = new Database();
    
    // Check voucher
    $voucher = checkVoucher($code);
    
    if (!$voucher) {
        jsonResponse(['success' => false, 'message' => 'Voucher không hợp lệ hoặc đã hết hạn']);
    }
    
    // Prepare response
    $discount = 0;
    if ($voucher['loai_khuyen_mai'] === 'giam_phan_tram') {
        $discountValue = (float)$voucher['gia_tri'];
        $discount = "Giảm {$discountValue}%";
    } else {
        $discountValue = (float)$voucher['gia_tri'];
        $discount = "Giảm " . formatCurrency($discountValue);
    }
    
    jsonResponse([
        'success'             => true,
        'message'             => 'Voucher hợp lệ',
        'discount_type'       => $voucher['loai_khuyen_mai'],
        'discount_value'      => $voucher['gia_tri'],
        'discount_display'    => $discount,
        'voucher_description' => $voucher['mo_ta'] ?? ''
    ]);
    
} catch (Exception $e) {
    error_log("Voucher Check Error: " . $e->getMessage());
    http_response_code(500);
    jsonResponse(['success' => false, 'message' => 'Lỗi hệ thống']);
}
?>
