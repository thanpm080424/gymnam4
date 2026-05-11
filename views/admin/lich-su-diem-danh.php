<?php
/**
 * View: Lịch sử điểm danh tất cả hội viên
 * Dùng cho Staff & Admin
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Điểm Danh | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .attendance-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.2rem;
            text-align: center;
        }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.4rem;
        }
        .filter-bar {
            display: flex;
            gap: 0.8rem;
            align-items: flex-end;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.2rem;
        }
        .filter-bar .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        .filter-bar label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .filter-bar input[type="date"],
        .filter-bar input[type="text"] {
            padding: 0.5rem 0.7rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg-secondary, #0f172a);
            color: var(--text-primary);
            font-size: 0.85rem;
            font-family: inherit;
        }
        .filter-bar input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(201,153,63,0.15);
        }
        .filter-bar .btn-filter {
            padding: 0.5rem 1.2rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background 0.2s;
        }
        .filter-bar .btn-filter:hover { background: var(--primary-dark, #a07c28); }
        .filter-bar .btn-reset {
            padding: 0.5rem 1rem;
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .filter-bar .btn-reset:hover { 
            background: var(--bg-secondary); 
            color: var(--text-primary);
        }
        .quick-filters {
            display: flex;
            gap: 0.4rem;
            margin-left: auto;
        }
        .quick-filters button {
            padding: 0.35rem 0.7rem;
            font-size: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }
        .quick-filters button:hover {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .member-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .member-link:hover { text-decoration: underline; }
        .badge-method {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-qr { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .badge-manual { background: rgba(16,185,129,0.15); color: #10b981; }
        .table-container {
            max-height: 600px;
            overflow-y: auto;
            border-radius: 12px;
        }
        @media (max-width: 768px) {
            .attendance-stats { grid-template-columns: 1fr; }
            .filter-bar { flex-direction: column; align-items: stretch; }
            .quick-filters { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>

        <div class="admin-content">
            <header class="dashboard-header">
                <div>
                    <h1>📋 Lịch Sử Ra Vào</h1>
                    <p class="text-muted">Xem toàn bộ lịch sử điểm danh của hội viên.</p>
                </div>
            </header>

            <!-- Thống kê -->
            <div class="attendance-stats">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($totalCheckins) ?></div>
                    <div class="stat-label">Tổng lượt điểm danh</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($uniqueMembers) ?></div>
                    <div class="stat-label">Hội viên đã check-in</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $uniqueMembers > 0 ? round($totalCheckins / $uniqueMembers, 1) : 0 ?></div>
                    <div class="stat-label">TB lượt/hội viên</div>
                </div>
            </div>

            <!-- Bộ lọc -->
            <form method="GET" action="<?= SITE_URL ?>/admin/attendance-history" class="filter-bar">
                <div class="filter-group">
                    <label>Từ ngày</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="filter-group">
                    <label>Đến ngày</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="filter-group">
                    <label>Tìm hội viên</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($searchName) ?>" placeholder="Tên hoặc email...">
                </div>
                <button type="submit" class="btn-filter">🔍 Lọc</button>
                <a href="<?= SITE_URL ?>/admin/attendance-history" class="btn-reset">↺ Reset</a>

                <div class="quick-filters">
                    <button type="button" onclick="setQuickDate('today')">Hôm nay</button>
                    <button type="button" onclick="setQuickDate('week')">7 ngày</button>
                    <button type="button" onclick="setQuickDate('month')">30 ngày</button>
                </div>
            </form>

            <!-- Bảng dữ liệu -->
            <div class="glass-panel" style="padding: 0;">
                <div class="table-container">
                    <table class="glass-table" style="margin: 0;">
                        <thead style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>#</th>
                                <th>Hội viên</th>
                                <th>SĐT</th>
                                <th>Gói tập</th>
                                <th>Ngày</th>
                                <th>Giờ vào</th>
                                <th>Phương thức</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($attendanceList)): ?>
                            <?php foreach($attendanceList as $i => $row): ?>
                            <tr>
                                <td style="color: var(--text-muted); font-size: 0.8rem;"><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= SITE_URL ?>/admin/members/detail?id=<?= $row['ma_hoi_vien'] ?>" class="member-link">
                                        <?= htmlspecialchars($row['ho_ten']) ?>
                                    </a>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);"><?= htmlspecialchars($row['ten_dang_nhap']) ?></div>
                                </td>
                                <td style="font-size: 0.85rem;"><?= htmlspecialchars($row['so_dien_thoai'] ?? '--') ?></td>
                                <td>
                                    <?php if (!empty($row['ten_goi'])): ?>
                                        <span style="background: rgba(139,92,246,0.12); color: #a78bfa; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                            <?= htmlspecialchars($row['ten_goi']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">--</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 600;"><?= date('d/m/Y', strtotime($row['ngay'])) ?></td>
                                <td style="font-family: monospace; font-weight: 600; color: var(--accent, #10b981);">
                                    <?= date('H:i:s', strtotime($row['gio_diem_danh'])) ?>
                                </td>
                                <td>
                                    <?php if ($row['phuong_thuc'] === 'qr_code'): ?>
                                        <span class="badge-method badge-qr">📱 QR Code</span>
                                    <?php else: ?>
                                        <span class="badge-method badge-manual">✍️ Thủ công</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-muted); max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($row['ghi_chu'] ?? '--') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding: 3rem 1rem;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📭</div>
                                    Không có dữ liệu điểm danh trong khoảng thời gian này.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($attendanceList)): ?>
                <div style="padding: 0.8rem 1.2rem; border-top: 1px solid var(--border); text-align: center;">
                    <span class="text-muted" style="font-size: 0.8rem;">
                        Hiển thị <?= count($attendanceList) ?> bản ghi
                        (<?= date('d/m/Y', strtotime($startDate)) ?> — <?= date('d/m/Y', strtotime($endDate)) ?>)
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script>
function setQuickDate(type) {
    const form = document.querySelector('.filter-bar');
    const startInput = form.querySelector('input[name="start_date"]');
    const endInput = form.querySelector('input[name="end_date"]');
    const today = new Date();
    const fmt = d => d.toISOString().split('T')[0];

    endInput.value = fmt(today);
    if (type === 'today') {
        startInput.value = fmt(today);
    } else if (type === 'week') {
        const d = new Date(today);
        d.setDate(d.getDate() - 7);
        startInput.value = fmt(d);
    } else if (type === 'month') {
        const d = new Date(today);
        d.setDate(d.getDate() - 30);
        startInput.value = fmt(d);
    }
    form.submit();
}
</script>
</body>
</html>
