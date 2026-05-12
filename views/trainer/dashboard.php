<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>HLV Dashboard | Monkey Gym</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { background: var(--bg-card); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-light); }
        .stat-label { font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .stat-value { font-size: 1.8rem; font-weight: 900; color: var(--text-main); }
        
        .schedule-list { display: flex; flex-direction: column; gap: 1rem; }
        .schedule-item { background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-light); display: flex; align-items: center; padding: 1rem; transition: 0.3s; }
        .schedule-item:hover { border-color: var(--primary); }
        
        .class-time { width: 100px; text-align: center; border-right: 1px solid var(--border-light); padding-right: 1rem; margin-right: 1.5rem; }
        .time-start { font-size: 1.2rem; font-weight: 900; color: var(--primary); }
        .time-date { font-size: 0.8rem; color: var(--text-muted); }
        
        .class-info { flex: 1; }
        .class-name { font-size: 1.1rem; font-weight: 800; margin-bottom: 4px; }
        .class-type { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>

        <main class="admin-content">
            <header style="margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; font-weight: 900;">Chào HLV, <span style="color: var(--primary);"><?= htmlspecialchars($_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap']) ?></span>! 👋</h1>
                <p style="color: var(--text-muted);">Hôm nay là <?= date('d/m/Y') ?>. Chúc bạn một ngày dạy học năng lượng!</p>
            </header>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Lớp dạy tháng này</div>
                    <div class="stat-value"><?= $so_buoi ?> <span style="font-size: 1rem; color: var(--text-muted);">buổi</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Thu nhập dự kiến</div>
                    <div class="stat-value" style="color: var(--primary);"><?= number_format($du_kien) ?>đ</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Đánh giá trung bình</div>
                    <div class="stat-value"><?= $avgStar > 0 ? $avgStar : 'N/A' ?> <?= $avgStar > 0 ? '⭐' : '' ?></div>
                </div>
            </div>

            <!-- LỊCH ĐẶT PT CÁ NHÂN TỪ HỘI VIÊN -->
            <section style="margin-bottom: 2.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="text-transform: uppercase; font-size: 1rem; letter-spacing: 2px; margin: 0;">🏋️ Buổi tập PT sắp tới</h2>
                    <a href="<?= SITE_URL ?>/trainer/schedule" style="font-size: 0.8rem; color: var(--primary); text-decoration: none; font-weight: 700;">📅 Xem toàn bộ TKB &rarr;</a>
                </div>
                <div class="schedule-list">
                    <?php if(!empty($ptBookings)): ?>
                        <?php foreach($ptBookings as $pt): ?>
                        <div class="schedule-item">
                            <div class="class-time">
                                <div class="time-start"><?= date('H:i', strtotime($pt['gio_tap'])) ?></div>
                                <div class="time-date"><?= date('d/m', strtotime($pt['ngay_tap'])) ?></div>
                            </div>
                            <div class="class-info">
                                <div class="class-name">PT - <?= htmlspecialchars($pt['ho_ten'] ?? $pt['ten_dang_nhap']) ?></div>
                                <div class="class-type"><?= htmlspecialchars(ucfirst($pt['loai_pt'] ?? 'Gym')) ?> · 1-on-1</div>
                            </div>
                            <div style="font-size: 0.75rem; font-weight: 700; padding: 4px 12px; border-radius: 100px; <?= $pt['trang_thai'] === 'confirmed' ? 'background: #ECFDF5; color: #059669;' : 'background: #FFFBEB; color: #B45309;' ?>">
                                <?= $pt['trang_thai'] === 'confirmed' ? 'Đã xác nhận' : 'Chờ duyệt' ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 2rem; text-align: center; background: var(--bg-card); border-radius: 16px; border: 1px dashed var(--border-light); color: var(--text-muted);">
                            Chưa có hội viên nào đặt lịch PT.
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- LỊCH DẠY LỚP NHÓM -->
            <section>
                <h2 style="margin-bottom: 1.5rem; text-transform: uppercase; font-size: 1rem; letter-spacing: 2px;">📅 Lịch dạy lớp nhóm</h2>
                <div class="schedule-list">
                    <?php if(!empty($schedules)): ?>
                        <?php foreach($schedules as $s): ?>
                        <div class="schedule-item">
                            <div class="class-time">
                                <div class="time-start"><?= date('H:i', strtotime($s['gio_bat_dau'])) ?></div>
                                <div class="time-date"><?= date('d/m', strtotime($s['ngay_hoc'])) ?></div>
                            </div>
                            <div class="class-info">
                                <div class="class-name"><?= htmlspecialchars($s['ten_lop']) ?></div>
                                <div class="class-type"><?= htmlspecialchars($s['loai_lop']) ?></div>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                📍 Studio A
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 2rem; text-align: center; background: var(--bg-card); border-radius: 16px; border: 1px dashed var(--border-light); color: var(--text-muted);">
                            Hiện tại chưa có lịch dạy lớp nhóm.
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
