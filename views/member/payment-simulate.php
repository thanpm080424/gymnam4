<?php
/**
 * Sandbox giả lập VNPay — chỉ dùng khi development/chưa config VNPay thật
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
startSession();
if (!isLoggedIn()) redirect('/login');

$maDangKy = (int)($_GET['ma_dang_ky'] ?? 0);
$pending  = $_SESSION['pending_payment'] ?? null;
$tenGoi   = $pending['ten_goi'] ?? 'Gói tập';
$soTien   = (float)($pending['so_tien'] ?? 0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán giả lập | Monkey Gym</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body style="background:var(--bg-primary)">
<div class="container">
    <div style="max-width:480px;margin:60px auto;background:#fff;border:2px dashed var(--gold-border);border-radius:var(--radius-xl);padding:36px;text-align:center">
        <div style="font-size:48px;margin-bottom:12px">🏦</div>
        <h2 style="font-size:18px;margin-bottom:4px">Sandbox Thanh Toán</h2>
        <p style="color:var(--text-muted);font-size:13px;margin-bottom:24px">
            VNPay chưa được cấu hình — đây là trang giả lập (chỉ dùng khi development)
        </p>

        <div style="background:var(--gold-bg);border:1px solid var(--gold-border);border-radius:var(--radius-md);padding:16px;text-align:left;margin-bottom:24px;font-size:14px">
            <p style="margin:5px 0"><strong>Gói tập:</strong> <?= htmlspecialchars($tenGoi) ?></p>
            <p style="margin:5px 0"><strong>Số tiền:</strong> <span style="color:var(--gold);font-weight:700"><?= number_format($soTien,0,',','.') ?>đ</span></p>
            <p style="margin:5px 0"><strong>Mã đơn:</strong> #<?= $maDangKy ?></p>
        </div>

        <div style="display:flex;gap:12px;justify-content:center">
            <a href="<?= SITE_URL ?>/vnpay-return?simulate=success"
               class="btn btn-primary" style="flex:1">✅ Thanh toán thành công</a>
            <a href="<?= SITE_URL ?>/member/store"
               class="btn btn-secondary" style="flex:1">❌ Hủy</a>
        </div>

        <p style="font-size:11px;color:var(--text-muted);margin-top:16px">
            Để dùng VNPay thật: cập nhật <code>VNPAY_MERCHANT_ID</code> và <code>VNPAY_SECRET_KEY</code> trong <code>config/config.php</code>
        </p>
    </div>
</div>
</body>
</html>
