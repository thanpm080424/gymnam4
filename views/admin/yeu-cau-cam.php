<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xét Duyệt Ban Hội Viên | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
    <div class="admin-content">
        <header class="dashboard-header"><h1>🚫 Xét Duyệt Yêu Cầu Ban Hội Viên</h1></header>
        <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>
        <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 24px; margin-bottom: 2rem;">
            <?php foreach($pendingBans as $ban): ?>
            <div class="glass-panel" style="padding: 24px; display: flex; flex-direction: column; gap: 16px; border-left: 5px solid #ef4444; background: linear-gradient(145deg, #ffffff, rgba(239,68,68,0.03));">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; background: #fee2e2; color: #ef4444; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            👤
                        </div>
                        <div>
                            <div style="font-size: 1rem; font-weight: 800; color: var(--text-primary);"><?= htmlspecialchars($ban['ten_hoi_vien']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Báo cáo bởi: <strong><?= htmlspecialchars($ban['ten_staff']) ?></strong></div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span style="background: rgba(239,68,68,0.1); color: #ef4444; padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; letter-spacing: 1px;">PENDING</span>
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 6px;"><?= date('d/m/Y H:i', strtotime($ban['created_at'])) ?></div>
                    </div>
                </div>

                <div style="padding: 15px; background: rgba(0,0,0,0.02); border-radius: 10px; border: 1px solid var(--border-light);">
                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; font-weight: 700;">Lý do chi tiết:</div>
                    <div style="font-size: 0.9rem; color: var(--text-primary); line-height: 1.5; font-style: italic;">
                        "<?= nl2br(htmlspecialchars($ban['ly_do'])) ?>"
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: auto;">
                    <form action="<?= SITE_URL ?>/admin/ban-requests/approve" method="POST" style="flex: 2;">
                        <input type="hidden" name="ma_ycb" value="<?= $ban['ma_ycb'] ?>">
                        <input type="hidden" name="ma_hoi_vien" value="<?= $ban['ma_hoi_vien'] ?>">
                        <button class="btn btn-danger btn-sm" style="width: 100%; justify-content: center; background: #ef4444; border-color: #ef4444; font-weight: 700;">✅ Duyệt Ban</button>
                    </form>
                    <form action="<?= SITE_URL ?>/admin/ban-requests/reject" method="POST" style="flex: 1;">
                        <input type="hidden" name="ma_ycb" value="<?= $ban['ma_ycb'] ?>">
                        <button class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;">❌ Hủy</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($pendingBans)): ?>
                <div class="glass-panel" style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">
                    Không có yêu cầu ban nào đang chờ xử lý.
                </div>
            <?php endif; ?>
        </div>


        <div class="glass-panel">
            <h3 style="margin-bottom:1rem;">📋 Lịch Sử Xử Lý</h3>
            <?php if(empty($processedBans)): ?>
            <p class="text-muted">Chưa có lịch sử xử lý.</p>
            <?php else: ?>
            <table class="glass-table" style="width:100%;">
                <thead><tr><th>Hội viên</th><th>Staff gửi</th><th>Lý do</th><th>Trạng thái</th><th>Ngày</th></tr></thead>
                <tbody>
                <?php foreach($processedBans as $ban): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($ban['ten_hoi_vien']) ?></strong></td>
                    <td><?= htmlspecialchars($ban['ten_staff']) ?></td>
                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars(substr($ban['ly_do'], 0, 60)) ?></td>
                    <td>
                        <?php if($ban['trang_thai'] === 'approved'): ?>
                        <span style="color:#f87171;">✅ Đã ban</span>
                        <?php else: ?>
                        <span style="color:#10b981;">❌ Từ chối</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted" style="font-size:0.8rem;"><?= date('d/m/Y', strtotime($ban['updated_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
