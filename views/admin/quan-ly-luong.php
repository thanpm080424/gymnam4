<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Lương | Admin</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .payroll-container { padding: 2rem; background: var(--bg-primary); min-height: 100vh; color: var(--text-primary); font-family: 'Inter', sans-serif; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-size: 2rem; font-weight: 800; color: var(--gold); text-transform: uppercase; letter-spacing: 1px; }
        
        .btn-calc { background: var(--gold); color: #fff; padding: 12px 24px; border-radius: 8px; font-weight: 700; text-transform: uppercase; text-decoration: none; border: none; cursor: pointer; transition: 0.3s; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; gap: 8px; }
        .btn-calc:hover { background: var(--gold-dark); box-shadow: var(--shadow-lg); transform: translateY(-2px); }
        
        /* Stats Box */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: var(--bg-card); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
        .stat-label { color: var(--text-secondary); font-size: 0.9rem; font-weight: 600; text-transform: uppercase; margin-bottom: 10px; }
        .stat-value { font-size: 2.2rem; font-weight: 800; color: var(--text-primary); }
        
        /* Table */
        .table-box { background: var(--bg-card); padding: 2rem; border-radius: 16px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); overflow-x: auto; }
        .payroll-table { width: 100%; border-collapse: collapse; }
        .payroll-table th { text-align: left; padding: 15px; background: var(--bg-primary); color: var(--text-secondary); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        .payroll-table td { padding: 15px; border-bottom: 1px solid var(--border-light); font-size: 0.95rem; color: var(--text-primary); }
        
        .amount { font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--text-primary); }
        .total-amount { color: var(--gold); font-size: 1.2rem; }
        
        .status-badge { padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: bold; }
        .status-unpaid { background: var(--danger-bg); color: var(--danger); border: 1px solid rgba(220, 38, 38, 0.2); }
        .status-paid { background: var(--success-bg); color: var(--success); border: 1px solid rgba(22, 163, 74, 0.2); }
 
        .alert { padding: 15px; background: var(--success-bg); color: var(--success); border: 1px solid var(--success); border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <div class="payroll-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Bảng Lương & Hoa Hồng</h1>
                <p style="color: var(--text-muted);">Kỳ lương: Tháng <?= htmlspecialchars($month) ?></p>
            </div>
            <a href="<?= SITE_URL ?>/admin/payroll/calculate" class="btn-calc" onclick="return confirm('Hệ thống sẽ quét lịch dạy và tính toán lại toàn bộ lương tháng này. Chắc chắn tiếp tục?')">
                ⚙️ CHỐT LƯƠNG THÁNG NÀY
            </a>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert">✓ Đã quét lịch dạy và tính toán xong bảng lương tháng này!</div>
        <?php endif; ?>
        <?php if(isset($_GET['paid'])): ?>
            <div class="alert" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.3);">✓ Đã xác nhận thanh toán lương thành công!</div>
        <?php endif; ?>

        <?php 
            // Tính nhẩm tổng quỹ lương để hiện lên Thống kê
            $tongQuy = 0; $tongBuoi = 0;
            foreach($payrolls as $p) { $tongQuy += $p['tong_luong']; $tongBuoi += $p['so_buoi_day']; }
        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Tổng Quỹ Lương Tháng</div>
                <div class="stat-value" style="color: #84cc16;"><?= number_format($tongQuy) ?>đ</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Tổng Buổi Đã Dạy</div>
                <div class="stat-value"><?= $tongBuoi ?> <span style="font-size: 1rem; color: var(--text-muted);">buổi</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Nhân sự nhận lương</div>
                <div class="stat-value"><?= count($payrolls) ?> <span style="font-size: 1rem; color: var(--text-muted);">người</span></div>
            </div>
        </div>

        <div class="table-box">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th>Mã HLV</th>
                        <th>Họ Tên</th>
                        <th>Lương Cứng</th>
                        <th>Buổi Dạy</th>
                        <th>Hoa Hồng PT</th>
                        <th>Thực Lãnh</th>
                        <th>Trạng Thái</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($payrolls)): ?>
                        <?php foreach($payrolls as $p): ?>
                            <tr>
                                <td style="color: var(--text-muted);">#<?= $p['ma_hlv'] ?></td>
                                <td style="font-weight: 700; color: var(--text-primary);"><?= htmlspecialchars($p['ho_ten'] ?? $p['ten_dang_nhap']) ?></td>
                                <td class="amount"><?= number_format($p['luong_cung']) ?>đ</td>
                                <td><strong style="color: var(--text-primary);"><?= $p['so_buoi_day'] ?></strong> buổi</td>
                                <td class="amount"><?= number_format($p['thuong_hoa_hong']) ?>đ</td>
                                <td class="amount total-amount"><?= number_format($p['tong_luong']) ?>đ</td>
                                <td>
                                    <?php if($p['trang_thai'] == 'chua_thanh_toan'): ?>
                                        <span class="status-badge status-unpaid">Chưa thanh toán</span>
                                    <?php else: ?>
                                        <span class="status-badge status-paid">Đã thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($p['trang_thai'] == 'chua_thanh_toan'): ?>
                                        <a href="<?= SITE_URL ?>/admin/payroll/pay?id=<?= $p['ma_luong'] ?>" 
                                           onclick="return confirm('Xác nhận đã thanh toán lương cho HLV này?')"
                                           style="color: #3b82f6; font-size: 0.9rem; font-weight: bold; text-decoration: none;">✅ Xác nhận thanh toán</a>
                                    <?php else: ?>
                                        <span style="color: #a1a1aa; font-size: 0.8rem;">✓ Hoàn tất</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align: center; color: #a1a1aa; padding: 3rem;">Chưa có dữ liệu bảng lương tháng này. Hãy bấm "Chốt Lương" để hệ thống tính toán.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
        </div>
    </div>
</body>
</html>
