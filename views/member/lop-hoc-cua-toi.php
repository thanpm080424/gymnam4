<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <style>
        .my-classes-container { padding: 1.5rem 0; font-family: 'Inter', sans-serif; color: #fff; }
        .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; }
        .page-title { font-size: 2.2rem; font-weight: 900; text-transform: uppercase; letter-spacing: -1px; }
        .page-title span { color: #84cc16; }

        /* Tabs */
        .tabs-header { display: flex; gap: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 2rem; }
        .tab-btn { padding: 12px 5px; background: none; border: none; color: #a1a1aa; font-weight: 700; cursor: pointer; position: relative; font-size: 1rem; transition: 0.3s; }
        .tab-btn.active { color: #84cc16; }
        .tab-btn.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background: #84cc16; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Card List */
        .class-list { display: flex; flex-direction: column; gap: 1rem; }
        .my-class-card { background: #18181b; border-radius: 16px; border: 1px solid rgba(255,255,255,0.08); display: flex; overflow: hidden; position: relative; transition: 0.3s; }
        .my-class-card:hover { border-color: rgba(255,255,255,0.2); }
        
        .card-img { width: 150px; background-size: cover; background-position: center; flex-shrink: 0; }
        .card-body { padding: 1.5rem; flex: 1; display: flex; justify-content: space-between; align-items: center; }
        
        .class-meta { display: flex; flex-direction: column; gap: 5px; }
        .class-time { font-size: 1.2rem; font-weight: 800; color: #84cc16; }
        .class-date { font-size: 0.9rem; color: #a1a1aa; }
        .class-name { font-size: 1.3rem; font-weight: 800; margin: 5px 0; }
        .class-trainer { font-size: 0.85rem; color: #71717a; }

        .btn-cancel { padding: 10px 20px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; text-transform: uppercase; font-size: 0.8rem; }
        .btn-cancel:hover { background: #ef4444; color: #fff; }

        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .status-success { background: rgba(132, 204, 22, 0.1); color: #84cc16; }
        .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .status-completed { background: rgba(255, 255, 255, 0.05); color: #a1a1aa; }

        @media (max-width: 600px) {
            .my-class-card { flex-direction: column; }
            .card-img { width: 100%; height: 120px; }
            .card-body { flex-direction: column; align-items: flex-start; gap: 20px; }
            .btn-cancel { width: 100%; }
        }
    </style>

    <div class="my-classes-container animate-up">
        <div class="page-header">
            <div>
                <h1 class="page-title">Lớp Học <span>Của Tôi</span></h1>
                <p style="color: #a1a1aa;">Quản lý và xem lại lịch trình tập luyện nhóm của bạn.</p>
            </div>
            <a href="<?= SITE_URL ?>/member/classes" class="mg-qr-btn" style="text-decoration: none; padding: 10px 20px; background: #84cc16; color: #000; border-radius: 8px; font-weight: 800; font-size: 0.9rem;">+ ĐẶT LỚP MỚI</a>
        </div>

        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchTab('upcoming', this)">SẮP DIỄN RA (<?= count($upcoming) ?>)</button>
            <button class="tab-btn" onclick="switchTab('history', this)">LỊCH SỬ (<?= count($history) ?>)</button>
        </div>

        <!-- Tab: Sắp diễn ra -->
        <div id="tab-upcoming" class="tab-content active">
            <div class="class-list">
                <?php if(!empty($upcoming)): ?>
                    <?php foreach($upcoming as $b): ?>
                    <div class="my-class-card">
                        <div class="card-img" style="background-image: url('<?= htmlspecialchars($b['hinh_anh']) ?>');"></div>
                        <div class="card-body">
                            <div class="class-meta">
                                <div class="class-time"><?= date('H:i', strtotime($b['gio_bat_dau'])) ?> - <?= date('H:i', strtotime($b['gio_ket_thuc'])) ?></div>
                                <div class="class-date">📅 <?= date('d/m/Y', strtotime($b['ngay_hoc'])) ?></div>
                                <h3 class="class-name"><?= htmlspecialchars($b['ten_lop']) ?></h3>
                                <div class="class-trainer">👤 HLV: <?= htmlspecialchars($b['ten_hlv']) ?></div>
                            </div>
                            <button class="btn-cancel" onclick="cancelBooking(<?= $b['ma_dat_cho'] ?>, this)">Hủy Đặt Chỗ</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; color: #a1a1aa; padding: 4rem; background: #18181b; border-radius: 16px; border: 1px dashed rgba(255,255,255,0.1);">
                        Bạn chưa có lịch học nào sắp tới.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Lịch sử -->
        <div id="tab-history" class="tab-content">
            <div class="class-list">
                <?php if(!empty($history)): ?>
                    <?php foreach($history as $b): 
                        $isCancelled = $b['trang_thai'] === 'da_huy';
                        $statusText = $isCancelled ? 'Đã Hủy' : 'Đã Kết Thúc';
                        $statusClass = $isCancelled ? 'status-cancelled' : 'status-completed';
                    ?>
                    <div class="my-class-card" style="opacity: 0.6;">
                        <div class="card-img" style="background-image: url('<?= htmlspecialchars($b['hinh_anh']) ?>'); filter: grayscale(1);"></div>
                        <div class="card-body">
                            <div class="class-meta">
                                <div class="class-time" style="color: #71717a;"><?= date('H:i', strtotime($b['gio_bat_dau'])) ?> - <?= date('H:i', strtotime($b['gio_ket_thuc'])) ?></div>
                                <div class="class-date">📅 <?= date('d/m/Y', strtotime($b['ngay_hoc'])) ?></div>
                                <h3 class="class-name"><?= htmlspecialchars($b['ten_lop']) ?></h3>
                                <div class="class-trainer">👤 HLV: <?= htmlspecialchars($b['ten_hlv']) ?></div>
                            </div>
                            <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; color: #a1a1aa; padding: 4rem; background: #18181b; border-radius: 16px;">
                        Chưa có lịch sử lớp học.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabId, btn) {
            // Hide all contents
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            // Deactivate all buttons
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            
            // Show target content and button
            document.getElementById('tab-' + tabId).classList.add('active');
            btn.classList.add('active');
        }

        function cancelBooking(id, btn) {
            if(!confirm('Bạn chắc chắn muốn hủy đặt chỗ cho lớp học này?')) return;

            btn.innerHTML = 'Đang xử lý...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('ma_dat_cho', id);

            fetch('<?= SITE_URL ?>/api/member/cancel-class', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                    btn.innerHTML = 'Hủy Đặt Chỗ';
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert('Lỗi kết nối mạng!');
                btn.innerHTML = 'Hủy Đặt Chỗ';
                btn.disabled = false;
            });
        }
    </script>
</main>
