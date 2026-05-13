<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tủ Đồ | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
    <div class="admin-content">
        <header class="dashboard-header" style="justify-content:space-between;">
            <h1>🔒 Quản Lý Tủ Đồ</h1>
            <button class="btn btn-primary" onclick="document.getElementById('addLockerModal').style.display='flex'">+ Thêm tủ mới</button>
        </header>
        <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>
        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 2rem;">
            <!-- Yêu cầu chờ duyệt -->
            <div class="glass-panel" style="display: flex; flex-direction: column; border-left: 5px solid #f59e0b;">
                <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.2rem;">⏳</span> Yêu Cầu Chờ Duyệt (<?= count($pendingRequests) ?>)
                </h3>
                <?php if(empty($pendingRequests)): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted); background: rgba(0,0,0,0.02); border-radius: 12px; flex: 1; display: flex; align-items: center; justify-content: center;">
                        Không có yêu cầu nào.
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach($pendingRequests as $req): ?>
                        <div style="border: 1px solid rgba(245,158,11,0.2); border-radius: 12px; padding: 16px; background: rgba(245,158,11,0.03); transition: transform 0.2s;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 800; color: var(--text-primary);"><?= htmlspecialchars($req['ten_dang_nhap']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                        Gửi lúc: <?= date('d/m/Y H:i', strtotime($req['created_at'])) ?>
                                    </div>
                                    <div style="display: flex; gap: 6px; margin-top: 6px;">
                                        <div style="font-size: 11px; font-weight: 800; color: var(--gold-dark); background: rgba(201,153,63,0.1); padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                            📅 <?= (int)($req['so_thang'] ?? 1) ?> THÁNG
                                        </div>
                                        <div style="font-size: 11px; font-weight: 800; color: #3b82f6; background: rgba(59,130,246,0.1); padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                            📦 SIZE <?= htmlspecialchars($req['loai_tu_mong_muon'] ?? 'M') ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn btn-primary btn-sm" onclick="openApprove(<?= $req['ma_yc'] ?>)">✅ Duyệt</button>
                                    <form action="<?= SITE_URL ?>/admin/lockers/reject" method="POST" style="margin: 0;">
                                        <input type="hidden" name="ma_yc" value="<?= $req['ma_yc'] ?>">
                                        <button class="btn btn-secondary btn-sm" style="color: #ef4444; border-color: #fca5a5;">❌ Từ chối</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Danh sách tủ -->
            <div class="glass-panel" style="display: flex; flex-direction: column; border-left: 5px solid #3b82f6;">
                <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.2rem;">🗄️</span> Tủ Đang Cho Thuê (<?= count($rentedLockers) ?>)
                </h3>
                <?php if(empty($rentedLockers)): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted); background: rgba(0,0,0,0.02); border-radius: 12px; flex: 1; display: flex; align-items: center; justify-content: center;">
                        Chưa có tủ nào đang cho thuê.
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach($rentedLockers as $l): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid var(--border-light); background: rgba(255,255,255,0.4); border-radius: 8px;">
                            <div>
                                <div style="font-weight: 800; color: var(--gold-dark);">Tủ <?= htmlspecialchars($l['so_tu']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-primary); margin-top: 2px;"><?= htmlspecialchars($l['ten_dang_nhap']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($l['ngay_ket_thuc'])) ?></div>
                            </div>
                            <form action="<?= SITE_URL ?>/admin/lockers/revoke" method="POST" style="margin: 0;" onsubmit="return confirm('Thu hồi tủ của khách này?')">
                                <input type="hidden" name="ma_yc" value="<?= $l['ma_yc'] ?>">
                                <button class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">Thu hồi</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>


                <!-- Danh sách tất cả tủ -->
        <div class="glass-panel">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.2rem;">📋</span> Tất cả Tủ (<?= count($allLockers) ?>)
            </h3>
            <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px;">
                <?php foreach($allLockers as $l): 
                    $statusStyles = [
                        'trong' => ['#10b981', 'rgba(16, 185, 129, 0.1)', 'TRỐNG'],
                        'dang_thue' => ['#ef4444', 'rgba(239, 68, 68, 0.1)', 'ĐANG THUÊ'],
                        'bao_tri' => ['#f59e0b', 'rgba(245, 158, 11, 0.1)', 'BẢO TRÌ']
                    ];
                    $st = $statusStyles[$l['trang_thai']] ?? ['gray', 'rgba(0,0,0,0.05)', $l['trang_thai']];
                ?>
                <div style="padding: 20px; border-radius: 16px; border: 1px solid var(--border-light); background: white; text-align: center; position: relative;">
                    <div style="position: absolute; top: 12px; right: 12px;">
                        <span style="font-size: 0.65rem; font-weight: 800; color: <?= $st[0] ?>; background: <?= $st[1] ?>; padding: 4px 8px; border-radius: 6px;">
                            <?= $st[2] ?>
                        </span>
                    </div>
                    
                    <div style="width: 50px; height: 50px; background: var(--bg-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.5rem;">
                        🔒
                    </div>
                    
                    <div style="font-size: 1.2rem; font-weight: 900; color: var(--text-primary);">Tủ <?= htmlspecialchars($l['so_tu']) ?></div>
                    <div style="margin-top: 8px;">
                        <span style="font-size: 0.7rem; font-weight: 800; background: #F1F5F9; padding: 2px 8px; border-radius: 4px; color: #64748B;">SIZE <?= $l['loai_tu'] ?></span>
                    </div>
                    <div style="font-size: 0.9rem; font-weight: 700; color: var(--gold-dark); margin-top: 4px;"><?= number_format($l['gia_thue_thang']) ?>đ/tháng</div>
                    
                    <?php if(!empty($l['ghi_chu'])): ?>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--border-light);">
                        <?= htmlspecialchars($l['ghi_chu']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

<!-- Modal duyệt (chọn tủ) -->
<div id="approveModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:20px; padding:2rem; max-width:400px; width:90%;">
        <h3 style="margin-bottom:1.5rem;">Gán tủ cho hội viên</h3>
        <form action="<?= SITE_URL ?>/admin/lockers/approve" method="POST">
            <input type="hidden" name="ma_yc" id="approveYcId">
            <div class="form-group">
                <label>Chọn tủ trống</label>
                <select name="ma_tu" class="form-control" required>
                    <?php foreach($availableLockers as $al): ?>
                    <option value="<?= $al['ma_tu'] ?>">Tủ <?= htmlspecialchars($al['so_tu']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Xác nhận</button>
                <button type="button" class="btn btn-secondary" style="flex:1;" onclick="document.getElementById('approveModal').style.display='none'">Huỷ</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal thêm tủ mới -->
<div id="addLockerModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:20px; padding:2rem; max-width:400px; width:90%;">
        <h3 style="margin-bottom:1.5rem;">➕ Thêm Tủ Mới</h3>
        <form action="<?= SITE_URL ?>/admin/lockers/create" method="POST">
            <div class="form-group">
                <label>Số tủ (VD: A01, B12)</label>
                <input type="text" name="so_tu" class="form-control" required placeholder="A01">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Kích thước</label>
                    <select name="loai_tu" class="form-control" onchange="updatePrice(this.value)">
                        <option value="S">Size S (Nhỏ)</option>
                        <option value="M" selected>Size M (Vừa)</option>
                        <option value="L">Size L (Lớn)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Giá thuê/tháng</label>
                    <input type="number" name="gia_thue" id="add_gia_thue" class="form-control" value="100000" required>
                </div>
            </div>
            <div class="form-group">
                <label>Ghi chú (Vị trí)</label>
                <input type="text" name="ghi_chu" class="form-control" placeholder="Dãy A - Khu vực thay đồ nam">
            </div>
            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Thêm tủ</button>
                <button type="button" class="btn btn-secondary" style="flex:1;" onclick="document.getElementById('addLockerModal').style.display='none'">Huỷ</button>
            </div>
        </form>
        <script>
            function updatePrice(size) {
                const priceMap = { 'S': 50000, 'M': 100000, 'L': 150000 };
                document.getElementById('add_gia_thue').value = priceMap[size];
            }
        </script>
    </div>
</div>

<style>
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); z-index:9999; display:flex; align-items:center; justify-content:center; }
</style>
<script>
function openApprove(ycId) {
    document.getElementById('approveYcId').value = ycId;
    document.getElementById('approveModal').style.display = 'flex';
}
</script>
</body>
</html>
