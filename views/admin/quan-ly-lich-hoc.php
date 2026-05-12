<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xếp Lịch Học Group X | Monkey Gym Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .schedule-container { padding: 30px; }
        
        .page-header { 
            margin-bottom: 30px; 
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .page-title h1 { font-size: 2rem; font-weight: 800; margin: 0; color: var(--text-primary); }
        .page-title p { color: var(--text-muted); margin: 5px 0 0; }

        .bento-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 25px;
            align-items: start;
        }

        .bento-card {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--border-light);
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-light);
        }
        .card-icon {
            width: 40px; height: 40px;
            background: var(--gold-bg);
            color: var(--gold-dark);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .card-title { font-size: 1.1rem; font-weight: 800; margin: 0; }

        /* Form Styles */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--gold);
            background: white;
            box-shadow: 0 0 0 4px rgba(201,153,63,0.1);
            outline: none;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--gold);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }
        .btn-submit:hover { background: var(--gold-dark); transform: translateY(-2px); }

        /* Table Styles */
        .schedule-table { width: 100%; border-collapse: collapse; }
        .schedule-table th {
            text-align: left; padding: 15px;
            font-size: 0.75rem; color: var(--text-muted);
            text-transform: uppercase; border-bottom: 1px solid var(--border-light);
        }
        .schedule-table td { padding: 20px 15px; border-bottom: 1px solid var(--border-light); }
        .schedule-table tr:last-child td { border-bottom: none; }

        .class-name { font-weight: 800; font-size: 1rem; color: var(--text-primary); margin-bottom: 4px; display: block; }
        .class-type { font-size: 0.7rem; font-weight: 700; background: var(--gold-bg); color: var(--gold-dark); padding: 2px 8px; border-radius: 4px; text-transform: uppercase; }
        
        .time-box { font-weight: 700; color: var(--text-primary); }
        .date-box { font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; }

        .trainer-info { display: flex; align-items: center; gap: 10px; }
        .trainer-avatar { 
            width: 32px; height: 32px; 
            background: var(--bg-primary); 
            border-radius: 50%; 
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: var(--gold);
            border: 1px solid var(--border-light);
        }

        .status-pill {
            font-weight: 800; font-size: 1.1rem; color: #10b981;
        }

        /* Actions */
        .actions { display: flex; gap: 8px; }
        .btn-icon {
            width: 34px; height: 34px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s;
            border: 1px solid var(--border-light);
            background: white;
        }
        .btn-edit { color: #f59e0b; }
        .btn-edit:hover { background: #f59e0b; color: white; border-color: #f59e0b; }
        .btn-delete { color: #ef4444; }
        .btn-delete:hover { background: #ef4444; color: white; border-color: #ef4444; }

        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4);
            backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center;
            z-index: 1000;
        }
        .modal-box {
            background: white; width: 500px; border-radius: 20px; padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        @media (max-width: 1100px) {
            .bento-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <div class="schedule-container">
                <header class="page-header">
                    <div class="page-title">
                        <h1>📅 Xếp Lịch Group X</h1>
                        <p>Điều phối lịch dạy các lớp học nhóm bùng nổ năng lượng.</p>
                    </div>
                </header>

                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 25px;">
                        <?php 
                            if($_GET['success'] == '1') echo "✅ Tạo lịch học mới thành công!";
                            elseif($_GET['success'] == 'updated') echo "✅ Cập nhật lịch học thành công!";
                            elseif($_GET['success'] == 'deleted') echo "✅ Đã xóa lịch học vĩnh viễn!";
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger" style="margin-bottom: 25px;">
                        ❌ <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <div class="bento-grid">
                    <!-- Form Tạo Mới -->
                    <aside class="bento-card">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-plus"></i></div>
                            <h2 class="card-title">Tạo Buổi Học</h2>
                        </div>
                        
                        <form action="<?= SITE_URL ?>/admin/schedule/create" method="POST">
                            <div class="form-group">
                                <label class="form-label">Lớp Học</label>
                                <select name="ma_lop" class="form-control" required>
                                    <option value="">-- Danh sách lớp --</option>
                                    <?php foreach($classes as $c): ?>
                                        <option value="<?= $c['ma_lop'] ?>"><?= htmlspecialchars($c['ten_lop']) ?> (<?= $c['loai_lop'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Huấn Luyện Viên</label>
                                <select name="ma_hlv" class="form-control" required>
                                    <option value="">-- Danh sách HLV --</option>
                                    <?php foreach($trainers as $t): ?>
                                        <option value="<?= $t['ma_hlv'] ?>"><?= htmlspecialchars($t['ho_ten'] ?? $t['ten_dang_nhap']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Ngày Học</label>
                                <input type="date" name="ngay_hoc" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label class="form-label">Bắt Đầu</label>
                                    <input type="time" name="gio_bat_dau" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Kết Thúc</label>
                                    <input type="time" name="gio_ket_thuc" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Sĩ số tối đa</label>
                                <input type="number" name="so_luong" class="form-control" value="20" min="5" max="50" required>
                            </div>

                            <button type="submit" class="btn-submit">Xếp Lịch Ngay</button>
                        </form>
                    </aside>

                    <!-- Bảng Danh Sách -->
                    <main class="bento-card">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
                            <h2 class="card-title">Lịch Trình Sắp Tới</h2>
                        </div>

                        <div style="overflow-x: auto;">
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th>Lớp Học</th>
                                        <th>Thời Gian</th>
                                        <th>HLV</th>
                                        <th>Sĩ Số</th>
                                        <th>Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($schedules)): ?>
                                        <?php foreach($schedules as $s): ?>
                                            <tr>
                                                <td>
                                                    <span class="class-name"><?= htmlspecialchars($s['ten_lop']) ?></span>
                                                    <span class="class-type"><?= htmlspecialchars($s['loai_lop']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="time-box"><?= date('H:i', strtotime($s['gio_bat_dau'])) ?> - <?= date('H:i', strtotime($s['gio_ket_thuc'])) ?></div>
                                                    <div class="date-box">📅 <?= date('d/m/Y', strtotime($s['ngay_hoc'])) ?></div>
                                                </td>
                                                <td>
                                                    <div class="trainer-info">
                                                        <div class="trainer-avatar"><?= strtoupper(mb_substr($s['ten_hlv'] ?? 'G', 0, 1)) ?></div>
                                                        <div style="font-weight: 600; font-size: 0.85rem;"><?= htmlspecialchars($s['ten_hlv'] ?? $s['ten_dang_nhap']) ?></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="status-pill">0 <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 500;">/ <?= $s['so_luong_toi_da'] ?></span></div>
                                                </td>
                                                <td>
                                                    <div class="actions">
                                                        <button class="btn-icon btn-edit" onclick='openEditModal(<?= json_encode($s) ?>)' title="Chỉnh sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="<?= SITE_URL ?>/admin/schedule/delete" method="POST" onsubmit="return confirm('Xóa lịch này?')">
                                                            <input type="hidden" name="ma_lich" value="<?= $s['ma_lich'] ?>">
                                                            <button type="submit" class="btn-icon btn-delete" title="Xóa bỏ">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 60px;">📭 Chưa có lịch trình nào được thiết lập.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-box">
            <div class="card-header">
                <div class="card-icon" style="background: #fef3c7; color: #d97706;"><i class="fas fa-edit"></i></div>
                <h2 class="card-title">Cập Nhật Lịch Học</h2>
            </div>
            <form action="<?= SITE_URL ?>/admin/schedule/update" method="POST">
                <input type="hidden" name="ma_lich" id="edit_id">
                
                <div class="form-group">
                    <label class="form-label">Lớp học</label>
                    <input type="text" id="edit_class_name" class="form-control" readonly style="opacity: 0.7;">
                </div>

                <div class="form-group">
                    <label class="form-label">Huấn luyện viên</label>
                    <select name="ma_hlv" id="edit_hlv" class="form-control" required>
                        <?php foreach($trainers as $t): ?>
                            <option value="<?= $t['ma_hlv'] ?>"><?= htmlspecialchars($t['ho_ten'] ?? $t['ten_dang_nhap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Ngày dạy</label>
                    <input type="date" name="ngay_hoc" id="edit_date" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Bắt đầu</label>
                        <input type="time" name="gio_bat_dau" id="edit_start" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kết thúc</label>
                        <input type="time" name="gio_ket_thuc" id="edit_end" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Sĩ số tối đa</label>
                    <input type="number" name="so_luong_toi_da" id="edit_slots" class="form-control" required>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 10px;">
                    <button type="button" class="btn-submit" style="background: #f1f5f9; color: var(--text-primary);" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="btn-submit">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.ma_lich;
            document.getElementById('edit_class_name').value = data.ten_lop;
            document.getElementById('edit_hlv').value = data.ma_hlv;
            document.getElementById('edit_date').value = data.ngay_hoc;
            document.getElementById('edit_start').value = data.gio_bat_dau;
            document.getElementById('edit_end').value = data.gio_ket_thuc;
            document.getElementById('edit_slots').value = data.so_luong_toi_da;
            
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(e) {
            if (e.target == document.getElementById('editModal')) closeEditModal();
        }
    </script>
</body>
</html>
