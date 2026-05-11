<?php
/**
 * View: Báo cáo & Thống kê
 * Merged từ MonkeyGym_Full/admin/bao-cao.php
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { background: #f5f7fa; }
        .stat-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: 100%; }
        .chart-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; }
    </style>
    <style>
        /* Light theme Bootstrap override */
        body { background: var(--bg-primary) !important; color: var(--text-primary) !important; }
        .card { background: var(--bg-card) !important; border: 1px solid var(--border) !important; color: var(--text-primary) !important; box-shadow: var(--shadow-sm) !important; }
        .card-header { background: var(--bg-primary) !important; border-bottom: 1px solid var(--border) !important; font-weight: 600; }
        .table { color: var(--text-primary) !important; font-size: 13px; }
        .table > :not(caption) > * > * { background: transparent !important; border-color: var(--border-light) !important; }
        .table-hover tbody tr:hover > * { background: var(--gold-bg) !important; }
        .thead-light th, .table thead th { background: var(--bg-primary) !important; color: var(--text-muted) !important; font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase; border-bottom: 1px solid var(--border) !important; }
        .form-control, .form-select { border: 1px solid var(--border); background: #fff; color: var(--text-primary); font-size: 13px; border-radius: var(--radius-sm); }
        .form-control:focus, .form-select:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,153,63,0.15); }
        .btn-primary { background: var(--gold) !important; border-color: var(--gold) !important; }
        .btn-primary:hover { background: var(--gold-dark) !important; }
        .btn-success { background: var(--success) !important; border-color: var(--success) !important; }
        .modal-content { background: #fff !important; border: 1px solid var(--border) !important; border-radius: var(--radius-xl) !important; }
        .modal-header { border-bottom: 1px solid var(--border) !important; }
        .modal-footer { border-top: 1px solid var(--border) !important; }
        .badge.bg-success { background: var(--success-bg) !important; color: var(--success) !important; }
        .badge.bg-warning { background: var(--warning-bg) !important; color: var(--warning) !important; }
        .badge.bg-danger  { background: var(--danger-bg)  !important; color: var(--danger)  !important; }
        .badge.bg-info    { background: var(--info-bg)    !important; color: var(--info)     !important; }
        .alert-success { background: var(--success-bg) !important; color: #14532D !important; border-color: #BBF7D0 !important; }
        .alert-danger  { background: var(--danger-bg)  !important; color: #7F1D1D !important; border-color: #FCA5A5 !important; }
        .progress { background: var(--border) !important; }
        .bg-light, .bg-white { background: var(--bg-primary) !important; }
        .text-muted { color: var(--text-muted) !important; }
        .border { border-color: var(--border) !important; }
        select option { background: #fff; color: var(--text-primary); }
        /* light-override */
    </style>
</head>
<body>

<?php include __DIR__ . '/layout/thanh-ben.php'; ?>

<div class="main-content" style="margin-left: 250px; padding: 30px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i> Báo cáo & Thống kê</h2>
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i> In báo cáo
        </button>
    </div>

    <!-- Bộ lọc ngày -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= SITE_URL ?>/" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="action" value="reports">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Từ ngày</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate ?? date('Y-m-01')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Đến ngày</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i> Lọc</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Tổng doanh thu</p>
                        <h3 class="fw-bold mb-0 text-success"><?= number_format($revenueStats['tong_doanh_thu'] ?? 0, 0, ',', '.') ?>đ</h3>
                        <small class="text-muted"><?= number_format((int)($revenueStats['so_giao_dich'] ?? 0)) ?> giao dịch</small>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded"><i class="fas fa-dollar-sign fa-2x"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Tổng hội viên</p>
                        <h3 class="fw-bold mb-0 text-primary"><?= number_format((int)($memberStats['tong_hoi_vien'] ?? 0)) ?></h3>
                        <small class="text-success">+<?= number_format((int)($memberStats['hoi_vien_moi'] ?? 0)) ?> mới</small>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded"><i class="fas fa-users fa-2x"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Lượt điểm danh</p>
                        <h3 class="fw-bold mb-0 text-info"><?= number_format((int)($checkinStats['tong_diem_danh'] ?? 0)) ?></h3>
                        <small class="text-muted"><?= number_format((int)($checkinStats['hoi_vien_diem_danh'] ?? 0)) ?> HV</small>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded"><i class="fas fa-qrcode fa-2x"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">TB / giao dịch</p>
                        <h3 class="fw-bold mb-0 text-warning"><?= number_format($revenueStats['doanh_thu_trung_binh'] ?? 0, 0, ',', '.') ?>đ</h3>
                        <small class="text-muted">Trung bình</small>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded"><i class="fas fa-chart-bar fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ -->
    <div class="row">
        <div class="col-md-8">
            <div class="chart-card">
                <h5 class="mb-3 fw-bold">Doanh thu theo ngày</h5>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-card">
                <h5 class="mb-3 fw-bold">Phương thức thanh toán</h5>
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bảng thống kê gói tập -->
    <div class="chart-card">
        <h5 class="mb-3 fw-bold">Thống kê gói tập</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="bg-light">
                    <tr><th>Tên gói</th><th>Số lượng bán</th><th>Doanh thu</th><th>% Doanh thu</th></tr>
                </thead>
                <tbody>
                    <?php
                    $totalRevenue = array_sum(array_column($packageStats ?? [], 'doanh_thu'));
                    foreach ($packageStats ?? [] as $pkg):
                        $percent = $totalRevenue > 0 ? ($pkg['doanh_thu'] / $totalRevenue * 100) : 0;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($pkg['ten_goi']) ?></strong></td>
                        <td><?= number_format((int)$pkg['so_luong']) ?></td>
                        <td class="text-success fw-bold"><?= number_format((float)$pkg['doanh_thu'], 0, ',', '.') ?>đ</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                    <div class="progress-bar" style="width: <?= number_format($percent, 1) ?>%"></div>
                                </div>
                                <small><?= number_format($percent, 1) ?>%</small>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($packageStats)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Biểu đồ doanh thu theo ngày
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($dailyRevenue ?? [], 'ngay')) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode(array_map(fn($d) => (float)($d['doanh_thu'] ?? 0), $dailyRevenue ?? [])) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.4, fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('vi-VN') } } }
    }
});

// Biểu đồ phương thức thanh toán
const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_map(fn($m) => ucfirst(str_replace('_', ' ', $m['phuong_thuc'] ?? 'unknown')), $revenueByMethod ?? [])) ?>,
        datasets: [{
            data: <?= json_encode(array_map(fn($m) => (float)($m['tong_tien'] ?? 0), $revenueByMethod ?? [])) ?>,
            backgroundColor: ['rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>
</body>
</html>
