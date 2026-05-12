<?php
/**
 * View: Dashboard Huấn Luyện Viên
 * Route: /trainer/dashboard
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HLV | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .booking-status {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px; font-size: 0.72rem;
            font-weight: 800; text-transform: uppercase; letter-spacing: .4px;
        }
        .st-pending   { background: rgba(245,158,11,.12); color: #b45309; border: 1px solid rgba(245,158,11,.35); }
        .st-confirmed { background: rgba(5,150,105,.12); color: #065f46; border: 1px solid rgba(5,150,105,.35); }
        .st-done      { background: rgba(100,116,139,.1); color: var(--text-muted); border: 1px solid var(--border); }
        .st-cancel    { background: rgba(239,68,68,.1); color: #b91c1c; border: 1px solid rgba(239,68,68,.3); }
        .st-penalty   { background: rgba(219,39,119,.1); color: #831843; border: 1px solid rgba(219,39,119,.3); }

        .action-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: 8px; font-size: 0.75rem;
            font-weight: 700; border: none; cursor: pointer; text-decoration: none;
            transition: all .18s;
        }
        .action-accept { background: rgba(5,150,105,.15); color: #059669; border: 1px solid rgba(5,150,105,.4); }
        .action-accept:hover { background: #059669; color: #fff; }
        .action-reject { background: rgba(239,68,68,.12); color: #dc2626; border: 1px solid rgba(239,68,68,.35); }
        .action-reject:hover { background: #dc2626; color: #fff; }
        .action-warn   { background: rgba(245,158,11,.14); color: #b45309; border: 1px solid rgba(245,158,11,.4); }
        .action-warn:hover { background: #f59e0b; color: #000; }

        .hlv-table { width: 100%; border-collapse: collapse; }
        .hlv-table th {
            text-align: left; padding: 12px 16px;
            background: rgba(255,255,255,.03); color: var(--text-muted);
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .6px; border-bottom: 1px solid var(--border);
        }
        .hlv-table td {
            padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,.04);
            vertical-align: middle; font-size: 0.88rem;
        }
        .hlv-table tr:last-child td { border-bottom: none; }
        .hlv-table tbody tr { transition: background .15s; }
        .hlv-table tbody tr:hover td { background: rgba(var(--gold-rgb, 201,153,63), .06); }

        .client-name { font-weight: 700; color: var(--text-primary); }
        .client-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }

        .empty-state {
            text-align: center; padding: 64px 24px; color: var(--text-muted);
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/layout/thanh-ben.php'; ?>

    <main class="admin-content">
        <header style="margin-bottom: 2rem;">
            <h1 style="font-size: 2.2rem; font-weight: 900;">
                🏋️ Dashboard <span style="color: var(--primary);">Huấn Luyện Viên</span>
            </h1>
            <p style="color: var(--text-muted);">Quản lý lịch hẹn và học viên của bạn.</p>
        </header>

        <?php
            $__f = getFlash();
            if ($__f): ?>
            <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                <?= htmlspecialchars($__f['message']) ?>
            </div>
        <?php endif; ?>

        <div class="glass-panel" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h3 style="font-weight: 800; margin: 0;">📋 Lịch Hẹn Của Tôi</h3>
                    <p style="color: var(--text-muted); font-size: 0.82rem; margin: 4px 0 0;">Danh sách học viên đặt lịch tập cá nhân (PT)</p>
                </div>
                <a href="<?= SITE_URL ?>/trainer/schedule" class="action-btn action-accept" style="font-size: 0.8rem;">
                    📅 Xem Thời Khóa Biểu
                </a>
            </div>

            <div style="overflow-x: auto;">
                <table class="hlv-table">
                    <thead>
                        <tr>
                            <th>Học Viên</th>
                            <th>Ngày & Giờ Hẹn</th>
                            <th>Lời Nhắn</th>
                            <th>Trạng Thái</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings as $b): ?>
                        <tr>
                            <td>
                                <div class="client-name"><?= htmlspecialchars($b['ho_ten'] ?? $b['ten_dang_nhap']) ?></div>
                                <div class="client-meta">
                                    📏 <?= $b['chieu_cao'] ?? '--' ?>cm &nbsp;·&nbsp;
                                    ⚖️ <?= $b['can_nang'] ?? '--' ?>kg
                                </div>
                            </td>
                            <td>
                                <strong style="color: var(--gold, #c9993f);">
                                    <?= date('H:i', strtotime($b['ngay_gio_tap'])) ?>
                                </strong>
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                    <?= date('d/m/Y', strtotime($b['ngay_gio_tap'])) ?>
                                </div>
                            </td>
                            <td style="max-width: 200px; color: var(--text-muted); font-size: 0.82rem;">
                                <?= htmlspecialchars($b['ghi_chu'] ?? 'Không có lời nhắn') ?>
                            </td>
                            <td>
                                <?php if($b['trang_thai'] === 'pending'): ?>
                                    <?php if(strtotime($b['ngay_gio_tap']) < time()): ?>
                                        <span class="booking-status st-done">⚠️ Quá hạn</span>
                                    <?php else: ?>
                                        <span class="booking-status st-pending">⏳ Chờ duyệt</span>
                                    <?php endif; ?>
                                <?php elseif($b['trang_thai'] === 'confirmed'): ?>
                                    <?php if(strtotime($b['ngay_gio_tap']) < time()): ?>
                                        <span class="booking-status st-done">✅ Hoàn thành</span>
                                    <?php else: ?>
                                        <span class="booking-status st-confirmed">✅ Đã xác nhận</span>
                                    <?php endif; ?>
                                <?php elseif($b['trang_thai'] === 'cancel_requested'): ?>
                                    <span class="booking-status st-warn" style="background:rgba(245,158,11,.12);color:#b45309;border-color:rgba(245,158,11,.35);">⚠️ Khách xin huỷ</span>
                                    <?php if(!empty($b['ly_do_huy'])): ?>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px; padding: 3px 8px; background: rgba(239,68,68,.06); border-radius: 6px;">
                                        Lý do: <?= htmlspecialchars($b['ly_do_huy']) ?>
                                    </div>
                                    <?php endif; ?>
                                <?php elseif($b['trang_thai'] === 'cancelled'): ?>
                                    <span class="booking-status st-done">Đã huỷ</span>
                                <?php elseif($b['trang_thai'] === 'cancel_rejected'): ?>
                                    <span class="booking-status st-penalty">🚫 Bị phạt</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($b['trang_thai'] === 'pending'): ?>
                                    <form action="<?= SITE_URL ?>/trainer/booking/resolve" method="POST" style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <input type="hidden" name="ma_lich" value="<?= $b['ma_lich'] ?>">
                                        <button type="submit" name="action" value="accept" class="action-btn action-accept">✓ Nhận</button>
                                        <button type="submit" name="action" value="reject" class="action-btn action-reject"
                                            onclick="return confirm('Từ chối lịch hẹn này? Khách sẽ được hoàn lại 1 buổi tập.')">✕ Từ chối</button>
                                    </form>
                                <?php elseif($b['trang_thai'] === 'cancel_requested'): ?>
                                    <form action="<?= SITE_URL ?>/trainer/cancel/resolve" method="POST" style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <input type="hidden" name="ma_lich" value="<?= $b['ma_lich'] ?>">
                                        <button type="submit" name="action" value="accept_cancel" class="action-btn action-warn"
                                            onclick="return confirm('Chấp nhận huỷ? Khách sẽ được hoàn lại buổi tập.')">✅ Chấp nhận</button>
                                        <button type="submit" name="action" value="reject_cancel" class="action-btn action-reject"
                                            onclick="return confirm('Bác bỏ lý do huỷ? Khách sẽ bị trừ mất buổi tập này!')">🚫 Phạt</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.78rem;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if(empty($bookings)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="icon">🎉</div>
                                    <p style="font-weight: 700; color: var(--text-primary); margin: 0 0 6px;">Tuyệt vời! Bạn chưa có lịch hẹn mới.</p>
                                    <p style="font-size: 0.82rem;">Khi học viên đặt lịch PT, chúng sẽ hiển thị ở đây.</p>
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
