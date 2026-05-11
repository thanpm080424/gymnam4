<?php
/**
 * View: Quản lý Huấn Luyện Viên
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Huấn Luyện Viên - Monkey Gym</title>
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
        .staff-name { font-size: 1.2rem; font-weight: 600; color: var(--text-primary); margin-bottom: 5px; word-break: break-all; }
        .staff-position { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px; font-weight: 500; }
        .staff-info { background: var(--bg-primary); border-radius: 8px; padding: 12px; margin-top: 15px; text-align: left; border: 1px solid var(--border-light); }
        .staff-info-item { display: flex; align-items: flex-start; margin-bottom: 8px; font-size: 0.85rem; color: var(--text-secondary); }
        .staff-info-item:last-child { margin-bottom: 0; }
        .staff-info-item span.icon { width: 20px; color: var(--gold); margin-right: 8px; font-weight: bold; margin-top: 2px; }
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

        /* Modal TKB Admin */
        #modalTkbAdmin {
            display: none; position: fixed; z-index: 999; inset: 0;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(5px);
            align-items: center; justify-content: center;
        }
        #modalTkbAdmin.show { display: flex; }
        .tkb-admin-box {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 16px; padding: 1.8rem;
            width: 92vw; max-width: 920px;
            max-height: 88vh; overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0,0,0,0.45);
            animation: tkbFade 0.2s ease;
        }
        @keyframes tkbFade {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }
        .tkb-admin-head {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.2rem;
        }
        .tkb-admin-head h4 { font-size: 1.05rem; font-weight: 700; color: var(--gold); }
        .tkb-admin-close { cursor: pointer; font-size: 1.4rem; color: var(--text-muted); line-height:1; padding: 2px 6px; border-radius: 6px; }
        .tkb-admin-close:hover { background: var(--danger-bg); color: var(--danger); }
        .tkb-mini { width: 100%; border-collapse: collapse; font-size: 0.78rem; min-width: 600px; }
        .tkb-mini th, .tkb-mini td { border: 1px solid var(--border); text-align: center; padding: 0.45rem 0.3rem; vertical-align: middle; }
        .tkb-mini thead th { background: linear-gradient(135deg,var(--gold-dark),var(--gold)); color: #000; font-weight: 700; font-size: 0.76rem; }
        .tkb-mini .col-time { background: var(--bg-primary); font-weight: 600; color: var(--text-secondary); white-space: nowrap; font-size: 0.72rem; min-width: 76px; }
        .tkb-mini .cell-da-dat { background: #d1fae5; color: #065f46; }
        .tkb-mini .cell-trong  { background: #fef9c3; color: #78350f; }
        .tkb-mini .cell-empty  { background: var(--bg-primary); color: var(--text-muted); }
        .tkb-mini .cell-nghi   { background: var(--bg-secondary); color: var(--text-muted); }
        .tkb-overflow { overflow-x: auto; }
        .tkb-legend-mini { display:flex; gap:1rem; font-size:0.76rem; margin-top:0.8rem; flex-wrap:wrap; }
        .legend-dot-a { display:inline-block; width:10px;height:10px;border-radius:2px;margin-right:4px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="page-topbar">
                <div>
                    <h1>Đội Ngũ Huấn Luyện Viên</h1>
                    <p>Quản lý chuyên môn Gym/Yoga của từng nhân sự.</p>
                </div>
                <button class="btn btn-primary" onclick="openModal('modalAddTrainer')">+ Thêm HLV mới</button>
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
                    <div class="stats-number"><?= count($trainers ?? []) ?></div>
                    <div class="stats-label">Tổng HLV</div>
                </div>
                <div class="card stats-card">
                    <div class="stats-number" style="color: var(--success);">
                        <?= count(array_filter($trainers ?? [], fn($t) => (!isset($t['trang_thai']) || $t['trang_thai'] !== 'banned'))) ?>
                    </div>
                    <div class="stats-label">Đang hoạt động</div>
                </div>
                <div class="card stats-card">
                    <div class="stats-number" style="color: #3b82f6;">
                        <?= count(array_filter($trainers ?? [], fn($t) => ($t['chuyen_mon'] === 'gym'))) ?>
                    </div>
                    <div class="stats-label">Chuyên môn Gym</div>
                </div>
                <div class="card stats-card">
                    <div class="stats-number" style="color: #f472b6;">
                        <?= count(array_filter($trainers ?? [], fn($t) => ($t['chuyen_mon'] === 'yoga'))) ?>
                    </div>
                    <div class="stats-label">Chuyên môn Yoga</div>
                </div>
            </div>

            <!-- Danh sách HLV -->
            <?php if (empty($trainers)): ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <h4 style="color: var(--text-muted);">Chưa có Huấn luyện viên nào</h4>
                    <p style="color: var(--text-muted);">Hãy thêm một huấn luyện viên mới vào hệ thống.</p>
                </div>
            <?php else: ?>
                <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                    <?php foreach ($trainers as $t):
                        $fullName = trim((string)($t['ten_dang_nhap']));
                        $initials = strtoupper(substr($fullName, 0, 1)) ?: 'PT';
                        $isBanned = isset($t['trang_thai']) && $t['trang_thai'] === 'banned';
                        $isYoga = ($t['chuyen_mon'] === 'yoga');
                        
                        $trainerData = json_encode([
                            'maNguoiDung' => $t['ma_nguoi_dung'] ?? '',
                            'tenDangNhap' => $t['ten_dang_nhap'] ?? '',
                            'chuyenMonRaw' => $t['chuyen_mon'] ?? 'gym',
                            'chuyenMon' => $isYoga ? '🧘‍♀️ Yoga' : '🏋️ Gym',
                            'moTa' => $t['mo_ta'] ?? '',
                            'trangThaiRaw' => $isBanned ? 'banned' : 'active',
                            'trangThai' => !$isBanned ? 'Đang hoạt động' : 'Đã khoá',
                            'isBanned' => $isBanned
                        ]);
                    ?>
                    <div class="glass-panel" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; border-top: 4px solid <?= $isYoga ? '#f472b6' : '#3b82f6' ?>;">
                        <div style="padding: 24px; text-align: center;">
                            <div style="width: 70px; height: 70px; margin: 0 auto 15px; border-radius: 50%; overflow: hidden; border: 3px solid var(--border-light); background: var(--gold-bg); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; color: var(--gold-dark);">
                                <?php if (!empty($t['anh_dai_dien'])): ?>
                                    <img src="<?= ASSET_URL . htmlspecialchars($t['anh_dai_dien']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <?= $initials ?>
                                <?php endif; ?>
                            </div>
                            
                            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--text-primary);"><?= htmlspecialchars($t['ten_dang_nhap']) ?></h3>
                            <div style="font-size: 0.8rem; color: <?= $isYoga ? '#f472b6' : '#3b82f6' ?>; font-weight: 700; text-transform: uppercase; margin-top: 4px;">
                                <?= $isYoga ? '🧘‍♀️ Chuyên gia Yoga' : '🏋️ Huấn luyện viên Gym' ?>
                            </div>

                            <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 15px; padding: 10px; background: rgba(0,0,0,0.02); border-radius: 12px;">
                                <div style="text-align: center;">
                                    <div style="font-size: 0.9rem; font-weight: 800; color: var(--gold-dark);"><?= number_format($t['avg_rating'] ?? 5, 1) ?>⭐</div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Rating</div>
                                </div>
                                <div style="width: 1px; height: 20px; background: var(--border-light);"></div>
                                <div style="text-align: center;">
                                    <div style="font-size: 0.9rem; font-weight: 800; color: var(--text-primary);"><?= $t['review_count'] ?? 0 ?></div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Reviews</div>
                                </div>
                                <div style="width: 1px; height: 20px; background: var(--border-light);"></div>
                                <div style="text-align: center;">
                                    <div style="font-size: 0.8rem; font-weight: 700;">
                                        <?php if($isBanned): ?>
                                            <span style="color: var(--danger);">🔒 Banned</span>
                                        <?php else: ?>
                                            <span style="color: var(--success);">✅ Active</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Status</div>
                                </div>
                            </div>
                        </div>

                        <div style="padding: 0 24px 20px; flex: 1;">
                            <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.5; background: rgba(0,0,0,0.02); padding: 10px; border-radius: 8px; font-style: italic;">
                                "<?= htmlspecialchars($t['mo_ta'] ?: 'Sẵn sàng hỗ trợ học viên đạt được mục tiêu thể hình tối ưu.') ?>"
                            </div>
                        </div>

                        <div style="padding: 16px 24px; background: rgba(0,0,0,0.03); border-top: 1px solid var(--border-light); display: flex; gap: 8px; flex-wrap: wrap;">
                            <button class="btn btn-secondary btn-sm" style="flex: 1; justify-content: center;" onclick='viewTrainer(<?= htmlspecialchars($trainerData, ENT_QUOTES, 'UTF-8') ?>)'>Chi tiết</button>
                            <button class="btn btn-secondary btn-sm" style="flex: 1; justify-content: center;" onclick='editTrainer(<?= htmlspecialchars($trainerData, ENT_QUOTES, 'UTF-8') ?>)'>Sửa</button>
                            <button class="btn btn-secondary btn-sm" style="background:var(--gold-bg); color:var(--gold-dark); border-color:var(--gold-border); flex: 1; justify-content: center;"
                                    onclick="openAdminTkb(<?= $t['ma_hlv'] ?>, '<?= htmlspecialchars(addslashes($t['ten_dang_nhap'])) ?>')">
                                📅 Lịch
                            </button>

                            <?php if($isBanned): ?>
                            <form action="<?= SITE_URL ?>/admin/members/ban" method="POST" style="margin: 0; display: contents;">
                                <input type="hidden" name="ma_user" value="<?= $t['ma_nguoi_dung'] ?>">
                                <input type="hidden" name="action" value="unban">
                                <button type="submit" class="btn btn-success btn-sm" style="flex: 1; justify-content: center;">Mở khoá</button>
                            </form>
                            <?php endif; ?>

                            <form action="<?= SITE_URL ?>/admin/members/delete" method="POST" style="margin: 0; display: contents;">
                                <input type="hidden" name="ma_user" value="<?= $t['ma_nguoi_dung'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" style="flex: 1; justify-content: center;" onclick="return confirm('CẢNH BÁO: Xoá vĩnh viễn HLV này cùng lịch đặt PT?');">Xoá</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Thêm HLV -->
    <div id="modalAddTrainer" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Thêm Huấn Luyện Viên</h3>
                <span class="modal-close" onclick="closeModal('modalAddTrainer')">&times;</span>
            </div>
            <form action="<?= SITE_URL ?>/admin/trainers/create" method="POST">
                <div class="form-group">
                    <label>Email (Dùng để đăng nhập)</label>
                    <input type="email" name="email" class="form-control" placeholder="vidu: pt.nam@gym.com" required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu mặc định</label>
                    <input type="text" name="password" class="form-control" value="123" required>
                </div>
                <div class="form-group">
                    <label>Chuyên môn huấn luyện</label>
                    <select name="chuyen_mon" class="form-select" required>
                        <option value="gym">Gym (Thể hình)</option>
                        <option value="yoga">Yoga (Thiền & Uốn dẻo)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mô tả ngắn</label>
                    <textarea name="mo_ta" class="form-control" rows="3" placeholder="Ví dụ: Chuyên gia 5 năm kinh nghiệm..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-full mt-3">Tạo Tài Khoản HLV</button>
            </form>
        </div>
    </div>

    <!-- Modal Xem chi tiết -->
    <div id="modalViewTrainer" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Chi tiết Huấn Luyện Viên</h3>
                <span class="modal-close" onclick="closeModal('modalViewTrainer')">&times;</span>
            </div>
            <div style="background: var(--bg-primary); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--border-light); font-size: 14px;">
                <p class="mb-2"><strong>Tài khoản (Email):</strong> <span id="v-email" style="color: var(--gold-dark); font-weight: 600;"></span></p>
                <p class="mb-2"><strong>Chuyên môn:</strong> <span id="v-chuyen-mon"></span></p>
                <p class="mb-2"><strong>Trạng thái:</strong> <span id="v-trang-thai"></span></p>
                <p class="mb-2"><strong>Mô tả / Ghi chú:</strong></p>
                <div id="v-mo-ta" style="background: #fff; padding: 10px; border-radius: 6px; border: 1px solid var(--border); min-height: 60px;"></div>
            </div>
            <div style="margin-top: 1.5rem; text-align: right;">
                <button class="btn btn-secondary" onclick="closeModal('modalViewTrainer')">Đóng</button>
            </div>
        </div>
    </div>

    <!-- Modal Sửa HLV -->
    <div id="modalEditTrainer" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Sửa Huấn Luyện Viên</h3>
                <span class="modal-close" onclick="closeModal('modalEditTrainer')">&times;</span>
            </div>
            <form action="<?= SITE_URL ?>/admin/trainers/update" method="POST">
                <input type="hidden" name="ma_nguoi_dung" id="e-ma-nguoi-dung">
                
                <div class="form-group">
                    <label>Email (Tài khoản)</label>
                    <input type="email" name="email" id="e-email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="e-trang-thai" class="form-select" required>
                        <option value="active">Đang hoạt động</option>
                        <option value="banned">Khoá tài khoản</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Chuyên môn huấn luyện</label>
                    <select name="chuyen_mon" id="e-chuyen-mon" class="form-select" required>
                        <option value="gym">Gym (Thể hình)</option>
                        <option value="yoga">Yoga (Thiền & Uốn dẻo)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mô tả ngắn / Ghi chú</label>
                    <textarea name="mo_ta" id="e-mo-ta" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-full mt-3">Lưu Thay Đổi</button>
            </form>
        </div>
    </div>

    <!-- Modal Xem lịch TKB HLV (Admin) -->
    <div id="modalTkbAdmin">
        <div class="tkb-admin-box">
            <div class="tkb-admin-head">
                <h4 id="admin-tkb-title">📅 Thời Khóa Biểu</h4>
                <span class="tkb-admin-close" onclick="closeAdminTkb()">✕</span>
            </div>
            <div class="tkb-overflow">
                <div id="admin-tkb-loading" style="text-align:center;padding:2rem;color:var(--text-muted);">⏳ Đang tải...</div>
                <table class="tkb-mini" id="admin-tkb-table" style="display:none;">
                    <thead>
                        <tr>
                            <th>⏱ Giờ</th>
                            <th>Thứ 2</th><th>Thứ 3</th><th>Thứ 4</th><th>Thứ 5</th><th>Thứ 6</th><th>Thứ 7</th><th>CN</th>
                        </tr>
                    </thead>
                    <tbody id="admin-tkb-tbody"></tbody>
                </table>
                <p id="admin-tkb-empty" style="display:none;text-align:center;padding:1.5rem;color:var(--text-muted);">HLV này chưa thiết lập thời khóa biểu.</p>
            </div>
            <div class="tkb-legend-mini" style="margin-top:1rem">
                <span><span class="legend-dot-a" style="background:#059669;"></span>Đã có học viên</span>
                <span><span class="legend-dot-a" style="background:#d97706;"></span>Còn trống</span>
                <span><span class="legend-dot-a" style="background:var(--bg-primary);border:1px solid var(--border);"></span>Chưa có slot</span>
                <span style="margin-left:auto;font-size:0.75rem;color:var(--text-muted);">HLV tự quản lý lịch dạy của mình</span>
            </div>
            <div style="text-align:right;margin-top:1rem;">
                <button class="btn btn-secondary" onclick="closeAdminTkb()">'Đóng</button>
            </div>
        </div>
    </div>

    <script>
        const GYM_SLOTS_ADMIN = <?= json_encode(defined('GYM_TIME_SLOTS') ? GYM_TIME_SLOTS : []) ?>;
        const DOW_DB_ADMIN = [1,2,3,4,5,6,0]; // T2,T3,T4,T5,T6,T7,CN
        let adminTkbCache = {};

        function openAdminTkb(maHlv, tenHlv) {
            document.getElementById('admin-tkb-title').textContent = '📅 Thời Khóa Biểu: ' + tenHlv;
            document.getElementById('modalTkbAdmin').classList.add('show');
            loadAdminTkb(maHlv);
        }
        function closeAdminTkb() {
            document.getElementById('modalTkbAdmin').classList.remove('show');
        }
        function loadAdminTkb(maHlv) {
            if (adminTkbCache[maHlv]) { renderAdminTkb(adminTkbCache[maHlv]); return; }
            document.getElementById('admin-tkb-loading').style.display = 'block';
            document.getElementById('admin-tkb-table').style.display = 'none';
            document.getElementById('admin-tkb-empty').style.display = 'none';
            fetch(`<?= SITE_URL ?>/trainer/schedule/api?ma_hlv=${maHlv}`)
                .then(r => r.json())
                .then(data => { adminTkbCache[maHlv] = data; renderAdminTkb(data); })
                .catch(() => { document.getElementById('admin-tkb-loading').textContent = '❌ Lỗi tải lịch.'; });
        }
        function renderAdminTkb(data) {
            const bookings = data.bookings || [];
            const timeSlots = data.time_slots || GYM_SLOTS_ADMIN;
            document.getElementById('admin-tkb-loading').style.display = 'none';

            if (bookings.length === 0) { 
                document.getElementById('admin-tkb-empty').style.display = 'block'; 
                return; 
            }

            const mysqlToJsDow = { 1: 0, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 7: 6 };
            const tbody = document.getElementById('admin-tkb-tbody');
            tbody.innerHTML = '';

            timeSlots.forEach(ts => {
                const tsStart = ts.bat_dau;
                const tsEnd = ts.ket_thuc;
                
                const getMins = timeStr => {
                    const parts = timeStr.split(':');
                    return parseInt(parts[0]) * 60 + parseInt(parts[1]);
                };
                
                const startMins = getMins(tsStart);
                const endMins = (tsEnd === '23:59:59' || tsEnd === '00:00:00') ? 24*60 : getMins(tsEnd);

                let row = `<tr><td class="col-time">${ts.label}</td>`;

                DOW_DB_ADMIN.forEach(dow => {
                    const cellBookings = bookings.filter(b => {
                        const bDow = mysqlToJsDow[parseInt(b.mysql_dow)];
                        if (bDow !== dow) return false;
                        
                        const bMins = getMins(b.booking_time);
                        return bMins >= startMins && bMins < endMins;
                    });

                    if (cellBookings.length > 0) {
                        let content = '';
                        cellBookings.forEach(b => {
                            content += `<b style="font-size:0.74rem;">${b.booking_time}</b><br>`;
                            if (b.trang_thai === 'pending') {
                                content += `<span style="font-size:0.67rem;color:#f59e0b;">⏳chờ duyệt</span><br>`;
                            } else {
                                content += `<span style="font-size:0.67rem;">✅đã đặt</span><br>`;
                            }
                        });
                        row += `<td class="cell-da-dat">${content}</td>`;
                    } else {
                        row += `<td class="cell-empty">—</td>`;
                    }
                });
                row += '</tr>';
                tbody.innerHTML += row;
            });
            document.getElementById('admin-tkb-table').style.display = 'table';
        }

        function openModal(id) {
            document.getElementById(id).classList.add('show');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
        }

        function viewTrainer(data) {
            document.getElementById('v-email').textContent = data.tenDangNhap;
            document.getElementById('v-chuyen-mon').textContent = data.chuyenMon;
            document.getElementById('v-trang-thai').textContent = data.trangThai;
            document.getElementById('v-mo-ta').textContent = data.moTa || 'Chưa có mô tả';
            
            if(data.isBanned) {
                document.getElementById('v-trang-thai').style.color = 'var(--danger)';
            } else {
                document.getElementById('v-trang-thai').style.color = 'var(--success)';
            }
            
            openModal('modalViewTrainer');
        }

        function editTrainer(data) {
            document.getElementById('e-ma-nguoi-dung').value = data.maNguoiDung;
            document.getElementById('e-email').value = data.tenDangNhap;
            document.getElementById('e-chuyen-mon').value = data.chuyenMonRaw;
            document.getElementById('e-trang-thai').value = data.trangThaiRaw;
            document.getElementById('e-mo-ta').value = data.moTa;
            
            openModal('modalEditTrainer');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
            if (event.target === document.getElementById('modalTkbAdmin')) {
                closeAdminTkb();
            }
        }
    </script>
</body>
</html>
