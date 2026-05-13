<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <div class="mg-page-header animate-up">
        <h1>Đánh giá Huấn luyện viên ⭐</h1>
        <p>Gửi gắm những phản hồi quý báu để giúp đội ngũ HLV hoàn thiện hơn mỗi ngày.</p>
    </div>

    <style>
        .review-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
        
        .card-trainer-review { background: #fff; border-radius: 24px; padding: 28px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.03); display: flex; flex-direction: column; transition: all 0.3s; position: relative; overflow: hidden; }
        .card-trainer-review:hover { transform: translateY(-8px); border-color: var(--gold); box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
        
        .trainer-header { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
        .trainer-avatar { width: 56px; height: 56px; background: #F8FAFC; border-radius: 18px; border: 2px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; font-size: 24px; object-fit: cover; }
        .trainer-meta h3 { font-size: 16px; font-weight: 900; margin-bottom: 2px; }
        .trainer-meta span { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .review-box { background: #F8FAFC; border-radius: 18px; padding: 18px; margin-bottom: 20px; position: relative; border: 1px solid #F1F5F9; }
        .review-box::before { content: "“"; position: absolute; top: 10px; right: 15px; font-size: 40px; color: var(--gold); opacity: 0.1; font-family: serif; }
        .star-row { color: var(--gold); font-size: 14px; margin-bottom: 8px; }
        .review-text { font-size: 13px; font-style: italic; color: var(--text); line-height: 1.6; }
        .status-pill { display: inline-block; padding: 4px 10px; border-radius: 100px; font-size: 10px; font-weight: 800; margin-top: 10px; }
        
        .empty-state { grid-column: 1 / -1; text-align: center; padding: 80px 20px; background: #fff; border-radius: 32px; border: 1px solid var(--border); }
        
        /* Star Selector in Modal */
        .star-rating-select { display: flex; gap: 8px; justify-content: center; margin: 20px 0; }
        .star-rating-select span { font-size: 32px; cursor: pointer; color: #E2E8F0; transition: all 0.2s; }
        .star-rating-select span.active { color: var(--gold); transform: scale(1.1); text-shadow: 0 0 10px rgba(201,153,63,0.3); }

        @media (max-width: 768px) {
            .review-grid { grid-template-columns: 1fr; }
        }
    </style>

    <?php $__f = getFlash(); if ($__f): ?>
    <div class="mg-alert mg-alert-<?= htmlspecialchars($__f['type']) ?> animate-up" style="margin-bottom: 25px;">
        <?= htmlspecialchars($__f['message']) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($suggestedSession)): ?>
        <div class="animate-up" style="background: rgba(132, 204, 22, 0.1); border: 1px solid #84cc16; border-radius: 20px; padding: 24px; margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px rgba(132, 204, 22, 0.15); border-left: 6px solid #84cc16;">
            <div>
                <h3 style="color: #4d7c0f; margin: 0 0 6px 0; font-size: 1.25rem; font-weight: 900;">✨ Bạn vừa hoàn thành buổi tập!</h3>
                <p style="color: var(--text); margin: 0; font-size: 0.95rem; font-weight: 500;">
                    Buổi tập với HLV <strong><?= htmlspecialchars($suggestedSession['ten_hlv']) ?></strong> 
                    vào lúc <span style="color: #84cc16; font-weight: 700;"><?= date('H:i - d/m/Y', strtotime($suggestedSession['ngay_gio_tap'])) ?></span> vừa rồi thế nào?
                </p>
            </div>
            
            <button onclick="openReviewForm(<?= $suggestedSession['ma_hlv'] ?>, '<?= addslashes($suggestedSession['ten_hlv']) ?>', null)" 
                    class="mg-btn"
                    style="background: #84cc16; color: #fff; font-weight: 800; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(132, 204, 22, 0.4); white-space: nowrap;">
                Đánh giá ngay ⭐️
            </button>
        </div>
    <?php endif; ?>

    <div class="review-grid animate-up">
        <?php if (empty($trainedBy)): ?>
            <div class="empty-state">
                <div style="font-size: 60px; margin-bottom: 20px;">💪</div>
                <h3 style="font-weight: 900; margin-bottom: 10px;">Chưa có lịch sử tập luyện</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 25px;">Bạn chưa tập cùng HLV nào. Hãy đặt lịch tập PT để bắt đầu trải nghiệm nhé!</p>
                <a href="<?= SITE_URL ?>/member/booking" class="mg-btn mg-btn-primary">Đặt lịch PT ngay 🚀</a>
            </div>
        <?php else: ?>
            <?php foreach ($trainedBy as $trainer): ?>
                <div class="card-trainer-review">
                    <div class="trainer-header">
                        <?php if (!empty($trainer['anh_dai_dien'])): ?>
                            <img src="<?= ASSET_URL . htmlspecialchars($trainer['anh_dai_dien']) ?>" class="trainer-avatar">
                        <?php else: ?>
                            <div class="trainer-avatar"><?= mb_substr($trainer['ten_dang_nhap'], 0, 1) ?></div>
                        <?php endif; ?>
                        <div class="trainer-meta">
                            <h3><?= htmlspecialchars($trainer['ten_dang_nhap']) ?></h3>
                            <span><?= htmlspecialchars($trainer['chuyen_mon'] ?? 'Fitness Trainer') ?></span>
                        </div>
                    </div>

                    <?php if ($trainer['existing_review']): ?>
                        <div class="review-box">
                            <div class="star-row">
                                <?= str_repeat('★', $trainer['existing_review']['so_sao']) ?><span style="color:#E2E8F0;"><?= str_repeat('★', 5-$trainer['existing_review']['so_sao']) ?></span>
                            </div>
                            <div class="review-text">"<?= htmlspecialchars($trainer['existing_review']['noi_dung'] ?? '') ?>"</div>
                            <div class="status-pill <?= $trainer['existing_review']['trang_thai']==='approved' ? 'mg-badge-success' : 'mg-badge-warning' ?>">
                                <?= ['pending'=>'Đang chờ duyệt', 'approved'=>'Đã công khai', 'rejected'=>'Bị từ chối'][$trainer['existing_review']['trang_thai']] ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 30px 10px; background: #F8FAFC; border-radius: 18px; margin-bottom: 20px; border: 1px dashed #E2E8F0;">
                            <span style="font-size: 24px; margin-bottom: 8px;">🌟</span>
                            <span style="font-size: 11px; font-weight: 800; color: var(--text-muted);">BẠN CHƯA GỬI ĐÁNH GIÁ</span>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; gap: 10px; margin-top: auto;">
                        <button class="mg-btn mg-btn-block" style="background: #F8FAFC; color: var(--text); border: 1px solid var(--border);"
                                onclick="openReviewForm(<?= $trainer['ma_hlv'] ?>, '<?= addslashes($trainer['ten_dang_nhap']) ?>', <?= htmlspecialchars(json_encode($trainer['existing_review']), ENT_QUOTES, 'UTF-8') ?>)">
                            <?= $trainer['existing_review'] ? '✎ Chỉnh sửa' : '⭐ Gửi đánh giá' ?>
                        </button>
                        <?php if ($trainer['existing_review']): ?>
                            <form action="<?= SITE_URL ?>/member/reviews/delete" method="POST" style="margin:0;" onsubmit="return confirm('Xoá đánh giá này?');">
                                <?= csrfField('review_delete') ?>
                                <input type="hidden" name="ma_dg" value="<?= $trainer['existing_review']['ma_dg'] ?>">
                                <button type="submit" class="mg-btn" style="background:#FEF2F2; color:#EF4444; border:1px solid #FEE2E2; padding:12px;">🗑️</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- REVIEW MODAL -->
    <div id="reviewModal" style="display:none; position: fixed; z-index: 10000; inset: 0; background: rgba(0,0,0,0.8); align-items: center; justify-content: center; padding: 20px;">
        <div class="mg-card animate-up" style="max-width: 450px; width: 100%; padding: 32px; background: #fff;">
            <h2 id="modalTitle" style="font-size: 22px; font-weight: 900; text-align: center; margin-bottom: 10px;">Đánh giá HLV</h2>
            <p style="text-align: center; color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">Ý kiến của bạn là nguồn động lực lớn cho đội ngũ!</p>
            
            <form action="<?= SITE_URL ?>/member/reviews/submit" method="POST">
                <?= csrfField('review_submit') ?>
                <input type="hidden" name="ma_hlv" id="hlvId">
                
                <div class="star-rating-select" id="starSelector">
                    <span data-val="1" onclick="setStars(1)">★</span>
                    <span data-val="2" onclick="setStars(2)">★</span>
                    <span data-val="3" onclick="setStars(3)">★</span>
                    <span data-val="4" onclick="setStars(4)">★</span>
                    <span data-val="5" onclick="setStars(5)">★</span>
                </div>
                <input type="hidden" name="so_sao" id="starInput" value="5">

                <div style="margin-bottom: 25px;">
                    <label style="display:block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Nhận xét chi tiết</label>
                    <textarea name="noi_dung" id="textContent" class="mg-input" rows="4" style="width: 100%;" placeholder="Ghi lại cảm nhận của bạn về sự tận tâm, chuyên môn..."></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" class="mg-btn" style="flex: 1; background: #F1F5F9;" onclick="document.getElementById('reviewModal').style.display='none'">ĐÓNG</button>
                    <button type="submit" class="mg-btn mg-btn-primary" style="flex: 2;">GỬI ĐÁNH GIÁ ✨</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function setStars(val) {
            document.getElementById('starInput').value = val;
            document.querySelectorAll('#starSelector span').forEach((el, i) => {
                el.classList.toggle('active', i < val);
            });
        }

        function openReviewForm(id, name, existing) {
            document.getElementById('hlvId').value = id;
            document.getElementById('modalTitle').textContent = 'Đánh giá: ' + name;
            if (existing) {
                setStars(existing.so_sao);
                document.getElementById('textContent').value = existing.noi_dung || '';
            } else {
                setStars(5);
                document.getElementById('textContent').value = '';
            }
            document.getElementById('reviewModal').style.display = 'flex';
        }
    </script>
</main>
