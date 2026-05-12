<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Học Viên | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .student-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            transition: transform 0.3s ease;
        }
        .student-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent);
        }
        .student-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .student-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--text-primary);
        }
        .student-info {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }
        .bmi-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-top: 5px;
        }
        .bmi-normal { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .bmi-warning { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .bmi-danger { background: rgba(244, 67, 54, 0.2); color: #f44336; }
        
        .stats-row {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        .stat-item {
            flex: 1;
            text-align: center;
        }
        .stat-value {
            display: block;
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--accent);
        }
        .stat-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
    <main class="admin-content">
        <header style="margin-bottom: 2rem;">
            <h1 style="font-size: 2.2rem; font-weight: 900;">👥 Danh Sách <span style="color: var(--primary);">Học Viên</span></h1>
            <p style="color: var(--text-muted);">Quản lý và theo dõi tiến độ của các học viên bạn đang kèm cặp.</p>
        </header>

        <?php if (empty($students)): ?>
            <div class="glass-panel text-center py-5">
                <div style="font-size: 3rem; margin-bottom: 20px;">👤</div>
                <h3>Chưa có học viên nào</h3>
                <p class="text-muted">Khi có hội viên đặt lịch và tập luyện với bạn, họ sẽ xuất hiện tại đây.</p>
            </div>
        <?php else: ?>
            <div class="student-grid">
                <?php foreach ($students as $s): 
                    $bmi = 0;
                    if ($s['chieu_cao'] > 0) {
                        $bmi = $s['can_nang'] / (($s['chieu_cao']/100) * ($s['chieu_cao']/100));
                    }
                    $bmiClass = 'bmi-normal';
                    $bmiText = 'Bình thường';
                    if ($bmi < 18.5) { $bmiClass = 'bmi-warning'; $bmiText = 'Gầy'; }
                    elseif ($bmi >= 25 && $bmi < 30) { $bmiClass = 'bmi-warning'; $bmiText = 'Tiền béo phì'; }
                    elseif ($bmi >= 30) { $bmiClass = 'bmi-danger'; $bmiText = 'Béo phì'; }
                ?>
                    <div class="student-card">
                        <div class="student-header">
                            <div>
                                <div class="student-name"><?= htmlspecialchars($s['ho_ten'] ?? '') ?></div>
                                <div class="student-info">📧 <?= htmlspecialchars($s['email'] ?? '') ?></div>
                                <div class="student-info">📞 <?= htmlspecialchars($s['so_dien_thoai'] ?? 'Chưa có SĐT') ?></div>
                            </div>
                        </div>
                        
                        <div class="student-body">
                            <div class="student-info">
                                <strong>Chỉ số:</strong> <?= $s['chieu_cao'] ?? '--' ?>cm | <?= $s['can_nang'] ?? '--' ?>kg
                                <?php if ($bmi > 0): ?>
                                    <span class="bmi-tag <?= $bmiClass ?>">BMI: <?= number_format($bmi, 1) ?> (<?= $bmiText ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="stats-row">
                            <div class="stat-item">
                                <span class="stat-value"><?= $s['so_buoi_da_tap'] ?></span>
                                <span class="stat-label">Đã tập</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value" style="color: var(--primary);"><?= $s['so_buoi_pt_con_lai'] ?></span>
                                <span class="stat-label">Còn lại</span>
                            </div>
                        </div>

                        <div style="margin-top: 15px; text-align: right;">
                            <a href="<?= SITE_URL ?>/trainer/dashboard" class="btn btn-primary" style="padding: 5px 15px; font-size: 0.8rem;">Xem lịch tập</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div><!-- .admin-layout -->
</body>
</html>
