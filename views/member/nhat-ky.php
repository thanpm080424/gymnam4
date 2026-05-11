<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <div class="mg-page-header animate-up">
        <h1>Nhật ký hành trình 📓</h1>
        <p>Ghi lại những giọt mồ hôi, những cột mốc và cảm xúc trên con đường chinh phục bản thân.</p>
    </div>

    <style>
        .journal-layout { display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; align-items: start; }
        
        /* Left: Timeline Feed */
        .timeline { position: relative; padding-left: 30px; }
        .timeline::before { content: ''; position: absolute; left: 0; top: 10px; bottom: 0; width: 2px; background: var(--border); border-radius: 100px; }
        
        .timeline-item { position: relative; margin-bottom: 40px; }
        .timeline-dot { position: absolute; left: -34px; top: 10px; width: 10px; height: 10px; background: var(--gold); border: 3px solid #fff; border-radius: 50%; box-shadow: 0 0 0 4px rgba(201,153,63,0.15); z-index: 2; }
        
        .card-entry { background: #fff; border-radius: 24px; padding: 25px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: transform 0.3s; }
        .card-entry:hover { transform: translateX(5px); border-color: var(--gold); }
        
        .entry-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .entry-date { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .entry-title { font-size: 18px; font-weight: 900; color: var(--text); margin-top: 4px; }
        
        .entry-content { font-size: 14px; line-height: 1.7; color: var(--text); }
        
        .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; margin-top: 20px; }
        .media-item { border-radius: 14px; overflow: hidden; border: 1px solid #F1F5F9; background: #F8FAFC; cursor: zoom-in; }
        .media-item img { width: 100%; height: 120px; object-fit: cover; transition: transform 0.3s; }
        .media-item:hover img { transform: scale(1.05); }
        .media-item video { width: 100%; border-radius: 14px; }

        /* Right: Post Box */
        .card-post { background: #fff; border-radius: 24px; padding: 30px; border: 1px solid var(--border); position: sticky; top: 30px; }
        .post-input-group { margin-bottom: 20px; }
        .post-input-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; }
        
        .drop-area { border: 2px dashed var(--border); border-radius: 16px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.2s; background: #F8FAFC; }
        .drop-area:hover { border-color: var(--gold); background: #FFFBEB; }
        .drop-area .icon { font-size: 30px; display: block; margin-bottom: 10px; }
        .drop-area .text { font-size: 12px; font-weight: 700; color: var(--text-muted); }

        @media (max-width: 1024px) {
            .journal-layout { grid-template-columns: 1fr; }
            .card-post { position: static; margin-bottom: 40px; order: -1; }
        }
    </style>

    <?php $__f = getFlash(); if ($__f): ?>
    <div class="mg-alert mg-alert-<?= $__f['type'] === 'success' ? 'success' : 'danger' ?> animate-up" style="margin-bottom: 25px;">
        <?= htmlspecialchars($__f['message']) ?>
    </div>
    <?php endif; ?>

    <div class="journal-layout animate-up">
        <!-- LEFT: TIMELINE FEED -->
        <section class="timeline">
            <?php if (empty($entries)): ?>
                <div class="card-entry" style="text-align: center; padding: 60px 20px;">
                    <div style="font-size: 60px; margin-bottom: 20px;">📖</div>
                    <h3 style="font-weight: 900; margin-bottom: 10px;">Chưa có nhật ký tập luyện</h3>
                    <p style="color: var(--text-muted); font-size: 14px;">Hãy bắt đầu ghi lại những thành tựu đầu tiên của bạn ngay hôm nay!</p>
                </div>
            <?php else: ?>
                <?php foreach ($entries as $entry): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="card-entry">
                            <div class="entry-header">
                                <div>
                                    <div class="entry-date"><?= date('l, d/m/Y - H:i', strtotime($entry['created_at'])) ?></div>
                                    <h3 class="entry-title"><?= htmlspecialchars($entry['tieu_de']) ?></h3>
                                </div>
                                <form action="<?= SITE_URL ?>/member/journal/delete" method="POST" onsubmit="return confirm('Xoá bài viết này?')">
                                    <input type="hidden" name="ma_nk" value="<?= $entry['ma_nk'] ?>">
                                    <button type="submit" style="background:none; border:none; color:#EF4444; font-size:16px; cursor:pointer; opacity:0.5; transition:0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.5'">✕</button>
                                </form>
                            </div>
                            <div class="entry-content">
                                <?= nl2br(htmlspecialchars($entry['noi_dung'])) ?>
                            </div>
                            
                            <?php if (!empty($entry['media'])): ?>
                                <div class="media-grid">
                                    <?php foreach ($entry['media'] as $m): ?>
                                        <div class="media-item">
                                            <?php if ($m['loai_media'] === 'image'): ?>
                                                <img src="<?= ASSET_URL . htmlspecialchars($m['duong_dan']) ?>" onclick="window.open(this.src)">
                                            <?php else: ?>
                                                <video src="<?= ASSET_URL . htmlspecialchars($m['duong_dan']) ?>" controls></video>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- RIGHT: NEW POST FORM -->
        <aside class="card-post">
            <h3 style="font-size: 18px; font-weight: 900; margin-bottom: 25px; display: flex; align-items: center; gap: 8px;">
                <span>✍️</span> Viết nhật ký mới
            </h3>
            <form action="<?= SITE_URL ?>/member/journal/create" method="POST" enctype="multipart/form-data">
                <div class="post-input-group">
                    <label>Hôm nay bạn tập gì? *</label>
                    <input type="text" name="tieu_de" class="mg-input" placeholder="Ví dụ: Chân & Mông cháy hết mình..." required>
                </div>
                <div class="post-input-group">
                    <label>Cảm nhận sau buổi tập</label>
                    <textarea name="noi_dung" class="mg-input" rows="5" placeholder="Cơ thể bạn cảm thấy thế nào? Mục tiêu hôm nay đã hoàn thành chưa?"></textarea>
                </div>
                <div class="post-input-group">
                    <label>Ảnh hoặc Video khoảnh khắc</label>
                    <div class="drop-area" onclick="document.getElementById('mediaFiles').click()">
                        <span class="icon">📸</span>
                        <span class="text">Thêm tối đa 3 ảnh hoặc video tập luyện</span>
                    </div>
                    <input type="file" id="mediaFiles" name="media[]" multiple accept="image/*,video/mp4" style="display:none;" onchange="previewMedia(this)">
                    <div id="mediaPreviewBox" style="display:flex; gap:8px; flex-wrap:wrap; margin-top:12px;"></div>
                </div>
                <button type="submit" class="mg-btn mg-btn-primary mg-btn-block" style="padding: 16px;">LƯU HÀNH TRÌNH ✨</button>
            </form>
        </aside>
    </div>

    <script>
    function previewMedia(input) {
        const box = document.getElementById('mediaPreviewBox');
        box.innerHTML = '';
        const files = Array.from(input.files).slice(0, 3);
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const wrap = document.createElement('div');
                wrap.style.cssText = 'position:relative; width:60px; height:60px; border-radius:8px; overflow:hidden; border:1px solid var(--gold);';
                const el = file.type.startsWith('video') ? document.createElement('video') : document.createElement('img');
                el.src = e.target.result;
                el.style.cssText = 'width:100%; height:100%; object-fit:cover;';
                wrap.appendChild(el);
                box.appendChild(wrap);
            };
            reader.readAsDataURL(file);
        });
    }
    </script>
</main>
