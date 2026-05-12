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
            background: white;
            border: 1px solid var(--border-light);
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
        .grid {
            display: grid;
        }
    </style>

</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 30px; background: white; border-bottom: 1px solid var(--border-light);">
                <div>
                    <h1 style="margin: 0; font-size: 1.5rem; font-weight: 800;">Trung Tâm Phân Tích (Analytics Hub)</h1>
                    <p class="text-muted" style="margin: 5px 0 0 0;">Báo cáo doanh thu, hội viên và hoạt động phòng Gym.</p>
                </div>
                <div style="display:flex; gap:12px; align-items:center;">
                    <form method="GET" action="<?= SITE_URL ?>/admin/dashboard" class="filter-form" style="margin:0; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Lọc Dữ Liệu: </span>
                        <select name="filter" onchange="this.form.submit()" style="padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-light); font-size: 0.85rem; font-weight: 600;">
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

            <div style="padding: 30px;">
                <!-- KHỐI DỮ LIỆU TỔNG -->
                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
                    <div class="stat-card" style="border-bottom: 4px solid var(--gold);">
                        <h3>Doanh Thu Thuần</h3>
                        <div class="stat-value"><?= number_format($totalRevenue) ?> <span style="font-size: 1rem; font-weight: 700;">đ</span></div>
                        <div class="stat-trend" style="font-size: 11px; font-weight: 700; color: <?= $revGrowth >= 0 ? '#10b981' : '#ef4444' ?>;">
                            <?= $revGrowth >= 0 ? '↗' : '↘' ?> <?= abs($revGrowth) ?>% so với tháng trước
                        </div>
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-card" style="border-bottom: 4px solid #3b82f6;">
                        <h3>Hội Viên Mới</h3>
                        <div class="stat-value"><?= number_format($newMembers) ?> <span style="font-size: 1rem; font-weight: 700;">người</span></div>
                        <div class="stat-label" style="font-size: 11px; color: var(--text-muted);">Tổng cộng: <?= number_format($totalMembers) ?></div>
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-card" style="border-bottom: 4px solid #f59e0b;">
                        <h3>Chi Phí Lương</h3>
                        <div class="stat-value"><?= number_format($totalPayroll) ?> <span style="font-size: 1rem; font-weight: 700;">đ</span></div>
                        <div class="stat-label" style="font-size: 11px; color: var(--text-muted);">Tháng <?= date('m/Y') ?></div>
                        <div class="stat-icon">💸</div>
                    </div>
                    <div class="stat-card" style="border-bottom: 4px solid #ef4444;">
                        <h3>Giờ Cao Điểm</h3>
                        <div class="stat-value" style="font-size: 1.8rem;"><?= $peakHour ?></div>
                        <div class="stat-label" style="font-size: 11px; color: var(--text-muted);">Lượt khách vào</div>
                        <div class="stat-icon">⚡</div>
                    </div>
                </div>

                <!-- BIỂU ĐỒ DOANH THU & NGUỒN -->
                <div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 24px; margin-top: 24px;">
                    <div class="chart-box">
                        <h3>📈 Biểu đồ tăng trưởng Doanh thu</h3>
                        <div style="height: 300px;"><canvas id="revChart"></canvas></div>
                    </div>
                    <div class="chart-box">
                        <h3>🥧 Cơ cấu Doanh thu</h3>
                        <div style="height: 300px;"><canvas id="sourceChart"></canvas></div>
                    </div>
                </div>

                <!-- BIỂU ĐỒ HỘI VIÊN & GÓI TẬP -->
                <div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 24px; margin-top: 24px;">
                    <div class="chart-box">
                        <h3>📈 Xu hướng Hội viên mới</h3>
                        <div style="height: 300px;"><canvas id="memChart"></canvas></div>
                    </div>
                    <div class="chart-box">
                        <h3>🍕 Cơ cấu doanh thu gói tập</h3>
                        <div style="height: 300px;"><canvas id="pkgRevChart"></canvas></div>
                    </div>
                </div>

                <!-- HIỆU SUẤT GÓI TẬP -->
                <div class="grid" style="grid-template-columns: 1fr; gap: 24px; margin-top: 24px;">
                    <div class="chart-box">
                        <h3>📊 Hiệu suất đăng ký gói tập</h3>
                        <div style="height: 350px;"><canvas id="pkgCountChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MÃ NHÚNG CHART.JS -->
    <script>
        // Cấu hình chung Chart.js
        Chart.defaults.color = '#4B5563';
        Chart.defaults.borderColor = 'rgba(0,0,0,0.08)';
        Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', system-ui, sans-serif";

        // Helper null-safe
        function safeChart(id, config) {
            const el = document.getElementById(id);
            if (el) return new Chart(el, config);
            return null;
        }

        // Dữ liệu từ PHP
        const revLabels = <?= json_encode($chartRevLabels) ?>;
        const revVals   = <?= json_encode($chartRevVals) ?>;
        const memLabels = <?= json_encode($chartMemLabels) ?>;
        const memVals   = <?= json_encode($chartMemVals) ?>;
        const pkgLabels = <?= json_encode($chartPkgLabels) ?>;
        const pkgRev    = <?= json_encode($chartPkgRev) ?>;
        const pkgCount  = <?= json_encode($chartPkgCount) ?>;
        const bgColors  = ['#f59e0b','#3b82f6','#10b981','#ef4444','#8b5cf6','#ec4899','#14b8a6'];

        // 1. Biểu đồ Doanh thu (Bar)
        safeChart('revChart', {
            type: 'bar',
            data: {
                labels: revLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revVals,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value/1000000) + 'M';
                                if (value >= 1000) return (value/1000) + 'k';
                                return value;
                            }
                        }
                    }
                }
            }
        });

        // 2. Cơ cấu doanh thu theo nguồn (Doughnut)
        safeChart('sourceChart', {
            type: 'doughnut',
            data: {
                labels: ['Gói Tập', 'Sản Phẩm'],
                datasets: [{
                    data: [<?= $membershipRev ?>, <?= $productRev ?>],
                    backgroundColor: ['#c9993f', '#3b82f6'],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: { 
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                }
            }
        });

        // 3. Xu hướng hội viên mới (Line)
        safeChart('memChart', {
            type: 'line',
            data: {
                labels: memLabels,
                datasets: [{
                    label: 'Hội viên mới',
                    data: memVals,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

        // 4. Cơ cấu doanh thu gói tập (Doughnut)
        safeChart('pkgRevChart', {
            type: 'doughnut',
            data: {
                labels: pkgLabels,
                datasets: [{ 
                    data: pkgRev, 
                    backgroundColor: bgColors, 
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                cutout: '70%',
                plugins: { 
                    legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, boxWidth: 8 } }
                }
            }
        });

        // 5. Hiệu suất gói tập (Bar nằm ngang)
        safeChart('pkgCountChart', {
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
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { 
                    x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } 
                }
            }
        });
    </script>
</body>
</html>
