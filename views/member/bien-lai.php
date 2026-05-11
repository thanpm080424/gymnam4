<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biên Lai Mua Hàng | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .receipt-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.1) 100%);
            border: 2px dashed rgba(255,255,255,0.2);
            border-radius: 20px; padding: 3rem; max-width: 500px; margin: 3rem auto;
            text-align: center;
        }
        .barcode-box { background:white; padding:15px; display:inline-block; margin-top:1.5rem; border-radius:5px;}
        .barcode-text { color:black; font-family:monospace; font-size:2rem; font-weight:bold; letter-spacing:8px; line-height:1;}
    </style>
</head>
<body>
<?php require __DIR__ . '/layout/topbar.php'; ?>
<main class="mg-main">
    <div class="mg-page-header">
        <h1>Biên lai đơn hàng</h1>
        <p>Chi tiết giao dịch của bạn.</p>
    </div>
        
        <div class="receipt-card">
            <div style="font-size: 4rem; margin-bottom:1rem;">✅</div>
            <h2 style="color:var(--accent);">Thanh toán Thành Công!</h2>
            <p class="text-muted" style="margin-bottom: 2rem;">Giao dịch mua hàng đã hoàn tất.</p>

            <div style="text-align:left; background:rgba(0,0,0,0.3); padding:1.5rem; border-radius:10px;">
                <p style="margin:5px 0;"><strong>Sản phẩm:</strong> <span style="float:right; color:white; font-size:1.1rem;"><?= htmlspecialchars($receipt['ten_sp']) ?></span></p>
                <?php if(isset($receipt['gia_thanh_toan']) && $receipt['gia_thanh_toan'] < $receipt['gia_tien']): ?>
                    <p style="margin:5px 0;"><strong>Giá gốc:</strong> <span style="float:right; color:#999; text-decoration: line-through;"><?= number_format($receipt['gia_tien']) ?> VNĐ</span></p>
                    <p style="margin:5px 0;"><strong>Thanh toán:</strong> <span style="float:right; color:var(--accent); font-weight:bold; font-size:1.2rem;"><?= number_format($receipt['gia_thanh_toan']) ?> VNĐ</span></p>
                <?php else: ?>
                    <p style="margin:5px 0;"><strong>Số tiền:</strong> <span style="float:right; color:var(--accent);"><?= number_format($receipt['gia_tien']) ?> VNĐ</span></p>
                <?php endif; ?>
                <p style="margin:5px 0;"><strong>Ngày giờ:</strong> <span style="float:right; color:white;"><?= date('H:i - d/m/Y', strtotime($receipt['ngay_mua'])) ?></span></p>
            </div>

            <div class="barcode-box">
                <div style="font-size: 0.7rem; color:gray; margin-bottom:5px;">TRÌNH MÃ NÀY CHO LỄ TÂN VÀ NHẬN HÀNG</div>
                <div class="barcode-text"><?= $receipt['ma_giao_dich'] ?></div>
            </div>

            <div style="margin-top: 2rem;">
                <a href="<?= SITE_URL ?>/member/store" class="btn btn-secondary" style="width:100%;">Trở về cửa hàng</a>
            </div>
        </div>

    </main>
</div><!-- end mg-layout-wrapper -->
</body>
</html>

