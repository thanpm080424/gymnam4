<?php
/**
 * View: Điểm danh hội viên (QR nâng cao)
 * Merged từ MonkeyGym_Full/admin/diem-danh.php
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điểm danh - Monkey Gym</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        #reader { border-radius: 16px; overflow: hidden; margin: 20px 0; border: 2px dashed var(--border); background: var(--bg-primary); }
        .btn-scan { background: linear-gradient(135deg, var(--gold), var(--gold-dark)); border: none; padding: 12px 30px; font-size: 16px; color: white; border-radius: 12px; font-weight: 700; box-shadow: var(--shadow-md); transition: all 0.2s; }
        .btn-scan:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); color: white; }
        .result-card { border-radius: 16px; padding: 24px; margin-top: 20px; display: none; border: 1px solid var(--border); }
        .result-card.success { background: var(--success-bg); border-color: var(--success); color: var(--success); }
        .result-card.error { background: var(--danger-bg); border-color: var(--danger); color: var(--danger); }
        .checkin-list { max-height: 520px; overflow-y: auto; padding-right: 4px; }
        .checkin-item { background: var(--bg-card); border-radius: 12px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--border-light); transition: all 0.2s; }
        .checkin-item:hover { border-color: var(--gold); transform: translateX(4px); box-shadow: var(--shadow-sm); }
        .date-badge { background: var(--gold-bg); color: var(--gold-dark); padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header">
                <div>
                    <h1>📱 Điểm Danh Hội Viên</h1>
                    <p class="text-muted">Quét mã QR hoặc nhập SĐT để ghi nhận ra vào</p>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-icon gold">🎯</div>
                    <div>
                        <div class="stat-value" id="today-checkins"><?= number_format((int)($todayStats['total'] ?? 0)) ?></div>
                        <div class="stat-label">Check-in hôm nay</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">🕒</div>
                    <div>
                        <div class="stat-value" id="current-time"><?= date('H:i') ?></div>
                        <div class="stat-label">Thời gian hệ thống</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">📅</div>
                    <div>
                        <div class="stat-value"><?= date('d/m') ?></div>
                        <div class="stat-label">Ngày hiện tại</div>
                    </div>
                </div>
            </div>

            <div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 24px; align-items: start;">
                <!-- QR Scanner Panel -->
                <div class="glass-panel">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h3 style="font-weight: 800; margin-bottom: 8px;">QUÉT MÃ QR</h3>
                        <p class="text-muted" style="font-size: 0.9rem;">Đưa mã QR của hội viên vào khung quét bên dưới</p>
                    </div>
                    
                    <div id="reader"></div>
                    
                    <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 20px;">
                        <button id="btn-start-scan" class="btn-scan"><i class="fas fa-camera" style="margin-right: 8px;"></i> Bắt đầu quét</button>
                        <button id="btn-stop-scan" class="btn btn-danger" style="display: none; padding: 12px 30px; border-radius: 12px; font-weight: 700;"><i class="fas fa-stop" style="margin-right: 8px;"></i> Dừng quét</button>
                    </div>

                    <div style="border-top: 1px solid var(--border-light); padding-top: 20px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="label">Nhập mã / SĐT thủ công</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="manual-qr" class="form-control" placeholder="Nhập mã QR hoặc số điện thoại..." style="padding: 12px;">
                                <button class="btn btn-primary" onclick="checkInManual()" style="padding: 0 24px; border-radius: 12px; font-weight: 700;">Check-in</button>
                            </div>
                        </div>
                    </div>

                    <div id="result" class="result-card"></div>
                </div>

                <!-- Recent Activity Panel -->
                <div class="glass-panel" style="padding: 0; overflow: hidden;">
                    <div style="padding: 20px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 1rem; font-weight: 800; margin: 0;">⚡ Hoạt động gần đây</h3>
                        <span class="badge badge-gold"><?= count($recentCheckins) ?> lượt</span>
                    </div>
                    
                    <div class="card-body" style="padding: 20px;">
                        <div class="checkin-list">
                            <?php if (empty($recentCheckins)): ?>
                            <div style="text-align: center; padding: 40px 0;">
                                <div style="font-size: 2.5rem; margin-bottom: 12px;">📭</div>
                                <p class="text-muted">Chưa có ai điểm danh</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($recentCheckins as $checkin): ?>
                            <div class="checkin-item">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-primary);"><?= htmlspecialchars($checkin['ho_ten'] ?? '') ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-phone-alt" style="font-size: 0.7rem; margin-right: 4px;"></i><?= htmlspecialchars($checkin['so_dien_thoai'] ?? 'N/A') ?></div>
                                        <?php if (!empty($checkin['ten_goi'])): ?>
                                        <span class="badge badge-info" style="margin-top: 6px; font-size: 0.65rem;"><?= htmlspecialchars($checkin['ten_goi']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="text-align: right;">
                                        <div class="date-badge"><?= date('H:i', strtotime($checkin['gio_diem_danh'])) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;"><?= date('d/m', strtotime($checkin['ngay'])) ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const CHECK_IN_URL = '<?= SITE_URL ?>/admin/checkin/do';
    const CSRF_TOKEN = '<?= generate_csrf_token() ?>';

    setInterval(() => {
        const now = new Date();
        document.getElementById('current-time').textContent = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    }, 1000);

    let html5QrCode, isScanning = false;
    document.getElementById('btn-start-scan').addEventListener('click', startScanning);
    document.getElementById('btn-stop-scan').addEventListener('click', stopScanning);

    function startScanning() {
        if (isScanning) return;
        html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => { processQRCode(decodedText); stopScanning(); },
            () => {}
        ).then(() => {
            isScanning = true;
            document.getElementById('btn-start-scan').style.display = 'none';
            document.getElementById('btn-stop-scan').style.display = 'inline-block';
        }).catch(() => alert('Không thể mở camera. Vui lòng dùng "Nhập mã thủ công".'));
    }

    function stopScanning() {
        if (!isScanning || !html5QrCode) return;
        html5QrCode.stop().then(() => {
            isScanning = false;
            document.getElementById('btn-start-scan').style.display = 'inline-block';
            document.getElementById('btn-stop-scan').style.display = 'none';
        });
    }

    function checkInManual() {
        const qr = document.getElementById('manual-qr').value.trim();
        if (!qr) { alert('Vui lòng nhập mã QR hoặc SĐT!'); return; }
        processQRCode(qr);
    }

    function processQRCode(qrCode) {
        const resultDiv = document.getElementById('result');
        resultDiv.className = 'result-card';
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div style="text-align: center;"><i class="fas fa-circle-notch fa-spin fa-2x" style="color: var(--gold);"></i><p style="margin-top: 12px; font-weight: 600;">Đang xử lý...</p></div>';
        
        fetch(CHECK_IN_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ qr_code: qrCode, csrf_token: CSRF_TOKEN })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                resultDiv.className = 'result-card success';
                resultDiv.innerHTML = `<div style="text-align: center;"><h4 style="margin-bottom: 12px; font-weight: 800;">✅ CHECK-IN THÀNH CÔNG!</h4><p style="font-size: 0.95rem;">${data.message}</p></div>`;
                setTimeout(() => location.reload(), 2000);
            } else {
                resultDiv.className = 'result-card error';
                resultDiv.innerHTML = `<div style="text-align: center;"><h4 style="margin-bottom: 12px; font-weight: 800;">❌ THẤT BẠI</h4><p style="font-size: 0.95rem;">${data.message}</p></div>`;
                setTimeout(() => { resultDiv.style.display = 'none'; document.getElementById('manual-qr').value = ''; }, 4000);
            }
        })
        .catch(err => {
            resultDiv.className = 'result-card error';
            resultDiv.innerHTML = `<div style="text-align: center;"><h4 style="margin-bottom: 12px; font-weight: 800;">Lỗi kết nối!</h4><p>${err}</p></div>`;
        });
    }

    document.getElementById('manual-qr').addEventListener('keypress', e => { if (e.key === 'Enter') checkInManual(); });
    </script>
</body>
</html>