<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Gói Tập | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        /* Đơn giản CSS Modal */
        .modal {
            display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; 
            background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px);
        }
        .modal-content {
            background-color: var(--bg-secondary); margin: 10% auto; padding: 2rem; border-radius: 15px; 
            width: 400px; border: 1px solid var(--border);
        }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: white; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header" style="justify-content: space-between;">
                <div>
                    <h1>💎 Quản Lý Gói Tập</h1>
                    <p class="text-muted">Danh sách các gói dịch vụ phân phối và quyền lợi thành viên</p>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()" style="padding: 12px 24px; border-radius: 12px; font-weight: 700; box-shadow: var(--shadow-md);">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> Thêm Gói Mới
                </button>
            </header>

            <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>

            <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px;">
                <?php foreach($packages as $p): ?>
                <div class="glass-panel" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s; cursor: default;">
                    <div style="padding: 24px; flex: 1; background: linear-gradient(135deg, rgba(201,153,63,0.05), transparent);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                            <div style="width: 48px; height: 48px; background: var(--gold-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                <?= $p['loai_goi'] === 'pt' ? '🤝' : '🏆' ?>
                            </div>
                            <span class="badge" style="background: <?= $p['loai_goi'] === 'pt' ? 'rgba(59, 130, 246, 0.12)' : 'var(--gold-bg)' ?>; color: <?= $p['loai_goi'] === 'pt' ? '#3b82f6' : 'var(--gold-dark)' ?>; border: 1px solid <?= $p['loai_goi'] === 'pt' ? 'rgba(59, 130, 246, 0.2)' : 'var(--gold-light)' ?>; text-transform: uppercase; letter-spacing: 1px; font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 6px;">
                                <?= $p['loai_goi'] === 'pt' ? 'Tập Lẻ PT' : 'Hội Viên' ?>
                            </span>
                        </div>

                        <h3 style="font-size: 1.3rem; font-weight: 900; color: var(--text-primary); margin-bottom: 8px;">
                            <?= htmlspecialchars($p['ten_goi']) ?>
                        </h3>
                        
                        <div style="font-size: 1.6rem; font-weight: 900; color: var(--gold-dark); margin-bottom: 24px;">
                            <?= number_format($p['gia_tien']) ?><span style="font-size: 0.9rem; font-weight: 700; margin-left: 4px;">VNĐ</span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                            <div style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; color: var(--text-primary);">
                                <i class="far fa-calendar-check" style="color: var(--gold); width: 20px;"></i>
                                <span>Thời hạn: <strong><?= $p['thoi_han_thang'] ?> tháng</strong></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; color: var(--text-primary);">
                                <i class="fas fa-user-tie" style="color: var(--gold); width: 20px;"></i>
                                <span>Dịch vụ PT: <strong><?= $p['so_buoi_pt'] ?> buổi</strong></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; color: var(--text-primary);">
                                <i class="fas fa-check-circle" style="color: var(--success); width: 20px;"></i>
                                <span><?= $p['loai_goi'] === 'pt' ? 'HLV kèm cặp 1-1' : 'Toàn quyền sử dụng thiết bị' ?></span>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 16px 24px; background: rgba(0,0,0,0.02); border-top: 1px solid var(--border-light); display: flex; justify-content: flex-end; gap: 12px;">
                        <button onclick='openEditModal(<?= json_encode($p) ?>)' class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 8px;">Sửa gói</button>
                        <form action="<?= SITE_URL ?>/admin/packages/delete" method="POST" style="margin: 0;" onsubmit="return confirm('Xóa gói tập này?')">
                            <input type="hidden" name="ma_goi" value="<?= $p['ma_goi'] ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 8px;">Xóa</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Unified Modal (Add/Edit) -->
        <div id="pkgModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(6px); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
            <div class="glass-panel" style="width: 100%; max-width: 500px; padding: 32px; box-shadow: var(--shadow-2xl); position: relative;">
                <button onclick="closeModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
                <h2 id="modalTitle" style="font-weight: 900; margin-bottom: 24px; font-size: 1.5rem;">💎 Thêm Gói Tập Mới</h2>
                
                <form id="pkgForm" action="<?= SITE_URL ?>/admin/packages/create" method="POST">
                    <input type="hidden" name="ma_goi" id="f_ma_goi">
                    
                    <div class="form-group">
                        <label class="label">Tên gói tập</label>
                        <input type="text" name="ten_goi" id="f_ten_goi" class="form-control" required placeholder="VD: Gói Gold 6 Tháng">
                    </div>

                    <div class="form-group">
                        <label class="label">Phân Loại Dịch Vụ</label>
                        <select name="loai_goi" id="f_loai_goi" class="form-control">
                            <option value="membership">Thẻ Hội viên theo tháng</option>
                            <option value="pt">Gói Tập riêng với HLV (PT Session)</option>
                        </select>
                    </div>

                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="label">Giá tiền (VNĐ)</label>
                            <input type="number" name="gia_tien" id="f_gia_tien" class="form-control" required min="0" step="1000">
                        </div>
                        <div class="form-group">
                            <label class="label">Thời hạn (Tháng)</label>
                            <input type="number" name="thoi_han" id="f_thoi_han" class="form-control" required min="1" value="1">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Số buổi PT tặng kèm / buổi PT</label>
                        <input type="number" name="so_pt" id="f_so_pt" class="form-control" required min="0" value="0">
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 32px;">
                        <button type="button" onclick="closeModal()" class="btn btn-secondary" style="flex: 1; padding: 14px; border-radius: 12px; font-weight: 700;">Hủy</button>
                        <button type="submit" class="btn btn-primary" style="flex: 2; padding: 14px; border-radius: 12px; font-weight: 700;">💾 Lưu Gói Tập</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const PKG_MODAL = document.getElementById('pkgModal');
            const PKG_FORM = document.getElementById('pkgForm');

            function openAddModal() {
                document.getElementById('modalTitle').textContent = '💎 Thêm Gói Tập Mới';
                PKG_FORM.action = '<?= SITE_URL ?>/admin/packages/create';
                PKG_FORM.reset();
                document.getElementById('f_ma_goi').value = '';
                PKG_MODAL.style.display = 'flex';
            }

            function openEditModal(data) {
                document.getElementById('modalTitle').textContent = '📝 Chỉnh Sửa Gói Tập';
                PKG_FORM.action = '<?= SITE_URL ?>/admin/packages/update';
                document.getElementById('f_ma_goi').value = data.ma_goi;
                document.getElementById('f_ten_goi').value = data.ten_goi;
                document.getElementById('f_loai_goi').value = data.loai_goi;
                document.getElementById('f_thoi_han').value = data.thoi_han_thang;
                document.getElementById('f_gia_tien').value = data.gia_tien;
                document.getElementById('f_so_pt').value = data.so_buoi_pt;
                PKG_MODAL.style.display = 'flex';
            }

            function closeModal() {
                PKG_MODAL.style.display = 'none';
            }

            PKG_MODAL.addEventListener('click', e => { if(e.target === PKG_MODAL) closeModal(); });
        </script>
</body>
</html>
