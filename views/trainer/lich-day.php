<?php
/**
 * View: Thời Khóa Biểu Tuần - Huấn Luyện Viên (tự động từ LICH_DAT_PT)
 * Route: /trainer/schedule
 */
function trangThaiLabel(string $tt): array {
    return match($tt) {
        'pending'          => ['Chờ duyệt',    'chip-pending'],
        'confirmed'        => ['Đã xác nhận',  'chip-confirmed'],
        'cancel_requested' => ['Xin huỷ',      'chip-cancel'],
        'cancel_rejected'  => ['Bị phạt',      'chip-rejected'],
        'completed'        => ['Hoàn thành',   'chip-done'],
        default            => [$tt,             'chip-pending'],
    };
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thời Khóa Biểu | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .week-nav {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;
        }
        .week-nav-title {
            font-size: 1.1rem; font-weight: 700; color: var(--text-primary);
            text-align: center; flex: 1;
        }
        .btn-nav {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .5rem 1.1rem; border-radius: 10px;
            border: 1px solid var(--border); background: var(--bg-card);
            color: var(--text-primary); font-size: .88rem; font-weight: 600;
            cursor: pointer; text-decoration: none; transition: all .2s;
        }
        .btn-nav:hover { background: var(--gold); color:#000; border-color:var(--gold); }
        .btn-today { background:var(--gold-bg); border-color:var(--gold-border); color:var(--gold-dark); }

        /* Bảng lưới */
        .tkb-wrap { overflow-x: auto; }
        .tkb-table { width:100%; min-width:780px; border-collapse:collapse; font-size:.82rem; }
        .tkb-table th, .tkb-table td { border:1px solid var(--border); vertical-align:top; padding:0; }

        /* Header thứ */
        .tkb-table thead th {
            background: linear-gradient(135deg,var(--gold-dark),var(--gold));
            color:#000; font-weight:700; font-size:.78rem; text-align:center;
            padding:.6rem .3rem; text-transform:uppercase; letter-spacing:.3px;
        }
        .th-today { outline:3px solid #92400e !important; }
        .th-time { background:var(--bg-secondary) !important; color:var(--text-muted) !important;
                   font-weight:600 !important; font-size:.75rem !important; }

        /* Cột giờ */
        .col-time {
            width:96px; min-width:80px; background:var(--bg-primary);
            padding:.5rem .4rem; font-weight:600; color:var(--text-secondary);
            font-size:.75rem; text-align:center; white-space:nowrap;
        }

        /* Cell lịch hẹn */
        .day-cell { min-height:64px; padding:.3rem; }
        .day-cell-empty { background:var(--bg-primary); min-height:64px; }

        /* Chip lịch hẹn */
        .booking-chip {
            border-radius:8px; padding:.28rem .4rem; margin-bottom:.25rem;
            font-size:.73rem; line-height:1.3; border-left:3px solid;
        }
        .chip-pending   { background:#fef3c7; border-color:#f59e0b; color:#78350f; }
        .chip-confirmed { background:#d1fae5; border-color:#059669; color:#065f46; }
        .chip-cancel    { background:#fee2e2; border-color:#ef4444; color:#7f1d1d; }
        .chip-rejected  { background:#fce7f3; border-color:#db2777; color:#831843; }
        .chip-done      { background:var(--bg-primary); border-color:var(--text-muted); color:var(--text-muted); }
        .chip-time { font-weight:700; }
        .chip-name { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:110px; }
        .chip-label { font-size:.68rem; opacity:.85; }

        /* Legend */
        .tkb-legend { display:flex; gap:1rem; flex-wrap:wrap; margin-top:1rem; font-size:.78rem; }
        .leg-dot { display:inline-block; width:10px;height:10px;border-radius:2px;margin-right:4px;vertical-align:middle; }

        /* Tổng kết tuần */
        .week-summary { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.2rem; }
        .summary-chip {
            padding:.35rem .85rem; border-radius:20px; font-size:.8rem; font-weight:600;
            background:var(--gold-bg); color:var(--gold-dark); border:1px solid var(--gold-border);
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/layout/topbar.php'; ?>
<div class="member-content">

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="glass-panel">
    <!-- Điều hướng tuần -->
    <div class="week-nav">
        <a href="<?= SITE_URL ?>/trainer/schedule?week=<?= $weekOffset - 1 ?>" class="btn-nav">◀ Tuần trước</a>
        <div class="week-nav-title">
            📅 Thời Khóa Biểu &nbsp;—&nbsp; <span style="color:var(--gold)"><?= htmlspecialchars($weekLabel) ?></span>
        </div>
        <?php if ($weekOffset !== 0): ?>
            <a href="<?= SITE_URL ?>/trainer/schedule?week=0" class="btn-nav btn-today">Tuần này</a>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/trainer/schedule?week=<?= $weekOffset + 1 ?>" class="btn-nav">Tuần sau ▶</a>
    </div>

    <?php
    // Tổng kết nhanh
    $totalBookings   = array_sum(array_map('count', $bookingByDate));
    $pendingCount    = 0;
    $confirmedCount  = 0;
    foreach ($bookingByDate as $dayBk) {
        foreach ($dayBk as $b) {
            if ($b['trang_thai'] === 'pending')   $pendingCount++;
            if ($b['trang_thai'] === 'confirmed') $confirmedCount++;
        }
    }
    ?>
    <div class="week-summary">
        <span class="summary-chip">📋 Tổng lịch hẹn: <?= $totalBookings ?></span>
        <?php if ($pendingCount): ?><span class="summary-chip" style="background:#fef3c7;color:#78350f;border-color:#fcd34d;">⏳ Chờ duyệt: <?= $pendingCount ?></span><?php endif; ?>
        <?php if ($confirmedCount): ?><span class="summary-chip" style="background:#d1fae5;color:#065f46;border-color:#6ee7b7;">✅ Đã xác nhận: <?= $confirmedCount ?></span><?php endif; ?>
        <?php if (!$totalBookings): ?><span class="summary-chip" style="background:var(--bg-primary);color:var(--text-muted);border-color:var(--border);">Tuần này chưa có lịch hẹn</span><?php endif; ?>
    </div>

    <!-- Bảng lưới TKB -->
    <div class="tkb-wrap">
        <table class="tkb-table">
            <thead>
                <tr>
                    <th class="th-time">⏱ Giờ</th>
                    <?php foreach ($weekDays as $day): ?>
                        <th class="<?= $day['is_today'] ? 'th-today' : '' ?>">
                            <?= $day['thu_vn'] ?><br>
                            <span style="font-size:.72rem;font-weight:500;opacity:.85"><?= $day['label'] ?></span>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Định nghĩa khung giờ (có thêm "Ngoài giờ" cho lịch không khớp)
                foreach ($timeSlots as $ts):
                    if (!empty($ts['is_break'])): ?>
                        <tr>
                            <td class="col-time" style="background:#fee2e2; color:#991b1b; white-space:normal; font-size:0.7rem;"><?= htmlspecialchars($ts['label']) ?></td>
                            <td colspan="7" style="background: repeating-linear-gradient(45deg, #fef2f2, #fef2f2 10px, #fee2e2 10px, #fee2e2 20px); text-align:center; vertical-align:middle; color:#b91c1c; font-weight:700; letter-spacing:2px; height: 40px;">THỜI GIAN NGHỈ</td>
                        </tr>
                    <?php continue; endif;

                    $slotStart = strtotime('2000-01-01 ' . $ts['bat_dau']);
                    $slotEnd   = strtotime('2000-01-01 ' . $ts['ket_thuc']);
                ?>
                <tr>
                    <td class="col-time"><?= htmlspecialchars($ts['label']) ?></td>
                    <?php foreach ($weekDays as $day):
                        // Lọc bookings của ngày này nằm trong khung giờ này
                        $cellBookings = [];
                        foreach ($bookingByDate[$day['date']] ?? [] as $b) {
                            $bt = strtotime('2000-01-01 ' . substr($b['ngay_gio_tap'], 11, 8));
                            if ($bt >= $slotStart && $bt < $slotEnd) {
                                $cellBookings[] = $b;
                            }
                        }
                    ?>
                        <td>
                            <?php if (!empty($cellBookings)): ?>
                                <div class="day-cell">
                                    <?php foreach ($cellBookings as $b):
                                        [$label, $cls] = trangThaiLabel($b['trang_thai']);
                                    ?>
                                        <div class="booking-chip <?= $cls ?>">
                                            <div class="chip-time"><?= htmlspecialchars($b['gio_hien']) ?></div>
                                            <div class="chip-name" title="<?= htmlspecialchars($b['ten_hoi_vien']) ?>">
                                                <?= htmlspecialchars($b['ten_hoi_vien']) ?>
                                            </div>
                                            <div class="chip-label"><?= $label ?></div>
                                            <?php if (!empty($b['ghi_chu'])): ?>
                                                <div style="font-size:0.65rem; color:inherit; opacity:0.85; margin-top:2px; font-style:italic; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($b['ghi_chu']) ?>">
                                                    📝 <?= htmlspecialchars($b['ghi_chu']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="day-cell-empty"></div>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>

                <?php
                // Hàng "Ngoài khung giờ" — lịch hẹn không rơi vào bất kỳ slot nào
                $hasOutOfSlot = false;
                $outOfSlotMap = [];
                foreach ($bookingByDate as $date => $dayBks) {
                    foreach ($dayBks as $b) {
                        $bt = strtotime('2000-01-01 ' . substr($b['ngay_gio_tap'], 11, 8));
                        $inSlot = false;
                        foreach ($timeSlots as $ts) {
                            if ($bt >= strtotime('2000-01-01 '.$ts['bat_dau']) && $bt < strtotime('2000-01-01 '.$ts['ket_thuc'])) {
                                $inSlot = true; break;
                            }
                        }
                        if (!$inSlot) { $outOfSlotMap[$date][] = $b; $hasOutOfSlot = true; }
                    }
                }
                if ($hasOutOfSlot):
                ?>
                <tr>
                    <td class="col-time" style="color:var(--text-muted);font-style:italic;">Ngoài<br>khung giờ</td>
                    <?php foreach ($weekDays as $day): $extra = $outOfSlotMap[$day['date']] ?? []; ?>
                        <td>
                            <?php if (!empty($extra)): ?>
                                <div class="day-cell">
                                    <?php foreach ($extra as $b):
                                        [$label, $cls] = trangThaiLabel($b['trang_thai']);
                                    ?>
                                        <div class="booking-chip <?= $cls ?>">
                                            <div class="chip-time"><?= htmlspecialchars($b['gio_hien']) ?></div>
                                            <div class="chip-name"><?= htmlspecialchars($b['ten_hoi_vien']) ?></div>
                                            <div class="chip-label"><?= $label ?></div>
                                            <?php if (!empty($b['ghi_chu'])): ?>
                                                <div style="font-size:0.65rem; color:inherit; opacity:0.85; margin-top:2px; font-style:italic; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($b['ghi_chu']) ?>">
                                                    📝 <?= htmlspecialchars($b['ghi_chu']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="day-cell-empty"></div>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Legend -->
    <div class="tkb-legend">
        <span><span class="leg-dot" style="background:#f59e0b"></span>Chờ duyệt</span>
        <span><span class="leg-dot" style="background:#059669"></span>Đã xác nhận</span>
        <span><span class="leg-dot" style="background:#ef4444"></span>Xin huỷ</span>
        <span><span class="leg-dot" style="background:var(--text-muted)"></span>Hoàn thành</span>
        <span style="margin-left:auto;color:var(--text-muted);font-size:.75rem;">
            💡 Lịch tự cập nhật theo hội viên đặt hẹn. Xem lịch hẹn chi tiết tại <a href="<?= SITE_URL ?>/trainer/dashboard" style="color:var(--gold);">Lịch Hẹn</a>
        </span>
    </div>
</div>

</div>
</body>
</html>
