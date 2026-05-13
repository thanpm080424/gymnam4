<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <style>
        .groupx-container { 
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

        /* Class Grid */
        .class-list { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 24px; 
        }

        .class-card { 
            background: #fff; 
            border: 1px solid var(--border); 
            border-radius: 28px; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            position: relative;
            box-shadow: var(--shadow-sm);
        }

        .class-card:hover { 
            transform: translateY(-8px); 
            border-color: var(--gold-light);
            box-shadow: var(--shadow-md); 
        }
        
        .class-img-wrapper {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        .class-img { 
            width: 100%;
            height: 100%;
            background-size: cover; 
            background-position: center; 
            transition: transform 0.6s ease;
        }
        .class-card:hover .class-img { transform: scale(1.08); }

        .class-badge { 
            position: absolute; 
            top: 15px; 
            left: 15px; 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(8px);
            color: var(--text); 
            padding: 6px 14px; 
            border-radius: 10px; 
            font-size: 11px; 
            font-weight: 800; 
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .class-info { padding: 24px; flex: 1; display: flex; flex-direction: column; }
        .class-time-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .class-time { font-size: 18px; font-weight: 900; color: var(--gold); }
        .class-date { font-size: 12px; color: var(--text-muted); font-weight: 700; background: #F8FAFC; padding: 4px 10px; border-radius: 8px; border: 1px solid #F1F5F9; }
        
        .class-name { font-size: 20px; font-weight: 900; margin-bottom: 10px; line-height: 1.2; letter-spacing: -0.5px; color: var(--text); }
        .class-trainer { font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-weight: 600; }
        .trainer-avatar { width: 24px; height: 24px; background: var(--gold-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--gold-dark); font-size: 11px; font-weight: 800; border: 1px solid var(--gold-border); }
        
        /* Seat Status */
        .slot-container { margin-top: auto; }
        .slot-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 12px; font-weight: 800; }
        .slot-label { color: var(--text-muted); }
        .slot-count { color: var(--text); }
        .slot-count.full { color: #ef4444; }

        .slot-progress-bg { height: 8px; background: #F1F5F9; border-radius: 10px; overflow: hidden; margin-bottom: 20px; }
        .slot-progress-fill { height: 100%; background: var(--gold); border-radius: 10px; transition: width 1s ease-in-out; }
        .slot-progress-fill.warning { background: #f59e0b; }
        .slot-progress-fill.full { background: #ef4444; }

        /* Premium Buttons */
        .btn-book { 
            width: 100%; 
            padding: 12px; 
            font-weight: 800; 
            border-radius: 14px; 
            cursor: pointer; 
            transition: all 0.3s ease; 
            text-transform: uppercase; 
            font-size: 13px; 
            border: none;
            letter-spacing: 0.5px;
        }
        .btn-available { 
            background: var(--gold); 
            color: #fff; 
            box-shadow: 0 6px 15px var(--gold-bg);
        }
        .btn-available:hover { 
            background: var(--gold-dark); 
            transform: scale(1.02);
        }
        .btn-booked { background: #ECFDF5; color: #059669; cursor: not-allowed; border: 1px solid rgba(5,150,105,0.2); }
        .btn-full { background: #FEF2F2; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); cursor: not-allowed; }

        /* Modal Redesign */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); display: none; justify-content: center; align-items: center; z-index: 9999; padding: 20px; }
        .modal-card { background: #fff; width: 100%; max-width: 480px; border-radius: 32px; border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-lg); }
        .modal-img { height: 200px; background-size: cover; background-position: center; position: relative; }
        .modal-img::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, #fff, transparent); }
        .modal-content { padding: 30px; margin-top: -30px; position: relative; z-index: 2; }
        .modal-title { font-size: 24px; font-weight: 900; margin-bottom: 8px; letter-spacing: -1px; color: var(--text); }
        .modal-desc { color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 24px; font-weight: 500; }
        .modal-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 30px; }
        .info-item { background: #F8FAFC; padding: 12px 15px; border-radius: 16px; border: 1px solid #F1F5F9; }
        .info-label { display: block; font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; margin-bottom: 4px; letter-spacing: 0.5px; }
        .info-value { display: block; font-size: 14px; font-weight: 700; color: var(--text); }
        .modal-footer { display: grid; grid-template-columns: 1fr 1.5fr; gap: 12px; }
        .btn-modal-cancel { background: #F1F5F9; color: var(--text-muted); border: 1px solid var(--border); padding: 14px; border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.3s; }
        .btn-modal-confirm { background: var(--gold); color: #fff; border: none; padding: 14px; border-radius: 14px; font-weight: 800; cursor: pointer; text-transform: uppercase; transition: 0.3s; box-shadow: 0 6px 15px var(--gold-bg); }
        .btn-modal-confirm:hover { transform: scale(1.03); background: var(--gold-dark); }

        @media (max-width: 600px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 15px; }
        }
    </style>

    <div class="groupx-container animate-up">
        <div class="page-header">
            <div class="header-text">
                <h1>Monkey <span>Group X</span></h1>
                <p>Khám phá các lớp học bùng nổ năng lượng và đặt chỗ ngay hôm nay.</p>
            </div>
            <div class="header-actions">
                <a href="<?= SITE_URL ?>/member/my-classes" class="mg-btn" style="background: var(--gold-bg); color: var(--gold-dark); border: 1px solid var(--gold-border);">Lớp của tôi 📋</a>
            </div>
        </div>

        <div class="class-list">
            <?php if(!empty($schedules)): ?>
                <?php foreach($schedules as $s): 
                    $percent = ($s['so_nguoi_da_dat'] / $s['so_luong_toi_da']) * 100;
                    $isFull = $s['so_nguoi_da_dat'] >= $s['so_luong_toi_da'];
                    $isBooked = $s['toi_da_dat'] > 0;
                    
                    $fillClass = '';
                    if ($percent >= 100) $fillClass = 'full';
                    elseif ($percent >= 80) $fillClass = 'warning';
                ?>
                <div class="class-card">
                    <div class="class-img-wrapper">
                        <div class="class-img" style="background-image: url('<?= htmlspecialchars((string)($s['hinh_anh'] ?? '')) ?>');"></div>
                        <div class="class-badge"><?= htmlspecialchars((string)($s['loai_lop'] ?? 'CLASS')) ?></div>
                    </div>

                    <div class="class-info">
                        <div class="class-time-row">
                            <div class="class-time">
                                <?= date('H:i', strtotime($s['gio_bat_dau'])) ?> - <?= date('H:i', strtotime($s['gio_ket_thuc'])) ?>
                            </div>
                            <div class="class-date">
                                <?= date('d/m/Y', strtotime($s['ngay_hoc'])) ?>
                            </div>
                        </div>

                        <h3 class="class-name"><?= htmlspecialchars((string)($s['ten_lop'] ?? 'Tên lớp học')) ?></h3>
                        
                        <div class="class-trainer">
                            <div class="trainer-avatar"><?= strtoupper(substr($s['ten_hlv'] ?? 'G', 0, 1)) ?></div>
                            <span>HLV: <?= htmlspecialchars((string)($s['ten_hlv'] ?? 'Chưa cập nhật')) ?></span>
                        </div>
                        
                        <div class="slot-container">
                            <div class="slot-header">
                                <span class="slot-label">Trạng thái chỗ ngồi</span>
                                <span class="slot-count <?= $isFull ? 'full' : '' ?>">
                                    <?= (int)$s['so_nguoi_da_dat'] ?> / <?= (int)$s['so_luong_toi_da'] ?>
                                </span>
                            </div>
                            <div class="slot-progress-bg">
                                <div class="slot-progress-fill <?= $fillClass ?>" style="width: <?= $percent ?>%;"></div>
                            </div>

                            <?php if($isBooked): ?>
                                <button class="btn-book btn-booked" disabled>✓ BẠN ĐÃ ĐẶT CHỖ</button>
                            <?php elseif($isFull): ?>
                                <button class="btn-book btn-full" disabled>LỚP ĐÃ ĐẦY</button>
                            <?php else: ?>
                                <button class="btn-book btn-available" 
                                        onclick="openBookingModal(<?= htmlspecialchars(json_encode([
                                            'id' => $s['ma_lich'],
                                            'name' => (string)$s['ten_lop'],
                                            'desc' => (string)($s['mo_ta'] ?? 'Khám phá buổi tập bùng nổ cùng các chuyên gia hàng đầu tại Monkey Gym.'),
                                            'img' => (string)($s['hinh_anh'] ?? ''),
                                            'date' => date('d/m/Y', strtotime($s['ngay_hoc'])),
                                            'time' => date('H:i', strtotime($s['gio_bat_dau'])) . ' - ' . date('H:i', strtotime($s['gio_ket_thuc'])),
                                            'trainer' => (string)$s['ten_hlv']
                                        ])) ?>)">ĐẶT CHỖ NGAY</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 80px 20px; background: #fff; border: 1px dashed var(--border); border-radius: 32px;">
                    <div style="font-size: 50px; margin-bottom: 20px;">🕒</div>
                    <h3 style="color: var(--text); font-weight: 900; margin-bottom: 10px;">Chưa có lịch học</h3>
                    <p style="font-size: 14px;">Hiện tại chưa có lớp Group X nào sắp diễn ra. Vui lòng quay lại sau.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Booking Redesigned -->
    <div id="bookingModal" class="modal-overlay">
        <div class="modal-card">
            <div id="modalImg" class="modal-img"></div>
            <div class="modal-content">
                <h2 id="modalName" class="modal-title">Tên Lớp Học</h2>
                <p id="modalDesc" class="modal-desc">Mô tả lớp học...</p>
                
                <div class="modal-info-grid">
                    <div class="info-item">
                        <span class="info-label">Ngày học</span>
                        <span id="modalDate" class="info-value">--/--/----</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Thời gian</span>
                        <span id="modalTime" class="info-value">00:00 - 00:00</span>
                    </div>
                    <div class="info-item" style="grid-column: span 2;">
                        <span class="info-label">Huấn luyện viên</span>
                        <span id="modalTrainer" class="info-value">---</span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn-modal-cancel" onclick="closeBookingModal()">HUỶ BỎ</button>
                    <button id="btnConfirmBooking" class="btn-modal-confirm">XÁC NHẬN ĐẶT</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentLichId = null;

        function openBookingModal(data) {
            currentLichId = data.id;
            document.getElementById('modalImg').style.backgroundImage = `url('${data.img}')`;
            document.getElementById('modalName').innerText = data.name;
            document.getElementById('modalDesc').innerText = data.desc;
            document.getElementById('modalDate').innerText = data.date;
            document.getElementById('modalTime').innerText = data.time;
            document.getElementById('modalTrainer').innerText = data.trainer;
            
            document.getElementById('bookingModal').style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Lock scroll
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Unlock scroll
        }

        document.getElementById('btnConfirmBooking').addEventListener('click', function() {
            if(!currentLichId) return;

            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = 'ĐANG XỬ LÝ...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('ma_lich', currentLichId);

            fetch('<?= SITE_URL ?>/api/member/book-class', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('🎉 ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert('⚠️ Lỗi kết nối mạng!');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target == modal) {
                closeBookingModal();
            }
        }
    </script>
</main>
