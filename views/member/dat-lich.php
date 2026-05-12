<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<main class="mg-main">
    <div class="mg-page-header animate-up">
        <h1>Đặt lịch Huấn luyện 🗓️</h1>
        <p>Chọn huấn luyện viên và thời gian phù hợp để bắt đầu buổi tập chuyên sâu.</p>
    </div>

    <style>
        .booking-layout { display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; align-items: start; }
        
        /* Left Column: Form & Selection */
        .card-booking-form { background: #fff; border-radius: 24px; padding: 32px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .pt-balance { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 20px; border-radius: 20px; text-align: center; margin-bottom: 30px; border: 1px dashed #7dd3fc; }
        .pt-balance .label { font-size: 11px; font-weight: 800; color: #0369a1; text-transform: uppercase; letter-spacing: 1px; }
        .pt-balance .count { font-size: 32px; font-weight: 900; color: #0c4a6e; display: block; margin-top: 5px; }

        .form-section-title { font-size: 15px; font-weight: 800; color: var(--text-muted); margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Discipline Selector */
        .discipline-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 25px; }
        .discipline-item { padding: 16px; border: 2px solid #F1F5F9; border-radius: 16px; cursor: pointer; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }
        .discipline-item:hover { border-color: var(--gold); background: #FFFBEB; }
        .discipline-item.active { border-color: var(--gold); background: #FFFBEB; box-shadow: 0 8px 16px rgba(201,153,63,0.1); }
        .discipline-item .icon { font-size: 24px; }
        .discipline-item .name { font-weight: 800; font-size: 14px; }

        /* Trainer List */
        .trainer-list { display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 30px; }
        .trainer-item { display: flex; align-items: center; gap: 16px; padding: 14px; border: 1px solid #F1F5F9; border-radius: 16px; cursor: pointer; transition: all 0.2s; }
        .trainer-item:hover { background: #F8FAFC; border-color: var(--border); }
        .trainer-item.active { border-color: var(--gold); background: #FFFBEB; box-shadow: 0 4px 12px rgba(201,153,63,0.08); }
        .trainer-avatar { width: 48px; height: 48px; background: #E2E8F0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--gold); font-size: 18px; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .trainer-info .t-name { font-weight: 800; font-size: 14px; }
        .trainer-info .t-spec { font-size: 11px; color: var(--text-muted); font-weight: 600; }

        /* Date Scroll */
        .date-picker-wrap { margin-bottom: 30px; }
        .date-scroll { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; scrollbar-width: none; }
        .date-scroll::-webkit-scrollbar { display: none; }
        .date-btn { min-width: 65px; padding: 12px 10px; border-radius: 16px; background: #F8FAFC; border: 1px solid #F1F5F9; text-align: center; cursor: pointer; transition: all 0.2s; }
        .date-btn:hover { border-color: var(--gold); }
        .date-btn.active { background: var(--gold); color: #fff; border-color: var(--gold); box-shadow: 0 8px 16px rgba(201,153,63,0.25); }
        .date-btn .d-dow { font-size: 10px; font-weight: 800; opacity: 0.8; margin-bottom: 4px; }
        .date-btn .d-num { font-size: 18px; font-weight: 900; }

        /* Slots Grid */
        .slots-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
        .slot-btn { padding: 14px; border: 1px solid #F1F5F9; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s; background: #fff; }
        .slot-btn:hover:not(.disabled) { border-color: var(--gold); background: #FFFBEB; }
        .slot-btn.disabled { opacity: 0.5; cursor: not-allowed; background: #F1F5F9; }
        .slot-btn .s-time { font-weight: 900; font-size: 16px; display: block; }
        .slot-btn .s-status { font-size: 10px; font-weight: 700; color: var(--text-muted); margin-top: 4px; }

        /* Right Column: My Bookings */
        .card-my-bookings { background: #fff; border-radius: 24px; padding: 24px; border: 1px solid var(--border); }
        .booking-item { padding: 16px; border-radius: 16px; border: 1px solid #F1F5F9; margin-bottom: 12px; position: relative; }
        .booking-item .status-badge { position: absolute; top: 16px; right: 16px; padding: 4px 10px; border-radius: 100px; font-size: 10px; font-weight: 800; }
        .status-confirmed { background: #ECFDF5; color: #059669; }
        .status-pending { background: #FFFBEB; color: #B45309; }
        .status-cancelled { background: #FEF2F2; color: #B91C1C; }

        @media (max-width: 1024px) {
            .booking-layout { grid-template-columns: 1fr; }
        }
    </style>

    <?php 
    $__f = getFlash();
    if ($__f): ?>
    <div class="mg-alert mg-alert-<?= htmlspecialchars($__f['type']) ?> animate-up" style="margin-bottom: 20px;">
        <?= htmlspecialchars($__f['message']) ?>
    </div>
    <?php endif; ?>

    <div class="booking-layout animate-up">
        <!-- LEFT: SELECTION FORM -->
        <section class="card-booking-form">
            <div class="pt-balance">
                <span class="label">BUỔI PT HIỆN CÓ</span>
                <span class="count"><?= (int)($member['so_buoi_pt_con_lai'] ?? 0) ?> buổi</span>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">1. Chọn bộ môn</h3>
                <div class="discipline-grid">
                    <div class="discipline-item active" onclick="selectDiscipline('gym', this)">
                        <span class="icon">🏋️</span>
                        <span class="name">Gym / Thể hình</span>
                    </div>
                    <div class="discipline-item" onclick="selectDiscipline('yoga', this)">
                        <span class="icon">🧘</span>
                        <span class="name">Yoga / Dẻo dai</span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">2. Chọn Huấn luyện viên</h3>
                <div class="trainer-list" id="trainerList">
                    <!-- Sinh bởi JS -->
                </div>
            </div>

            <div class="form-section" id="dateSection" style="display:none;">
                <h3 class="form-section-title">3. Chọn ngày tập</h3>
                <div class="date-picker-wrap">
                    <div class="date-scroll" id="dateScroll">
                        <!-- Sinh bởi JS -->
                    </div>
                </div>
            </div>

            <div class="form-section" id="slotSection" style="display:none;">
                <h3 class="form-section-title">4. Chọn khung giờ</h3>
                <div class="slots-grid" id="slotsGrid">
                    <!-- Sinh bởi JS -->
                </div>
            </div>
        </section>

        <!-- RIGHT: MY BOOKINGS -->
        <section class="card-my-bookings">
            <div class="list-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="font-size: 18px; font-weight: 900;">📅 Lịch tập của tôi</h3>
            </div>
            
            <div style="max-height: 700px; overflow-y: auto; padding-right: 5px;">
                <?php foreach($bookings as $b): ?>
                    <div class="booking-item">
                        <?php
                            $statusLabel = 'Đang chờ';
                            $statusClass = 'status-pending';
                            if($b['trang_thai'] === 'confirmed') { $statusLabel = 'Đã chốt'; $statusClass = 'status-confirmed'; }
                            if($b['trang_thai'] === 'cancelled') { $statusLabel = 'Đã huỷ'; $statusClass = 'status-cancelled'; }
                        ?>
                        <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        <div style="font-size: 14px; font-weight: 800; margin-bottom: 8px;">HLV: <?= htmlspecialchars($b['ten_dang_nhap']) ?></div>
                        <div style="display: flex; flex-direction: column; gap: 4px; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                            <span>🗓️ <?= date('H:i, d/m/Y', strtotime($b['ngay_gio_tap'])) ?></span>
                            <span>🏋️ Môn: <?= ucfirst($b['loai_pt']) ?></span>
                            <?php if($b['trang_thai'] === 'cancelled' && !empty($b['ly_do_huy'])): ?>
                                <span style="color: #ef4444; font-style: italic;">Lý do: <?= htmlspecialchars($b['ly_do_huy']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if($b['trang_thai'] === 'confirmed' || $b['trang_thai'] === 'pending'): ?>
                            <button class="mg-btn" style="margin-top: 15px; padding: 6px 12px; font-size: 11px; background: #F1F5F9; color: var(--text);" onclick="openCancelModal(<?= $b['ma_lich'] ?>)">Huỷ lịch</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if(empty($bookings)): ?>
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                        <div style="font-size: 40px; margin-bottom: 15px;">🗓️</div>
                        <p style="font-weight: 700;">Bạn chưa có lịch tập nào.</p>
                        <p style="font-size: 12px; margin-top: 5px;">Hãy chọn HLV và thời gian để bắt đầu!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- MODAL XÁC NHẬN ĐẶT LỊCH -->
    <div id="bookingModal" style="display:none; position: fixed; z-index: 10000; inset: 0; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; padding: 20px;">
        <div class="mg-card animate-up" style="max-width: 400px; width: 100%; padding: 32px; background: #fff;">
            <h2 style="font-size: 22px; font-weight: 900; margin-bottom: 15px;">Xác nhận đặt lịch 📋</h2>
            <div id="confirmSummary" style="background: #F8FAFC; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; line-height: 1.6;">
                <!-- Sinh bởi JS -->
            </div>
            <form action="<?= SITE_URL ?>/member/booking/create" method="POST">
                <input type="hidden" name="loai_pt" id="f_loai">
                <input type="hidden" name="ma_hlv" id="f_hlv">
                <input type="hidden" name="ngay_tap" id="f_ngay">
                <input type="hidden" name="gio_tap" id="f_gio">
                <?= csrfField('booking_create') ?>
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px;">GHI CHÚ CHO HLV</label>
                    <textarea name="ghi_chu" class="mg-input" style="width:100%; height: 80px;" placeholder="Ví dụ: Tập trung giảm mỡ bụng..."></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="mg-btn" style="flex:1; background: #F1F5F9;" onclick="closeModal('bookingModal')">HỦY</button>
                    <button type="submit" class="mg-btn mg-btn-primary" style="flex:2;">XÁC NHẬN ✨</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL HUỶ LỊCH -->
    <div id="cancelModal" style="display:none; position: fixed; z-index: 10000; inset: 0; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; padding: 20px;">
        <div class="mg-card animate-up" style="max-width: 400px; width: 100%; padding: 32px; background: #fff;">
            <h2 style="font-size: 20px; font-weight: 900; margin-bottom: 15px;">Huỷ lịch tập ⚠️</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Bạn có chắc chắn muốn huỷ lịch tập này? Buổi tập sẽ được hoàn trả vào tài khoản của bạn.</p>
            <form action="<?= SITE_URL ?>/member/booking/cancel" method="POST">
                <input type="hidden" name="ma_lich" id="f_cancel_id">
                <?= csrfField('booking_cancel') ?>
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px;">LÝ DO HUỶ</label>
                    <textarea name="ly_do" class="mg-input" style="width:100%; height: 60px;" required placeholder="Ghi rõ lý do..."></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="mg-btn" style="flex:1; background: #F1F5F9;" onclick="closeModal('cancelModal')">QUAY LẠI</button>
                    <button type="submit" class="mg-btn" style="flex:1; background: #EF4444; color: #fff;">XÁC NHẬN HUỶ</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const TRAINERS = <?= json_encode($trainers) ?>;
        const SLOTS = <?= json_encode(defined('GYM_TIME_SLOTS') ? GYM_TIME_SLOTS : []) ?>;
        
        let selDiscipline = 'gym';
        let selHlvId = null;
        let selHlvName = '';
        let selDate = null;
        let cachedSchedule = {};

        function selectDiscipline(val, el) {
            selDiscipline = val;
            document.querySelectorAll('.discipline-item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');
            renderTrainers();
            resetSelection();
        }

        function renderTrainers() {
            const list = document.getElementById('trainerList');
            list.innerHTML = '';
            const filtered = TRAINERS.filter(t => t.chuyen_mon === selDiscipline);
            
            if (filtered.length === 0) {
                list.innerHTML = '<p style="font-size:13px; color:var(--text-muted);">Chưa có HLV cho bộ môn này.</p>';
                return;
            }

            filtered.forEach(t => {
                const item = document.createElement('div');
                item.className = 'trainer-item';
                item.onclick = () => selectTrainer(t.ma_hlv, t.ten_dang_nhap, item);
                item.innerHTML = `
                    <div class="trainer-avatar">${t.ten_dang_nhap.charAt(0).toUpperCase()}</div>
                    <div class="trainer-info">
                        <div class="t-name">${t.ten_dang_nhap}</div>
                        <div class="t-spec">${t.chuyen_mon.toUpperCase()} SPECIALIST</div>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        function selectTrainer(id, name, el) {
            selHlvId = id;
            selHlvName = name;
            document.querySelectorAll('.trainer-item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');
            
            document.getElementById('dateSection').style.display = 'block';
            renderDateScroll();
            loadSchedule(id);
        }

        function renderDateScroll() {
            const scroll = document.getElementById('dateScroll');
            scroll.innerHTML = '';
            const today = new Date();
            const dows = ['CN','T2','T3','T4','T5','T6','T7'];

            for(let i=0; i<14; i++) {
                const d = new Date(today);
                d.setDate(today.getDate() + i);
                const dateStr = d.toISOString().split('T')[0];
                
                const btn = document.createElement('div');
                btn.className = 'date-btn';
                if(i === 0) { btn.classList.add('active'); selDate = dateStr; }
                btn.onclick = () => {
                    selDate = dateStr;
                    document.querySelectorAll('.date-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    renderSlots();
                };
                btn.innerHTML = `
                    <div class="d-dow">${dows[d.getDay()]}</div>
                    <div class="d-num">${d.getDate()}</div>
                `;
                scroll.appendChild(btn);
            }
            renderSlots();
        }

        async function loadSchedule(id) {
            if (cachedSchedule[id]) { renderSlots(); return; }
            try {
                const r = await fetch(`<?= SITE_URL ?>/trainer/schedule/api?ma_hlv=${id}`);
                cachedSchedule[id] = await r.json();
                renderSlots();
            } catch(e) { console.error(e); }
        }

        function renderSlots() {
            const grid = document.getElementById('slotsGrid');
            grid.innerHTML = '';
            document.getElementById('slotSection').style.display = 'block';

            if (!selHlvId || !selDate) return;
            const data = cachedSchedule[selHlvId] || { bookings: [] };
            
            const now = new Date();

            SLOTS.forEach(s => {
                if(s.is_break) return;
                const timeStr = s.bat_dau.substring(0,5);
                
                // Kiểm tra xem slot đã có người đặt chưa
                const isTaken = data.bookings.some(b => b.booking_date === selDate && b.booking_time === timeStr);
                
                // Kiểm tra xem slot có trong quá khứ không
                const slotDateTime = new Date(`${selDate}T${timeStr}:00`);
                const isPast = slotDateTime < now;
                
                const btn = document.createElement('div');
                btn.className = 'slot-btn' + (isTaken || isPast ? ' disabled' : '');
                btn.onclick = (isTaken || isPast) ? null : () => confirmBooking(timeStr);
                
                let statusText = 'CÒN TRỐNG';
                if (isPast) statusText = 'HẾT HẠN';
                else if (isTaken) statusText = 'KÍN LỊCH';

                btn.innerHTML = `
                    <span class="s-time">${timeStr}</span>
                    <span class="s-status">${statusText}</span>
                `;
                grid.appendChild(btn);
            });
        }

        function confirmBooking(time) {
            document.getElementById('f_loai').value = selDiscipline;
            document.getElementById('f_hlv').value = selHlvId;
            document.getElementById('f_ngay').value = selDate;
            document.getElementById('f_gio').value = time;

            document.getElementById('confirmSummary').innerHTML = `
                <b>HLV:</b> ${selHlvName}<br>
                <b>Bộ môn:</b> ${selDiscipline.toUpperCase()}<br>
                <b>Thời gian:</b> ${time}, ${selDate.split('-').reverse().join('/')}
            `;
            document.getElementById('bookingModal').style.display = 'flex';
        }

        function openCancelModal(id) {
            document.getElementById('f_cancel_id').value = id;
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function resetSelection() {
            selHlvId = null;
            document.getElementById('dateSection').style.display = 'none';
            document.getElementById('slotSection').style.display = 'none';
        }

        // Initialize
        renderTrainers();
    </script>
</main>
