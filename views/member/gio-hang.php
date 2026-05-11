<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <div class="mg-page-header animate-up">
        <h1>Giỏ hàng của bạn 🛒</h1>
        <p>Kiểm tra các sản phẩm đã chọn trước khi tiến hành đặt hàng.</p>
    </div>

    <style>
        .cart-layout { display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; align-items: start; }
        
        /* Left: Cart Items */
        .card-cart-items { background: #fff; border-radius: 24px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .cart-table { width: 100%; border-collapse: collapse; }
        .cart-table th { background: #F8FAFC; padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        .cart-table td { padding: 25px 20px; border-bottom: 1px solid #F1F5F9; }
        .cart-table tr:last-child td { border-bottom: none; }
        
        .item-info { display: flex; align-items: center; gap: 15px; }
        .item-thumb { width: 50px; height: 50px; background: #F8FAFC; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid #F1F5F9; }
        .item-name { font-weight: 800; font-size: 15px; color: var(--text); }
        
        .item-price { font-size: 14px; font-weight: 600; color: var(--text-muted); }
        .item-qty { font-size: 14px; font-weight: 800; color: var(--text); }
        .item-total { font-size: 16px; font-weight: 900; color: var(--gold); }
        
        .remove-btn { background: #FEF2F2; color: #EF4444; border: none; width: 32px; height: 32px; border-radius: 10px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
        .remove-btn:hover { background: #EF4444; color: #fff; }

        /* Right: Checkout Summary */
        .card-checkout-summary { background: #fff; border-radius: 24px; padding: 32px; border: 1px solid var(--border); position: sticky; top: 30px; }
        .summary-title { font-size: 18px; font-weight: 900; margin-bottom: 25px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; font-weight: 600; color: var(--text-muted); }
        .summary-total { margin-top: 25px; padding-top: 25px; border-top: 2px dashed var(--border); display: flex; justify-content: space-between; align-items: center; }
        .total-label { font-size: 16px; font-weight: 900; color: var(--text); }
        .total-amount { font-size: 28px; font-weight: 900; color: var(--gold); }

        .empty-cart { text-align: center; padding: 80px 20px; background: #fff; border-radius: 32px; border: 1px solid var(--border); grid-column: 1 / -1; }

        @media (max-width: 1024px) {
            .cart-layout { grid-template-columns: 1fr; }
            .card-checkout-summary { position: static; order: -1; margin-bottom: 30px; }
        }
    </style>

    <?php $__f = getFlash(); if ($__f): ?>
    <div class="mg-alert mg-alert-<?= $__f['type'] === 'success' ? 'success' : 'danger' ?> animate-up" style="margin-bottom: 25px;">
        <?= htmlspecialchars($__f['message']) ?>
    </div>
    <?php endif; ?>

    <div class="cart-layout animate-up">
        <?php if (empty($cart)): ?>
            <div class="empty-cart">
                <div style="font-size: 70px; margin-bottom: 20px;">🛒</div>
                <h3 style="font-weight: 900; margin-bottom: 10px;">Giỏ hàng của bạn đang trống</h3>
                <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 30px;">Có vẻ như bạn chưa chọn được sản phẩm nào ưng ý.</p>
                <a href="<?= SITE_URL ?>/member/store" class="mg-btn mg-btn-primary" style="padding: 16px 32px;">KHÁM PHÁ CỬA HÀNG 🛍️</a>
            </div>
        <?php else: ?>
            <!-- LEFT: ITEMS LIST -->
            <section class="card-cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>SẢN PHẨM</th>
                            <th>GIÁ</th>
                            <th>SỐ LƯỢNG</th>
                            <th style="text-align: right;">THÀNH TIỀN</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): ?>
                            <tr>
                                <td>
                                    <div class="item-info">
                                        <div class="item-thumb">📦</div>
                                        <div class="item-name"><?= htmlspecialchars($item['ten_sp']) ?></div>
                                    </div>
                                </td>
                                <td><span class="item-price"><?= number_format($item['gia_ban'],0,',','.') ?>đ</span></td>
                                <td><span class="item-qty"><?= $item['qty'] ?></span></td>
                                <td style="text-align: right;"><span class="item-total"><?= number_format($item['gia_ban']*$item['qty'],0,',','.') ?>đ</span></td>
                                <td style="text-align: right;">
                                    <form method="POST" action="<?= SITE_URL ?>/member/cart/remove" style="margin:0">
                                        <?= csrfField('cart_remove') ?>
                                        <input type="hidden" name="ma_san_pham" value="<?= $item['ma_san_pham'] ?>">
                                        <button type="submit" class="remove-btn">✕</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- RIGHT: SUMMARY -->
            <aside class="card-checkout-summary">
                <h3 class="summary-title">💳 Tóm tắt đơn hàng</h3>
                <div class="summary-row">
                    <span>Tạm tính (<?= count($cart) ?> sản phẩm)</span>
                    <span><?= number_format($total,0,',','.') ?>đ</span>
                </div>
                <div class="summary-row">
                    <span>Phí dịch vụ</span>
                    <span style="color: #10B981;">MIỄN PHÍ</span>
                </div>
                <div class="summary-row">
                    <span>Khuyến mãi</span>
                    <span>0đ</span>
                </div>
                
                <div class="summary-total">
                    <span class="total-label">Tổng cộng</span>
                    <span class="total-amount"><?= number_format($total,0,',','.') ?>đ</span>
                </div>
                
                <!-- PHƯƠNG THỨC THANH TOÁN -->
                <div style="margin-top: 25px;">
                    <p style="font-size: 13px; font-weight: 800; color: var(--text-muted); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Phương thức thanh toán</p>
                    <div style="display: grid; gap: 10px;">
                        <label style="display: flex; align-items: center; gap: 12px; padding: 15px; background: #F8FAFC; border: 2px solid var(--border); border-radius: 16px; cursor: pointer; transition: all 0.2s;" class="payment-option">
                            <input type="radio" name="phuong_thuc" value="tien_mat" checked style="accent-color: var(--gold); width: 18px; height: 18px;">
                            <div>
                                <div style="font-weight: 800; font-size: 14px;">Tiền mặt tại quầy</div>
                                <div style="font-size: 11px; color: var(--text-muted);">Thanh toán khi nhận hàng</div>
                            </div>
                        </label>
                        <label style="display: flex; align-items: center; gap: 12px; padding: 15px; background: #F8FAFC; border: 2px solid var(--border); border-radius: 16px; cursor: pointer; transition: all 0.2s;" class="payment-option">
                            <input type="radio" name="phuong_thuc" value="chuyen_khoan" style="accent-color: var(--gold); width: 18px; height: 18px;">
                            <div>
                                <div style="font-weight: 800; font-size: 14px;">Chuyển khoản (VNPay)</div>
                                <div style="font-size: 11px; color: var(--text-muted);">Thanh toán online an toàn</div>
                            </div>
                        </label>
                    </div>
                </div>

                <style>
                    .payment-option:has(input:checked) { border-color: var(--gold) !important; background: #FFFDF5 !important; }
                </style>

                <form method="POST" action="<?= SITE_URL ?>/member/cart/checkout" style="margin-top: 25px;" id="checkout-form">
                    <?= csrfField('cart_checkout') ?>
                    <input type="hidden" name="phuong_thuc" id="hidden-phuong-thuc" value="tien_mat">
                    <button type="submit" class="mg-btn mg-btn-primary mg-btn-block" style="padding: 18px; font-size: 15px;">XÁC NHẬN ĐẶT HÀNG ✨</button>
                </form>

                <script>
                    document.querySelectorAll('input[name="phuong_thuc"]').forEach(radio => {
                        radio.addEventListener('change', (e) => {
                            document.getElementById('hidden-phuong-thuc').value = e.target.value;
                        });
                    });
                </script>
                
                <div style="margin-top: 25px; padding: 20px; background: #F0F9FF; border-radius: 16px; border: 1px solid #E0F2FE;">
                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <span style="font-size: 20px;">💡</span>
                        <p style="font-size: 12px; line-height: 1.6; color: #0369A1; font-weight: 600;">
                            Sau khi đặt hàng, bạn có thể nhận sản phẩm trực tiếp tại quầy lễ tân Monkey Gym.
                        </p>
                    </div>
                </div>
            </aside>
        <?php endif; ?>
    </div>
</main>
