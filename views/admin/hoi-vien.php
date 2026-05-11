<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Hội Viên | Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h1>👥 Quản Lý Hội Viên</h1>
                    <p class="text-muted">Danh sách thành viên đăng ký ứng dụng Monkey Gym.</p>
                </div>
                <div class="glass-panel" style="padding: 10px 20px; display: flex; align-items: center; gap: 15px; background: rgba(255,255,255,0.05);">
                    <form method="GET" action="<?= SITE_URL ?>/admin/members" style="display: flex; align-items: center; gap: 10px; margin: 0;">
                        <div style="position: relative;">
                            <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" 
                                   placeholder="Tìm tên, email, QR..." 
                                   style="padding: 8px 12px 8px 35px; border-radius: 8px; border: 1px solid var(--border-light); background: var(--bg-secondary); color: var(--text-primary); font-size: 0.9rem; min-width: 250px;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); opacity: 0.6;">🔍</span>
                        </div>
                        
                        <select name="filter" onchange="this.form.submit()" 
                                style="padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-light); background: var(--bg-secondary); color: var(--text-primary); font-size: 0.9rem; cursor: pointer;">
                            <option value="all" <?= ($filter ?? 'all') === 'all' ? 'selected' : '' ?>>Tất cả gói tập</option>
                            <option value="active" <?= ($filter ?? '') === 'active' ? 'selected' : '' ?>>💎 Còn hạn</option>
                            <option value="expired" <?= ($filter ?? '') === 'expired' ? 'selected' : '' ?>>⏰ Hết hạn</option>
                            <option value="no_package" <?= ($filter ?? '') === 'no_package' ? 'selected' : '' ?>>⚪ Trống</option>
                        </select>

                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 15px;">Lọc</button>
                        
                        <?php if(!empty($search) || ($filter ?? 'all') !== 'all'): ?>
                            <a href="<?= SITE_URL ?>/admin/members" style="font-size: 0.8rem; color: var(--primary); text-decoration: none;">Xóa lọc</a>
                        <?php endif; ?>
                    </form>
                </div>
            </header>

            <?php
                $__f = getFlash();
                if ($__f): ?>
                <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                    <?= htmlspecialchars($__f['message']) ?>
                </div>
            <?php endif; ?>
            <div class="glass-panel" style="padding: 0; overflow-x: auto; border-radius: 16px;">
                <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
                    <thead>
                        <tr style="background: rgba(0,0,0,0.03); border-bottom: 1px solid var(--border-light);">
                            <th style="padding: 15px 20px; text-align: left; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Hội viên</th>
                            <th style="padding: 15px 20px; text-align: left; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Thông tin liên hệ</th>
                            <th style="padding: 15px 20px; text-align: left; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Mã QR</th>
                            <th style="padding: 15px 20px; text-align: left; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Thể chất</th>
                            <th style="padding: 15px 20px; text-align: left; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Gói tập</th>
                            <th style="padding: 15px 20px; text-align: center; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($members as $m): ?>
                        <tr style="border-bottom: 1px solid var(--border-light); transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.01)'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 15px 20px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: var(--primary); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
                                        <?= strtoupper(mb_substr($m['ho_ten'] ?: $m['ten_dang_nhap'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-primary);"><?= htmlspecialchars($m['ho_ten'] ?: $m['ten_dang_nhap']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($m['ten_dang_nhap']) ?></div>
                                        <div style="margin-top: 4px;">
                                            <?php if(isset($m['trang_thai_tk']) && $m['trang_thai_tk'] === 'banned'): ?>
                                                <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 6px;">⛔ Banned</span>
                                            <?php else: ?>
                                                <span class="badge bg-success" style="font-size: 0.65rem; padding: 2px 6px;">✅ Active</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 15px 20px;">
                                <div style="font-size: 0.9rem; font-weight: 600;"><?= htmlspecialchars($m['so_dien_thoai'] ?: '--') ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($m['email'] ?? '--') ?></div>
                            </td>
                            <td style="padding: 15px 20px;">
                                <code style="background: rgba(0,0,0,0.05); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; color: var(--primary);"><?= htmlspecialchars($m['ma_qr']) ?></code>
                            </td>
                            <td style="padding: 15px 20px;">
                                <div style="font-size: 0.9rem; font-weight: 600;"><?= $m['chieu_cao'] ?? '--' ?>cm / <?= $m['can_nang'] ?? '--' ?>kg</div>
                            </td>
                            <td style="padding: 15px 20px;">
                                <?php if($m['trang_thai_goi'] === 'active'): ?>
                                    <span style="color: #10b981; font-weight: 700; font-size: 0.9rem;">💎 Còn hạn</span>
                                <?php elseif($m['trang_thai_goi'] === 'expired'): ?>
                                    <span style="color: #ef4444; font-weight: 700; font-size: 0.9rem;">⏰ Hết hạn</span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-weight: 700; font-size: 0.9rem;">⚪ Trống</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px 20px; text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    <a href="<?= SITE_URL ?>/admin/members/detail?id=<?= $m['ma_hoi_vien'] ?>" class="btn btn-primary btn-sm" title="Chi tiết" style="padding: 5px 10px; font-size: 0.75rem;">Chi tiết</a>
                                    
                                    <form action="<?= SITE_URL ?>/admin/members/reset-password" method="POST" style="display:contents;" onsubmit="return confirm('Bạn có chắc muốn cấp lại mật khẩu cho hội viên này về mặc định (123456)?');">
                                        <input type="hidden" name="ma_user" value="<?= $m['ma_nguoi_dung'] ?>">
                                        <button type="submit" class="btn btn-sm" title="Reset Mật khẩu" style="background: #10b981; color: white; border: none; padding: 5px 10px; font-size: 0.75rem; border-radius: 6px;">MK</button>
                                    </form>

                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <form action="<?= SITE_URL ?>/admin/members/ban" method="POST" style="display:contents;">
                                        <input type="hidden" name="ma_user" value="<?= $m['ma_nguoi_dung'] ?>">
                                        <?php if(isset($m['trang_thai_tk']) && $m['trang_thai_tk'] === 'banned'): ?>
                                            <input type="hidden" name="action" value="unban">
                                            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 5px 10px; font-size: 0.75rem;">Mở</button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="ban">
                                            <button type="submit" class="btn btn-sm" style="background: #fbbf24; color: black; border: none; padding: 5px 10px; font-size: 0.75rem; border-radius: 6px;" onclick="return confirm('Bạn có chắc muốn khóa người dùng này?');">Khóa</button>
                                        <?php endif; ?>
                                    </form>

                                    <form action="<?= SITE_URL ?>/admin/members/delete" method="POST" style="display:contents;" onsubmit="return confirm('CANH BAO TOI THUONG: Xóa nguoi dung se xoa BINH VIEN toan bo thong tin hoi vien, goi tap, lich su lien quan. Tiep tuc?');">
                                        <input type="hidden" name="ma_user" value="<?= $m['ma_nguoi_dung'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 5px 10px; font-size: 0.75rem;">Xóa</button>
                                    </form>
                                    <?php else: ?>
                                        <?php if(!isset($m['trang_thai_tk']) || $m['trang_thai_tk'] !== 'banned'): ?>
                                            <button type="button" class="btn btn-sm" style="background: #ef4444; color: white; border: none; padding: 5px 10px; font-size: 0.75rem; border-radius: 6px;" onclick="openBanModal('<?= $m['ma_hoi_vien'] ?>', '<?= htmlspecialchars(addslashes($m['ten_dang_nhap'])) ?>')">⚠️ Report</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($members)): ?>
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: var(--text-muted);">
                                Không có dữ liệu hội viên.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>


            <?php if ($_SESSION['role'] !== 'admin'): ?>
            <!-- Modal yêu cầu ban -->
            <div id="banModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:var(--bg-card, #1e293b); border:1px solid var(--border, #334155); border-radius:16px; padding:2rem; width:90%; max-width:460px; box-shadow:0 20px 60px rgba(0,0,0,0.4);">
                    <h3 style="margin:0 0 0.5rem; color:var(--text-primary, #fff);">⚠️ Yêu Cầu Ban Hội Viên</h3>
                    <p id="banModalTarget" style="color:var(--text-muted, #94a3b8); font-size:0.85rem; margin-bottom:1rem;"></p>
                    <form action="<?= SITE_URL ?>/admin/members/ban-request" method="POST" onsubmit="return validateBanForm()">
                        <input type="hidden" name="ma_hoi_vien" id="banMaHoiVien">
                        <div style="margin-bottom:1rem;">
                            <label style="display:block; font-size:0.85rem; color:var(--text-primary, #fff); margin-bottom:0.4rem; font-weight:600;">Lý do báo cáo <span style="color:#ef4444;">*</span></label>
                            <textarea name="ly_do" id="banLyDo" rows="4" required placeholder="Nhập lý do yêu cầu ban hội viên này..." style="width:100%; padding:0.7rem; border:1px solid var(--border, #334155); border-radius:8px; background:var(--bg-secondary, #0f172a); color:var(--text-primary, #fff); font-size:0.9rem; resize:vertical; font-family:inherit;"></textarea>
                            <small id="banLyDoError" style="color:#ef4444; display:none; margin-top:0.3rem;">Vui lòng nhập lý do (tối thiểu 10 ký tự).</small>
                        </div>
                        <div style="display:flex; gap:0.8rem; justify-content:flex-end;">
                            <button type="button" onclick="closeBanModal()" class="btn btn-secondary" style="padding:0.5rem 1.2rem; font-size:0.9rem;">Hủy</button>
                            <button type="submit" class="btn btn-primary" style="padding:0.5rem 1.2rem; font-size:0.9rem; background:#ef4444; border:none;">🚫 Gửi Yêu Cầu</button>
                        </div>
                    </form>
                </div>
            </div>
            <script>
            function openBanModal(maHoiVien, tenDangNhap) {
                document.getElementById('banMaHoiVien').value = maHoiVien;
                document.getElementById('banModalTarget').textContent = 'Hội viên: ' + tenDangNhap;
                document.getElementById('banLyDo').value = '';
                document.getElementById('banLyDoError').style.display = 'none';
                document.getElementById('banModal').style.display = 'flex';
            }
            function closeBanModal() {
                document.getElementById('banModal').style.display = 'none';
            }
            function validateBanForm() {
                var lyDo = document.getElementById('banLyDo').value.trim();
                if (lyDo.length < 10) {
                    document.getElementById('banLyDoError').style.display = 'block';
                    return false;
                }
                return confirm('Bạn có chắc muốn gửi yêu cầu ban hội viên này lên Admin?');
            }
            // Đóng modal khi click ra ngoài
            document.getElementById('banModal').addEventListener('click', function(e) {
                if (e.target === this) closeBanModal();
            });
            </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
