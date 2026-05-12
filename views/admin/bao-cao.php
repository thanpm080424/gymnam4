<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Chi Tiết | Monkey Gym Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-container { padding: 30px; }
        .report-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .report-header h1 { font-size: 2rem; font-weight: 800; margin: 0; color: var(--text-primary); }
        
        .filter-panel {
            background: white;
            padding: 20px 30px;
            border-radius: 16px;
            border: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }
        
        .filter-group { display: flex; align-items: center; gap: 10px; }
        .filter-group label { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
        .filter-group input { 
            padding: 8px 12px; 
            border-radius: 8px; 
            border: 1px solid var(--border-light); 
            background: #f8fafc;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 120px;
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0; font-weight: 800; }
        .stat-card .value { font-size: 1.8rem; font-weight: 900; color: var(--text-primary); }
        .stat-card .trend { font-size: 0.8rem; margin-top: 5px; font-weight: 600; }
        .trend-up { color: #10b981; }
        .trend-down { color: #ef4444; }

        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-panel {
            background: white;
            padding: 25px;
            border-radius: 20px;
            border: 1px solid var(--border-light);
        }
        .chart-panel h4 { font-size: 1.1rem; font-weight: 800; margin: 0 0 20px 0; color: var(--text-primary); }

        .data-table-panel {
            background: white;
            padding: 0;
            border-radius: 20px;
            border: 1px solid var(--border-light);
            overflow: hidden;
        }
        .data-table-header { padding: 20px 25px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; }
        .data-table-header h4 { font-size: 1.1rem; font-weight: 800; margin: 0; }

        table { width: 100%; border-collapse: collapse; }
        th { padding: 15px 25px; text-align: left; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid var(--border-light); }
        td { padding: 15px 25px; border-bottom: 1px solid var(--border-light); font-size: 0.9rem; }
        tr:last-child td { border-bottom: none; }
        
        .progress-bar { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; flex: 1; }
        .progress-fill { height: 100%; background: var(--gold); border-radius: 4px; }

        @media (max-width: 1200px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .chart-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <div class="report-container">
                <div class="report-header">
                    <h1>📊 Báo Cáo & Thống Kê</h1>
                    <button class="btn" style="background: white; border: 1px solid var(--border-light); font-weight: 700; display: flex; align-items: center; gap: 8px;" onclick="window.print()">
                        <span>🖨️</span> In báo cáo
                    </button>
                </div>

                <form method="GET" action="<?= SITE_URL ?>/admin/reports" class="filter-panel">
                    <div class="filter-group">
                        <label>Từ ngày</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate ?? date('Y-m-01')) ?>">
                    </div>
                    <div class="filter-group">
                        <label>Đến ngày</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate ?? date('Y-m-d')) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 8px 25px;">🔍 Lọc Dữ Liệu</button>
                </form>

                <div class="stat-grid">
                    <div class="stat-card">
                        <h3>Tổng Doanh Thu</h3>
                        <div class="value" style="color: var(--gold-dark);"><?= number_format($revenueStats['tong_doanh_thu'] ?? 0) ?>đ</div>
                        <div class="trend trend-up">▲ <?= number_format($revenueStats['so_giao_dich'] ?? 0) ?> giao dịch</div>
                    </div>
                    <div class="stat-card">
                        <h3>Tổng Hội Viên</h3>
                        <div class="value"><?= number_format($memberStats['tong_hoi_vien'] ?? 0) ?></div>
                        <div class="trend trend-up">▲ <?= number_format($memberStats['hoi_vien_moi'] ?? 0) ?> mới</div>
                    </div>
                    <div class="stat-card">
                        <h3>Lượt Điểm Danh</h3>
                        <div class="value"><?= number_format($checkinStats['tong_diem_danh'] ?? 0) ?></div>
                        <div class="trend" style="color: #3b82f6;">Hôm nay: <?= number_format($checkinStats['diem_danh_hom_nay'] ?? 0) ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Giá Trị Đơn TB</h3>
                        <div class="value"><?= number_format($revenueStats['doanh_thu_trung_binh'] ?? 0) ?>đ</div>
                        <div class="trend" style="color: var(--text-muted);">Hiệu suất bán hàng</div>
                    </div>
                </div>

                <div class="chart-grid">
                    <div class="chart-panel">
                        <h4>📈 Xu hướng doanh thu</h4>
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                    <div class="chart-panel">
                        <h4>💳 Phương thức thanh toán</h4>
                        <canvas id="paymentChart" height="300"></canvas>
                    </div>
                </div>

                <div class="data-table-panel">
                    <div class="data-table-header">
                        <h4>💎 Hiệu suất Gói tập</h4>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Tên gói</th>
                                <th>Số lượng bán</th>
                                <th>Doanh thu</th>
                                <th>Tỷ trọng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalRevenue = array_sum(array_column($packageStats ?? [], 'doanh_thu'));
                            foreach ($packageStats ?? [] as $pkg): 
                                $percent = $totalRevenue > 0 ? ($pkg['doanh_thu'] / $totalRevenue * 100) : 0;
                            ?>
                            <tr>
                                <td style="font-weight: 700;"><?= htmlspecialchars($pkg['ten_goi']) ?></td>
                                <td><?= number_format($pkg['so_luong']) ?> lượt</td>
                                <td style="font-weight: 700; color: #10b981;"><?= number_format($pkg['doanh_thu']) ?>đ</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= $percent ?>%"></div>
                                        </div>
                                        <span style="font-size: 0.8rem; font-weight: 700; min-width: 40px;"><?= number_format($percent, 1) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart Config
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600' } } }
            }
        };

        // Revenue Chart
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($dailyRevenue ?? [], 'ngay')) ?>,
                datasets: [{
                    label: 'Doanh thu',
                    data: <?= json_encode(array_column($dailyRevenue ?? [], 'doanh_thu')) ?>,
                    borderColor: '#C9993F',
                    backgroundColor: 'rgba(201, 153, 63, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#C9993F',
                    pointBorderWidth: 2
                }]
            },
            options: {
                ...commonOptions,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(0,0,0,0.03)' },
                        ticks: { callback: v => v.toLocaleString() + 'đ' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Payment Chart
        new Chart(document.getElementById('paymentChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_map(fn($m) => strtoupper(str_replace('_', ' ', $m['phuong_thuc'])), $revenueByMethod ?? [])) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($revenueByMethod ?? [], 'tong_tien')) ?>,
                    backgroundColor: ['#C9993F', '#F59E0B', '#3B82F6', '#10B981'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20 } }
                }
            }
        });
    </script>
</body>
</html>
