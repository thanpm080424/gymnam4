<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Báo Hệ Thống | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        <div class="admin-content">
            <header class="dashboard-header" style="justify-content: space-between;">
                <div>
                    <h1>📢 Thông Báo Hệ Thống</h1>
                    <p class="text-muted">Quản lý tin tức, khuyến mãi và thông báo lịch tập cho hội viên</p>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()" style="padding: 12px 24px; border-radius: 12px; font-weight: 700; box-shadow: var(--shadow-md);">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> Thêm Thông Báo
                </button>
            </header>

            <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>

            <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px;">
                <?php foreach($announcements as $a): ?>
                <div class="glass-panel" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s; border-color: <?= $a['trang_thai'] === 'active' ? 'var(--success)' : 'var(--border)' ?>;">
                    <div style="padding: 24px; flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                            <span class="badge" style="background: var(--gold-bg); color: var(--gold-dark); border: 1px solid var(--gold-light); text-transform: uppercase; letter-spacing: 1px; font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 6px;">
                                THÔNG BÁO
                            </span>
                            <?php if($a['trang_thai'] === 'active'): ?>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981; animation: pulse 2s infinite;"></div>
                                <span style="font-size: 0.7rem; font-weight: 800; color: #10b981;">LIVE</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 10px; color: var(--text-primary); line-height: 1.4;">
                            <?= htmlspecialchars($a['tieu_de']) ?>
                        </h3>
                        
                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($a['noi_dung']) ?>
                        </p>

                        <?php if(!empty($a['hinh_anh'])): ?>
                        <div style="margin-bottom: 20px; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-light);">
                            <img src="<?= ASSET_URL . htmlspecialchars($a['hinh_anh']) ?>" alt="Ảnh thông báo" style="width: 100%; height: 160px; object-fit: cover;">
                        </div>
                        <?php endif; ?>

                        <div style="display: flex; align-items: center; gap: 10px; font-size: 0.75rem; color: var(--text-muted); margin-top: auto;">
                            <i class="far fa-clock"></i>
                            <span><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></span>
                        </div>
                    </div>

                    <div style="padding: 16px 24px; background: rgba(0,0,0,0.02); border-top: 1px solid var(--border-light); display: flex; justify-content: flex-end; gap: 12px;">
                        <button onclick='openEditModal(<?= json_encode($a, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>)' class="btn btn-secondary" style="padding: 6px 14px; font-size: 0.75rem; border-radius: 8px;">Sửa</button>
                        
                        <form action="<?= SITE_URL ?>/admin/announcements/toggle" method="POST" style="margin: 0;">
                            <input type="hidden" name="ma_tb" value="<?= $a['ma_tb'] ?>">
                            <input type="hidden" name="trang_thai" value="<?= $a['trang_thai'] === 'active' ? 'inactive' : 'active' ?>">
                            <button type="submit" class="btn <?= $a['trang_thai'] === 'active' ? 'btn-outline' : 'btn-primary' ?>" style="padding: 6px 14px; font-size: 0.75rem; border-radius: 8px;">
                                <?= $a['trang_thai'] === 'active' ? 'Tắt' : 'Kích hoạt' ?>
                            </button>
                        </form>

                        <form action="<?= SITE_URL ?>/admin/announcements/delete" method="POST" style="margin: 0;" onsubmit="return confirm('Xóa thông báo này?')">
                            <input type="hidden" name="ma_tb" value="<?= $a['ma_tb'] ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 6px 14px; font-size: 0.75rem; border-radius: 8px;">Xóa</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if(empty($announcements)): ?>
                <div class="glass-panel" style="grid-column: 1 / -1; text-align: center; padding: 80px 20px;">
                    <div style="font-size: 4rem; margin-bottom: 20px;">📢</div>
                    <h3 style="font-weight: 800;">Chưa có thông báo nào</h3>
                    <p class="text-muted">Hãy tạo thông báo đầu tiên để cập nhật tin tức cho hội viên.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add/Edit Modal (Unified & Premium) -->
        <div id="annoModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(6px); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
            <div class="glass-panel" style="width: 100%; max-width: 540px; padding: 32px; box-shadow: var(--shadow-2xl); position: relative; max-height: 90vh; overflow-y: auto;">
                <button onclick="closeModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
                <h2 id="modalTitle" style="font-weight: 900; margin-bottom: 24px; font-size: 1.5rem;">📢 Tạo Thông Báo Mới</h2>
                
                <form id="annoForm" action="<?= SITE_URL ?>/admin/announcements/create" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="ma_tb" id="f_ma_tb">
                    <input type="hidden" name="xoa_anh" id="f_xoa_anh" value="">
                    
                    <div class="form-group">
                        <label class="label">Tiêu đề thông báo</label>
                        <input type="text" name="tieu_de" id="f_tieu_de" class="form-control" required placeholder="VD: Thông báo nghỉ lễ 30/4...">
                    </div>

                    <div class="form-group">
                        <label class="label">Nội dung chi tiết</label>
                        <textarea name="noi_dung" id="f_noi_dung" class="form-control" rows="5" required placeholder="Nội dung sẽ hiển thị trên trang chủ hội viên..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="label">Hình ảnh đính kèm (Tùy chọn)</label>
                        <input type="file" name="hinh_anh" id="f_hinh_anh" class="form-control" accept="image/*" onchange="previewImg(this)">
                        <div id="imgPreviewContainer" style="display: none; margin-top: 15px; position: relative;">
                            <img id="imgPreview" src="" style="width: 100%; height: 180px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border);">
                            <button type="button" onclick="clearPreview()" style="position: absolute; top: 10px; right: 10px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer;">&times;</button>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 32px;">
                        <button type="button" onclick="closeModal()" class="btn btn-secondary" style="flex: 1; padding: 14px; border-radius: 12px; font-weight: 700;">Hủy</button>
                        <button type="submit" class="btn btn-primary" style="flex: 2; padding: 14px; border-radius: 12px; font-weight: 700;">💾 Lưu Thông Báo</button>
                    </div>
                </form>
            </div>
        </div>

        <style>
            @keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
        </style>

        <script>
            const ANNO_MODAL = document.getElementById('annoModal');
            const ANNO_FORM = document.getElementById('annoForm');
            const IMG_PREVIEW = document.getElementById('imgPreview');
            const IMG_CONTAINER = document.getElementById('imgPreviewContainer');
            const ASSET_URL = '<?= ASSET_URL ?>';

            function openAddModal() {
                document.getElementById('modalTitle').textContent = '📢 Tạo Thông Báo Mới';
                ANNO_FORM.action = '<?= SITE_URL ?>/admin/announcements/create';
                ANNO_FORM.reset();
                document.getElementById('f_ma_tb').value = '';
                clearPreview();
                ANNO_MODAL.style.display = 'flex';
            }

            function openEditModal(data) {
                document.getElementById('modalTitle').textContent = '📝 Chỉnh Sửa Thông Báo';
                ANNO_FORM.action = '<?= SITE_URL ?>/admin/announcements/update';
                document.getElementById('f_ma_tb').value = data.ma_tb;
                document.getElementById('f_tieu_de').value = data.tieu_de;
                document.getElementById('f_noi_dung').value = data.noi_dung;
                document.getElementById('f_xoa_anh').value = '';
                
                if (data.hinh_anh) {
                    IMG_PREVIEW.src = ASSET_URL + data.hinh_anh;
                    IMG_CONTAINER.style.display = 'block';
                } else {
                    clearPreview();
                }
                
                ANNO_MODAL.style.display = 'flex';
            }

            function closeModal() {
                ANNO_MODAL.style.display = 'none';
            }

            function previewImg(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        IMG_PREVIEW.src = e.target.result;
                        IMG_CONTAINER.style.display = 'block';
                        document.getElementById('f_xoa_anh').value = '';
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function clearPreview() {
                document.getElementById('f_hinh_anh').value = '';
                IMG_CONTAINER.style.display = 'none';
                IMG_PREVIEW.src = '';
                document.getElementById('f_xoa_anh').value = '1';
            }

            ANNO_MODAL.addEventListener('click', e => { if(e.target === ANNO_MODAL) closeModal(); });
        </script>
</body>
</html>
