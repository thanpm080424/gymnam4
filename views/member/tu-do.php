<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <div class="mg-page-header animate-up">
        <h1>Quản lý Tủ đồ 🔒</h1>
        <p>Không gian lưu trữ cá nhân an toàn và tiện lợi tại Monkey Gym.</p>
    </div>

    <style>
        .locker-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; align-items: start; }
        
        /* My Locker Card */
        .card-my-locker { background: #fff; border-radius: 24px; padding: 32px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.03); text-align: center; }
        .locker-display { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 20px; padding: 40px 20px; color: #fff; position: relative; overflow: hidden; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.1); }
        .locker-display::after { content: ""; position: absolute; top: -50%; right: -20%; width: 200px; height: 200px; background: var(--gold); opacity: 0.15; filter: blur(50px); border-radius: 50%; }
        
        .locker-num { font-size: 64px; font-weight: 900; color: var(--gold); line-height: 1; margin-bottom: 8px; text-shadow: 0 10px 20px rgba(201,153,63,0.3); }
        .locker-label { font-size: 11px; font-weight: 800; color: #94A3B8; text-transform: uppercase; letter-spacing: 2px; }
        
        .status-pill-large { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 100px; font-size: 13px; font-weight: 800; margin-bottom: 25px; }
        .status-active { background: #ECFDF5; color: #059669; }
        .status-waiting { background: #FFFBEB; color: #B45309; }
        
        .locker-info-list { text-align: left; background: #F8FAFC; border-radius: 16px; padding: 20px; border: 1px solid #F1F5F9; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
        .info-row:last-child { margin-bottom: 0; }
        .info-row span:first-child { color: var(--text-muted); font-weight: 600; }
        .info-row span:last-child { font-weight: 800; color: var(--text); }

        /* System Overview Bento */
        .system-overview { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .overview-item { padding: 25px 15px; border-radius: 20px; text-align: center; border: 1px solid transparent; }
        .ov-val { font-size: 32px; font-weight: 900; margin-bottom: 5px; }
        .ov-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; }
        
        .ov-free { background: #F0FDF4; color: #10B981; border-color: #DCFCE7; }
        .ov-rented { background: #FEF2F2; color: #EF4444; border-color: #FEE2E2; }
        .ov-repair { background: #FFFBEB; color: #D97706; border-color: #FEF3C7; }

        @media (max-width: 1024px) {
            .locker-grid { grid-template-columns: 1fr; }
            .system-overview { grid-template-columns: 1fr; }
        }
    </style>

    <?php $__f = getFlash(); if ($__f): ?>
    <div class="mg-alert mg-alert-<?= $__f['type'] === 'success' ? 'success' : 'danger' ?> animate-up" style="margin-bottom: 20px;">
        <?= htmlspecialchars($__f['message']) ?>
    </div>
    <?php endif; ?>

    <div class="locker-grid animate-up">
        <!-- LEFT: MY LOCKER STATUS -->
        <section class="card-my-locker">
            <h3 style="font-size: 18px; font-weight: 900; margin-bottom: 25px; text-align: left;">📦 Tủ đồ của bạn</h3>
            
            <?php if ($myLocker): ?>
                <?php if ($myLocker['trang_thai'] === 'pending'): ?>
                    <div class="status-pill-large status-waiting">⏳ ĐANG CHỜ DUYỆT</div>
                    <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 25px; font-weight: 600;">Yêu cầu thuê tủ của bạn đã được gửi đi. Nhân viên sẽ phân tủ cho bạn sớm nhất có thể.</p>
                <?php else: ?>
                    <div class="status-pill-large status-active">✅ ĐANG SỬ DỤNG</div>
                    <div class="locker-display">
                        <div class="locker-num"><?= htmlspecialchars($myLocker['so_tu'] ?? 'N/A') ?></div>
                        <div class="locker-label">SỐ TỦ CỦA BẠN</div>
                    </div>
                    <div class="locker-info-list">
                        <div class="info-row">
                            <span>Ngày bắt đầu</span>
                            <span><?= date('d/m/Y', strtotime($myLocker['ngay_bat_dau'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span>Ngày hết hạn</span>
                            <span><?= date('d/m/Y', strtotime($myLocker['ngay_ket_thuc'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span>Chi phí</span>
                            <span>100.000đ / tháng</span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="padding: 40px 0;">
                    <div style="font-size: 60px; margin-bottom: 20px; opacity: 0.3;">🔒</div>
                    <p style="color: var(--text-muted); font-size: 15px; font-weight: 600; margin-bottom: 30px;">Bạn chưa có tủ đồ cá nhân.</p>
                    <div style="background: #FFFBEB; border: 1px dashed #FDE68A; border-radius: 16px; padding: 20px; margin-bottom: 30px;">
                        <div style="font-size: 24px; font-weight: 900; color: #B45309;">100.000đ <small style="font-size: 12px; font-weight: 600;">/ tháng</small></div>
                        <div style="font-size: 11px; color: #D97706; font-weight: 700; margin-top: 5px;">THUÊ TỦ CÁ NHÂN TẠI PHÒNG TẬP</div>
                    </div>
                    <?php if ($lockerCount > 0): ?>
                        <button onclick="document.getElementById('requestModal').style.display='flex'" class="mg-btn mg-btn-primary mg-btn-block" style="padding: 16px;">ĐĂNG KÝ THUÊ NGAY 🚀</button>
                    <?php else: ?>
                        <div style="padding: 15px; background: #FEF2F2; color: #EF4444; border-radius: 12px; font-weight: 800; font-size: 13px;">HẾT TỦ TRỐNG TẠI CƠ SỞ</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- RIGHT: SYSTEM OVERVIEW -->
        <section class="mg-card" style="padding: 32px; border-radius: 24px;">
            <h3 style="font-size: 18px; font-weight: 900; margin-bottom: 30px;">🗄️ Tình trạng hệ thống tủ đồ</h3>
            
            <div class="system-overview">
                <div class="overview-item ov-free">
                    <div class="ov-val"><?= $lockerCount ?></div>
                    <div class="ov-label">CÒN TRỐNG</div>
                </div>
                <div class="overview-item ov-rented">
                    <div class="ov-val"><?= $rentedCount ?></div>
                    <div class="ov-label">ĐANG THUÊ</div>
                </div>
                <div class="overview-item ov-repair">
                    <div class="ov-val"><?= $maintenanceCount ?></div>
                    <div class="ov-label">BẢO TRÌ</div>
                </div>
            </div>

            <div style="margin-top: 40px; padding: 25px; background: #F8FAFC; border-radius: 20px; border: 1px solid #F1F5F9;">
                <h4 style="font-size: 15px; font-weight: 800; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 20px;">💡</span> Hướng dẫn sử dụng
                </h4>
                <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px; line-height: 1.8; color: var(--text-muted); font-weight: 600;">
                    <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                        <span style="color: var(--gold);">1.</span>
                        <span>Đăng ký thuê tủ trực tuyến trên hệ thống.</span>
                    </li>
                    <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                        <span style="color: var(--gold);">2.</span>
                        <span>Liên hệ lễ tân Monkey Gym để nhận mã khóa tủ vật lý.</span>
                    </li>
                    <li style="margin-bottom: 0; display: flex; gap: 10px;">
                        <span style="color: var(--gold);">3.</span>
                        <span>Hoàn tất thanh toán phí thuê tủ hàng tháng (Tiền mặt hoặc Chuyển khoản).</span>
                    </li>
                </ul>
            </div>
        </section>
    </div>

    <!-- MODAL XÁC NHẬN -->
    <div id="requestModal" style="display:none; position: fixed; z-index: 10000; inset: 0; background: rgba(0,0,0,0.8); align-items: center; justify-content: center; padding: 20px;">
        <div class="mg-card animate-up" style="max-width: 420px; width: 100%; padding: 32px; background: #fff;">
            <div style="font-size: 48px; text-align: center; margin-bottom: 20px;">📬</div>
            <h2 style="font-size: 22px; font-weight: 900; text-align: center; margin-bottom: 15px;">Xác nhận thuê tủ đồ</h2>
            <p style="text-align: center; color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 30px;">
                Bạn sẽ đăng ký thuê tủ đồ với mức phí <strong style="color: var(--gold);">100.000đ/tháng</strong>. Yêu cầu sẽ được Staff Monkey Gym duyệt sớm nhất.
            </p>
            <form action="<?= SITE_URL ?>/member/locker/request" method="POST">
                <?= csrfField('locker_request') ?>

                <!-- Duration Select -->
                <div style="margin-bottom: 25px; text-align: left;">
                    <label class="mg-form-label">THỜI GIAN THUÊ MONG MUỐN</label>
                    <select name="so_thang" class="mg-input" required>
                        <option value="1">1 Tháng (Cơ bản)</option>
                        <option value="3" selected>3 Tháng (Tiết kiệm)</option>
                        <option value="6">6 Tháng (Phổ biến)</option>
                        <option value="9">9 Tháng</option>
                        <option value="12">1 Năm (VIP)</option>
                    </select>
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 6px; font-weight: 600;">* Phí thuê sẽ được tính căn cứ theo số tháng bạn chọn.</p>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" class="mg-btn" style="flex: 1; background: #F1F5F9;" onclick="document.getElementById('requestModal').style.display='none'">HỦY</button>
                    <button type="submit" class="mg-btn mg-btn-primary" style="flex: 2;">XÁC NHẬN GỬI ✨</button>
                </div>
            </form>
        </div>
    </div>
</main>
