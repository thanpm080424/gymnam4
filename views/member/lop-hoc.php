<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <style>
        :root {
            --accent: #84cc16;
            --accent-glow: rgba(132, 204, 22, 0.3);
            --card-bg: rgba(24, 24, 27, 0.8);
            --border: rgba(255, 255, 255, 0.08);
        }

        .groupx-container { 
            padding: 2rem 0; 
            font-family: 'Outfit', sans-serif; 
            color: #fff;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header Section */
        .page-header { 
            margin-bottom: 3rem; 
            text-align: left;
            position: relative;
        }
        .page-header::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }
        .page-title { 
            font-size: 2.8rem; 
            font-weight: 900; 
            text-transform: uppercase; 
            letter-spacing: -1.5px;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #fff, #a1a1aa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .page-title span { color: var(--accent); -webkit-text-fill-color: var(--accent); }
        .page-subtitle { color: #a1a1aa; font-size: 1.1rem; font-weight: 500; }

        /* Class Grid */
        .class-list { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); 
            gap: 2rem; 
        }

        .class-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 24px; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            position: relative;
            backdrop-filter: blur(10px);
        }

        .class-card:hover { 
            border-color: var(--accent); 
            transform: translateY(-10px); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 20px var(--accent-glow); 
        }
        
        .class-img-wrapper {
            position: relative;
            height: 220px;
            overflow: hidden;
        }
        .class-img { 
            width: 100%;
            height: 100%;
            background-size: cover; 
            background-position: center; 
            transition: transform 0.6s ease;
        }
        .class-card:hover .class-img { transform: scale(1.1); }

        .class-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(24, 24, 27, 1), transparent);
        }

        .class-badge { 
            position: absolute; 
            top: 20px; 
            left: 20px; 
            background: rgba(132, 204, 22, 0.9); 
            color: #000; 
            padding: 6px 16px; 
            border-radius: 12px; 
            font-size: 0.75rem; 
            font-weight: 800; 
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(132, 204, 22, 0.4);
        }
        
        .class-info { padding: 1.5rem; flex: 1; display: flex; flex-direction: column; position: relative; z-index: 1; }
        .class-time-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .class-time { font-size: 1.2rem; font-weight: 800; color: var(--accent); }
        .class-date { font-size: 0.85rem; color: #a1a1aa; font-weight: 600; background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 8px; }
        
        .class-name { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.8rem; line-height: 1.2; letter-spacing: -0.5px; }
        .class-trainer { font-size: 0.95rem; color: #d1d5db; display: flex; align-items: center; gap: 8px; margin-bottom: 1.5rem; }
        .trainer-avatar { width: 24px; height: 24px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #000; font-size: 0.7rem; font-weight: bold; }
        
        /* Seat Status */
        .slot-container { margin-top: auto; }
        .slot-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.85rem; font-weight: 600; }
        .slot-label { color: #a1a1aa; }
        .slot-count { color: #fff; }
        .slot-count.full { color: #ef4444; }

        .slot-progress-bg { height: 8px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; margin-bottom: 1.5rem; }
        .slot-progress-fill { height: 100%; background: var(--accent); border-radius: 10px; transition: width 1s ease-in-out; }
        .slot-progress-fill.warning { background: #f59e0b; }
        .slot-progress-fill.full { background: #ef4444; }

        /* Premium Buttons */
        .btn-book { 
            width: 100%; 
            padding: 14px; 
            font-weight: 800; 
            border-radius: 14px; 
            cursor: pointer; 
            transition: all 0.3s ease; 
            text-transform: uppercase; 
            font-size: 0.9rem; 
            border: none;
            letter-spacing: 0.5px;
        }
        .btn-available { 
            background: var(--accent); 
            color: #000; 
            box-shadow: 0 4px 15px var(--accent-glow);
        }
        .btn-available:hover { 
            background: #fff; 
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(255,255,255,0.2);
        }
        .btn-booked { background: rgba(255,255,255,0.05); color: #84cc16; cursor: not-allowed; border: 1px solid rgba(132,204,22,0.3); }
        .btn-full { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); cursor: not-allowed; }

        /* Modal Redesign */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(15px); display: none; justify-content: center; align-items: center; z-index: 9999; padding: 20px; }
        .modal-card { background: #121214; width: 100%; max-width: 520px; border-radius: 32px; border: 1px solid rgba(255,255,255,0.1); overflow: hidden; box-shadow: 0 30px 60px rgba(0,0,0,0.8); }
        .modal-img { height: 240px; background-size: cover; background-position: center; position: relative; }
        .modal-img::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, #121214, transparent); }
        .modal-content { padding: 2.5rem; margin-top: -40px; position: relative; z-index: 2; }
        .modal-title { font-size: 2.2rem; font-weight: 900; margin-bottom: 0.8rem; letter-spacing: -1px; }
        .modal-desc { color: #a1a1aa; font-size: 1rem; line-height: 1.6; margin-bottom: 2rem; }
        .modal-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 2.5rem; }
        .info-item { background: rgba(255,255,255,0.03); padding: 12px 15px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); }
        .info-label { display: block; font-size: 0.75rem; color: #71717a; text-transform: uppercase; font-weight: 800; margin-bottom: 4px; }
        .info-value { display: block; font-size: 0.95rem; font-weight: 700; color: #fff; }
        .modal-footer { display: grid; grid-template-columns: 1fr 1.5fr; gap: 15px; }
        .btn-modal-cancel { background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1); padding: 16px; border-radius: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-modal-confirm { background: var(--accent); color: #000; border: none; padding: 16px; border-radius: 16px; font-weight: 800; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .btn-modal-confirm:hover { transform: scale(1.03); filter: brightness(1.1); }
    </style>

    <div class="groupx-container animate-up">
        <div class="page-header">
            <h1 class="page-title">Monkey <span>Group X</span></h1>
            <p class="page-subtitle">Khám phá các lớp học bùng nổ năng lượng và đặt chỗ ngay hôm nay.</p>
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
                        <div class="class-overlay"></div>
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
                <div style="grid-column: 1/-1; text-align: center; color: #a1a1aa; padding: 4rem; background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1); border-radius: 32px;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🕒</div>
                    <h3 style="color: #fff; margin-bottom: 0.5rem;">Chưa có lịch học</h3>
                    <p>Hiện tại chưa có lớp Group X nào sắp diễn ra. Vui lòng quay lại sau.</p>
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
