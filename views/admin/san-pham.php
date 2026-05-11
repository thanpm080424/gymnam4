<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm TPCN | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px);}
        .modal-content { background-color: var(--bg-secondary); margin: 10% auto; padding: 2rem; border-radius: 15px; width: 400px; border: 1px solid var(--border);}
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header" style="justify-content: space-between;">
                <div>
                    <h1>💊 Quản Lý Hàng Hoá</h1>
                    <p class="text-muted">Quản lý kho thực phẩm bổ sung, giá bán và hình ảnh sản phẩm</p>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()" style="padding: 12px 24px; border-radius: 12px; font-weight: 700; box-shadow: var(--shadow-md);">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> Thêm Sản Phẩm
                </button>
            </header>

            <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>

            <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
                <?php foreach($products as $p): ?>
                <div class="glass-panel" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s; cursor: default;">
                    <div style="position: relative;">
                        <?php if(!empty($p['hinh_anh'])): ?>
                            <img src="<?= ASSET_URL . htmlspecialchars($p['hinh_anh']) ?>" alt="<?= htmlspecialchars($p['ten_sp']) ?>" style="width: 100%; height: 220px; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 220px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 3rem;">📦</div>
                        <?php endif; ?>
                        
                        <div style="position: absolute; top: 12px; right: 12px;">
                            <span class="badge" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                Kho: <?= $p['ton_kho'] ?>
                            </span>
                        </div>
                    </div>

                    <div style="padding: 20px; flex: 1;">
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($p['ten_sp']) ?>
                        </h3>
                        
                        <div style="font-size: 1.5rem; font-weight: 900; color: var(--gold-dark); margin-bottom: 16px;">
                            <?= number_format($p['gia_tien']) ?><span style="font-size: 0.8rem; font-weight: 700; margin-left: 4px;">VNĐ</span>
                        </div>

                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($p['mo_ta']) ?>
                        </p>
                    </div>

                    <div style="padding: 16px 20px; background: rgba(0,0,0,0.02); border-top: 1px solid var(--border-light); display: flex; justify-content: flex-end; gap: 12px;">
                        <button onclick='openEditModal(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>)' class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 8px;">Sửa</button>
                        <form action="<?= SITE_URL ?>/admin/products/delete" method="POST" style="margin: 0;" onsubmit="return confirm('Xóa sản phẩm này?')">
                            <input type="hidden" name="ma_sp" value="<?= $p['ma_sp'] ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 8px;">Xóa</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if(empty($products)): ?>
                <div class="glass-panel" style="grid-column: 1 / -1; text-align: center; padding: 80px 20px;">
                    <div style="font-size: 4rem; margin-bottom: 20px;">📦</div>
                    <h3 style="font-weight: 800;">Kho hàng trống</h3>
                    <p class="text-muted">Hãy thêm sản phẩm đầu tiên vào hệ thống.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Unified Product Modal (Add/Edit) -->
        <div id="productModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(6px); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
            <div class="glass-panel" style="width: 100%; max-width: 540px; padding: 32px; box-shadow: var(--shadow-2xl); position: relative; max-height: 90vh; overflow-y: auto;">
                <button onclick="closeModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
                <h2 id="modalTitle" style="font-weight: 900; margin-bottom: 24px; font-size: 1.5rem;">💊 Thêm Sản Phẩm Mới</h2>
                
                <form id="productForm" action="<?= SITE_URL ?>/admin/products/create" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="ma_sp" id="f_ma_sp">
                    
                    <div class="form-group">
                        <label class="label">Tên sản phẩm</label>
                        <input type="text" name="ten_sp" id="f_ten_sp" class="form-control" required placeholder="VD: Whey Protein Gold Standard 5lbs">
                    </div>

                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="label">Giá bán (VNĐ)</label>
                            <input type="number" name="gia_tien" id="f_gia_tien" class="form-control" required min="0" step="1000">
                        </div>
                        <div class="form-group">
                            <label class="label">Tồn kho (Số lượng)</label>
                            <input type="number" name="ton_kho" id="f_ton_kho" class="form-control" required min="0" value="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Mô tả sản phẩm</label>
                        <textarea name="mo_ta" id="f_mo_ta" class="form-control" rows="4" placeholder="Thông tin chi tiết về sản phẩm..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="label">Hình ảnh sản phẩm</label>
                        <input type="file" name="hinh_anh" id="f_hinh_anh" class="form-control" accept="image/*" onchange="previewImg(this)">
                        <div id="imgPreviewContainer" style="display: none; margin-top: 15px; position: relative;">
                            <img id="imgPreview" src="" style="width: 100%; height: 180px; object-fit: contain; border-radius: 12px; border: 1px solid var(--border); background: #f9f9f9;">
                            <button type="button" onclick="clearPreview()" style="position: absolute; top: 10px; right: 10px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer;">&times;</button>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 32px;">
                        <button type="button" onclick="closeModal()" class="btn btn-secondary" style="flex: 1; padding: 14px; border-radius: 12px; font-weight: 700;">Hủy</button>
                        <button type="submit" class="btn btn-primary" style="flex: 2; padding: 14px; border-radius: 12px; font-weight: 700;">💾 Lưu Sản Phẩm</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const PRODUCT_MODAL = document.getElementById('productModal');
            const PRODUCT_FORM = document.getElementById('productForm');
            const IMG_PREVIEW = document.getElementById('imgPreview');
            const IMG_CONTAINER = document.getElementById('imgPreviewContainer');
            const ASSET_URL = '<?= ASSET_URL ?>';

            function openAddModal() {
                document.getElementById('modalTitle').textContent = '💊 Thêm Sản Phẩm Mới';
                PRODUCT_FORM.action = '<?= SITE_URL ?>/admin/products/create';
                PRODUCT_FORM.reset();
                document.getElementById('f_ma_sp').value = '';
                clearPreview();
                PRODUCT_MODAL.style.display = 'flex';
            }

            function openEditModal(data) {
                document.getElementById('modalTitle').textContent = '📝 Chỉnh Sửa Sản Phẩm';
                PRODUCT_FORM.action = '<?= SITE_URL ?>/admin/products/update';
                document.getElementById('f_ma_sp').value = data.ma_sp;
                document.getElementById('f_ten_sp').value = data.ten_sp;
                document.getElementById('f_gia_tien').value = data.gia_tien;
                document.getElementById('f_ton_kho').value = data.ton_kho;
                document.getElementById('f_mo_ta').value = data.mo_ta;
                
                if (data.hinh_anh) {
                    IMG_PREVIEW.src = ASSET_URL + data.hinh_anh;
                    IMG_CONTAINER.style.display = 'block';
                } else {
                    clearPreview();
                }
                
                PRODUCT_MODAL.style.display = 'flex';
            }

            function closeModal() {
                PRODUCT_MODAL.style.display = 'none';
            }

            function previewImg(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        IMG_PREVIEW.src = e.target.result;
                        IMG_CONTAINER.style.display = 'block';
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function clearPreview() {
                document.getElementById('f_hinh_anh').value = '';
                IMG_CONTAINER.style.display = 'none';
                IMG_PREVIEW.src = '';
            }

            PRODUCT_MODAL.addEventListener('click', e => { if(e.target === PRODUCT_MODAL) closeModal(); });
        </script>
</body>
</html>
