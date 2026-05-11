<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Giao Dịch | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header">
                <div>
                    <h1>Lịch Sử Giao Dịch & Bán Hàng</h1>
                    <p class="text-muted">Hoá đơn Gói tập trực tuyến & TPCN cần Giao</p>
                </div>
            </header>

            <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>

            <div class="glass-panel" style="padding: 0; overflow: hidden;">
                <table class="glass-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Thời gian Mua</th>
                            <th>Khách Hàng</th>
                            <th>Mặt Hàng</th>
                            <th>Mã Giao Dịch</th>
                            <th>Giá Trị</th>
                            <th>Trạng Thái / Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($allPurchases as $p): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?= date('d/m/Y', strtotime($p['ngay_mua'])) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('H:i', strtotime($p['ngay_mua'])) ?></div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['nguoi_mua']) ?></strong>
                            </td>
                            
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <?php if($p['phan_loai'] === 'sản phẩm'): ?>
                                        <span style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700;">TPCN</span>
                                    <?php else: ?>
                                        <span style="background: rgba(59, 130, 246, 0.12); color: #3b82f6; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700;">GÓI TẬP</span>
                                    <?php endif; ?>
                                    <span style="font-weight: 500;"><?= htmlspecialchars($p['mon_hang']) ?></span>
                                </div>
                            </td>

                            <td style="font-family: monospace; color: var(--text-muted); letter-spacing: 0.5px;">
                                <?= $p['ma_giao_dich'] ?? '---' ?>
                            </td>
                            
                            <td style="color: var(--accent); font-weight: 800;">
                                <?= number_format($p['gia_tien']) ?>đ
                            </td>
                            
                            <td>
                                <?php if($p['phan_loai'] === 'gói tập'): ?>
                                    <span class="badge badge-success">✓ Đã kích hoạt</span>
                                <?php elseif($p['phan_loai'] === 'sản phẩm'): ?>
                                    <?php if($p['status'] === 'pending'): ?>
                                        <form action="<?= SITE_URL ?>/admin/purchases/update" method="POST" style="display:inline;">
                                            <input type="hidden" name="ma_dh" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Xác nhận đã trao sản phẩm tận tay cho khách?');">Duyệt Nhận Hàng</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--ok); font-weight: 600; font-size: 0.85rem;">✓ Đã giao hàng</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($allPurchases)): ?>
                            <tr><td colspan="6" class="text-center text-muted" style="padding: 3rem;">Chưa có giao dịch nào diễn ra.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
