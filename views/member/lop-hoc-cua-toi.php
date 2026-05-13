<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <style>
        .my-classes-container { 
            padding: 20px 0; 
            font-family: 'Inter', sans-serif; 
        }

        /* Header Section */
        .page-header { 
            margin-bottom: 35px; 
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .header-text h1 { 
            font-size: 32px; 
            font-weight: 900; 
            letter-spacing: -1.5px;
            margin-bottom: 4px;
            color: var(--text);
        }
        .header-text h1 span { color: var(--gold); }
        .header-text p { color: var(--text-muted); font-size: 15px; font-weight: 600; }

        /* Tabs Redesign */
        .tabs-header { 
            display: flex; 
            gap: 12px; 
            margin-bottom: 30px; 
            padding: 6px;
            background: #F1F5F9;
            border-radius: 16px;
            width: fit-content;
        }
        .tab-btn { 
            padding: 10px 24px; 
            background: transparent; 
            border: none; 
            color: var(--text-muted); 
            font-weight: 800; 
            cursor: pointer; 
            font-size: 13px; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tab-btn.active { 
            background: #fff; 
            color: var(--gold-dark); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Card List */
        .class-list { display: flex; flex-direction: column; gap: 16px; }
        
        .my-class-card { 
            background: #fff; 
            border-radius: 24px; 
            border: 1px solid var(--border); 
            display: flex; 
            overflow: hidden; 
            position: relative; 
            transition: all 0.3s; 
            box-shadow: var(--shadow-sm);
        }
        .my-class-card:hover { 
            transform: translateY(-4px);
            border-color: var(--gold-light);
            box-shadow: var(--shadow-md); 
        }
        
        .card-img { 
            width: 180px; 
            background-size: cover; 
            background-position: center; 
            flex-shrink: 0; 
            position: relative;
        }
        .card-img::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, rgba(255,255,255,0.1), transparent);
        }
        
        .card-body { 
            padding: 24px 30px; 
            flex: 1; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .class-meta { display: flex; flex-direction: column; gap: 6px; }
        .class-time { font-size: 1.25rem; font-weight: 900; color: var(--gold); }
        .class-date { font-size: 0.9rem; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; gap: 6px; }
        .class-name { font-size: 1.4rem; font-weight: 900; margin: 4px 0; color: var(--text); letter-spacing: -0.5px; }
        .class-trainer { font-size: 13px; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .trainer-avatar-mini { width: 22px; height: 22px; background: var(--gold-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--gold-dark); font-size: 10px; font-weight: 800; }

        .btn-cancel { 
            padding: 12px 24px; 
            background: #FEF2F2; 
            color: #ef4444; 
            border: 1px solid rgba(239, 68, 68, 0.1); 
            border-radius: 14px; 
            font-weight: 800; 
            cursor: pointer; 
            transition: all 0.3s; 
            text-transform: uppercase; 
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .btn-cancel:hover { background: #ef4444; color: #fff; transform: scale(1.05); }

        .status-badge { 
            padding: 8px 16px; 
            border-radius: 12px; 
            font-size: 11px; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .status-success { background: #ECFDF5; color: #059669; border: 1px solid rgba(5,150,105,0.1); }
        .status-cancelled { background: #FEF2F2; color: #ef4444; border: 1px solid rgba(239,68,68,0.1); }
        .status-completed { background: #F8FAFC; color: var(--text-muted); border: 1px solid var(--border); }

        .empty-state {
            text-align: center; 
            color: var(--text-muted); 
            padding: 80px 20px; 
            background: #fff; 
            border-radius: 32px; 
            border: 1px dashed var(--border);
        }

        @media (max-width: 768px) {
            .my-class-card { flex-direction: column; }
            .card-img { width: 100%; height: 160px; }
            .card-body { padding: 20px; flex-direction: column; align-items: flex-start; gap: 20px; }
            .btn-cancel { width: 100%; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 20px; }
        }
    </style>

    <div class="my-classes-container animate-up">
        <div class="page-header">
            <div class="header-text">
                <h1>Lớp Học <span>Của Tôi</span></h1>
                <p>Quản lý và xem lại lịch trình tập luyện nhóm của bạn.</p>
            </div>
            <a href="<?= SITE_URL ?>/member/classes" class="mg-btn mg-btn-primary">
                <span style="font-size: 18px;">+</span> ĐẶT LỚP MỚI
            </a>
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
                        <div class="card-img" style="background-image: url('<?= htmlspecialchars((string)($b['hinh_anh'] ?? '')) ?>');"></div>
                        <div class="card-body">
                            <div class="class-meta">
                                <div class="class-time"><?= date('H:i', strtotime($b['gio_bat_dau'])) ?> - <?= date('H:i', strtotime($b['gio_ket_thuc'])) ?></div>
                                <div class="class-date">
                                    <span style="font-size: 14px;">📅</span> <?= date('d/m/Y', strtotime($b['ngay_hoc'])) ?>
                                </div>
                                <h3 class="class-name"><?= htmlspecialchars((string)($b['ten_lop'] ?? '')) ?></h3>
                                <div class="class-trainer">
                                    <div class="trainer-avatar-mini"><?= strtoupper(substr($b['ten_hlv'] ?? 'G', 0, 1)) ?></div>
                                    <span>HLV: <?= htmlspecialchars((string)($b['ten_hlv'] ?? '')) ?></span>
                                </div>
                            </div>
                            <button class="btn-cancel" onclick="cancelBooking(<?= (int)$b['ma_dat_cho'] ?>, this)">Hủy Đặt Chỗ</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div style="font-size: 50px; margin-bottom: 20px;">🧘</div>
                        <h3 style="color: var(--text); font-weight: 900; margin-bottom: 10px;">Chưa có lịch học sắp tới</h3>
                        <p style="font-size: 14px; margin-bottom: 25px;">Hãy khám phá các lớp Group X bùng nổ năng lượng ngay!</p>
                        <a href="<?= SITE_URL ?>/member/classes" class="mg-btn" style="background: var(--gold-bg); color: var(--gold-dark); border: 1px solid var(--gold-border);">Xem lịch lớp học →</a>
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
                        $statusText = $isCancelled ? 'Đã Hủy' : 'Đã Tham Gia';
                        $statusClass = $isCancelled ? 'status-cancelled' : 'status-success';
                    ?>
                    <div class="my-class-card" style="opacity: 0.8;">
                        <div class="card-img" style="background-image: url('<?= htmlspecialchars((string)($b['hinh_anh'] ?? '')) ?>'); filter: grayscale(0.5);"></div>
                        <div class="card-body">
                            <div class="class-meta">
                                <div class="class-time" style="color: var(--text-muted);"><?= date('H:i', strtotime($b['gio_bat_dau'])) ?> - <?= date('H:i', strtotime($b['gio_ket_thuc'])) ?></div>
                                <div class="class-date">📅 <?= date('d/m/Y', strtotime($b['ngay_hoc'])) ?></div>
                                <h3 class="class-name"><?= htmlspecialchars((string)($b['ten_lop'] ?? '')) ?></h3>
                                <div class="class-trainer">
                                    <div class="trainer-avatar-mini" style="background: #F1F5F9; color: #94A3B8;"><?= strtoupper(substr($b['ten_hlv'] ?? 'G', 0, 1)) ?></div>
                                    <span>HLV: <?= htmlspecialchars((string)($b['ten_hlv'] ?? '')) ?></span>
                                </div>
                            </div>
                            <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div style="font-size: 50px; margin-bottom: 20px;">📜</div>
                        <h3 style="color: var(--text); font-weight: 900; margin-bottom: 10px;">Chưa có lịch sử lớp học</h3>
                        <p style="font-size: 14px;">Các lớp học bạn đã tham gia sẽ xuất hiện tại đây.</p>
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
