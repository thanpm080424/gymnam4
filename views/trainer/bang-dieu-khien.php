<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Huấn Luyện Viên</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
<?php require __DIR__ . '/layout/topbar.php'; ?>
<div class="member-content">
    

        <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>
<div class="glass-panel">
            <h3>Lịch Trình Làm Việc</h3>
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Khách Hàng</th>
                        <th>Ngày Giờ Hẹn</th>
                        <th>Lời nhắn của khách</th>
                        <th>Trạng thái & Yêu cầu huỷ</th>
                        <th>Hành động HLV</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $b): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['ho_ten'] ?? $b['ten_dang_nhap']) ?></strong><br>
                                <small class="text-muted">Cao: <?= $b['chieu_cao'] ?? '--'?>cm, Nặng: <?= $b['can_nang'] ?? '--' ?>kg</small>
                            </td>
                            <td><strong style="color:var(--accent);"><?= date('H:i d/m/Y', strtotime($b['ngay_gio_tap'])) ?></strong></td>
                            <td style="max-width:200px;"><small><?= htmlspecialchars($b['ghi_chu'] ?? 'Không có') ?></small></td>
                            <td>
                                <?php if($b['trang_thai'] === 'pending'): ?>
                                    <?php if(strtotime($b['ngay_gio_tap']) < time()): ?>
                                        <span style="color:gray; font-weight:bold;">Đã quá hạn (Chưa duyệt)</span>
                                    <?php else: ?>
                                        <span style="color:orange; font-weight:bold;">Đang xin Book giờ</span>
                                    <?php endif; ?>
                                <?php elseif($b['trang_thai'] === 'confirmed'): ?>
                                    <?php if(strtotime($b['ngay_gio_tap']) < time()): ?>
                                        <span style="color:gray; font-weight:bold;">✅ Đã hoàn thành</span>
                                    <?php else: ?>
                                        <span style="color:var(--accent); font-weight:bold;">Đã chốt lịch, chờ tập</span>
                                    <?php endif; ?>
                                <?php elseif($b['trang_thai'] === 'cancel_requested'): ?>
                                    <div style="color:orange; font-weight:bold; margin-bottom:5px;">⚠️ KHách xin huỷ lịch</div>
                                    <small style="background:rgba(255,0,0,0.1); padding:3px 5px; border-radius:5px;">Lý do: <?= htmlspecialchars($b['ly_do_huy']) ?></small>
                                <?php elseif($b['trang_thai'] === 'cancelled'): ?>
                                    <span class="text-muted">Khách đã Huỷ (Đã hoàn tiền PT)</span>
                                <?php elseif($b['trang_thai'] === 'cancel_rejected'): ?>
                                    <span style="color:var(--danger);">KH bị phạt (Huỷ vô lý)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Xử lý Booking Mới -->
                                <?php if($b['trang_thai'] === 'pending'): ?>
                                    <form action="<?= SITE_URL ?>/trainer/booking/resolve" method="POST" style="display:inline;">
                                        <input type="hidden" name="ma_lich" value="<?= $b['ma_lich'] ?>">
                                        <button type="submit" name="action" value="accept" class="btn btn-primary" style="padding: 0.3rem 0.5rem; font-size: 0.8rem;">Nhận lớp</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding: 0.3rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Từ chối khách này? Khách sẽ được nhận lại 1 buổi tập vào tài khoản.');">Khoá / Trùng giờ</button>
                                    </form>
                                <?php endif; ?>

                                <!-- Xử lý Yêu Cầu Huỷ Lịch từ Khách -->
                                <?php if($b['trang_thai'] === 'cancel_requested'): ?>
                                    <form action="<?= SITE_URL ?>/trainer/cancel/resolve" method="POST" style="display:inline;">
                                        <input type="hidden" name="ma_lich" value="<?= $b['ma_lich'] ?>">
                                        <button type="submit" name="action" value="accept_cancel" class="btn btn-secondary" style="padding: 0.3rem 0.5rem; font-size: 0.8rem; background:orange; color:black; border:none;" onclick="return confirm('Chấp thuận cho Khách huỷ? Họ sẽ được hoàn lại Buổi Tập.');">Chấp nhận Huỷ & Hoàn tiền</button>
                                        <br>
                                        <button type="submit" name="action" value="reject_cancel" class="btn btn-danger" style="margin-top:5px; padding: 0.3rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Bác bỏ lý do này do báo qúa sát giờ tập? Khách sẽ bị TRỪ mất buổi tập này!');">Bác bỏ (Phạt trừ thẻ Khách)</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($bookings)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Tuyệt vời, bạn chưa có lịch hẹn nào!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
