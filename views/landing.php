<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monkey Gym - Vuot Qua Gioi Han, Kien Tao Ban Than</title>
    <meta name="description" content="Monkey Gym - Phong tap hien dai voi doi ngu HLV chuyen nghiep, he thong quan ly thong minh va cong dong hoi vien nang dong.">
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/landing.css">
</head>
<body class="landing-body">

    <!-- THONG BAO DIALOG (Admin) -->
    <?php if (!empty($announcements_list)): ?>
    <div id="announcementModal" class="modal-overlay" style="display:flex;">
        <div class="modal-box" style="max-width:640px; width:95%; max-height:88vh; display:flex; flex-direction:column; padding:2rem;">
            <div class="modal-icon" style="margin-bottom:0.5rem; font-size:2.5rem;">📢</div>
            <h2 style="margin-bottom:1.2rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:1rem; font-size:1.5rem; color:white;">Thông Báo Hệ Thống</h2>
            
            <div class="announcement-scroll" style="overflow-y:auto; flex:1; padding-right:10px; margin-bottom:1.5rem; text-align:left;">
                <?php foreach($announcements_list as $index => $a): ?>
                <div class="announcement-card">
                    <div class="announcement-card-header">
                        <?php if($index === 0): ?>
                            <span class="announcement-badge-new">Mới nhất</span>
                        <?php endif; ?>
                        <h3 class="announcement-card-title"><?= htmlspecialchars($a['tieu_de']) ?></h3>
                    </div>

                    <?php if(!empty($a['hinh_anh'])): ?>
                    <div class="announcement-img-wrapper">
                        <img src="<?= ASSET_URL . htmlspecialchars($a['hinh_anh']) ?>" alt="<?= htmlspecialchars($a['tieu_de']) ?>" class="announcement-img" loading="lazy">
                    </div>
                    <?php endif; ?>

                    <div class="announcement-card-body"><?= htmlspecialchars($a['noi_dung']) ?></div>
                    <div class="announcement-card-date">
                        <span>📅</span> <?= date('H:i - d/m/Y', strtotime($a['created_at'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button class="btn btn-primary" onclick="document.getElementById('announcementModal').style.display='none'" style="width:100%; padding:1rem; font-weight:600; font-size:1rem; border-radius:12px; cursor:pointer;">Đã hiểu</button>
        </div>
    </div>
    <style>
        /* Scrollbar */
        .announcement-scroll::-webkit-scrollbar { width: 6px; }
        .announcement-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); border-radius: 10px; }
        .announcement-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 10px; }
        .announcement-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }

        /* Card container */
        .announcement-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 1.3rem;
            margin-bottom: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .announcement-card:last-child { margin-bottom: 0; }
        .announcement-card:hover {
            border-color: rgba(16,185,129,0.25);
            box-shadow: 0 4px 20px rgba(16,185,129,0.06);
        }

        /* Header */
        .announcement-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.7rem;
            flex-wrap: wrap;
        }
        .announcement-card-title {
            color: #10b981;
            font-size: 1.1rem;
            margin: 0;
            line-height: 1.4;
        }
        .announcement-badge-new {
            font-size: 0.68rem;
            background: rgba(239,68,68,0.15);
            color: #ef4444;
            padding: 3px 10px;
            border-radius: 5px;
            border: 1px solid rgba(239,68,68,0.25);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            flex-shrink: 0;
            animation: pulse-badge 2s ease-in-out infinite;
        }
        @keyframes pulse-badge {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* Image */
        .announcement-img-wrapper {
            width: 100%;
            max-height: 300px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 0.8rem;
            background: rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .announcement-img {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: contain;
            display: block;
        }

        /* Body */
        .announcement-card-body {
            font-size: 0.93rem;
            color: #94a3b8;
            line-height: 1.7;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Date */
        .announcement-card-date {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
            padding-top: 0.7rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
    </style>
    <?php endif; ?>

    <!-- HEADER NAV -->
    <nav class="landing-nav" id="landingNav">
        <div class="nav-container">
            <a href="<?= SITE_URL ?>/" class="nav-logo">
                <img src="<?= ASSET_URL ?>/favicon.png" alt="Monkey Gym" width="36">
                <span>Monkey <strong>Gym</strong></span>
            </a>
            <div class="nav-links">
                <a href="#features">Tính năng</a>
                <a href="#trainers">Huấn luyện viên</a>
                <a href="#products">Sản phẩm</a>
                <a href="#pricing">Gói tập</a>
            </div>
            <div class="nav-actions">
                <a href="<?= SITE_URL ?>/login" class="btn-nav-login">Đăng nhập</a>
                <a href="<?= SITE_URL ?>/register" class="btn-nav-register">Đăng ký ngay</a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="hero-bg-grid"></div>
        <div class="hero-container">
            <div class="hero-badge">🔥 Phòng Tập Hàng Đầu</div>
            <h1 class="hero-title">
                Vượt Qua <span class="gradient-text">Giới Hạn</span><br>
                Kiến Tạo Bản Thân
            </h1>
            <p class="hero-desc">
                Monkey Gym — nơi công nghệ gặp gỡ thể lực. Hệ thống quản lý thông minh, đội ngũ HLV cao cấp và cộng đồng hội viên nhiệt huyết đang chờ bạn.
            </p>
            <div class="hero-actions">
                <a href="<?= SITE_URL ?>/register" class="btn-hero-primary">Bắt đầu miễn phí →</a>
                <a href="#features" class="btn-hero-ghost">Khám phá thêm</a>
            </div>

            <!-- KHUYẾN MÃI DÀNH CHO HỘI VIÊN MỚI -->
            <?php if (!empty($promotions)): ?>
            <div style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); border-radius: 12px; padding: 15px; margin: 2rem auto; max-width: 600px; color: white; border: 2px dashed #60a5fa; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4); animation: pulsePromo 2s infinite; text-align: left;">
                <h3 style="margin-top:0; font-size: 1.1rem; text-align:center; color: #fcd34d;">🎁 QUÀ TẶNG THÀNH VIÊN MỚI</h3>
                <div style="font-size: 0.9rem; margin-bottom: 10px; text-align: center;">Đăng ký ngay hôm nay để sử dụng các ưu đãi cực khủng:</div>
                
                <?php foreach($promotions as $p): ?>
                    <div style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 8px; margin-bottom: 8px; display:flex; justify-content: space-between; align-items:center;">
                        <div>
                            <div style="font-family: monospace; font-size: 1.2rem; font-weight: bold; color: #fcd34d; letter-spacing: 2px;"><?= htmlspecialchars($p['code']) ?></div>
                            <div style="font-size: 0.8rem; margin-top:4px;">
                                <?php if($p['loai_ap_dung'] === 'package') echo 'Mã mua Gói Tập';
                                      elseif($p['loai_ap_dung'] === 'product') echo 'Mã mua Sản Phẩm';
                                      else echo 'Áp dụng mọi dịch vụ'; ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.3rem; font-weight: bold; color: #fff;">
                                Giảm <?php echo ($p['phan_tram_giam'] > 0) ? $p['phan_tram_giam'].'%' : number_format($p['so_tien_giam']).'đ'; ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #fca5a5; margin-top:2px;">
                                ⏳ Chỉ còn <strong><?= $p['so_luong_con'] ?></strong> lượt!<br>
                                Hết hạn: <?= date('d/m/Y', strtotime($p['ngay_het_han'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <style>
                @keyframes pulsePromo {
                    0% { box-shadow: 0 0 0 0 rgba(96, 165, 250, 0.7); }
                    70% { box-shadow: 0 0 0 10px rgba(96, 165, 250, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(96, 165, 250, 0); }
                }
            </style>
            <?php endif; ?>

            <div class="hero-stats">
                <div class="stat-item"><span class="stat-num">500+</span><span>Hội viên</span></div>
                <div class="stat-sep"></div>
                <div class="stat-item"><span class="stat-num"><?= count($trainers) ?>+</span><span>HLV</span></div>
                <div class="stat-sep"></div>
                <div class="stat-item"><span class="stat-num"><?= count($packages) ?>+</span><span>Gói tập</span></div>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="section" id="features">
        <div class="section-container">
            <div class="section-header">
                <h2>Vì Sao Chọn Monkey Gym?</h2>
                <p>Hệ thống toàn diện — từ mua gói đến check-in, từ PT đến dinh dưỡng.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🏋️</div>
                    <h3>HLV Chuyên Nghiệp</h3>
                    <p>Đội ngũ HLV được chứng nhận, lên lịch PT linh hoạt trực tiếp trên ứng dụng.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Check-in QR Code</h3>
                    <p>Quét mã QR điểm danh tức khắc. Mỗi lần check-in tích thêm điểm thưởng.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⭐</div>
                    <h3>Hệ Thống Giảm Giá</h3>
                    <p>Săn voucher, mua combo, ưu đãi khách hàng thân thiết — chốt đơn giá hời, nâng cấp buổi tập.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📓</div>
                    <h3>Giao diện hiện đại</h3>
                    <p>Phong cách tối giản, bố cục chuyên nghiệp và mượt mà — mang lại trải nghiệm người dùng hoàn hảo trên mọi thiết bị.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛍️</div>
                    <h3>Cửa Hàng Online</h3>
                    <p>Mua Whey Protein, Pre-Workout ngay trên app. Nhận hàng tại quầy với mã QR.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Thuê Tủ Đồ</h3>
                    <p>Hội viên có thể thuê tủ đồ cá nhân hàng tháng — không cần mang đồ mỗi ngày.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- TRAINERS -->
    <section class="section section-dark" id="trainers">
        <div class="section-container">
            <div class="section-header">
                <h2>Đội Ngũ Huấn Luyện Viên</h2>
                <p>Những chuyên gia hàng đầu đồng hành cùng hành trình của bạn.</p>
            </div>
            <?php if (!empty($trainers)): ?>
            <div class="trainers-grid">
                <?php foreach (array_slice($trainers, 0, 4) as $t): ?>
                <?php
                    $tName = htmlspecialchars($t['ho_ten'] ?? $t['ten_dang_nhap']);
                    $tReviews = $trainerReviews[$t['ma_hlv']] ?? [];
                ?>
                <div class="trainer-card" style="cursor:pointer;" onclick="openTrainerModal(this)"
                    data-name="<?= $tName ?>"
                    data-spec="<?= htmlspecialchars($t['chuyen_mon'] ?? 'Tập luyện') ?>"
                    data-exp="<?= (int)($t['nam_kinh_nghiem'] ?? 0) ?>"
                    data-desc="<?= htmlspecialchars($t['gioi_thieu'] ?? $t['mo_ta'] ?? '') ?>"
                    data-rating="<?= number_format($t['avg_rating'] ?? 5, 1) ?>"
                    data-review-count="<?= (int)($t['review_count'] ?? 0) ?>"
                    data-avatar="<?= !empty($t['anh_dai_dien']) ? ASSET_URL . htmlspecialchars($t['anh_dai_dien']) : '' ?>"
                    data-reviews="<?= htmlspecialchars(json_encode($tReviews, JSON_UNESCAPED_UNICODE)) ?>"
                >
                    <div class="trainer-avatar">
                        <?php if (!empty($t['anh_dai_dien'])): ?>
                            <img src="<?= ASSET_URL . htmlspecialchars($t['anh_dai_dien']) ?>" alt="<?= $tName ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder">👤</div>
                        <?php endif; ?>
                    </div>
                    <div class="trainer-info">
                        <h3><?= $tName ?></h3>
                        <span class="trainer-spec"><?= htmlspecialchars($t['chuyen_mon'] ?? 'Tập luyện') ?></span>
                        <?php if (!empty($t['nam_kinh_nghiem'])): ?>
                        <span class="trainer-exp"><?= $t['nam_kinh_nghiem'] ?> năm KN</span>
                        <?php endif; ?>
                        <div class="trainer-rating">
                            <?php $stars = round($t['avg_rating'] ?? 5); ?>
                            <?= str_repeat('⭐', min($stars, 5)) ?>
                            <span>(<?= number_format($t['avg_rating'] ?? 5, 1) ?>) · <?= (int)($t['review_count'] ?? 0) ?> đánh giá</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">Chưa có thông tin huấn luyện viên.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- TRAINER DETAIL MODAL -->
    <div id="trainerModal" class="modal-overlay" style="display:none;">
        <div class="modal-box" style="max-width:640px; width:95%; max-height:90vh; display:flex; flex-direction:column; padding:0; overflow:hidden;">
            <!-- Header -->
            <div style="padding:2rem 2rem 1.5rem; text-align:center; border-bottom:1px solid rgba(255,255,255,0.08); flex-shrink:0;">
                <div id="tmAvatar" style="width:100px; height:100px; border-radius:50%; margin:0 auto 1rem; overflow:hidden; border:3px solid rgba(139,92,246,0.4); background:rgba(139,92,246,0.1); display:flex; align-items:center; justify-content:center; font-size:2.5rem;">👤</div>
                <h2 id="tmName" style="color:white; font-size:1.4rem; margin-bottom:0.4rem;"></h2>
                <span id="tmSpec" style="background:rgba(139,92,246,0.15); color:#a78bfa; padding:0.3rem 1rem; border-radius:50px; font-size:0.85rem; display:inline-block;"></span>
                <div id="tmExp" style="color:var(--text-secondary); font-size:0.9rem; margin-top:0.8rem;"></div>
                <div id="tmRating" style="margin-top:0.6rem; font-size:1.1rem;"></div>
            </div>
            <!-- Scrollable body -->
            <div style="overflow-y:auto; padding:1.5rem 2rem 2rem; flex:1;">
                <div id="tmDescBlock" style="margin-bottom:1.5rem;">
                    <h4 style="color:white; margin-bottom:0.5rem; font-size:0.95rem;">📝 Giới thiệu</h4>
                    <p id="tmDesc" style="color:var(--text-secondary); font-size:0.9rem; line-height:1.6;"></p>
                </div>
                <div id="tmReviewsBlock">
                    <h4 style="color:white; margin-bottom:1rem; font-size:0.95rem;">💬 Đánh giá từ hội viên <span id="tmReviewCount" style="color:var(--text-secondary); font-size:0.85rem;"></span></h4>
                    <div id="tmReviewsList"></div>
                    <p id="tmNoReviews" style="color:var(--text-secondary); font-size:0.85rem; display:none;">Chưa có đánh giá nào.</p>
                </div>
            </div>
            <!-- Close -->
            <div style="padding:1rem 2rem; border-top:1px solid rgba(255,255,255,0.08); flex-shrink:0;">
                <button class="btn btn-primary" onclick="document.getElementById('trainerModal').style.display='none'" style="width:100%; padding:0.8rem; font-size:0.95rem; border-radius:10px; cursor:pointer;">Đóng</button>
            </div>
        </div>
    </div>

    <!-- PRICING -->
    <?php
    $getBestPrice = function($originalPrice, $type) use ($promotions) {
        if (empty($promotions)) return $originalPrice;
        $bestPrice = $originalPrice;
        foreach ($promotions as $p) {
            if ($p['loai_ap_dung'] === 'all' || $p['loai_ap_dung'] === $type) {
                if ($p['phan_tram_giam'] > 0) {
                    $discounted = max(0, $originalPrice * (1 - $p['phan_tram_giam'] / 100));
                } else {
                    $discounted = max(0, $originalPrice - $p['so_tien_giam']);
                }
                if ($discounted < $bestPrice) {
                    $bestPrice = $discounted;
                }
            }
        }
        return $bestPrice;
    };
    ?>
    <section class="section" id="pricing">
        <div class="section-container">
            <div class="section-header">
                <h2>💪 Gói Tập (Dịch Vụ Thành Viên)</h2>
                <p>Lựa chọn mềm mại cho từng chu kỳ và mục tiêu tập luyện của bạn.</p>
            </div>
            <?php if (!empty($packages)): ?>
            <div class="pricing-grid">
                <?php foreach ($packages as $i => $pkg): ?>
                <div class="pricing-card <?= $i === 1 ? 'pricing-popular' : '' ?>">
                    <?php if ($i === 1): ?><div class="popular-badge">Phổ Biến Nhất</div><?php endif; ?>
                    <h3><?= htmlspecialchars($pkg['ten_goi']) ?></h3>
                    <?php $bestPrice = $getBestPrice($pkg['gia_tien'], 'package'); ?>
                    <?php if ($bestPrice < $pkg['gia_tien']): ?>
                        <div class="pkg-price">
                            <span style="font-size: 1.1rem; color: #9ca3af; text-decoration: line-through; margin-right: 8px;"><?= number_format($pkg['gia_tien']) ?>đ</span>
                            <br><span style="font-size: 0.9rem; color: var(--danger); font-weight: normal;">Chỉ còn: </span><span style="color: var(--danger);"><?= number_format($bestPrice) ?><span>đ</span></span>
                        </div>
                    <?php else: ?>
                        <div class="pkg-price"><?= number_format($pkg['gia_tien']) ?><span>đ</span></div>
                    <?php endif; ?>
                    <div class="pkg-duration"><?= $pkg['thoi_han_thang'] ?> tháng</div>
                    <?php if ($pkg['so_buoi_pt'] > 0): ?>
                    <div class="pkg-pt">✓ <?= $pkg['so_buoi_pt'] ?> buổi PT</div>
                    <?php endif; ?>
                    <a href="<?= SITE_URL ?>/register" class="btn-pricing">Đăng Ký Ngay</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- PRODUCTS -->
    <section class="section section-dark" id="products">
        <div class="section-container">
            <div class="section-header">
                <h2>🛍️ Cửa Hàng Sản Phẩm</h2>
                <p>Whey Protein, Pre-Workout và các sản phẩm bổ sung thể dục chất lượng cao.</p>
            </div>
            <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <?php if (!empty($p['hinh_anh'])): ?>
                        <div class="product-image">
                            <img src="<?= ASSET_URL . $p['hinh_anh'] ?>" alt="<?= htmlspecialchars($p['ten_sp']) ?>">
                        </div>
                    <?php else: ?>
                        <div class="product-image-placeholder">
                            <div style="font-size: 2rem;">📦</div>
                        </div>
                    <?php endif; ?>
                    <div class="product-info">
                        <h4><?= htmlspecialchars($p['ten_sp']) ?></h4>
                        <p class="text-muted" style="font-size: 0.85rem; margin: 0.5rem 0;">Mo ta: <?= htmlspecialchars(substr($p['mo_ta'] ?? '', 0, 60)) ?>...</p>
                        <div class="product-footer">
                            <?php $bestPrice = $getBestPrice($p['gia_tien'], 'product'); ?>
                            <?php if ($bestPrice < $p['gia_tien']): ?>
                                <div style="display:flex; flex-direction:column; line-height:1.2;">
                                    <span style="font-size: 0.85rem; color: #9ca3af; text-decoration: line-through;"><?= number_format($p['gia_tien']) ?>đ</span>
                                    <span class="product-price" style="color: var(--danger); font-size:1.1rem;">
                                        <span style="font-size: 0.75rem; font-weight: normal;">Ưu đãi: </span><?= number_format($bestPrice) ?><span>đ</span>
                                    </span>
                                </div>
                            <?php else: ?>
                                <span class="product-price"><?= number_format($p['gia_tien']) ?><span>đ</span></span>
                            <?php endif; ?>
                            <a href="<?= SITE_URL ?>/member/store" class="btn-product-buy">Mua Ngay</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">Khong co san pham nao co san.</p>
            <?php endif; ?>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="<?= SITE_URL ?>/member/store" class="btn-shop-all">🏪 Xem Toàn Bộ Sản Phẩm →</a>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="section-container" style="text-align:center;">
            <h2>Sẵn sàng bắt đầu?</h2>
            <p style="margin: 1rem 0 2rem; color: var(--text-secondary);">Tham gia cộng đồng Monkey Gym ngay hôm nay — miễn phí đăng ký.</p>
            <a href="<?= SITE_URL ?>/register" class="btn-hero-primary" style="font-size: 1.1rem;">Tạo tài khoản miễn phí →</a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="landing-footer">
        <div class="section-container">
            <div class="footer-brand">
                <img src="<?= ASSET_URL ?>/favicon.png" width="30" alt="Logo">
                <span><strong>Monkey Gym</strong> &copy; <?= date('Y') ?></span>
            </div>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Vượt qua giới hạn, kiến tạo bản thân.</p>
        </div>
    </footer>

    <!-- AI CHATBOT COMPONENT -->
    <?php require_once __DIR__ . '/components/chatbot.php'; ?>

    <script>
        // Sticky nav on scroll
        window.addEventListener('scroll', () => {
            document.getElementById('landingNav').classList.toggle('scrolled', window.scrollY > 50);
        });
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                document.querySelector(a.getAttribute('href'))?.scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Trainer detail modal
        function openTrainerModal(el) {
            const modal = document.getElementById('trainerModal');
            const name = el.dataset.name;
            const spec = el.dataset.spec;
            const exp = parseInt(el.dataset.exp) || 0;
            const desc = el.dataset.desc;
            const rating = el.dataset.rating;
            const reviewCount = parseInt(el.dataset.reviewCount) || 0;
            const avatar = el.dataset.avatar;
            let reviews = [];
            try { reviews = JSON.parse(el.dataset.reviews); } catch(e) {}

            // Avatar
            const tmAvatar = document.getElementById('tmAvatar');
            if (avatar) {
                tmAvatar.innerHTML = `<img src="${avatar}" style="width:100%;height:100%;object-fit:cover;">`;
            } else {
                tmAvatar.innerHTML = '👤';
            }

            document.getElementById('tmName').textContent = name;
            document.getElementById('tmSpec').textContent = spec;

            const tmExp = document.getElementById('tmExp');
            if (exp > 0) {
                tmExp.style.display = '';
                tmExp.innerHTML = `🏆 ${exp} năm kinh nghiệm`;
            } else {
                tmExp.style.display = 'none';
            }

            // Rating
            const stars = Math.round(parseFloat(rating));
            document.getElementById('tmRating').innerHTML =
                '⭐'.repeat(Math.min(stars, 5)) +
                ` <span style="color:var(--text-secondary); font-size:0.9rem;">(${rating}) · ${reviewCount} đánh giá</span>`;

            // Description
            const tmDescBlock = document.getElementById('tmDescBlock');
            if (desc) {
                tmDescBlock.style.display = '';
                document.getElementById('tmDesc').textContent = desc;
            } else {
                tmDescBlock.style.display = 'none';
            }

            // Reviews list
            const listEl = document.getElementById('tmReviewsList');
            const noEl = document.getElementById('tmNoReviews');
            listEl.innerHTML = '';
            document.getElementById('tmReviewCount').textContent = `(${reviews.length})`;

            if (reviews.length === 0) {
                noEl.style.display = '';
            } else {
                noEl.style.display = 'none';
                reviews.forEach(r => {
                    const rStars = '★'.repeat(r.so_sao);
                    const date = new Date(r.created_at);
                    const dateStr = date.toLocaleDateString('vi-VN', { day:'2-digit', month:'2-digit', year:'numeric' });
                    const div = document.createElement('div');
                    div.style.cssText = 'border-bottom:1px solid rgba(255,255,255,0.06); padding:0.8rem 0;';
                    div.innerHTML = `
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.3rem;">
                            <strong style="color:white; font-size:0.85rem;">${r.ten_hoi_vien}</strong>
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <span style="color:#f59e0b; font-size:0.9rem;">${rStars}</span>
                                <span style="color:var(--text-secondary); font-size:0.75rem;">${dateStr}</span>
                            </div>
                        </div>
                        ${r.noi_dung ? `<p style="color:var(--text-secondary); font-size:0.85rem; margin:0; line-height:1.5;">"${r.noi_dung}"</p>` : ''}
                    `;
                    listEl.appendChild(div);
                });
            }

            modal.style.display = 'flex';
        }

        // Close modal on overlay click
        document.getElementById('trainerModal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
    </script>
</body>
</html>
