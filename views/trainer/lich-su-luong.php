<?php
/**
 * View: Lịch Sử Thu Nhập - Huấn Luyện Viên
 * Route: /trainer/my-payroll
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Thu Nhập | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        /* Stat cards */
        .income-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        .income-stat {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 1.4rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .income-stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--text-muted);
        }
        .income-stat-value {
            font-size: 1.85rem;
            font-weight: 900;
            color: var(--text-primary);
            line-height: 1;
        }
        .income-stat-value.highlight { color: var(--primary); }

        /* Table */
        .payroll-wrap { overflow-x: auto; }
        .payroll-table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .payroll-table thead th {
            text-align: left;
            padding: 12px 18px;
            background: rgba(255,255,255,.03);
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .payroll-table tbody td {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255,255,255,.04);
            font-size: 0.9rem;
            vertical-align: middle;
        }
        .payroll-table tbody tr:last-child td { border-bottom: none; }
        .payroll-table tbody tr { transition: background .15s; }
        .payroll-table tbody tr:hover td { background: rgba(var(--gold-rgb, 201,153,63), .06); }

        .amount-cell { font-weight: 700; font-family: 'Outfit', 'Inter', sans-serif; }
        .amount-total { font-weight: 900; color: var(--primary); font-size: 1.05rem; }

        .status-chip {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 20px;
            font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .4px;
        }
        .chip-paid   { background: rgba(132,204,22,.1); color: var(--primary); border: 1px solid rgba(132,204,22,.3); }
        .chip-unpaid { background: rgba(239,68,68,.1); color: #ef4444; border: 1px solid rgba(239,68,68,.3); }

        .period-badge {
            font-weight: 800;
            font-size: 0.95rem;
            color: var(--text-primary);
        }
        .period-sub {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .empty-state { text-align: center; padding: 64px 24px; color: var(--text-muted); }
        .empty-state .icon { font-size: 3.5rem; margin-bottom: 16px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/layout/thanh-ben.php'; ?>

    <main class="admin-content">
        <!-- Header -->
        <header style="margin-bottom: 2rem;">
            <h1 style="font-size: 2.2rem; font-weight: 900;">
                💰 Lịch Sử <span style="color: var(--primary);">Thu Nhập</span>
            </h1>
            <p style="color: var(--text-muted);">Theo dõi các khoản lương cứng và hoa hồng buổi dạy của bạn.</p>
        </header>

        <?php
            // Tính tổng thống kê
            $tongNhanDuoc = 0;
            $tongBuoiDay  = 0;
            $thangDaThanhToan = 0;
            foreach ($payrolls as $p) {
                $tongNhanDuoc += $p['tong_luong'];
                $tongBuoiDay  += $p['so_buoi_day'];
                if ($p['trang_thai'] === 'da_thanh_toan') $thangDaThanhToan++;
            }
        ?>

        <!-- Stats -->
        <?php if (!empty($payrolls)): ?>
        <div class="income-stats">
            <div class="income-stat">
                <div class="income-stat-label">Tổng Thu Nhập (Tất Cả)</div>
                <div class="income-stat-value highlight"><?= number_format($tongNhanDuoc) ?>đ</div>
            </div>
            <div class="income-stat">
                <div class="income-stat-label">Tổng Buổi Đã Dạy</div>
                <div class="income-stat-value"><?= $tongBuoiDay ?> <span style="font-size:1rem;color:var(--text-muted);">buổi</span></div>
            </div>
            <div class="income-stat">
                <div class="income-stat-label">Tháng Đã Thanh Toán</div>
                <div class="income-stat-value"><?= $thangDaThanhToan ?> <span style="font-size:1rem;color:var(--text-muted);">/ <?= count($payrolls) ?></span></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="glass-panel" style="padding: 0; overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border);">
                <h3 style="font-weight: 800; margin: 0;">📄 Chi Tiết Bảng Lương</h3>
            </div>

            <div class="payroll-wrap">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>Kỳ Lương</th>
                            <th>Lương Cứng</th>
                            <th>Số Buổi Dạy</th>
                            <th>Hoa Hồng PT</th>
                            <th>Tổng Nhận</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payrolls)): ?>
                            <?php foreach ($payrolls as $p): ?>
                            <tr>
                                <td>
                                    <div class="period-badge">📅 <?= htmlspecialchars($p['thang_nam']) ?></div>
                                </td>
                                <td class="amount-cell"><?= number_format($p['luong_cung']) ?>đ</td>
                                <td>
                                    <strong style="color: var(--text-primary);"><?= (int)$p['so_buoi_day'] ?></strong>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;"> buổi</span>
                                </td>
                                <td class="amount-cell"><?= number_format($p['thuong_hoa_hong']) ?>đ</td>
                                <td class="amount-cell amount-total"><?= number_format($p['tong_luong']) ?>đ</td>
                                <td>
                                    <?php if ($p['trang_thai'] === 'da_thanh_toan'): ?>
                                        <span class="status-chip chip-paid">✅ Đã thanh toán</span>
                                    <?php else: ?>
                                        <span class="status-chip chip-unpaid">⏳ Chờ xử lý</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="icon">📊</div>
                                        <p style="font-weight: 700; color: var(--text-primary); margin: 0 0 6px;">Chưa có dữ liệu bảng lương.</p>
                                        <p style="font-size: 0.82rem;">Bảng lương sẽ được Admin chốt vào cuối mỗi tháng.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div><!-- .glass-panel -->
    </main>
</div>
</body>
</html>
