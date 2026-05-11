<?php
/**
 * View: Quản lý Nhân viên
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhân Viên - Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <style>
        .staff-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; }
        .staff-card { text-align: center; padding: 2rem 1.5rem; }
        .staff-avatar {
            width: 80px; height: 80px;
            background: var(--gold-bg);
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 32px; font-weight: bold;
            color: var(--gold-dark); margin: 0 auto 15px;
            border: 2px solid var(--gold-border);
        }
        .staff-name { font-size: 1.2rem; font-weight: 600; color: var(--text-primary); margin-bottom: 5px; }
        .staff-position { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px; }
        .staff-info { background: var(--bg-primary); border-radius: 8px; padding: 12px; margin-top: 15px; text-align: left; border: 1px solid var(--border-light); }
        .staff-info-item { display: flex; align-items: center; margin-bottom: 8px; font-size: 0.85rem; color: var(--text-secondary); }
        .staff-info-item:last-child { margin-bottom: 0; }
        .staff-info-item span.icon { width: 20px; color: var(--gold); margin-right: 8px; font-weight: bold; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 500; }
        .status-active { background: var(--success-bg); color: var(--success); border: 1px solid #BBF7D0; }
        .status-inactive { background: var(--danger-bg); color: var(--danger); border: 1px solid #FCA5A5; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stats-card { text-align: center; padding: 1.5rem; }
        .stats-number { font-size: 2rem; font-weight: bold; color: var(--gold); margin-bottom: 5px; }
        .stats-label { color: var(--text-muted); font-size: 0.9rem; font-weight: 500; }
        
        .action-buttons { display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem; }
        
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="page-topbar">
                <div>
                    <h1>Quản lý Nhân viên</h1>
                    <p>Danh sách toàn bộ nhân viên trong hệ thống.</p>
                </div>
                <button class="btn btn-primary" onclick="openModal('modalAddStaff')">+ Thêm nhân viên mới</button>
            </header>

            <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Thống kê -->
            <div class="stats-grid">
                <div class="card stats-card">
                    <div class="stats-number"><?= count($staff ?? []) ?></div>
                    <div class="stats-label">Tổng nhân viên</div>
                </div>
                <div class="card stats-card">
                    <div class="stats-number" style="color: var(--success);">
                        <?= count(array_filter($staff ?? [], fn($s) => ($s['trang_thai'] ?? 1) == 1)) ?>
                    </div>
                    <div class="stats-label">Đang hoạt động</div>
                </div>
                <div class="card stats-card">
                    <div class="stats-number" style="color: var(--info);"><?= count($departments ?? []) ?></div>
                    <div class="stats-label">Phòng ban</div>
                </div>
                <div class="card stats-card">
                    <div class="stats-number" style="color: var(--danger);">
                        <?= count(array_filter($staff ?? [], fn($s) => ($s['trang_thai'] ?? 1) == 0)) ?>
                    </div>
                    <div class="stats-label">Ngừng hoạt động</div>
                </div>
            </div>

            <!-- Danh sách nhân viên -->
            <?php if (empty($staff)): ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <h4 style="color: var(--text-muted);">Chưa có nhân viên nào</h4>
                    <p style="color: var(--text-muted);">Bắt đầu bằng cách thêm nhân viên đầu tiên vào hệ thống.</p>
                </div>
            <?php else: ?>
                <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                    <?php foreach ($staff as $s):
                        $fullName = trim((string)($s['ho_ten'] ?? $s['ten_dang_nhap'] ?? ''));
                        $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                        $initials = 'NV';
                        if (count($nameParts) >= 1) {
                            $initials = strtoupper(mb_substr($nameParts[0], 0, 1, 'UTF-8'));
                            if (count($nameParts) > 1) $initials .= strtoupper(mb_substr(end($nameParts), 0, 1, 'UTF-8'));
                        }
                        $status = ($s['trang_thai'] ?? 1) == 1 ? 'active' : 'inactive';
                        $statusText = $status == 'active' ? 'Đang làm việc' : 'Ngừng làm việc';
                        
                        $staffData = json_encode([
                            'maNhanVien' => $s['ma_nhan_vien'] ?? '',
                            'maNguoiDung' => $s['ma_nguoi_dung'] ?? '',
                            'hoTen' => $s['ho_ten'] ?? '',
                            'email' => $s['email'] ?? $s['ten_dang_nhap'] ?? '',
                            'sdt' => $s['so_dien_thoai'] ?? '',
                            'chucVu' => $s['chuc_vu'] ?? '',
                            'maPhongBan' => $s['ma_phong_ban'] ?? '',
                            'tenPhongBan' => $s['ten_phong_ban'] ?? '',
                            'ngayVaoLam' => $s['ngay_vao_lam'] ?? '',
                            'trangThai' => $s['trang_thai'] ?? 1
                        ]);
                    ?>
                    <div class="glass-panel" style="padding: 24px; display: flex; flex-direction: column; align-items: center; text-align: center;">
                        <div style="width: 80px; height: 80px; background: var(--gold-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; color: var(--gold-dark); margin-bottom: 15px; border: 3px solid var(--gold-border);">
                            <?= htmlspecialchars($initials) ?>
                        </div>
                        
                        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">
                            <?= htmlspecialchars($s['ho_ten'] ?? $s['ten_dang_nhap'] ?? 'Nhân viên') ?>
                        </h3>
                        <div style="font-size: 0.85rem; color: var(--gold-dark); font-weight: 600; margin-top: 4px;">
                            <?= htmlspecialchars($s['chuc_vu'] ?? 'Nhân viên') ?>
                        </div>

                        <div style="margin-top: 12px;">
                            <?php if($status === 'active'): ?>
                                <span class="badge" style="background: var(--success-bg); color: var(--success); border: 1px solid #BBF7D0;">✅ Active</span>
                            <?php else: ?>
                                <span class="badge" style="background: var(--danger-bg); color: var(--danger); border: 1px solid #FCA5A5;">🔒 Inactive</span>
                            <?php endif; ?>
                        </div>

                        <div style="width: 100%; margin-top: 20px; padding: 15px; background: rgba(0,0,0,0.02); border-radius: 12px; text-align: left; display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem;">
                                <span style="color: var(--gold);">✉</span>
                                <span style="color: var(--text-secondary);"><?= htmlspecialchars($s['email'] ?? $s['ten_dang_nhap'] ?? '') ?></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem;">
                                <span style="color: var(--gold);">📞</span>
                                <span style="color: var(--text-secondary);"><?= htmlspecialchars($s['so_dien_thoai'] ?: 'Chưa có') ?></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem;">
                                <span style="color: var(--gold);">🏢</span>
                                <span style="color: var(--text-secondary);"><?= htmlspecialchars($s['ten_phong_ban'] ?: 'Chưa phân công') ?></span>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 8px; margin-top: 20px; width: 100%;">
                            <button class="btn btn-secondary btn-sm" style="flex: 1; justify-content: center;" onclick='viewStaff(<?= $staffData ?>)'>Xem</button>
                            <button class="btn btn-secondary btn-sm" style="flex: 1; justify-content: center;" onclick='editStaff(<?= $staffData ?>)'>Sửa</button>
                            <button class="btn btn-danger btn-sm" style="flex: 1; justify-content: center;" onclick='deleteStaff(<?= $staffData ?>)'>Xoá</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Thêm nhân viên -->
    <div id="modalAddStaff" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Thêm nhân viên mới</h3>
                <span class="modal-close" onclick="closeModal('modalAddStaff')">&times;</span>
            </div>
            <form method="POST" action="<?= SITE_URL ?>/admin/staff/add">
                <div class="form-group">
                    <label>Họ tên</label>
                    <input name="ho_ten" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email đăng nhập</label>
                    <input name="ten_dang_nhap" type="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input name="mat_khau" type="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input name="so_dien_thoai" class="form-control">
                </div>
                <div class="form-group">
                    <label>Chức vụ</label>
                    <input name="chuc_vu" class="form-control" placeholder="Lễ tân / Kế toán...">
                </div>
                <div class="form-group">
                    <label>Phòng ban</label>
                    <select name="ma_phong_ban" class="form-select">
                        <option value="">-- Chưa phân công --</option>
                        <?php foreach ($departments ?? [] as $pb): ?>
                        <option value="<?= (int)$pb['ma_phong_ban'] ?>"><?= htmlspecialchars($pb['ten_phong_ban']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngày vào làm</label>
                    <input name="ngay_vao_lam" type="date" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary w-full mt-3">Lưu Nhân Viên</button>
            </form>
        </div>
    </div>

    <!-- Modal Xem chi tiết -->
    <div id="modalViewStaff" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Chi tiết nhân viên</h3>
                <span class="modal-close" onclick="closeModal('modalViewStaff')">&times;</span>
            </div>
            <div style="background: var(--bg-primary); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--border-light); font-size: 14px;">
                <p class="mb-2"><strong>Họ tên:</strong> <span id="v-ho-ten" style="color: var(--gold-dark); font-weight: 600;"></span></p>
                <p class="mb-2"><strong>Email:</strong> <span id="v-email"></span></p>
                <p class="mb-2"><strong>Số điện thoại:</strong> <span id="v-sdt"></span></p>
                <p class="mb-2"><strong>Chức vụ:</strong> <span id="v-chuc-vu"></span></p>
                <p class="mb-2"><strong>Phòng ban:</strong> <span id="v-phong-ban"></span></p>
                <p class="mb-2"><strong>Ngày vào làm:</strong> <span id="v-ngay-vao-lam"></span></p>
                <p class="mb-2"><strong>Trạng thái:</strong> <span id="v-trang-thai"></span></p>
            </div>
            <div style="margin-top: 1.5rem; text-align: right;">
                <button class="btn btn-secondary" onclick="closeModal('modalViewStaff')">Đóng</button>
            </div>
        </div>
    </div>

    <!-- Modal Sửa nhân viên -->
    <div id="modalEditStaff" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Sửa thông tin nhân viên</h3>
                <span class="modal-close" onclick="closeModal('modalEditStaff')">&times;</span>
            </div>
            <form method="POST" action="<?= SITE_URL ?>/admin/staff/update">
                <input type="hidden" name="ma_nhan_vien" id="e-ma-nhan-vien">
                <input type="hidden" name="ma_nguoi_dung" id="e-ma-nguoi-dung">
                
                <div class="form-group">
                    <label>Họ tên</label>
                    <input name="ho_ten" id="e-ho-ten" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input name="email" id="e-email" type="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input name="so_dien_thoai" id="e-sdt" class="form-control">
                </div>
                <div class="form-group">
                    <label>Chức vụ</label>
                    <input name="chuc_vu" id="e-chuc-vu" class="form-control">
                </div>
                <div class="form-group">
                    <label>Phòng ban</label>
                    <select name="ma_phong_ban" id="e-ma-phong-ban" class="form-select">
                        <option value="">-- Chưa phân công --</option>
                        <?php foreach ($departments ?? [] as $pb): ?>
                        <option value="<?= (int)$pb['ma_phong_ban'] ?>"><?= htmlspecialchars($pb['ten_phong_ban']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngày vào làm</label>
                    <input name="ngay_vao_lam" id="e-ngay-vao-lam" type="date" class="form-control">
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="e-trang-thai" class="form-select">
                        <option value="1">Đang làm việc</option>
                        <option value="0">Ngừng làm việc</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-full mt-3">Lưu Thay Đổi</button>
            </form>
        </div>
    </div>

    <!-- Modal Xóa nhân viên -->
    <div id="modalDeleteStaff" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="color: var(--danger);">Ngừng hoạt động</h3>
                <span class="modal-close" onclick="closeModal('modalDeleteStaff')">&times;</span>
            </div>
            <form method="POST" action="<?= SITE_URL ?>/admin/staff/delete">
                <input type="hidden" name="ma_nguoi_dung" id="d-ma-nguoi-dung">
                <p>Bạn chắc chắn muốn ngừng hoạt động nhân viên <strong id="d-ho-ten"></strong>?</p>
                <p class="text-muted mt-2" style="font-size: 0.9rem;">Tài khoản sẽ bị vô hiệu hóa (xóa mềm).</p>
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteStaff')">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('show');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
        }

        function viewStaff(data) {
            document.getElementById('v-ho-ten').textContent = data.hoTen;
            document.getElementById('v-email').textContent = data.email;
            document.getElementById('v-sdt').textContent = data.sdt || '—';
            document.getElementById('v-chuc-vu').textContent = data.chucVu || 'Nhân viên';
            document.getElementById('v-phong-ban').textContent = data.tenPhongBan || 'Chưa phân công';
            document.getElementById('v-ngay-vao-lam').textContent = data.ngayVaoLam || '—';
            document.getElementById('v-trang-thai').textContent = data.trangThai == 1 ? 'Đang làm việc' : 'Ngừng làm việc';
            openModal('modalViewStaff');
        }

        function editStaff(data) {
            document.getElementById('e-ma-nhan-vien').value = data.maNhanVien;
            document.getElementById('e-ma-nguoi-dung').value = data.maNguoiDung;
            document.getElementById('e-ho-ten').value = data.hoTen;
            document.getElementById('e-email').value = data.email;
            document.getElementById('e-sdt').value = data.sdt;
            document.getElementById('e-chuc-vu').value = data.chucVu;
            document.getElementById('e-ngay-vao-lam').value = data.ngayVaoLam;
            document.getElementById('e-trang-thai').value = data.trangThai;
            
            let sel = document.getElementById('e-ma-phong-ban');
            if (sel) sel.value = data.maPhongBan || "";
            
            openModal('modalEditStaff');
        }

        function deleteStaff(data) {
            document.getElementById('d-ma-nguoi-dung').value = data.maNguoiDung;
            document.getElementById('d-ho-ten').textContent = data.hoTen;
            openModal('modalDeleteStaff');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
    </script>
</body>
</html>
