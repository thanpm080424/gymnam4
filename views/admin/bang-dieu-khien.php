<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <!-- Nạp Chart.js qua CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 140px;
            border-radius: 16px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        .stat-card h3 { 
            margin: 0; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            color: var(--text-muted);
            font-weight: 800;
        }
        .stat-card .stat-value { 
            font-size: 2.2rem; 
            font-weight: 900; 
            line-height: 1.1; 
            color: var(--text-primary);
            margin-top: 10px;
        }
        .stat-card .stat-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 4rem;
            opacity: 0.05;
            transform: rotate(-15deg);
        }
        
        .chart-box {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }
        .chart-box h3 {
            margin: 0 0 20px 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>

</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header" style="justify-content: space-between;">
                <div>
                    <h1>Trung Tâm Phân Tích (Analytics Hub)</h1>
                    <p class="text-muted">Báo cáo doanh thu, hội viên và hoạt động phòng Gym.</p>
                </div>
                <div style="display:flex; gap:12px; align-items:center;">
                    <form method="GET" action="<?= SITE_URL ?>/admin/dashboard" class="filter-form" style="margin:0;">
                        <span>Lọc Dữ Liệu: </span>
                        <select name="filter" onchange="this.form.submit()">
                            <option value="today" <?= $filter==='today'?'selected':'' ?>>Hôm Nay</option>
                            <option value="month" <?= $filter==='month'?'selected':'' ?>>Tháng Này</option>
                            <option value="year" <?= $filter==='year'?'selected':'' ?>>Năm Này</option>
                            <option value="all" <?= $filter==='all'?'selected':'' ?>>Toàn Bộ Thời Gian</option>
                        </select>
                    </form>
                    <form method="POST" action="<?= SITE_URL ?>/admin/send-reminders" style="margin:0;"
                          onsubmit="return confirm('Gửi email nhắc nhở cho hội viên vắng 15 ngày và gói sắp hết hạn?');">
                        <?= csrfField('send_reminders') ?>
                        <button type="submit" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;padding:8px 16px;border-radius:8px;font-weight:700;font-size:12px;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;">
                            📧 Gửi Nhắc Nhở
                        </button>
                    </form>
                </div>
            </header>

            <!-- KHỐI DỮ LIỆU TỔNG -->
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-top: 24px;">
                <div class="glass-panel stat-card" style="border-bottom: 4px solid var(--gold);">
                    <h3>Doanh Thu Thuần</h3>
                    <div class="stat-value"><?= number_format($totalRevenue) ?> <span style="font-size: 1rem; font-weight: 700;">đ</span></div>
                    <div class="stat-icon">💰</div>
                </div>
                <div class="glass-panel stat-card" style="border-bottom: 4px solid #3b82f6;">
                    <h3>Hội Viên Mới</h3>
                    <div class="stat-value"><?= number_format($newMembers) ?> <span style="font-size: 1rem; font-weight: 700;">người</span></div>
                    <div class="stat-icon">👥</div>
                </div>
                <div class="glass-panel stat-card" style="border-bottom: 4px solid #10b981;">
                    <h3>Check-in Hôm Nay</h3>
                    <div class="stat-value"><?= number_format($checkInsToday) ?> <span style="font-size: 1rem; font-weight: 700;">lượt</span></div>
                    <div class="stat-icon">🎟️</div>
                </div>
                <div class="glass-panel stat-card" style="border-bottom: 4px solid #f59e0b;">
                    <h3>Giờ Cao Điểm</h3>
                    <div class="stat-value" style="font-size: 1.8rem;"><?= $peakHour ?></div>
                    <div class="stat-icon">🕒</div>
                </div>
            </div>

            <!-- BIỂU ĐỒ -->
            <div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 24px; margin-top: 24px;">
                <div class="chart-box">
                    <h3>📈 Biểu đồ tăng trưởng</h3>
                    <div style="height: 300px;"><canvas id="revChart"></canvas></div>
                </div>
                <div class="chart-box">
                    <h3>👥 Phân bổ hội viên mới</h3>
                    <div style="height: 300px;"><canvas id="memChart"></canvas></div>
                </div>
            </div>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px;">
                <div class="chart-box">
                    <h3>🍕 Cơ cấu doanh thu gói tập</h3>
                    <div style="height: 300px;"><canvas id="pkgRevChart"></canvas></div>
                </div>
                <div class="chart-box">
                    <h3>📊 Hiệu suất gói tập</h3>
                    <div style="height: 300px;"><canvas id="pkgCountChart"></canvas></div>
                </div>
            </div>


        </div>
    </div>

    <!-- MÃ NHÚNG CHART.JS -->
    <script>
        // Cấu hình chung cho Chart.js – Light Theme
        Chart.defaults.color = '#4B5563';
        Chart.defaults.borderColor = 'rgba(0, 0, 0, 0.08)';

        // Dữ liệu từ PHP (Doanh thu)
        const revLabels = <?= json_encode($chartRevLabels) ?>;
        const revVals = <?= json_encode($chartRevVals) ?>;
        
        new Chart(document.getElementById('revChart'), {
            type: 'bar',
            data: {
                labels: revLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revVals,
                    backgroundColor: 'rgba(52, 211, 153, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Dữ liệu từ PHP (Hội viên mới)
        const memLabels = <?= json_encode($chartMemLabels) ?>;
        const memVals = <?= json_encode($chartMemVals) ?>;

        new Chart(document.getElementById('memChart'), {
            type: 'line',
            data: {
                labels: memLabels,
                datasets: [{
                    label: 'Hội viên mới',
                    data: memVals,
                    backgroundColor: 'rgba(96, 165, 250, 0.2)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    tension: 0.3, // Đường cong mượt
                    fill: true
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: {
                    y: { ticks: { stepSize: 1, precision: 0 } }
                }
            }
        });

        // Dữ liệu Gói tập
        const pkgLabels = <?= json_encode($chartPkgLabels) ?>;
        const pkgRev = <?= json_encode($chartPkgRev) ?>;
        const pkgCount = <?= json_encode($chartPkgCount) ?>;

        // Bảng màu cho Gói tập
        const bgColors = [
            '#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'
        ];

        new Chart(document.getElementById('pkgRevChart'), {
            type: 'doughnut',
            data: {
                labels: pkgLabels,
                datasets: [{
                    data: pkgRev,
                    backgroundColor: bgColors,
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
        });

        new Chart(document.getElementById('pkgCountChart'), {
            type: 'bar',
            data: {
                labels: pkgLabels,
                datasets: [{
                    label: 'Số Lượt ĐK',
                    data: pkgCount,
                    backgroundColor: 'rgba(245, 158, 11, 0.7)',
                    borderColor: '#f59e0b',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                indexAxis: 'y', // Chuyển thành biểu đồ cột nằm ngang
                scales: {
                    x: { ticks: { stepSize: 1, precision: 0 } }
                }
            }
        });
    </script>
</body>
</html>
