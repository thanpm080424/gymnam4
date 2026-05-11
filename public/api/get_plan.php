<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../database/config.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$db = Database::getConnection();

// Lấy ma_hoi_vien
$stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
$stmtM->execute([$userId]);
$memberId = $stmtM->fetchColumn();

if (!$memberId) {
    echo json_encode(['success' => false, 'message' => 'User is not a member']);
    exit;
}

// Lấy plan mới nhất
$stmt = $db->prepare("SELECT * FROM PLAN_CA_NHAN WHERE ma_hoi_vien = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$memberId]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);

if ($plan) {
    $plan['lich_tap'] = json_decode($plan['lich_tap'], true);
    $plan['che_do_an'] = json_decode($plan['che_do_an'], true);
    echo json_encode(['success' => true, 'data' => $plan]);
} else {
    echo json_encode(['success' => false, 'message' => 'No plan found for this user']);
}
