<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Học Viên | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f7f4; }
        
        .page-header {
            margin-bottom: 2.5rem;
            border-bottom: 1px solid var(--border-light);
            padding-bottom: 20px;
        }
        .page-title {
            font-size: 2.4rem;
            font-weight: 900;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -1px;
        }
        .page-title span { color: var(--gold); }
        .page-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            margin-top: 5px;
        }

        .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 24px;
        }

        .student-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .student-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(201, 153, 63, 0.12);
            border-color: var(--gold-border);
        }
        .student-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
            background: var(--gold); opacity: 0; transition: 0.3s;
        }
        .student-card:hover::before { opacity: 1; }

        .student-header { margin-bottom: 20px; }
        .student-name {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .contact-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }
        .contact-icon { width: 16px; opacity: 0.6; }

        .body-metrics {
            background: var(--gold-bg);
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .metric-item { font-size: 0.85rem; font-weight: 600; color: var(--gold-dark); }
        
        .bmi-badge {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .bmi-normal  { background: #dcfce7; color: #166534; }
        .bmi-warning { background: #fef3c7; color: #92400e; }
        .bmi-danger  { background: #fee2e2; color: #991b1b; }

        .stats-divider { height: 1px; background: var(--border-light); margin: 20px 0; }
        
        .stats-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-box {
            text-align: center;
            padding: 12px;
            background: #fafafa;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
        }
        .stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--text-primary);
            line-height: 1;
        }
        .stat-label {
            font-size: 0.65rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            margin-top: 6px;
            letter-spacing: 0.5px;
        }

        .btn-view-schedule {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--gold);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(201, 153, 63, 0.2);
        }
        .btn-view-schedule:hover {
            background: var(--gold-dark);
            transform: scale(1.02);
            box-shadow: 0 6px 18px rgba(201, 153, 63, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 80px 40px;
            background: white;
            border-radius: 24px;
            border: 2px dashed var(--border-light);
        }
        .empty-icon { font-size: 4rem; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
    
    <main class="admin-content">
        <header class="page-header">
            <h1 class="page-title">👥 Danh Sách <span>Học Viên</span></h1>
            <p class="page-subtitle">Quản lý và theo dõi tiến độ tập luyện của các hội viên bạn đang hướng dẫn.</p>
        </header>

        <?php if (empty($students)): ?>
            <div class="empty-state">
                <div class="empty-icon">🧘‍♂️</div>
                <h2 style="font-weight: 800; color: var(--text-primary);">Chưa có học viên nào</h2>
                <p style="color: var(--text-muted);">Khi có hội viên đặt lịch và tập luyện với bạn, thông tin của họ sẽ tự động xuất hiện tại đây.</p>
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
                            <div class="student-name"><?= htmlspecialchars($s['ho_ten'] ?? 'Ẩn danh') ?></div>
                            <div class="contact-info">
                                <span class="contact-icon">📧</span>
                                <?= htmlspecialchars($s['email'] ?? 'Chưa có email') ?>
                            </div>
                            <div class="contact-info">
                                <span class="contact-icon">📞</span>
                                <?= htmlspecialchars($s['so_dien_thoai'] ?? 'Chưa có SĐT') ?>
                            </div>
                        </div>
                        
                        <div class="body-metrics">
                            <div class="metric-item">
                                📏 <?= $s['chieu_cao'] ?? '--' ?>cm &nbsp;·&nbsp; ⚖️ <?= $s['can_nang'] ?? '--' ?>kg
                            </div>
                            <?php if ($bmi > 0): ?>
                                <span class="bmi-badge <?= $bmiClass ?>">
                                    BMI: <?= number_format($bmi, 1) ?> (<?= $bmiText ?>)
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="stats-container">
                            <div class="stat-box">
                                <span class="stat-value"><?= number_format($s['so_buoi_da_tap']) ?></span>
                                <span class="stat-label">Buổi đã tập</span>
                            </div>
                            <div class="stat-box" style="border-color: var(--gold-border); background: var(--gold-bg);">
                                <span class="stat-value" style="color: var(--gold);"><?= number_format($s['so_buoi_pt_con_lai']) ?></span>
                                <span class="stat-label" style="color: var(--gold-dark);">Buổi còn lại</span>
                            </div>
                        </div>

                        <a href="<?= SITE_URL ?>/trainer/dashboard" class="btn-view-schedule">
                            📅 Xem Lịch Tập Chi Tiết
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
