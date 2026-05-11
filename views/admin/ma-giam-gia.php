<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khuyến Mãi | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .modal-overlay { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); align-items: center; justify-content: center;}
        .modal-box { background-color: var(--bg-secondary); padding: 2rem; border-radius: 15px; width: 500px; border: 1px solid var(--border); max-width: 90%;}
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .modal-close { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; }
        .status-active { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .status-banned { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header">
                <div>
                    <h1>🏷️ Quản Lý Khuyến Mãi</h1>
                    <p class="text-muted">Quản lý mã giảm giá cho Gói Tập và Sản Phẩm.</p>
                </div>
                <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='flex'">+ Thêm Mã Giảm Giá</button>
            </header>

            <?php $__f = getFlash(); if ($__f): ?>
                <div class="alert alert-<?= $__f['type'] ?>">
                    <?= $__f['message'] ?>
                </div>
            <?php endif; ?>

                        <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                <?php foreach($promotions as $p): 
                    $isExpired = strtotime($p['ngay_het_han']) < time();
                    $isOut = $p['so_luong_con'] <= 0;
                    $isActive = (!$isExpired && !$isOut);
                    $discountLabel = ($p['phan_tram_giam'] > 0) ? $p['phan_tram_giam'] . '%' : number_format($p['so_tien_giam'] / 1000) . 'K';
                ?>
                <div class="glass-panel" style="padding: 0; overflow: hidden; border-radius: 16px; border: 1px solid <?= $isActive ? 'var(--gold-border)' : 'var(--border)' ?>; background: <?= $isActive ? 'linear-gradient(145deg, #ffffff, var(--gold-bg))' : 'white' ?>;">
                    <div style="padding: 20px; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <span class="badge" style="background: <?= $isActive ? 'var(--gold-dark)' : 'var(--text-muted)' ?>; color: white; font-size: 0.7rem; font-weight: 800; border-radius: 4px; padding: 3px 8px;">
                                <?= strtoupper($p['loai_ap_dung']) ?>
                            </span>
                            <?php if($isActive): ?>
                                <span style="font-size: 0.7rem; color: #10b981; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                                    <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></div> LIVE
                                </span>
                            <?php else: ?>
                                <span style="font-size: 0.7rem; color: var(--danger); font-weight: 800;">EXPIRED</span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 60px; height: 60px; background: white; border: 2px dashed var(--gold); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <div style="text-align: center;">
                                    <div style="font-size: 1.2rem; font-weight: 900; color: var(--gold-dark); line-height: 1;"><?= $discountLabel ?></div>
                                    <div style="font-size: 0.6rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">OFF</div>
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 1.1rem; font-weight: 900; color: var(--text-primary); letter-spacing: 1px; font-family: monospace;"><?= htmlspecialchars($p['code']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;"><?= htmlspecialchars($p['mo_ta']) ?></div>
                            </div>
                        </div>

                        <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 10px; background: rgba(0,0,0,0.02); border-radius: 8px;">
                            <div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Còn lại</div>
                                <div style="font-size: 0.85rem; font-weight: 700; color: <?= $p['so_luong_con'] < 10 ? 'var(--danger)' : 'var(--text-primary)' ?>;"><?= $p['so_luong_con'] ?> lượt</div>
                            </div>
                            <div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Hạn dùng</div>
                                <div style="font-size: 0.85rem; font-weight: 700;"><?= date('d/m/Y', strtotime($p['ngay_het_han'])) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="padding: 12px 20px; background: rgba(0,0,0,0.02); border-top: 1px solid var(--border-light); text-align: right;">
                        <form action="<?= SITE_URL ?>/admin/promotions/delete" method="POST" style="margin: 0;" onsubmit="return confirm('Xóa mã này?');">
                            <input type="hidden" name="ma_giam_gia" value="<?= $p['ma_giam_gia'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.75rem; border-radius: 6px;">Xóa mã</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($promotions)): ?>
                    <div class="glass-panel" style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">
                        Chưa có mã giảm giá nào được tạo.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Modal Thêm -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 style="margin:0;">Thêm Mã Khuyến Mãi Mới</h3>
                <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            </div>
            <form action="<?= SITE_URL ?>/admin/promotions/create" method="POST">
                <div class="form-group">
                    <label>Mã Code (viết liền, không dấu)</label>
                    <input type="text" name="code" class="form-control" required placeholder="VD: SUMMER2026" style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label>Loại Giảm Giá</label>
                    <select name="loai_giam_gia" class="form-control" id="loaiGiamGia" onchange="toggleGiamGia()">
                        <option value="phan_tram">Giảm theo Phần Trăm (%)</option>
                        <option value="so_tien">Giảm theo Số Tiền (VNĐ)</option>
                    </select>
                </div>
                <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group" id="divPhanTram">
                        <label>Phần trăm giảm (%)</label>
                        <input type="number" name="phan_tram_giam" class="form-control" min="1" max="100" value="10">
                    </div>
                    <div class="form-group" id="divSoTien" style="display:none;">
                        <label>Số tiền giảm (VNĐ)</label>
                        <input type="number" name="so_tien_giam" class="form-control" min="1000" step="1000" value="50000">
                    </div>
                    <div class="form-group">
                        <label>Số lượng sử dụng</label>
                        <input type="number" name="so_luong_con" class="form-control" required min="1" value="100">
                    </div>
                </div>
                <div class="form-group">
                    <label>Loại áp dụng</label>
                    <select name="loai_ap_dung" class="form-control" required>
                        <option value="all">Tất cả (Sản phẩm & Gói tập)</option>
                        <option value="package">Chỉ Gói Tập</option>
                        <option value="product">Chỉ Sản Phẩm</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngày hết hạn</label>
                    <input type="datetime-local" name="ngay_het_han" class="form-control" required min="<?= date('Y-m-d\TH:i') ?>">
                </div>
                <div class="form-group">
                    <label>Mô tả ngắn gọn</label>
                    <input type="text" name="mo_ta" class="form-control" placeholder="Mô tả nội dung khuyến mãi...">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:1rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function toggleGiamGia() {
            const loai = document.getElementById('loaiGiamGia').value;
            if(loai === 'phan_tram') {
                document.getElementById('divPhanTram').style.display = 'block';
                document.getElementById('divSoTien').style.display = 'none';
            } else {
                document.getElementById('divPhanTram').style.display = 'none';
                document.getElementById('divSoTien').style.display = 'block';
            }
        }
    </script>
</body>
</html>
