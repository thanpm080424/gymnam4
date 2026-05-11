<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
startSession();
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['error'=>'unauth']); exit; }
$id = (int)($_GET['id'] ?? 0);
// Chỉ cho lấy QR của chính mình
if ($id !== (int)($_SESSION['user_id'] ?? 0) && !in_array($_SESSION['role']??'', ['admin','nhanvien'])) {
    echo json_encode(['error'=>'forbidden']); exit;
}
echo json_encode(['qr' => generateDynamicQR($id), 'expires_in' => 60 - (time() % 60)]);
