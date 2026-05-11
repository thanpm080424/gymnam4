<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <div class="mg-page-header animate-up">
        <h1>Lịch sử mua hàng 📜</h1>
        <p>Xem lại tất cả các giao dịch và đơn hàng bạn đã thực hiện.</p>
    </div>

    <style>
        .history-card { background: #fff; border-radius: 24px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .history-table { width: 100%; border-collapse: collapse; }
        .history-table th { background: #F8FAFC; padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        .history-table td { padding: 20px; border-bottom: 1px solid #F1F5F9; font-size: 14px; }
        .history-table tr:last-child td { border-bottom: none; }
        
        .type-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .type-package { background: #EEF2FF; color: #6366F1; }
        .type-product { background: #F0FDF4; color: #22C55E; }
        
        .status-badge { padding: 4px 10px; border-radius: 100px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .status-completed { background: #DCFCE7; color: #166534; }
        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-cancelled { background: #FEE2E2; color: #991B1B; }

        .empty-history { text-align: center; padding: 80px 20px; background: #fff; border-radius: 32px; border: 1px solid var(--border); }
    </style>

    <div class="animate-up">
        <?php if (empty($transactions)): ?>
            <div class="empty-history">
                <div style="font-size: 60px; margin-bottom: 20px;">📦</div>
                <h3 style="font-weight: 900; margin-bottom: 10px;">Chưa có giao dịch nào</h3>
                <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 30px;">Bạn chưa thực hiện bất kỳ giao dịch nào trên Monkey Gym.</p>
                <a href="<?= SITE_URL ?>/member/store" class="mg-btn mg-btn-primary" style="padding: 16px 32px;">ĐẾN CỬA HÀNG NGAY 🛒</a>
            </div>
        <?php else: ?>
            <div class="history-card">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Giao dịch</th>
                            <th>Ngày mua</th>
                            <th>Mã GD</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tran): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <div class="type-icon <?= $tran['type'] === 'package' ? 'type-package' : 'type-product' ?>">
                                            <?= $tran['type'] === 'package' ? '💎' : '🥛' ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 800; color: var(--text);"><?= htmlspecialchars($tran['ten_sp']) ?></div>
                                            <div style="font-size: 11px; color: var(--text-muted);"><?= $tran['type'] === 'package' ? 'Gói tập' : 'Sản phẩm' ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted); font-weight: 600;">
                                    <?= !empty($tran['ngay_mua']) ? date('d/m/Y H:i', strtotime($tran['ngay_mua'])) : 'N/A' ?>
                                </td>
                                <td style="font-family: monospace; font-weight: 700; color: var(--text-muted);">
                                    <?= htmlspecialchars($tran['ma_giao_dich'] ?? 'N/A') ?>
                                </td>
                                <td style="font-weight: 900; color: var(--gold);">
                                    <?= number_format($tran['gia_tien'], 0, ',', '.') ?>đ
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $tran['trang_thai'] ?>">
                                        <?= $tran['trang_thai'] === 'completed' ? 'Hoàn tất' : ($tran['trang_thai'] === 'pending' ? 'Chờ xử lý' : 'Đã hủy') ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($tran['type'] === 'product'): ?>
                                        <a href="<?= SITE_URL ?>/member/receipt?ma_gd=<?= $tran['ma_giao_dich'] ?>" class="mg-btn" style="padding: 6px 12px; font-size: 11px;">Chi tiết</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
