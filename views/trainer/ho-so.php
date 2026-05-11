<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Cá Nhân | HLV</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
<?php require __DIR__ . '/layout/topbar.php'; ?>
<div class="member-content">


    <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>
<div class="grid" style="grid-template-columns: 1fr 1.5fr; gap:2rem;">
        <!-- Preview Card -->
        <div>
            <div class="card glass-panel" style="text-align:center;">
                <div style="width:120px; height:120px; border-radius:50%; margin:0 auto 1.5rem; overflow:hidden; border:3px solid rgba(139,92,246,0.4);">
                    <?php if(!empty($trainer['anh_dai_dien'])): ?>
                        <img src="<?= ASSET_URL . htmlspecialchars($trainer['anh_dai_dien']) ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="width:100%;height:100%;background:rgba(139,92,246,0.2);display:flex;align-items:center;justify-content:center;font-size:3rem;">👤</div>
                    <?php endif; ?>
                </div>
                <h2 style="color:white; margin-bottom:0.5rem;"><?= htmlspecialchars($trainer['ho_ten'] ?? $trainer['ten_dang_nhap']) ?></h2>
                <p class="text-muted" style="font-size:0.85rem; margin-bottom:0.8rem;"><?= htmlspecialchars($trainer['ten_dang_nhap']) ?></p>
                <span style="background:rgba(139,92,246,0.15); color:#a78bfa; padding:0.3rem 1rem; border-radius:50px; font-size:0.9rem;"><?= htmlspecialchars($trainer['chuyen_mon'] ?? 'Tập luyện') ?></span>
                <?php if(!empty($trainer['nam_kinh_nghiem'])): ?>
                <p class="text-muted" style="margin-top:1rem; font-size:0.9rem;">🏆 <?= $trainer['nam_kinh_nghiem'] ?> năm kinh nghiệm</p>
                <?php endif; ?>
                <div style="margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:1.5rem; color:#f59e0b; margin-bottom:0.3rem;"><?= str_repeat('★', round($avgRating ?? 5)) ?></div>
                    <div style="font-size:1.2rem; font-weight:700; color:white;"><?= number_format($avgRating ?? 5, 1) ?> / 5.0</div>
                    <div class="text-muted" style="font-size:0.85rem;"><?= $reviewCount ?> đánh giá</div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card glass-panel">
            <h3 style="margin-bottom:1.5rem;">✏️ Chỉnh Sửa Thông Tin</h3>
            <form action="<?= SITE_URL ?>/trainer/profile/update" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Họ tên</label>
                    <input type="text" name="ho_ten" class="form-control" value="<?= htmlspecialchars($trainer['ho_ten'] ?? '') ?>" placeholder="VD: Nguyễn Văn A">
                </div>
                <div class="form-group">
                    <label>Chuyên môn</label>
                    <input type="text" name="chuyen_mon" class="form-control" value="<?= htmlspecialchars($trainer['chuyen_mon'] ?? '') ?>" placeholder="VD: Gym, Yoga, Boxing...">
                </div>
                <div class="form-group">
                    <label>Số năm kinh nghiệm</label>
                    <input type="number" name="nam_kinh_nghiem" class="form-control" value="<?= htmlspecialchars($trainer['nam_kinh_nghiem'] ?? 0) ?>" min="0" max="50">
                </div>
                <div class="form-group">
                    <label>Giới thiệu bản thân</label>
                    <textarea name="gioi_thieu" class="form-control" rows="5" placeholder="Giới thiệu về chuyên môn, kinh nghiệm, phong cách huấn luyện..." style="resize:vertical;"><?= htmlspecialchars($trainer['gioi_thieu'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Ảnh đại diện</label>
                    <input type="file" name="anh_dai_dien" class="form-control" accept="image/*">
                    <?php if(!empty($trainer['anh_dai_dien'])): ?>
                    <p class="text-muted" style="font-size:0.8rem; margin-top:0.5rem;">Ảnh hiện tại: <a href="<?= ASSET_URL . htmlspecialchars($trainer['anh_dai_dien']) ?>" target="_blank">Xem</a></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Lưu thay đổi</button>
            </form>
        </div>
    </div>

    <!-- Reviews section -->
    <?php if(!empty($reviews)): ?>
    <div class="card glass-panel" style="margin-top:2rem;">
        <h3 style="margin-bottom:1.5rem;">💬 Các Đánh Giá Từ Học Viên</h3>
        <?php foreach($reviews as $r): ?>
        <div style="border-bottom:1px solid rgba(255,255,255,0.07); padding:1rem 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                <strong style="color:white;"><?= htmlspecialchars($r['ten_hoi_vien']) ?></strong>
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span style="color:#f59e0b;"><?= str_repeat('★', $r['so_sao']) ?></span>
                    <span class="text-muted" style="font-size:0.8rem;"><?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
                    <?php if($r['trang_thai'] !== 'reported'): ?>
                    <form action="<?= SITE_URL ?>/trainer/reviews/report" method="POST" style="display:inline;">
                        <input type="hidden" name="ma_dg" value="<?= $r['ma_dg'] ?>">
                        <button class="btn btn-secondary" style="font-size:0.75rem; padding:0.2rem 0.5rem; color:#f59e0b;" title="Báo cáo bài này không hợp lệ">🚩</button>
                    </form>
                    <?php else: ?>
                    <span class="text-muted" style="font-size:0.75rem;">🚩 Đã báo cáo</span>
                    <?php endif; ?>
                </div>
            </div>
            <p style="color:var(--text-secondary); font-size:0.9rem;">"<?= htmlspecialchars($r['noi_dung'] ?? '') ?>"</p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</div>
</body>
</html>
