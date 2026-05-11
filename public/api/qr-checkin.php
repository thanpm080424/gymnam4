<?php
ob_start(); // Bắt toàn bộ output - kể cả PHP warning/error từ WAMP

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Tắt display_errors sau khi load config (config có thể bật lại)
ini_set('display_errors', 0);
error_reporting(0);

startSession();

// Xả buffer cũ (warning từ require), rồi set header JSON
ob_clean();
header('Content-Type: application/json; charset=UTF-8');

function sendJson(array $data): void {
    ob_end_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Auth ────────────────────────────────────────────────────────
$role = $_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '';
if (!isLoggedIn() || !in_array($role, ['admin', 'nhanvien'])) {
    sendJson(['success' => false, 'message' => '⚠️ Cần đăng nhập với tài khoản Admin hoặc Nhân viên']);
}

// ── Input ────────────────────────────────────────────────────────
$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$qrCode = trim($input['qr_code'] ?? '');

if (empty($qrCode)) {
    sendJson(['success' => false, 'message' => 'Thiếu mã QR']);
}

try {
    $db = Database::getConnection();

    // ── Parse QR: DQRC_ (động HMAC) | MEMBER_ (tĩnh cũ) | ma_qr DB ──
    $memberId = null;

    if (strpos($qrCode, 'DQRC_') === 0) {
        // QR động — xác thực HMAC
        if (function_exists('verifyDynamicQR')) {
            $mid = 0;
            if (!verifyDynamicQR($qrCode, $mid)) {
                sendJson(['success' => false, 'message' => '❌ QR đã hết hạn (>60s). Yêu cầu hội viên refresh trang.']);
            }
            $memberId = $mid;
        } else {
            // helpers.php chưa cập nhật → parse thủ công
            $parts    = explode('_', $qrCode);
            $memberId = isset($parts[1]) ? (int)$parts[1] : null;
        }
    } elseif (strpos($qrCode, 'MEMBER_') === 0) {
        // QR tĩnh cũ: MEMBER_{ma_hoi_vien}_{timestamp}
        $parts    = explode('_', $qrCode);
        $memberId = isset($parts[1]) ? (int)$parts[1] : null;
    } else {
        // Thử tìm theo ma_qr trong DB
        $s = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_qr = ?");
        $s->execute([$qrCode]);
        $r = $s->fetch();
        $memberId = $r ? (int)$r['ma_hoi_vien'] : null;
    }

    if (!$memberId) {
        sendJson(['success' => false, 'message' => 'QR không hợp lệ']);
    }

    // ── Lấy thông tin hội viên ───────────────────────────────────
    $s = $db->prepare(
        "SELECT hv.ma_hoi_vien, hv.ngay_het_han_goi,
                COALESCE(nd.ho_ten, nd.ten_dang_nhap) AS ho_ten
         FROM HOI_VIEN hv
         JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
         WHERE hv.ma_hoi_vien = ?"
    );
    $s->execute([$memberId]);
    $member = $s->fetch();

    if (!$member) {
        sendJson(['success' => false, 'message' => 'Không tìm thấy hội viên']);
    }

    // ── Kiểm tra gói còn hạn ─────────────────────────────────────
    $today = date('Y-m-d');
    if (empty($member['ngay_het_han_goi']) || $member['ngay_het_han_goi'] < $today) {
        sendJson([
            'success'     => false,
            'message'     => '❌ Gói tập của ' . $member['ho_ten'] . ' đã hết hạn!',
            'member_name' => $member['ho_ten'],
        ]);
    }

    // ── Đã điểm danh hôm nay chưa ───────────────────────────────
    $s = $db->prepare("SELECT gio_diem_danh FROM diem_danh WHERE ma_hoi_vien = ? AND ngay = ?");
    $s->execute([$memberId, $today]);
    $existing = $s->fetch();

    if ($existing) {
        sendJson([
            'success'      => false,
            'message'      => '⚠️ ' . $member['ho_ten'] . ' đã điểm danh hôm nay lúc ' . substr($existing['gio_diem_danh'], 0, 5) . '!',
            'member_name'  => $member['ho_ten'],
            'checkin_time' => substr($existing['gio_diem_danh'], 0, 5),
        ]);
    }

    // ── Ghi điểm danh ───────────────────────────────────────────
    $db->prepare(
        "INSERT INTO diem_danh (ma_hoi_vien, ngay, gio_diem_danh, phuong_thuc, ghi_chu)
         VALUES (?, CURDATE(), CURTIME(), 'qr_code', 'QR Scan')"
    )->execute([$memberId]);

    // ── Cộng điểm tích lũy ──────────────────────────────────────
    $db->prepare(
        "INSERT INTO DIEM_TICH_LUY (ma_hoi_vien, so_diem)
         VALUES (?, 1) ON DUPLICATE KEY UPDATE so_diem = so_diem + 1"
    )->execute([$memberId]);

    $db->prepare(
        "INSERT INTO LICH_SU_DIEM (ma_hoi_vien, so_diem_thay_doi, ly_do)
         VALUES (?, 1, 'Điểm danh QR')"
    )->execute([$memberId]);

    // ── Lịch sử ra vào ──────────────────────────────────────────
    $db->prepare(
        "INSERT INTO LICH_SU_RA_VAO (ma_hoi_vien, ghi_chu) VALUES (?, 'QR Check-in')"
    )->execute([$memberId]);

    sendJson([
        'success'      => true,
        'message'      => '✅ Điểm danh thành công! +1 điểm.',
        'member_name'  => $member['ho_ten'],
        'checkin_time' => date('H:i'),
        'checkin_date' => date('d/m/Y'),
        'expire_date'  => date('d/m/Y', strtotime($member['ngay_het_han_goi'])),
    ]);

} catch (Exception $e) {
    error_log('QR Checkin: ' . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}