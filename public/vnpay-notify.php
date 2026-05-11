<?php
/**
 * VNPay IPN (Instant Payment Notification) Handler
 * Server-to-server callback from VNPay
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';
require_once '../includes/VNPayGateway.php';

// Disable output buffering for proper IPN response
header('Content-Type: application/json; charset=UTF-8');

// Parse IPN data
$inputData = $_GET;

if (empty($inputData)) {
    http_response_code(400);
    echo json_encode(['RspCode' => '07', 'Message' => 'Invalid request']);
    exit;
}

try {
    $db = new Database();
    $vnpay = new VNPayGateway($db);
    
    // Verify signature
    if (!$vnpay->verifyReturnData($inputData)) {
        http_response_code(401);
        echo json_encode(['RspCode' => '07', 'Message' => 'Invalid signature']);
        exit;
    }
    
    // Process payment
    $result = $vnpay->processPaymentReturn($inputData);
    
    if (!$result['success']) {
        echo json_encode(['RspCode' => '01', 'Message' => $result['message']]);
        exit;
    }
    
    // Extract payment info
    $transactionId = $result['transaction_id'];
    $orderId = $result['order_id'];
    $amount = $result['amount'];
    
    // Check if payment already recorded
    $existingPayment = $db->selectOne(
        "SELECT * FROM thanh_toan WHERE ma_giao_dich = ?",
        [$transactionId]
    );
    
    if ($existingPayment) {
        echo json_encode(['RspCode' => '00', 'Message' => 'Success']);
        exit;
    }
    
    // Extract member and package from order ID
    // Format: memberId_packageId_timestamp
    $orderParts = explode('_', $orderId);
    if (count($orderParts) < 2) {
        echo json_encode(['RspCode' => '02', 'Message' => 'Invalid order format']);
        exit;
    }
    
    $memberId = $orderParts[0];
    $packageId = $orderParts[1];
    
    // Record payment
    $paymentId = $vnpay->recordPayment($memberId, $packageId, $amount, $transactionId, 'completed');
    
    if (!$paymentId) {
        echo json_encode(['RspCode' => '99', 'Message' => 'Database error']);
        exit;
    }
    
    // Success response
    echo json_encode(['RspCode' => '00', 'Message' => 'Success']);
    exit;
    
} catch (Exception $e) {
    error_log("VNPay IPN Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['RspCode' => '99', 'Message' => $e->getMessage()]);
    exit;
}
?>
