<?php
$nameDisplay = $memberInfo['ho_ten'] ?: $memberInfo['ten_dang_nhap'];
$statusLabel = ($memberInfo['trang_thai'] === 'banned') ? '⛔ Đã Khóa' : '✅ Hoạt động';
$statusColor = ($memberInfo['trang_thai'] === 'banned') ? '#ef4444' : '#10b981';

// Tính BMI mới nhất
$latestBmi = null;
if (!empty($bmiHistory)) {
    $b = $bmiHistory[0];
    if ($b['can_nang'] > 0 && $b['chieu_cao'] > 0) {
        $heightM = $b['chieu_cao'] / 100;
        $latestBmi = round($b['can_nang'] / ($heightM * $heightM), 1);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Hội Viên - <?= htmlspecialchars($nameDisplay) ?> | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .detail-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        .profile-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
        }
        .avatar-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin: 0 auto 1rem;
        }
        .profile-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        .profile-email {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            word-break: break-all;
        }
        .badge-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .info-rows .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
        }
        .info-rows .info-row:last-child { border-bottom: none; }
        .info-row .label { color: var(--text-muted); }
        .info-row .value { font-weight: 600; color: var(--text-primary); text-align: right; }
        .action-buttons { display: flex; flex-direction: column; gap: 0.6rem; margin-top: 1.5rem; }
        .action-buttons .btn { width: 100%; text-align: center; padding: 0.6rem; font-size: 0.9rem; }

        .info-panels { display: flex; flex-direction: column; gap: 1.5rem; }
        .panel-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
        }
        .panel-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .bmi-value {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            padding: 1rem 0;
        }
        .bmi-label { font-size: 0.85rem; text-align: center; color: var(--text-muted); margin-top: -0.5rem; margin-bottom: 1rem; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .stat-box {
            background: var(--bg-secondary, #1e293b);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
        }
        .stat-box .stat-num { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
        .stat-box .stat-lbl { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }
        @media (max-width: 768px) {
            .detail-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>

        <div class="admin-content">
            <header class="dashboard-header">
                <div>
                    <h1>👤 Chi Tiết Hội Viên</h1>
                    <p class="text-muted">
                        <a href="<?= SITE_URL ?>/admin/members" style="color: var(--primary); text-decoration: none;">← Quay lại danh sách</a>
                    </p>
                </div>
            </header>

            <?php $__f = getFlash(); if ($__f): ?>
            <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                <?= htmlspecialchars($__f['message']) ?>
            </div>
            <?php endif; ?>

            <div class="detail-grid">
                <!-- CỘT TRÁI: Thẻ hồ sơ -->
                <div class="profile-card">
                    <div class="avatar-circle"><?= mb_strtoupper(mb_substr($nameDisplay, 0, 1, 'UTF-8'), 'UTF-8') ?></div>
                    <div class="profile-name"><?= htmlspecialchars($nameDisplay) ?></div>
                    <div class="profile-email"><?= htmlspecialchars($memberInfo['ten_dang_nhap']) ?></div>
                    <span class="badge-status" style="background: <?= $statusColor ?>22; color: <?= $statusColor ?>;">
                        <?= $statusLabel ?>
                    </span>

                    <div class="info-rows">
                        <div class="info-row">
                            <span class="label">Mã QR</span>
                            <span class="value" style="font-family: monospace; font-size: 0.75rem;"><?= htmlspecialchars($memberInfo['ma_qr'] ?? '--') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">SĐT</span>
                            <span class="value"><?= htmlspecialchars($memberInfo['so_dien_thoai'] ?? '--') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Ngày tham gia</span>
                            <span class="value"><?= isset($memberInfo['ngay_tao']) ? date('d/m/Y', strtotime($memberInfo['ngay_tao'])) : '--' ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Chiều cao</span>
                            <span class="value"><?= $memberInfo['chieu_cao'] ?? '--' ?> cm</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Cân nặng</span>
                            <span class="value"><?= $memberInfo['can_nang'] ?? '--' ?> kg</span>
                        </div>
                    </div>

                    <!-- Các nút hành động -->
                    <div class="action-buttons">
                        <form action="<?= SITE_URL ?>/admin/members/reset-password" method="POST"
                              onsubmit="return confirm('Cấp lại mật khẩu về mặc định (123456) cho hội viên này?');">
                            <input type="hidden" name="ma_user" value="<?= $memberInfo['ma_nguoi_dung'] ?>">
                            <button type="submit" class="btn btn-primary" style="width:100%; background:#10b981; border:none;">
                                🔑 Cấp Lại Mật Khẩu
                            </button>
                        </form>

                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <form action="<?= SITE_URL ?>/admin/members/ban" method="POST">
                            <input type="hidden" name="ma_user" value="<?= $memberInfo['ma_nguoi_dung'] ?>">
                            <?php if ($memberInfo['trang_thai'] === 'banned'): ?>
                                <input type="hidden" name="action" value="unban">
                                <button type="submit" class="btn btn-secondary" style="width:100%;">✅ Mở Khóa Tài Khoản</button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="ban">
                                <button type="submit" class="btn btn-secondary" style="width:100%; background:#f59e0b; border:none; color:#000;"
                                        onclick="return confirm('Bạn có chắc muốn khóa hội viên này?');">
                                    🔒 Khóa Tài Khoản
                                </button>
                            <?php endif; ?>
                        </form>

                        <form action="<?= SITE_URL ?>/admin/members/delete" method="POST"
                              onsubmit="return confirm('⚠️ CẢNH BÁO: Xóa vĩnh viễn toàn bộ dữ liệu của hội viên. Tiếp tục?');">
                            <input type="hidden" name="ma_user" value="<?= $memberInfo['ma_nguoi_dung'] ?>">
                            <button type="submit" class="btn btn-danger" style="width:100%;">🗑️ Xóa Tài Khoản</button>
                        </form>
                        <?php endif; ?>

                        <?php if ($_SESSION['role'] !== 'admin' && $memberInfo['trang_thai'] !== 'banned'): ?>
                        <button type="button" class="btn btn-primary" style="width:100%; background:#ef4444; border:none;" onclick="openBanModalDetail()">
                            ⚠️ Yêu Cầu Ban Hội Viên
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CỘT PHẢI: Các panel thông tin -->
                <div class="info-panels">

                    <!-- Chỉ số cơ thể & BMI -->
                    <div class="panel-card">
                        <div class="panel-title">📊 Chỉ Số Cơ Thể & BMI</div>
                        <?php if ($latestBmi !== null): ?>
                            <?php
                            if ($latestBmi < 18.5) { $bmiCls = '#3b82f6'; $bmiText = 'Thiếu cân'; }
                            elseif ($latestBmi < 25) { $bmiCls = '#10b981'; $bmiText = 'Bình thường ✓'; }
                            elseif ($latestBmi < 30) { $bmiCls = '#f59e0b'; $bmiText = 'Thừa cân'; }
                            else { $bmiCls = '#ef4444'; $bmiText = 'Béo phì'; }
                            ?>
                            <div class="bmi-value" style="color: <?= $bmiCls ?>;"><?= $latestBmi ?></div>
                            <div class="bmi-label"><?= $bmiText ?></div>
                        <?php else: ?>
                            <div style="text-align:center; padding: 1.5rem 0; color: var(--text-muted);">Chưa có dữ liệu BMI</div>
                        <?php endif; ?>

                        <?php if (!empty($bmiHistory)): ?>
                        <table class="glass-table" style="margin-top: 0.5rem;">
                            <thead>
                                <tr>
                                    <th>Ngày đo</th>
                                    <th>Chiều cao</th>
                                    <th>Cân nặng</th>
                                    <th>BMI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($bmiHistory as $b): 
                                    $bmi = '--';
                                    if ($b['can_nang'] > 0 && $b['chieu_cao'] > 0) {
                                        $h = $b['chieu_cao'] / 100;
                                        $bmi = round($b['can_nang'] / ($h * $h), 1);
                                    }
                                ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($b['ngay_do'])) ?></td>
                                    <td><?= $b['chieu_cao'] ?> cm</td>
                                    <td><?= $b['can_nang'] ?> kg</td>
                                    <td><strong><?= $bmi ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-muted" style="text-align:center; padding:1rem 0;">Chưa có lịch sử đo chỉ số.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Gói tập -->
                    <div class="panel-card">
                        <div class="panel-title">🏋️ Lịch Sử Gói Tập</div>
                        <?php if (!empty($packages)): ?>
                        <table class="glass-table">
                            <thead>
                                <tr>
                                    <th>Gói Tập</th>
                                    <th>Ngày Đăng Ký</th>
                                    <th>Ngày Hết Hạn</th>
                                    <th>Số Tiền</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($packages as $pkg): 
                                    $ttColor = $pkg['trang_thai_tt'] === 'success' ? '#10b981' : '#f59e0b';
                                    $ttLabel = $pkg['trang_thai_tt'] === 'success' ? '✅ Đã thanh toán' : '⏳ ' . $pkg['trang_thai_tt'];
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pkg['ten_goi']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($pkg['created_at'])) ?></td>
                                    <td>
                                        <?php if (!empty($pkg['ngay_ket_thuc'])): ?>
                                            <?= date('d/m/Y', strtotime($pkg['ngay_ket_thuc'])) ?>
                                        <?php else: ?>--<?php endif; ?>
                                    </td>
                                    <td style="color: var(--accent); font-weight: 600;">
                                        <?= number_format($pkg['so_tien']) ?>đ
                                    </td>
                                    <td><span style="color: <?= $ttColor ?>; font-size: 0.85rem;"><?= $ttLabel ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-muted" style="text-align:center; padding:1rem 0;">Hội viên chưa đăng ký gói tập nào.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Lịch sử điểm danh QR -->
                    <div class="panel-card">
                        <div class="panel-title">📋 Lịch Sử Điểm Danh QR</div>
                        <?php if (!empty($checkinHistory)): ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <table class="glass-table">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Giờ</th>
                                        <th>Phương thức</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($checkinHistory as $ci): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($ci['ngay'])) ?></td>
                                        <td style="font-family: monospace; font-weight: 600; color: var(--primary);"><?= date('H:i', strtotime($ci['gio_diem_danh'])) ?></td>
                                        <td>
                                            <?php if ($ci['phuong_thuc'] === 'qr_code'): ?>
                                                <span style="background: rgba(59,130,246,0.15); color: #3b82f6; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">📱 QR Code</span>
                                            <?php else: ?>
                                                <span style="background: rgba(16,185,129,0.15); color: #10b981; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">✍️ Thủ công</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size: 0.8rem; color: var(--text-muted); max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?= htmlspecialchars($ci['ghi_chu'] ?? '--') ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted" style="text-align:center; font-size:0.78rem; margin-top:0.5rem;">Hiển thị <?= count($checkinHistory) ?> lần điểm danh gần nhất</p>
                        <?php else: ?>
                        <p class="text-muted" style="text-align:center; padding:1rem 0;">Hội viên chưa có lịch sử điểm danh.</p>
                        <?php endif; ?>
                    </div>

                </div><!-- end info-panels -->
            </div><!-- end detail-grid -->

            <?php if ($_SESSION['role'] !== 'admin' && $memberInfo['trang_thai'] !== 'banned'): ?>
            <!-- Modal yêu cầu ban -->
            <div id="banModalDetail" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:var(--bg-card, #1e293b); border:1px solid var(--border, #334155); border-radius:16px; padding:2rem; width:90%; max-width:460px; box-shadow:0 20px 60px rgba(0,0,0,0.4);">
                    <h3 style="margin:0 0 0.5rem; color:var(--text-primary, #fff);">⚠️ Yêu Cầu Ban Hội Viên</h3>
                    <p style="color:var(--text-muted, #94a3b8); font-size:0.85rem; margin-bottom:1rem;">
                        Hội viên: <strong style="color:var(--text-primary, #fff);"><?= htmlspecialchars($nameDisplay) ?></strong>
                    </p>
                    <form action="<?= SITE_URL ?>/admin/members/ban-request" method="POST" onsubmit="return validateBanFormDetail()">
                        <input type="hidden" name="ma_hoi_vien" value="<?= $memberInfo['ma_hoi_vien'] ?>">
                        <div style="margin-bottom:1rem;">
                            <label style="display:block; font-size:0.85rem; color:var(--text-primary, #fff); margin-bottom:0.4rem; font-weight:600;">Lý do báo cáo <span style="color:#ef4444;">*</span></label>
                            <textarea name="ly_do" id="banLyDoDetail" rows="4" required placeholder="Nhập lý do yêu cầu ban hội viên này..." style="width:100%; padding:0.7rem; border:1px solid var(--border, #334155); border-radius:8px; background:var(--bg-secondary, #0f172a); color:var(--text-primary, #fff); font-size:0.9rem; resize:vertical; font-family:inherit; box-sizing:border-box;"></textarea>
                            <small id="banLyDoErrorDetail" style="color:#ef4444; display:none; margin-top:0.3rem;">Vui lòng nhập lý do (tối thiểu 10 ký tự).</small>
                        </div>
                        <div style="display:flex; gap:0.8rem; justify-content:flex-end;">
                            <button type="button" onclick="closeBanModalDetail()" class="btn btn-secondary" style="padding:0.5rem 1.2rem; font-size:0.9rem;">Hủy</button>
                            <button type="submit" class="btn btn-primary" style="padding:0.5rem 1.2rem; font-size:0.9rem; background:#ef4444; border:none;">🚫 Gửi Yêu Cầu</button>
                        </div>
                    </form>
                </div>
            </div>
            <script>
            function openBanModalDetail() {
                document.getElementById('banLyDoDetail').value = '';
                document.getElementById('banLyDoErrorDetail').style.display = 'none';
                document.getElementById('banModalDetail').style.display = 'flex';
            }
            function closeBanModalDetail() {
                document.getElementById('banModalDetail').style.display = 'none';
            }
            function validateBanFormDetail() {
                var lyDo = document.getElementById('banLyDoDetail').value.trim();
                if (lyDo.length < 10) {
                    document.getElementById('banLyDoErrorDetail').style.display = 'block';
                    return false;
                }
                return confirm('Bạn có chắc muốn gửi yêu cầu ban hội viên này lên Admin?');
            }
            document.getElementById('banModalDetail').addEventListener('click', function(e) {
                if (e.target === this) closeBanModalDetail();
            });
            </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
