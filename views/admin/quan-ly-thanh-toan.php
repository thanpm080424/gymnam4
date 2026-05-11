<?php
/**
 * Quản Lý Thanh Toán — Admin/Staff
 * Flow: Hội viên đăng ký gói → chờ thanh toán → Admin xác nhận → Kích hoạt + Email
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thanh Toán | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
    <div class="admin-content">
        <div class="page-topbar">
            <div>
                <h1>💳 Quản Lý Thanh Toán</h1>
                <p>Xác nhận thu tiền và kích hoạt gói tập cho hội viên</p>
            </div>
        </div>

        <?php $__f = getFlash(); if ($__f): ?>
            <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>" style="margin-bottom:20px">
                <?= $__f['message'] ?>
            </div>
        <?php endif; ?>

        <!-- THỐNG KÊ NHANH -->
        <div class="grid" style="margin-bottom:24px">
            <div class="stat-card">
                <div class="stat-icon orange">⏳</div>
                <div>
                    <div class="stat-value" style="color:var(--warning)"><?= count($pendingPayments) ?></div>
                    <div class="stat-label">Chờ thanh toán</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div>
                    <div class="stat-value" style="color:var(--success)"><?= count($recentPayments) ?></div>
                    <div class="stat-label">Giao dịch gần đây</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon gold">💰</div>
                <div>
                    <div class="stat-value" style="color:var(--gold)">
                        <?= number_format(array_sum(array_column($recentPayments, 'so_tien')),0,',','.') ?>đ
                    </div>
                    <div class="stat-label">Tổng giao dịch tháng</div>
                </div>
            </div>
        </div>

        <!-- DANH SÁCH CHỜ THANH TOÁN -->
        <div class="card" style="margin-bottom:24px">
            <div class="card-header">
                ⏳ Chờ Thanh Toán
                <?php if (count($pendingPayments) > 0): ?>
                    <span class="badge badge-warning" style="margin-left:6px"><?= count($pendingPayments) ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($pendingPayments)): ?>
                <div style="text-align:center;padding:40px;color:var(--text-muted)">
                    <div style="font-size:40px;margin-bottom:8px">🎉</div>
                    Không có khoản thanh toán nào đang chờ
                </div>
            <?php else: ?>
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Hội viên</th>
                        <th>Gói tập</th>
                        <th>Số tiền</th>
                        <th>Ngày đăng ký</th>
                        <th>Hạn dự kiến</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingPayments as $p): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:36px;height:36px;border-radius:50%;background:var(--gold-bg);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--gold);font-size:14px;flex-shrink:0">
                                    <?= mb_strtoupper(mb_substr($p['ho_ten'] ?? $p['ten_dang_nhap'] ?? '?', 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($p['ho_ten'] ?? $p['ten_dang_nhap']) ?></div>
                                    <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($p['ten_dang_nhap'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td><strong><?= htmlspecialchars($p['ten_goi']) ?></strong></td>
                        <td style="color:var(--gold);font-weight:700;font-size:15px">
                            <?= number_format((float)($p['gia_tien'] ?? $p['gia_thanh_toan']),0,',','.') ?>đ
                        </td>
                        <td style="color:var(--text-muted);font-size:13px">
                            <?= $p['ngay_kich_hoat'] ? date('d/m/Y', strtotime($p['ngay_kich_hoat'])) : date('d/m/Y') ?>
                        </td>
                        <td style="font-size:13px;color:var(--success)">
                            <?= $p['ngay_ket_thuc'] ? date('d/m/Y', strtotime($p['ngay_ket_thuc'])) : '—' ?>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm"
                                    onclick="openPayModal(
                                        <?= (int)$p['ma_dang_ky'] ?>,
                                        <?= (float)($p['gia_tien'] ?? $p['gia_thanh_toan'] ?? 0) ?>,
                                        '<?= htmlspecialchars($p['ho_ten'] ?? $p['ten_dang_nhap'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($p['ten_goi'], ENT_QUOTES) ?>'
                                    )">
                                ✅ Xác nhận thu tiền
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- LỊCH SỬ THANH TOÁN GẦN ĐÂY -->
        <div class="card">
            <div class="card-header">📋 Lịch Sử Thanh Toán (20 gần nhất)</div>
            <?php if (empty($recentPayments)): ?>
                <div style="text-align:center;padding:40px;color:var(--text-muted)">Chưa có giao dịch nào.</div>
            <?php else: ?>
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Ngày TT</th>
                        <th>Hội viên</th>
                        <th>Gói tập</th>
                        <th>Số tiền</th>
                        <th>P.thức</th>
                        <th>Người thu</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPayments as $p): ?>
                    <tr>
                        <td style="font-size:12px;color:var(--text-muted)">
                            <?= $p['ngay_thanh_toan'] ? date('d/m/Y H:i', strtotime($p['ngay_thanh_toan'])) : '—' ?>
                        </td>
                        <td style="font-weight:600"><?= htmlspecialchars($p['ho_ten'] ?? $p['ten_dang_nhap'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($p['ten_goi'] ?? '—') ?></td>
                        <td style="color:var(--gold);font-weight:600"><?= number_format((float)$p['so_tien'],0,',','.') ?>đ</td>
                        <td>
                            <?php
                            $ptLabel = ['tien_mat'=>'Tiền mặt','chuyen_khoan'=>'Chuyển khoản','the'=>'Thẻ','cash'=>'Tiền mặt','vnpay'=>'VNPay'];
                            echo $ptLabel[$p['phuong_thuc']] ?? ucfirst($p['phuong_thuc']);
                            ?>
                        </td>
                        <td style="font-size:13px"><?= htmlspecialchars($p['nguoi_thu_ten'] ?? 'Hệ thống') ?></td>
                        <td>
                            <?php if (in_array($p['trang_thai'], ['success','thanh_cong'])): ?>
                                <span class="badge badge-success">✅ Thành công</span>
                            <?php elseif ($p['trang_thai'] === 'pending'): ?>
                                <span class="badge badge-warning">⏳ Chờ</span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?= htmlspecialchars($p['trang_thai']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL XÁC NHẬN THANH TOÁN -->
<div class="modal" id="payModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>💳 Xác Nhận Thu Tiền</h3>
            <button class="modal-close" onclick="closePayModal()">✕</button>
        </div>

        <div id="payInfo" style="background:var(--gold-bg);border:1px solid var(--gold-border);border-radius:10px;padding:16px;margin-bottom:20px;font-size:14px">
        </div>

        <form method="POST" action="<?= SITE_URL ?>/admin/payment-management/process" onsubmit="return handlePaySubmit(this)">
            <input type="hidden" name="ma_dang_ky" id="modal_ma_dang_ky">
            <input type="hidden" name="so_tien" id="modal_so_tien">

            <div class="form-group">
                <label>Phương thức thanh toán</label>
                <select name="phuong_thuc" class="form-control" required>
                    <option value="tien_mat">💵 Tiền mặt</option>
                    <option value="chuyen_khoan">🏦 Chuyển khoản</option>
                    <option value="the">💳 Thẻ ngân hàng</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ghi chú (tuỳ chọn)</label>
                <textarea name="ghi_chu" class="form-control" rows="2"
                          placeholder="Ví dụ: Khách thanh toán đủ, hoặc mã chuyển khoản..."></textarea>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="closePayModal()">Hủy</button>
                <button type="submit" class="btn btn-primary" id="btnPaySubmit">✅ Xác nhận thanh toán & Kích hoạt gói</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPayModal(maDangKy, soTien, hoTen, tenGoi) {
    document.getElementById('modal_ma_dang_ky').value = maDangKy;
    document.getElementById('modal_so_tien').value = soTien;
    document.getElementById('payInfo').innerHTML =
        '<p style="margin:4px 0"><strong>Hội viên:</strong> ' + hoTen + '</p>' +
        '<p style="margin:4px 0"><strong>Gói tập:</strong> ' + tenGoi + '</p>' +
        '<p style="margin:4px 0"><strong>Số tiền:</strong> <span style="color:var(--gold);font-weight:700;font-size:16px">' +
        soTien.toLocaleString('vi-VN') + 'đ</span></p>';
    // Reset nút submit khi mở modal
    var btn = document.getElementById('btnPaySubmit');
    btn.disabled = false;
    btn.innerHTML = '✅ Xác nhận thanh toán & Kích hoạt gói';
    document.getElementById('payModal').classList.add('show');
}
function closePayModal() {
    document.getElementById('payModal').classList.remove('show');
}
function handlePaySubmit(form) {
    var btn = document.getElementById('btnPaySubmit');
    if (btn.disabled) return false; // Đã nhấn rồi
    btn.disabled = true;
    btn.innerHTML = '⏳ Đang xử lý...';
    return true;
}
window.onclick = function(e) {
    const modal = document.getElementById('payModal');
    if (e.target === modal) closePayModal();
}
</script>
</body>
</html>
