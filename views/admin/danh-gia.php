<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyệt Đánh Giá HLV | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
    <div class="admin-content">
        <header class="dashboard-header">
            <h1>⭐ Duyệt Đánh Giá Huấn Luyện Viên</h1>
        </header>
        <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>
<?php
        $pendingReviews = array_filter($reviews, fn($r) => $r['trang_thai'] === 'pending');
        $otherReviews = array_filter($reviews, fn($r) => $r['trang_thai'] !== 'pending');
        ?>

                <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 24px; margin-bottom: 2rem;">
            <?php foreach($pendingReviews as $r): ?>
            <div class="glass-panel" style="padding: 24px; display: flex; flex-direction: column; gap: 16px; border-left: 5px solid #f59e0b; background: linear-gradient(145deg, #ffffff, rgba(245,158,11,0.03));">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-size: 0.9rem; font-weight: 800; color: var(--text-primary);"><?= htmlspecialchars($r['ten_hoi_vien']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">→ <?= htmlspecialchars($r['ten_hlv']) ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="color: #f59e0b; font-size: 1.1rem; letter-spacing: 2px;"><?= str_repeat('★', $r['so_sao']) ?><span style="color: #e5e7eb;"><?= str_repeat('★', 5 - $r['so_sao']) ?></span></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></div>
                    </div>
                </div>

                <div style="flex: 1; padding: 15px; background: rgba(255,255,255,0.5); border-radius: 12px; border: 1px solid var(--border-light); font-style: italic; color: var(--text-primary); font-size: 0.9rem; line-height: 1.5;">
                    "<?= htmlspecialchars($r['noi_dung'] ?? '') ?>"
                </div>

                <?php if($r['trang_thai'] === 'reported'): ?>
                <div style="background: rgba(239,68,68,0.1); color: #ef4444; padding: 8px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    🚩 BỊ BÁO CÁO BỞI HLV
                </div>
                <?php endif; ?>

                <div style="display: flex; gap: 10px; margin-top: auto;">
                    <form action="<?= SITE_URL ?>/admin/reviews/approve" method="POST" style="flex: 1;">
                        <input type="hidden" name="ma_dg" value="<?= $r['ma_dg'] ?>">
                        <button class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">✅ Duyệt</button>
                    </form>
                    <form action="<?= SITE_URL ?>/admin/reviews/reject" method="POST" style="flex: 1;">
                        <input type="hidden" name="ma_dg" value="<?= $r['ma_dg'] ?>">
                        <button class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center; color: #ef4444; border-color: #fca5a5;">❌ Từ chối</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($pendingReviews)): ?>
                <div class="glass-panel" style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">
                    Không có đánh giá nào đang chờ duyệt.
                </div>
            <?php endif; ?>
        </div>


        <div class="glass-panel">
            <h3 style="margin-bottom:1.5rem;">📋 Lịch Sử Đánh Giá (<?= count($otherReviews) ?>)</h3>
            <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px;">
                <?php foreach($otherReviews as $r): ?>
                <div style="padding: 16px; border-radius: 12px; border: 1px solid var(--border-light); background: var(--bg-primary); display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary);"><?= htmlspecialchars($r['ten_hoi_vien']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($r['ten_hlv']) ?></div>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: #f59e0b; font-size: 0.9rem;"><?= str_repeat('★', $r['so_sao']) ?><span style="color: #e5e7eb;"><?= str_repeat('★', 5 - $r['so_sao']) ?></span></div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($r['created_at'])) ?></div>
                        </div>
                    </div>
                    
                    <div style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.8em;">
                        "<?= htmlspecialchars($r['noi_dung'] ?? '') ?>"
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 10px; border-top: 1px solid rgba(0,0,0,0.05);">
                        <?php 
                            $statusColors = ['approved' => ['#10b981', '✅ Approved'], 'rejected' => ['#ef4444', '❌ Rejected'], 'reported' => ['#f59e0b', '🚩 Reported']];
                            $s = $statusColors[$r['trang_thai']] ?? ['gray', $r['trang_thai']];
                        ?>
                        <span style="font-size: 0.75rem; font-weight: 800; color: <?= $s[0] ?>;"><?= strtoupper($s[1]) ?></span>
                        
                        <?php if($r['trang_thai'] === 'reported'): ?>
                        <div style="display: flex; gap: 6px;">
                            <form action="<?= SITE_URL ?>/admin/reviews/approve" method="POST" style="margin: 0;">
                                <input type="hidden" name="ma_dg" value="<?= $r['ma_dg'] ?>">
                                <button class="btn btn-primary" style="padding: 4px 8px; font-size: 0.65rem; border-radius: 4px;">Duyệt</button>
                            </form>
                            <form action="<?= SITE_URL ?>/admin/reviews/reject" method="POST" style="margin: 0;">
                                <input type="hidden" name="ma_dg" value="<?= $r['ma_dg'] ?>">
                                <button class="btn btn-secondary" style="padding: 4px 8px; font-size: 0.65rem; border-radius: 4px; color: #ef4444;">Xóa</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if(empty($otherReviews)): ?>
                <p class="text-muted text-center" style="padding: 20px;">Chưa có lịch sử đánh giá.</p>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>
