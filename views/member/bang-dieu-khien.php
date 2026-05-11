<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <?php
        $today     = date('Y-m-d');
        $ho_ten    = $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? 'Hội viên';
        
        // Việt hóa ngày tháng
        $daysVN = [
            'Sunday' => 'Chủ Nhật', 'Monday' => 'Thứ Hai', 'Tuesday' => 'Thứ Ba',
            'Wednesday' => 'Thứ Tư', 'Thursday' => 'Thứ Năm', 'Friday' => 'Thứ Sáu', 'Saturday' => 'Thứ Bảy'
        ];
        $todayVN = $daysVN[date('l')] . ', ' . date('d/m/Y');

        $expireStr = $memberInfo['ngay_het_han_goi'] ?? null;
        $daysLeft  = 0;
        $isActive  = false;
        if ($expireStr && $expireStr >= $today) {
            $isActive = true;
            $daysLeft = (new DateTime($today))->diff(new DateTime($expireStr))->days;
        }
        $bmi = ($memberInfo['chieu_cao'] && $memberInfo['can_nang'])
            ? round($memberInfo['can_nang'] / (($memberInfo['chieu_cao']/100)**2), 1)
            : 0;
    ?>

    <?php $__f = getFlash(); if ($__f): ?>
    <div class="mg-alert mg-alert-<?= $__f['type'] === 'success' ? 'success' : 'danger' ?>" style="margin-bottom: 20px;">
        <?= $__f['message'] ?>
    </div>
    <?php endif; ?>

    <div class="animate-up">
        <!-- TOP WELCOME SECTION -->
        <header class="dash-welcome">
            <div class="welcome-text">
                <h1>Chào buổi sáng, <span><?= htmlspecialchars($ho_ten) ?></span> 👋</h1>
                <p><?= $todayVN ?></p>
            </div>
            <div class="membership-badge">
                <span class="badge-icon">⭐</span>
                <div class="badge-info">
                    <span class="label">CẤP BẬC</span>
                    <span class="value">PREMIUM</span>
                </div>
            </div>
        </header>

        <style>
            .dash-welcome { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
            .welcome-text h1 { font-size: 32px; font-weight: 900; letter-spacing: -1.5px; margin-bottom: 4px; }
            .welcome-text h1 span { color: var(--gold); }
            .welcome-text p { color: var(--text-muted); font-weight: 600; font-size: 15px; }
            
            .membership-badge { display: flex; align-items: center; gap: 12px; background: #fff; padding: 10px 20px; border-radius: 100px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
            .badge-icon { font-size: 22px; }
            .badge-info { display: flex; flex-direction: column; }
            .badge-info .label { font-size: 10px; font-weight: 800; color: var(--text-muted); letter-spacing: 0.8px; }
            .badge-info .value { font-size: 14px; font-weight: 900; color: var(--gold); }

            /* Bento Grid Layout - Laptop Optimized */
            .dash-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 24px; margin-bottom: 40px; }
            
            /* Membership Pass Card */
            .card-pass { grid-column: span 6; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 32px; padding: 36px; color: #fff; position: relative; overflow: hidden; display: flex; align-items: center; border: 1px solid rgba(255,255,255,0.1); box-shadow: var(--shadow-lg); }
            .card-pass::before { content: ""; position: absolute; top: -50%; right: -20%; width: 350px; height: 350px; background: var(--gold); opacity: 0.2; filter: blur(70px); border-radius: 50%; }
            .pass-content { flex: 1; z-index: 2; }
            .pass-title { font-size: 12px; font-weight: 800; color: var(--gold); letter-spacing: 2.5px; margin-bottom: 14px; text-transform: uppercase; }
            .pass-name { font-size: 30px; font-weight: 900; margin-bottom: 18px; letter-spacing: -1px; }
            .pass-status { display: flex; gap: 18px; align-items: center; margin-bottom: 28px; flex-wrap: wrap; }
            .status-pill { padding: 5px 14px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 100px; color: #34d399; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
            .status-pill::before { content: ""; width: 7px; height: 7px; background: #34d399; border-radius: 50%; box-shadow: 0 0 10px #34d399; }
            .pass-expiry { font-size: 14px; color: #94A3B8; }
            .pass-expiry strong { color: #fff; }
            .pass-btns { display: flex; gap: 14px; }
            
            .card-qr-mini { width: 150px; height: 150px; background: #fff; border-radius: 24px; padding: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 20px 40px rgba(0,0,0,0.4); z-index: 2; transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); cursor: pointer; }
            .card-qr-mini:hover { transform: scale(1.1) rotate(3deg); }

            /* BMI Card */
            .card-bmi { grid-column: span 3; background: #fff; border-radius: 32px; padding: 28px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; border: 1px solid var(--border); transition: all 0.3s ease; box-shadow: var(--shadow-sm); }
            .card-bmi:hover { transform: translateY(-8px); border-color: var(--gold); box-shadow: var(--shadow-md); }
            .bmi-circle { position: relative; width: 140px; height: 140px; margin-bottom: 18px; }
            .bmi-value { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; }
            .bmi-number { font-size: 40px; font-weight: 900; line-height: 1; color: var(--text-primary); }
            .bmi-label { font-size: 11px; font-weight: 800; color: var(--text-muted); }

            /* Stats Column (Right Side on Laptop) */
            .card-stats-col { grid-column: span 3; display: flex; flex-direction: column; gap: 20px; }
            .stat-mini { background: #fff; border-radius: 24px; padding: 18px; display: flex; align-items: center; gap: 14px; border: 1px solid var(--border); transition: all 0.3s; box-shadow: var(--shadow-sm); cursor: pointer; }
            .stat-mini:hover { background: var(--gold-bg); transform: translateX(8px); border-color: var(--gold-light); }
            .stat-mini-icon { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
            .stat-mini-info .label { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 2px; }
            .stat-mini-info .value { font-size: 18px; font-weight: 900; color: var(--text-primary); }

            /* Profile Info Card */
            .card-profile-wide { grid-column: span 8; background: #fff; border-radius: 32px; padding: 32px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
            .profile-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-top: 20px; }
            .profile-item .label { font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
            .profile-item .value { font-size: 16px; font-weight: 700; color: var(--text-primary); }

            /* Announcement Card (Compact Sidebar) */
            .card-ann-side { grid-column: span 4; background: linear-gradient(135deg, #FFF7ED 0%, #fff 100%); border-radius: 32px; padding: 32px; border: 1px solid #FFEDD5; box-shadow: var(--shadow-sm); overflow: hidden; }
            .ann-item { display: flex; gap: 14px; align-items: center; padding: 14px; background: #fff; border-radius: 20px; border: 1px solid #FFEDD5; margin-bottom: 14px; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
            .ann-item:hover { transform: scale(1.03); box-shadow: 0 10px 20px rgba(154, 52, 18, 0.08); }

            /* Bottom Row Sections */
            .card-schedule { grid-column: span 7; background: #fff; border-radius: 32px; padding: 32px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
            .card-transactions { grid-column: span 5; background: #fff; border-radius: 32px; padding: 32px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
            
            .schedule-item { display: flex; justify-content: space-between; align-items: center; padding: 18px; border-radius: 24px; background: #F8FAFC; margin-bottom: 14px; border: 1px solid #F1F5F9; transition: all 0.3s; }
            .schedule-item:hover { background: #fff; border-color: var(--gold); transform: scale(1.01); box-shadow: var(--shadow-md); }
            .item-date { width: 52px; height: 52px; background: #fff; border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
            .item-date span:first-child { font-size: 11px; font-weight: 800; color: var(--gold); }
            .item-date span:last-child { font-size: 18px; font-weight: 900; }

            .list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
            .list-header h3 { font-size: 20px; font-weight: 900; color: var(--text-primary); letter-spacing: -0.5px; }
            .btn-link { font-size: 13px; font-weight: 700; color: var(--gold); text-decoration: none; padding: 8px 16px; border-radius: 10px; transition: 0.3s; background: var(--gold-bg); }
            .btn-link:hover { background: var(--gold-light); color: var(--gold-dark); transform: translateX(5px); }

            /* Responsive Adjustments */
            @media (max-width: 1200px) {
                .card-pass { grid-column: span 12; }
                .card-bmi { grid-column: span 6; }
                .card-stats-col { grid-column: span 6; }
            }

            @media (max-width: 1024px) {
                .card-schedule, .card-transactions { grid-column: span 12; }
                .card-profile-wide { grid-column: span 12; }
                .card-ann-side { grid-column: span 12; }
            }

            @media (max-width: 768px) {
                .dash-welcome { flex-direction: column; align-items: flex-start; gap: 20px; }
                .card-pass { grid-column: span 12; flex-direction: column; text-align: center; }
                .card-qr-mini { display: none; }
                .card-bmi, .card-stats-col { grid-column: span 12; }
                .profile-grid { grid-template-columns: 1fr; }
            }
        </style>

        <div class="dash-grid">
            <!-- Digital Membership Pass -->
            <div class="card-pass animate-up" style="animation-delay: 0.1s;">
                <div class="pass-content">
                    <div class="pass-title">Gói Tập Hiện Tại</div>
                    <div class="pass-name">Monkey Platinum Plus</div>
                    <div class="pass-status">
                        <div class="status-pill"><?= $isActive ? 'Đang hoạt động' : 'Hết hạn' ?></div>
                        <div class="pass-expiry">
                            Còn <strong><?= $daysLeft ?> ngày</strong> 
                            <span style="opacity: 0.6; margin-left: 5px;">
                                (Hạn: <?= $expireStr ? date('d/m/Y', strtotime($expireStr)) : '---' ?>)
                            </span>
                        </div>
                    </div>
                    <div class="pass-btns">
                        <button class="mg-btn mg-btn-primary" onclick="openQrModal()">🔳 QUÉT MÃ CHECK-IN</button>
                        <a href="<?= SITE_URL ?>/member/store" class="mg-btn" style="background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.1);">GIA HẠN</a>
                    </div>
                </div>
                <div class="card-qr-mini" onclick="openQrModal()">
                    <img id="miniQr" src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=MEMBER_<?= $memberInfo['ma_hoi_vien'] ?>_<?= time() ?>&color=0f172a&bgcolor=fff" width="120" height="120">
                </div>
            </div>

            <!-- BMI Card -->
            <div class="card-bmi animate-up" style="animation-delay: 0.2s;">
                <div class="bmi-circle">
                    <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#F1F5F9" stroke-width="3"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--gold)" stroke-width="3" 
                                stroke-dasharray="<?= min(($bmi/40)*100, 100) ?>, 100" stroke-linecap="round"/>
                    </svg>
                    <div class="bmi-value">
                        <span class="bmi-number"><?= $bmi ?></span>
                        <span class="bmi-label">ĐIỂM BMI</span>
                    </div>
                </div>
                <div style="padding: 6px 16px; background: #ECFDF5; color: #059669; border-radius: 100px; font-size: 13px; font-weight: 800; border: 1px solid rgba(5, 150, 105, 0.2);">Cân đối ✨</div>
            </div>

            <!-- Stats Column (Right) -->
            <div class="card-stats-col animate-up" style="animation-delay: 0.3s;">
                <div class="stat-mini" onclick="openBmiModal()">
                    <div class="stat-mini-icon" style="background: #FFF7ED;">📏</div>
                    <div class="stat-mini-info">
                        <div class="label">CHIỀU CAO</div>
                        <div class="value"><?= $memberInfo['chieu_cao'] ?? 0 ?> <small>cm</small></div>
                    </div>
                </div>
                <div class="stat-mini" onclick="openBmiModal()">
                    <div class="stat-mini-icon" style="background: #F0FDF4;">⚖️</div>
                    <div class="stat-mini-info">
                        <div class="label">CÂN NẶNG</div>
                        <div class="value"><?= $memberInfo['can_nang'] ?? 0 ?> <small>kg</small></div>
                    </div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-icon" style="background: #FEF2F2;">🔥</div>
                    <div class="stat-mini-info">
                        <div class="label">CALO ĐỐT</div>
                        <div class="value">2,450 <small>kcal</small></div>
                    </div>
                </div>
            </div>

            <!-- Profile Info Wide -->
            <div class="card-profile-wide animate-up" style="animation-delay: 0.4s;">
                <div class="list-header">
                    <h3>👤 Thông tin cá nhân</h3>
                    <button class="btn-link" onclick="openProfileModal()">Chỉnh sửa hồ sơ →</button>
                </div>
                <div class="profile-grid">
                    <div class="profile-item">
                        <div class="label">HỌ TÊN</div>
                        <div class="value"><?= htmlspecialchars($memberInfo['ho_ten']) ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="label">EMAIL</div>
                        <div class="value"><?= htmlspecialchars($memberInfo['email'] ?? 'Chưa cập nhật') ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="label">SỐ ĐIỆN THOẠI</div>
                        <div class="value"><?= htmlspecialchars($memberInfo['so_dien_thoai'] ?? 'Chưa cập nhật') ?></div>
                    </div>
                </div>
            </div>

            <!-- Announcements Sidebar -->
            <div class="card-ann-side animate-up" style="animation-delay: 0.5s;">
                <div class="list-header">
                    <h3 style="color: #9A3412;"><i class="fas fa-bullhorn me-2"></i> Thông báo</h3>
                    <?php if (!empty($announcements)): ?>
                    <span class="badge" style="background: #EA580C; color: #fff; font-size: 11px; padding: 4px 10px; border-radius: 6px;"><?= count($announcements) ?> Mới</span>
                    <?php endif; ?>
                </div>
                <div style="display: flex; flex-direction: column;">
                    <?php if (empty($announcements)): ?>
                        <div style="text-align: center; color: #9A3412; opacity: 0.5; font-size: 13px; padding: 30px;">Không có thông báo mới</div>
                    <?php else: ?>
                        <?php foreach (array_slice($announcements, 0, 2) as $ann): ?>
                        <div class="ann-item">
                            <div style="width: 44px; height: 44px; background: #FFEDD5; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">📢</div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 800; font-size: 14px; color: #9A3412; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($ann['tieu_de']) ?></div>
                                <div style="font-size: 11px; color: #C2410C; opacity: 0.7; font-weight: 700;"><?= date('d/m/Y', strtotime($ann['created_at'])) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Schedule Section -->
            <div class="card-schedule animate-up" style="animation-delay: 0.6s;">
                <div class="list-header">
                    <h3>📅 Lịch tập tuần này</h3>
                    <a href="<?= SITE_URL ?>/member/planner" class="btn-link">Lịch tập AI →</a>
                </div>
                <div class="schedule-item">
                    <div style="display: flex; gap: 14px; align-items: center;">
                        <div class="item-date">
                            <span>T2</span>
                            <span>10</span>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 15px; color: var(--text-primary);">Full Body Strength A</div>
                            <div style="font-size: 12px; color: var(--text-muted);">18:00 - PT: Nguyễn Văn A</div>
                        </div>
                    </div>
                    <div style="font-size: 11px; font-weight: 800; color: #059669; background: #ECFDF5; padding: 6px 12px; border-radius: 10px; border: 1px solid rgba(5, 150, 105, 0.2);">ĐÃ XONG</div>
                </div>
                <div class="schedule-item" style="border-color: var(--gold); background: #fff; box-shadow: 0 15px 30px rgba(201,153,63,0.08);">
                    <div style="display: flex; gap: 14px; align-items: center;">
                        <div class="item-date" style="background: var(--gold); color: #fff;">
                            <span>CN</span>
                            <span>11</span>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 15px; color: var(--text-primary);">Active Recovery <span style="font-size: 10px; background: #FEF2F2; color: #EF4444; padding: 3px 6px; border-radius: 6px; margin-left: 5px;">Hôm nay</span></div>
                            <div style="font-size: 12px; color: var(--text-muted);">06:30 - Tự tập</div>
                        </div>
                    </div>
                    <button class="mg-btn mg-btn-primary" style="padding: 10px 18px; font-size: 12px;" onclick="openQrModal()">🎯 ĐIỂM DANH</button>
                </div>
            </div>

            <!-- Transactions Section -->
            <div class="card-transactions animate-up" style="animation-delay: 0.7s;">
                <div class="list-header">
                    <h3>🛍️ Giao dịch gần đây</h3>
                    <a href="<?= SITE_URL ?>/member/history" class="btn-link">Lịch sử →</a>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <?php if (empty($transactions)): ?>
                        <div style="text-align: center; padding: 40px;">
                            <div style="font-size: 40px; margin-bottom: 12px;">🛒</div>
                            <div style="font-size: 14px; color: var(--text-muted);">Bạn chưa có giao dịch nào</div>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($transactions, 0, 4) as $tran): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid #F1F5F9;">
                            <div style="display: flex; gap: 14px; align-items: center;">
                                <div style="width: 44px; height: 44px; background: #F8FAFC; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                    <?= $tran['type'] === 'package' ? '💎' : (strpos($tran['ten_sp'] ?? '', 'Whey') !== false ? '🥛' : '📦') ?>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 15px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-primary);"><?= htmlspecialchars($tran['ten_sp'] ?? 'Giao dịch') ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;"><?= !empty($tran['ngay_mua']) ? date('d/m/Y', strtotime($tran['ngay_mua'])) : 'N/A' ?></div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 900; font-size: 15px; color: var(--text-primary);"><?= number_format($tran['gia_tien'], 0, ',', '.') ?>đ</div>
                                <div style="font-size: 10px; font-weight: 800; color: <?= $tran['trang_thai'] === 'completed' ? '#059669' : '#F59E0B' ?>;">
                                    <?= strtoupper($tran['trang_thai']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- QR Modal -->
<div id="qrModal" class="mg-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(8px);">
    <div style="background:#fff; border-radius:32px; padding:40px; width:90%; max-width:400px; text-align:center;">
        <h2 style="font-weight:900; margin-bottom:10px;">Mã QR Động</h2>
        <p style="font-size:14px; color:var(--text-muted); margin-bottom:10px;">Đưa mã này cho nhân viên quầy để điểm danh</p>
        
        <div style="font-size: 13px; font-weight: 800; color: #059669; margin-bottom: 20px; display: inline-block; background: #ECFDF5; padding: 6px 16px; border-radius: 100px;">
            Mã tự động làm mới sau <span id="qrTimer">60</span>s ⏳
        </div>

        <div style="background:#F8FAFC; padding:20px; border-radius:24px; margin-bottom:30px; display: inline-block; border: 2px dashed #E2E8F0;">
            <img id="mainQr" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=MEMBER_<?= $memberInfo['ma_hoi_vien'] ?>_<?= time() ?>&color=0f172a&bgcolor=F8FAFC" width="200" height="200">
        </div>
        <button class="mg-btn mg-btn-primary" style="width:100%; padding:16px;" onclick="closeQrModal()">ĐÓNG</button>
    </div>
</div>

<!-- BMI Modal -->
<div id="bmiModal" class="mg-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(8px);">
    <div style="background:#fff; border-radius:32px; padding:40px; width:90%; max-width:400px;">
        <h2 style="font-weight:900; margin-bottom:10px;">Chỉ số cơ thể</h2>
        <p style="font-size:14px; color:var(--text-muted); margin-bottom:25px;">Cập nhật để theo dõi tiến trình tập luyện</p>
        
        <form action="<?= SITE_URL ?>/member/dashboard/update-bmi" method="POST">
            <?= csrfField('update_bmi') ?>
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:800; color:var(--text-muted); margin-bottom:8px;">CHIỀU CAO (CM)</label>
                <input type="number" name="chieu_cao" value="<?= $memberInfo['chieu_cao'] ?>" class="mg-input" step="0.1" required style="width:100%; border-radius:12px; padding:12px;">
            </div>
            <div style="margin-bottom:30px;">
                <label style="display:block; font-size:12px; font-weight:800; color:var(--text-muted); margin-bottom:8px;">CÂN NẶNG (KG)</label>
                <input type="number" name="can_nang" value="<?= $memberInfo['can_nang'] ?>" class="mg-input" step="0.1" required style="width:100%; border-radius:12px; padding:12px;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <button type="button" class="mg-btn" style="border:1px solid var(--border);" onclick="closeBmiModal()">HỦY</button>
                <button type="submit" class="mg-btn mg-btn-primary">CẬP NHẬT</button>
            </div>
        </form>
    </div>
</div>

<!-- Profile Modal -->
<div id="profileModal" class="mg-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(8px);">
    <div style="background:#fff; border-radius:32px; padding:40px; width:90%; max-width:460px;">
        <h2 style="font-weight:900; margin-bottom:10px;">Hồ sơ cá nhân</h2>
        <p style="font-size:14px; color:var(--text-muted); margin-bottom:25px;">Chỉnh sửa thông tin liên hệ của bạn</p>
        
        <form action="<?= SITE_URL ?>/member/dashboard/update-profile" method="POST">
            <?= csrfField('update_profile') ?>
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:11px; font-weight:800; color:var(--text-muted); margin-bottom:6px;">HỌ TÊN</label>
                <input type="text" name="ho_ten" value="<?= htmlspecialchars($memberInfo['ho_ten']) ?>" class="mg-input" required style="width:100%; border-radius:12px; padding:12px;">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:11px; font-weight:800; color:var(--text-muted); margin-bottom:6px;">EMAIL</label>
                <input type="email" name="email" value="<?= htmlspecialchars($memberInfo['email'] ?? '') ?>" class="mg-input" required style="width:100%; border-radius:12px; padding:12px;">
            </div>
            <div style="margin-bottom:25px;">
                <label style="display:block; font-size:11px; font-weight:800; color:var(--text-muted); margin-bottom:6px;">SỐ ĐIỆN THOẠI</label>
                <input type="tel" name="so_dien_thoai" value="<?= htmlspecialchars($memberInfo['so_dien_thoai'] ?? '') ?>" class="mg-input" required style="width:100%; border-radius:12px; padding:12px;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <button type="button" class="mg-btn" style="border:1px solid var(--border);" onclick="closeProfileModal()">HỦY</button>
                <button type="submit" class="mg-btn mg-btn-primary">LƯU THAY ĐỔI</button>
            </div>
        </form>
    </div>
</div>

<script>
let qrInterval;
let timeLeft = 60;

function openQrModal() { 
    document.getElementById('qrModal').style.display = 'flex'; 
    startQrTimer();
}

function closeQrModal() { 
    document.getElementById('qrModal').style.display = 'none'; 
    clearInterval(qrInterval);
}

function startQrTimer() {
    timeLeft = 60;
    document.getElementById('qrTimer').innerText = timeLeft;
    
    clearInterval(qrInterval);
    qrInterval = setInterval(() => {
        timeLeft--;
        if(timeLeft <= 0) {
            // Truly dynamic QR: change the timestamp in the payload
            const dynamicData = "MEMBER_<?= $memberInfo['ma_hoi_vien'] ?>_" + Date.now();
            const qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + encodeURIComponent(dynamicData) + "&color=0f172a&bgcolor=F8FAFC";
            
            document.getElementById('mainQr').src = qrUrl;
            
            // Update mini QR too
            document.getElementById('miniQr').src = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" + encodeURIComponent(dynamicData) + "&color=0f172a&bgcolor=fff";
            
            // Add a brief flash animation
            document.getElementById('mainQr').style.opacity = 0.5;
            setTimeout(() => { document.getElementById('mainQr').style.opacity = 1; }, 200);
            
            timeLeft = 60;
        }
        document.getElementById('qrTimer').innerText = timeLeft;
    }, 1000);
}

function openBmiModal() { document.getElementById('bmiModal').style.display = 'flex'; }
function closeBmiModal() { document.getElementById('bmiModal').style.display = 'none'; }
function openProfileModal() { document.getElementById('profileModal').style.display = 'flex'; }
function closeProfileModal() { document.getElementById('profileModal').style.display = 'none'; }

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.className === 'mg-modal') {
        event.target.style.display = 'none';
        clearInterval(qrInterval);
    }
}
</script>
