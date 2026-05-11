<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <!-- STORE HERO SECTION -->
    <div class="store-hero animate-up">
        <div class="hero-content">
            <h1>Monkey Gym <span>Marketplace</span> 🛒</h1>
            <p>Nâng tầm tập luyện với các gói Premium & Thực phẩm bổ sung chính hãng.</p>
        </div>
        <div class="hero-actions">
            <a href="<?= SITE_URL ?>/member/cart" class="cart-btn">
                <span class="cart-icon">🛒</span>
                <span class="cart-text">Giỏ hàng của bạn</span>
                <!-- <span class="cart-count">2</span> -->
            </a>
        </div>
    </div>

    <style>
        .store-hero { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 40px; border-radius: 24px; margin-bottom: 30px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.03); border-left: 6px solid var(--gold); }
        .hero-content h1 { font-size: 30px; font-weight: 900; letter-spacing: -1px; margin-bottom: 8px; }
        .hero-content h1 span { color: var(--gold); }
        .hero-content p { color: var(--text-muted); font-weight: 600; font-size: 15px; }
        
        .cart-btn { display: flex; align-items: center; gap: 12px; background: var(--gold); color: #fff; padding: 12px 24px; border-radius: 100px; text-decoration: none; font-weight: 800; transition: all 0.3s; box-shadow: 0 8px 20px rgba(201,153,63,0.3); }
        .cart-btn:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(201,153,63,0.4); }
        .cart-count { background: #fff; color: var(--gold); font-size: 10px; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        /* Store Sections */
        .section-title { font-size: 20px; font-weight: 900; margin: 40px 0 20px; display: flex; align-items: center; gap: 12px; }
        .section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); margin-left: 10px; }
        
        .store-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
        
        /* Product Cards */
        .card-item { background: #fff; border-radius: 24px; padding: 28px; display: flex; flex-direction: column; border: 1px solid var(--border); transition: all 0.3s; position: relative; overflow: hidden; }
        .card-item:hover { transform: translateY(-8px); border-color: var(--gold); box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
        
        .card-item h3 { font-size: 20px; font-weight: 900; margin-bottom: 10px; }
        .card-item .price { font-size: 28px; font-weight: 900; color: var(--gold); margin-bottom: 20px; }
        
        .features-list { list-style: none; padding: 0; margin: 0 0 25px 0; flex: 1; }
        .features-list li { margin-bottom: 12px; font-size: 14px; color: var(--text-muted); display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .features-list li .check { color: var(--gold); font-weight: 900; }

        .image-box { height: 200px; background: #F8FAFC; border-radius: 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; font-size: 50px; background-size: cover; background-position: center; border: 1px solid #F1F5F9; }
        
        .buy-group { display: flex; flex-direction: column; gap: 10px; }
        .promo-row { position: relative; margin-bottom: 5px; }
        .promo-row input { width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border); font-size: 13px; font-weight: 700; background: #F8FAFC; }
        
        /* PT Gold Card */
        .card-pt { background: linear-gradient(135deg, #FFFBEB 0%, #FFFFFF 100%); border: 1px solid #FDE68A; }
        .card-pt .price { color: #B45309; }
        .card-pt .check { color: #D97706 !important; }

        @media (max-width: 768px) {
            .store-hero { flex-direction: column; text-align: center; padding: 30px 20px; }
            .hero-actions { margin-top: 20px; width: 100%; }
            .cart-btn { justify-content: center; }
            .store-grid { grid-template-columns: 1fr; }
        }
    </style>

    <?php $__f = getFlash(); if ($__f): ?>
    <div class="mg-alert mg-alert-<?= $__f['type'] === 'success' ? 'success' : 'danger' ?>" style="margin-bottom: 20px;">
        <?= htmlspecialchars($__f['message']) ?>
    </div>
    <?php endif; ?>

    <!-- CATEGORY: GYM PASS -->
    <h2 class="section-title">🎫 Thẻ Hội Viên Cơ Sở</h2>
    <div class="store-grid animate-up">
        <?php foreach($packages as $p): ?>
        <div class="card-item">
            <div class="pass-tag" style="position: absolute; top: 15px; right: 15px; background: rgba(201,153,63,0.1); color: var(--gold); padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800;">OFFICIAL</div>
            <h3><?= htmlspecialchars($p['ten_goi']) ?></h3>
            <div class="price"><?= number_format($p['gia_tien']) ?>đ</div>
            <ul class="features-list">
                <li><span class="check">✔</span> Thời hạn tập luyện: <strong><?= $p['thoi_han_thang'] ?> tháng</strong></li>
                <li><span class="check">✔</span> Tặng kèm PT: <strong><?= $p['so_buoi_pt'] ?> buổi</strong></li>
                <li><span class="check">✔</span> Dịch vụ Full: Máy lạnh, Tủ đồ</li>
            </ul>
            <div class="buy-group">
                <div class="promo-row">
                    <input type="text" class="promo-input" placeholder="Mã giảm giá (nếu có)">
                </div>
                <form action="<?= SITE_URL ?>/member/buy" method="POST" onsubmit="copyPromo(this)">
                    <input type="hidden" name="ma_goi" value="<?= $p['ma_goi'] ?>">
                    <input type="hidden" name="phuong_thuc" value="tien_mat">
                    <input type="hidden" name="ma_giam_gia" value="">
                    <?= csrfField('buy_package') ?>
                    <button type="submit" class="mg-btn mg-btn-block" style="background: #F8FAFC; color: var(--text); border: 1px solid var(--border);">💵 Tiền mặt tại quầy</button>
                </form>
                <form action="<?= SITE_URL ?>/member/buy" method="POST" onsubmit="copyPromo(this)">
                    <input type="hidden" name="ma_goi" value="<?= $p['ma_goi'] ?>">
                    <input type="hidden" name="phuong_thuc" value="chuyen_khoan">
                    <input type="hidden" name="ma_giam_gia" value="">
                    <?= csrfField('buy_package') ?>
                    <button type="submit" class="mg-btn mg-btn-primary mg-btn-block">🏦 Chuyển khoản VNPay</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- CATEGORY: PT SESSIONS -->
    <h2 class="section-title">💪 Gói Huấn Luyện Viên 1:1</h2>
    <div class="store-grid animate-up">
        <?php foreach($ptPackages as $pt): ?>
        <div class="card-item card-pt">
            <h3><?= htmlspecialchars($pt['ten_goi']) ?></h3>
            <div class="price"><?= number_format($pt['gia_tien']) ?>đ</div>
            <ul class="features-list">
                <li><span class="check">⚡</span> Số buổi kèm: <strong><?= $pt['so_buoi_pt'] ?> buổi</strong></li>
                <li><span class="check">⚡</span> Lộ trình 1:1 cá nhân hóa</li>
                <li><span class="check">⚡</span> Tư vấn dinh dưỡng hàng ngày</li>
            </ul>
            <form action="<?= SITE_URL ?>/member/buy" method="POST">
                <input type="hidden" name="ma_goi" value="<?= $pt['ma_goi'] ?>">
                <input type="hidden" name="phuong_thuc" value="tien_mat">
                <?= csrfField('buy_package') ?>
                <button type="submit" class="mg-btn mg-btn-block" style="background: #D97706; color: #fff;">Mua ngay 🔥</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- CATEGORY: SUPPLEMENTS -->
    <h2 class="section-title">💊 Thực Phẩm Bổ Sung</h2>
    <div class="store-grid animate-up">
        <?php foreach($products as $prod): ?>
        <div class="card-item">
            <?php
                $maSP   = $prod['ma_san_pham'] ?? $prod['ma_sp'] ?? 0;
                $tenSP  = $prod['ten_sp'] ?? 'Sản phẩm';
                $giaBan = $prod['gia_ban'] ?? $prod['gia_tien'] ?? 0;
                $tonKho = $prod['so_luong_ton'] ?? $prod['ton_kho'] ?? 0;
                $anhSP  = $prod['anh_sp'] ?? $prod['hinh_anh'] ?? '';
                $moTa   = $prod['mo_ta'] ?? '';
            ?>
            <div class="image-box" <?= !empty($anhSP) ? "style=\"background-image:url('".ASSET_URL.htmlspecialchars($anhSP)."')\"" : "" ?>>
                <?= empty($anhSP) ? "📦" : "" ?>
            </div>
            <h3><?= htmlspecialchars($tenSP) ?></h3>
            <p style="color: var(--text-muted); font-size: 13px; line-height: 1.5; margin-bottom: 20px; flex: 1;"><?= htmlspecialchars(mb_strimwidth($moTa, 0, 80, '...')) ?></p>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
                <div>
                    <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); letter-spacing: 0.5px;">GIÁ BÁN</div>
                    <div style="font-size: 24px; font-weight: 900; color: var(--gold); line-height: 1;"><?= number_format((float)$giaBan) ?>đ</div>
                </div>
                <div style="font-size: 11px; font-weight: 700; color: var(--text-muted);">Kho: <?= (int)$tonKho ?></div>
            </div>

            <div style="display: flex; gap: 10px;">
                <form action="<?= SITE_URL ?>/member/cart/add" method="POST" style="flex: 1;">
                    <?= csrfField('cart_add') ?>
                    <input type="hidden" name="ma_san_pham" value="<?= $maSP ?>">
                    <input type="hidden" name="so_luong" value="1">
                    <button type="submit" class="mg-btn mg-btn-block" style="background: #F8FAFC; color: var(--text); border: 1px solid var(--border); font-size: 12px;">+ Giỏ</button>
                </form>
                <form action="<?= SITE_URL ?>/member/store/buy-product" method="POST" style="flex: 1.5;">
                    <?= csrfField('buy_product') ?>
                    <input type="hidden" name="ma_sp" value="<?= $maSP ?>">
                    <button type="submit" class="mg-btn mg-btn-primary mg-btn-block" style="font-size: 12px;">Mua ngay</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
function copyPromo(form) {
    const card = form.closest('.card-item');
    const promoInput = card ? card.querySelector('.promo-input') : null;
    const hiddenPromo = form.querySelector('input[name="ma_giam_gia"]');
    if (promoInput && hiddenPromo) {
        hiddenPromo.value = promoInput.value.trim().toUpperCase();
    }
}
</script>