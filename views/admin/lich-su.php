<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Ra Vào | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header">
                <div>
                    <h1>Lịch sử Điểm Danh</h1>
                    <p class="text-muted">Ghi nhận các lượt check-in qua máy quét QR.</p>
                </div>
            </header>

                        <div class="glass-panel" style="padding: 0; overflow: hidden;">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-light); background: rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 1rem; font-weight: 800; margin: 0;">📋 Nhật ký ra vào</h3>
                    <span class="badge" style="background: var(--gold-bg); color: var(--gold-dark); border: 1px solid var(--gold-light); font-size: 0.75rem;"><?= count($historyList) ?> lượt quét</span>
                </div>
                
                <div style="display: flex; flex-direction: column;">
                    <?php foreach($historyList as $h): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid var(--border-light); transition: background 0.2s;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 44px; height: 44px; background: var(--gold-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                👤
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--text-primary); font-size: 1rem;"><?= htmlspecialchars($h['ten_dang_nhap']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                    <span style="font-family: monospace; background: rgba(0,0,0,0.05); padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($h['ma_qr']) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: var(--gold-dark); font-size: 0.95rem;"><?= date('H:i:s', strtotime($h['thoi_gian_vao'])) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($h['thoi_gian_vao'])) ?></div>
                        </div>

                        <div style="margin-left: 20px;">
                            <span style="color: #10b981; font-size: 0.8rem; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                                <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></div> SUCCESS
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($historyList)): ?>
                    <div style="padding: 60px 20px; text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 15px;">🔍</div>
                        <h4 style="font-weight: 800; color: var(--text-primary);">Chưa có dữ liệu ra vào</h4>
                        <p class="text-muted" style="font-size: 0.9rem;">Toàn bộ lượt quét QR sẽ được hiển thị tại đây.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
