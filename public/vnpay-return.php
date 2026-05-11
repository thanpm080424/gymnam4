<?php
/**
 * VNPay Return Handler — Kích hoạt gói + Gửi email
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/VNPayGateway.php';
require_once __DIR__ . '/../includes/EmailService.php';

startSession();

$db    = new Database();
$vnpay = new VNPayGateway($db);

$success = false;
$message = 'Thanh toán thất bại.';
$pending = $_SESSION['pending_payment'] ?? null;

if (!empty($_GET)) {
    // Xác thực chữ ký VNPay
    $valid = $vnpay->verifyReturnData($_GET);
    $responseCode = $_GET['vnp_ResponseCode'] ?? '99';
    $txnRef       = $_GET['vnp_TxnRef'] ?? '';

    // Ghi log để debug
    error_log("VNPay Return - Valid: " . ($valid?'YES':'NO') . " | Code: $responseCode | TxnRef: $txnRef");

    if ($valid && $responseCode === '00' && $pending) {
        // Chữ ký hợp lệ + thanh toán thành công
        $success = true;
    } else {
        if (!$valid) {
            $message = 'Chữ ký VNPay không hợp lệ.';
        } elseif ($responseCode !== '00') {
            $message = 'Thanh toán thất bại (mã: ' . $responseCode . ')';
        } else {
            $message = 'Không tìm thấy thông tin đơn hàng trong phiên.';
        }
    }
} elseif (isset($_GET['simulate']) && $_GET['simulate'] === 'success' && $pending) {
    // Sandbox giả lập
    $success = true;
}

if ($success && $pending) {
    try {
        $db->beginTransaction();
        $type = $pending['type'] ?? 'package';

        if ($type === 'package') {
            // 1. Kích hoạt đăng ký gói
            $db->execute(
                "UPDATE DANG_KY_GOI SET trang_thai='active', ngay_ket_thuc=? WHERE ma_dang_ky=?",
                [$pending['new_expire'], $pending['ma_dang_ky']]
            );

            // 2. Cập nhật thanh toán -> success
            $db->execute(
                "UPDATE THANH_TOAN SET trang_thai='success', phuong_thuc='chuyen_khoan', ngay_thanh_toan=NOW()
                 WHERE ma_dang_ky=? AND trang_thai='pending' LIMIT 1",
                [$pending['ma_dang_ky']]
            );

            // 3. Cộng dồn hạn + buổi PT
            $curInfo = $db->selectOne(
                "SELECT ngay_het_han_goi, so_buoi_pt_con_lai FROM HOI_VIEN WHERE ma_hoi_vien=?",
                [$pending['ma_hoi_vien']]
            );
            $newExpire = $pending['new_expire'];
            $newPT     = ((int)($curInfo['so_buoi_pt_con_lai'] ?? 0)) + ((int)$pending['so_buoi_pt']);

            $db->execute(
                "UPDATE HOI_VIEN SET ngay_het_han_goi=?, so_buoi_pt_con_lai=? WHERE ma_hoi_vien=?",
                [$newExpire, $newPT, $pending['ma_hoi_vien']]
            );
            
            $message = 'Gói tập <strong>' . htmlspecialchars($pending['ten_goi']) . '</strong> đã được kích hoạt thành công!';
        } 
        elseif ($type === 'cart') {
            // XỬ LÝ GIỎ HÀNG
            $maGD = $pending['ma_giao_dich'];
            
            // 1. Cập nhật trạng thái đơn hàng sản phẩm
            $db->execute(
                "UPDATE DON_HANG_SP SET trang_thai='completed' WHERE ma_giao_dich=?",
                [$maGD]
            );
            
            // 2. Trừ kho và cộng điểm
            if (!empty($pending['cart_data'])) {
                foreach ($pending['cart_data'] as $item) {
                    $maSP = $item['ma_san_pham'];
                    $qty  = $item['qty'];
                    $tongTien = $item['gia_ban'] * $qty;
                    
                    // Trừ kho (Dùng query thông minh detect tên cột)
                    $db->execute("UPDATE SAN_PHAM SET ton_kho = ton_kho - ? WHERE ma_sp = ?", [$qty, $maSP]);
                    
                    // Cộng điểm
                    $diem = max(1, (int)floor($tongTien / 50000));
                    if (!empty($pending['ma_hoi_vien'])) {
                        // Import helper function if needed or use raw SQL
                        $db->execute("UPDATE DIEM_TICH_LUY SET so_diem = so_diem + ? WHERE ma_hoi_vien = ?", [$diem, $pending['ma_hoi_vien']]);
                        $db->execute("INSERT INTO LICH_SU_DIEM (ma_hoi_vien, so_diem_thay_doi, ly_do) VALUES (?, ?, ?)", 
                            [$pending['ma_hoi_vien'], $diem, 'Thanh toán đơn hàng: ' . $maGD]);
                    }
                }
            }
            
            $_SESSION['cart'] = []; // Xóa giỏ hàng
            $message = 'Đơn hàng <strong>#' . $maGD . '</strong> đã được thanh toán và xác nhận thành công!';
        }

        $db->commit();

        // 4. Gửi email xác nhận (Chỉ cho Package hiện tại, có thể mở rộng cho Cart sau)
        if ($type === 'package') {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                $user = $db->selectOne("SELECT email, ten_dang_nhap, ho_ten FROM NGUOI_DUNG WHERE ma_nguoi_dung=?", [$userId]);
                $toEmail = !empty($user['email']) ? $user['email'] : ($user['ten_dang_nhap'] ?? '');
                
                if (filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                    $emailService = new EmailService();
                    $emailService->sendPaymentConfirmation($toEmail, $user['ho_ten'] ?? $toEmail, (float)$pending['so_tien'], $pending['ten_goi'], $pending['txn_ref'] ?? $pending['ma_giao_dich']);
                }
            }
        }

        unset($_SESSION['pending_payment']);

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log('VNPay return error: ' . $e->getMessage());
        $success = false;
        $message = 'Lỗi xử lý giao dịch. Vui lòng liên hệ quản trị viên. (' . $e->getMessage() . ')';
    }
}


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $success ? 'Thanh toán thành công' : 'Thanh toán thất bại' ?> | Monkey Gym</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .pay-card {
            max-width: 520px; margin: 60px auto; padding: 40px;
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);
            text-align: center;
        }
        .pay-icon { font-size: 56px; margin-bottom: 16px; }
        .pay-card h2 { font-size: 20px; font-weight: 700; margin-bottom: 10px; }
        .pay-detail {
            background: var(--gold-bg); border: 1px solid var(--gold-border);
            border-radius: var(--radius-md); padding: 16px 20px;
            text-align: left; margin: 20px 0; font-size: 14px;
        }
        .pay-detail p { margin: 6px 0; color: var(--text-secondary); }
        .pay-detail strong { color: var(--text-primary); }
    </style>
</head>
<body style="background:var(--bg-primary)">
<div class="container">
    <div class="pay-card">
        <?php if ($success): ?>
            <div class="pay-icon">✅</div>
            <h2 style="color:var(--success)">Thanh toán thành công!</h2>
            <p style="color:var(--text-muted);font-size:14px;margin-bottom:16px">
                Chúng tôi đã gửi email xác nhận đến hộp thư của bạn.
            </p>
            <div class="pay-detail">
                <?php if (($pending['type'] ?? 'package') === 'package'): ?>
                    <p><strong>Gói tập:</strong> <?= htmlspecialchars($pending['ten_goi'] ?? '') ?></p>
                    <p><strong>Hiệu lực đến:</strong> <?= date('d/m/Y', strtotime($pending['new_expire'])) ?></p>
                <?php else: ?>
                    <p><strong>Mã đơn hàng:</strong> #<?= htmlspecialchars($pending['ma_giao_dich'] ?? '') ?></p>
                    <p><strong>Số lượng:</strong> <?= count($pending['cart_data'] ?? []) ?> sản phẩm</p>
                <?php endif; ?>
                <p><strong>Số tiền:</strong> <span style="color:var(--gold);font-weight:700"><?= number_format((float)($pending['so_tien'] ?? 0), 0, ',', '.') ?>đ</span></p>
                <p><strong>Thời gian:</strong> <?= date('d/m/Y H:i:s') ?></p>
            </div>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                <a href="<?= SITE_URL ?>/member/dashboard" class="btn btn-primary">🏠 Về Dashboard</a>
                <a href="<?= SITE_URL ?>/member/store" class="btn btn-secondary">Mua thêm gói</a>
            </div>
        <?php else: ?>
            <div class="pay-icon">❌</div>
            <h2 style="color:var(--danger)">Thanh toán thất bại</h2>
            <p style="color:var(--text-muted);font-size:14px;margin-bottom:24px"><?= $message ?></p>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                <a href="<?= SITE_URL ?>/member/store" class="btn btn-primary">Thử lại</a>
                <a href="<?= SITE_URL ?>/member/dashboard" class="btn btn-secondary">Về Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
