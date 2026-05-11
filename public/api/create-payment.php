<?php
/**
 * API: Tạo link thanh toán VNPay cho gói tập
 * POST: ma_goi
 * Redirect thẳng sang VNPay
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/VNPayGateway.php';

startSession();

if (!isLoggedIn()) {
    redirect('/login');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/member/store');
}

$db     = new Database();
$userId = $_SESSION['user_id'];
$maGoi  = (int)($_POST['ma_goi'] ?? 0);

if (!$maGoi) {
    setFlash('danger', 'Gói tập không hợp lệ.');
    redirect('/member/store');
}

// Lấy thông tin gói tập
$package = $db->selectOne("SELECT * FROM GOI_TAP WHERE ma_goi = ?", [$maGoi]);
if (!$package) {
    setFlash('danger', 'Gói tập không tồn tại.');
    redirect('/member/store');
}

// Lấy thông tin hội viên
$member = $db->selectOne(
    "SELECT hv.ma_hoi_vien FROM HOI_VIEN hv WHERE hv.ma_nguoi_dung = ?",
    [$userId]
);
if (!$member) {
    setFlash('danger', 'Không tìm thấy thông tin hội viên.');
    redirect('/member/store');
}

// Tạo đăng ký gói trạng thái pending (chờ thanh toán)
$today = date('Y-m-d');
$thang = (int)$package['thoi_han_thang'];

// Lấy ngày hết hạn hiện tại để cộng dồn
$currentInfo = $db->selectOne(
    "SELECT ngay_het_han_goi, so_buoi_pt_con_lai FROM HOI_VIEN WHERE ma_nguoi_dung = ?",
    [$userId]
);
$currentExpire = $currentInfo['ngay_het_han_goi'] ?? null;
$baseDate = ($currentExpire && $currentExpire >= $today) ? $currentExpire : $today;
$newExpire = $thang > 0
    ? date('Y-m-d', strtotime("$baseDate + $thang months"))
    : $baseDate;

$maDangKy = $db->insert(
    "INSERT INTO DANG_KY_GOI (ma_hoi_vien, ma_goi, ngay_kich_hoat, ngay_ket_thuc, trang_thai) VALUES (?,?,?,?,'pending')",
    [$member['ma_hoi_vien'], $maGoi, $today, $newExpire]
);

if (!$maDangKy) {
    setFlash('danger', 'Lỗi tạo đơn đăng ký. Vui lòng thử lại.');
    redirect('/member/store');
}

// Tạo mã giao dịch
$txnRef = 'VNP_' . $maDangKy . '_' . time();

// Lưu giao dịch pending
$db->execute(
    "INSERT INTO THANH_TOAN (ma_dang_ky, vnp_TxnRef, so_tien, phuong_thuc, trang_thai) VALUES (?,?,?,'vnpay','pending')",
    [$maDangKy, $txnRef, $package['gia_tien']]
);

// Lưu vào session để xử lý sau return
$_SESSION['pending_payment'] = [
    'type'          => 'package',
    'ma_dang_ky'    => $maDangKy,
    'ma_goi'        => $maGoi,
    'ma_hoi_vien'   => $member['ma_hoi_vien'],
    'so_tien'       => $package['gia_tien'],
    'ten_goi'       => $package['ten_goi'],
    'new_expire'    => $newExpire,
    'so_buoi_pt'    => (int)$package['so_buoi_pt'],
    'txn_ref'       => $txnRef,
];

// Tạo URL VNPay
try {
    $vnpay      = new VNPayGateway();
    $orderInfo  = 'Thanh toan goi tap: ' . $package['ten_goi'];
    // Dùng txnRef cố định, KHÔNG để createPaymentUrl tự ghép thêm timestamp
    $paymentUrl = $vnpay->createPaymentUrl($txnRef, $package['gia_tien'], $orderInfo, $txnRef);
    // Lưu đúng vnp_TxnRef thực tế mà VNPay sẽ trả về (= txnRef + '_' + YmdHis)
    // -> Không thể predict nên ta verify theo ma_dang_ky thay vì khớp txn_ref chính xác
    $_SESSION['pending_payment']['txn_ref_prefix'] = $txnRef; // Prefix để so sánh
    header('Location: ' . $paymentUrl);
    exit;
} catch (Exception $e) {
    // VNPay chưa config -> dùng sandbox giả lập
    error_log('VNPay error: ' . $e->getMessage());
    redirect('/member/payment-simulate?ma_dang_ky=' . $maDangKy);
}
