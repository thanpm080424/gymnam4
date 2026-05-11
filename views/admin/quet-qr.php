<?php
/**
 * Máy Quét QR Điểm Danh - Admin View
 * Cho phép admin quét mã QR để điểm danh thành viên
 */


// Auth đã được kiểm tra bởi AdminController::__construct()

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quét QR Điểm Danh | Monkey Gym</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <!-- QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <!-- Confetti Animation -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        .qr-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        #reader {
            width: 100%;
            max-width: 500px;
            border-radius: 15px;
            overflow: hidden;
            border: 2px solid var(--border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        #reader button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        #reader button:hover {
            background-color: #e0ac00;
        }

        .scan-result {
            display: none;
            width: 100%;
            max-width: 500px;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 20px;
            animation: slideIn 0.3s ease-in-out;
        }

        .scan-result.success {
            background-color: rgba(76, 175, 80, 0.2);
            border: 2px solid #4CAF50;
            color: #4CAF50;
        }

        .scan-result.error {
            background-color: rgba(244, 67, 54, 0.2);
            border: 2px solid #f44336;
            color: #f44336;
        }

        .scan-result h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }

        .scan-result p {
            margin: 5px 0;
            font-size: 14px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .scan-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
            max-width: 500px;
            width: 100%;
        }

        .stat-box {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid var(--border);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-box .label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .stat-box .value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <header class="dashboard-header">
                <div>
                    <h1>📱 Quét QR Điểm Danh</h1>
                    <p class="text-muted">Cấp quyền camera và quét mã QR của thành viên để điểm danh</p>
                </div>
            </header>

                        <div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 24px; align-items: start;">
                <div class="glass-panel" style="padding: 24px; text-align: center;">
                    <div id="reader" style="border: 2px solid var(--gold-border); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-lg);"></div>
                    
                    <div id="scanResult" class="scan-result" style="margin-top: 24px;">
                        <div id="resultCard" style="padding: 20px; border-radius: 12px; background: white; border: 1px solid var(--border);">
                            <h3 id="resultTitle" style="font-weight: 800; margin-bottom: 8px;">Kết quả</h3>
                            <p id="resultMessage" style="font-size: 0.9rem; color: var(--text-secondary);"></p>
                            <button class="btn btn-primary btn-sm" onclick="resetScanner()" style="margin-top: 15px;">
                                Quét tiếp
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <div class="glass-panel" style="padding: 24px; border-left: 5px solid var(--gold);">
                        <h3 style="font-size: 1rem; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            📊 Thống kê hôm nay
                        </h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div style="padding: 20px; background: var(--gold-bg); border: 1px solid var(--gold-border); border-radius: 12px; text-align: center;">
                                <div style="font-size: 0.65rem; font-weight: 800; color: var(--gold-dark); text-transform: uppercase; margin-bottom: 5px;">Thành công</div>
                                <div style="font-size: 2rem; font-weight: 900; color: var(--gold-dark);" id="statsScanned">0</div>
                            </div>
                            <div style="padding: 20px; background: var(--danger-bg); border: 1px solid #fca5a5; border-radius: 12px; text-align: center;">
                                <div style="font-size: 0.65rem; font-weight: 800; color: var(--danger); text-transform: uppercase; margin-bottom: 5px;">Lỗi quét</div>
                                <div style="font-size: 2rem; font-weight: 900; color: var(--danger);" id="statsError">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel" style="padding: 24px;">
                        <h3 style="font-size: 1rem; font-weight: 800; margin-bottom: 15px;">💡 Hướng dẫn</h3>
                        <ul style="padding-left: 18px; color: var(--text-secondary); font-size: 0.85rem; line-height: 1.6; display: flex; flex-direction: column; gap: 10px;">
                            <li>Đảm bảo hội viên mở mã QR từ ứng dụng Monkey Gym.</li>
                            <li>Giữ khoảng cách điện thoại và camera từ 15-20cm.</li>
                            <li>Tránh ánh sáng chói trực tiếp vào màn hình điện thoại.</li>
                            <li>Hệ thống sẽ tự động ghi nhận lượt ra vào khi quét thành công.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const QR_CONFIG = {
            apiEndpoint: '/public/api/qr-checkin.php',
            fps: 10,
            qrBoxSize: 250,
            csrfToken: '<?= $csrfToken ?>'
        };

        let scanner = null;
        let stats = { scanned: 0, errors: 0 };
        let lastScannedTime = 0;
        const SCAN_COOLDOWN = 1000; // ms

        /**
         * Initialize QR Scanner
         */
        function initScanner() {
            scanner = new Html5QrcodeScanner(
                'reader',
                {
                    fps: QR_CONFIG.fps,
                    qrbox: {
                        width: QR_CONFIG.qrBoxSize,
                        height: QR_CONFIG.qrBoxSize
                    }
                },
                false
            );

            scanner.render(onScanSuccess, onScanError);
        }

        /**
         * Handle successful QR scan
         */
        function onScanSuccess(decodedText) {
            // Prevent duplicate scans
            if (Date.now() - lastScannedTime < SCAN_COOLDOWN) return;
            lastScannedTime = Date.now();

            scanner.pause();

            // Send checkin request
            fetch(QR_CONFIG.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    qr_code: decodedText,
                    csrf_token: QR_CONFIG.csrfToken
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                displayResult(data);
                updateStats(data.success);
            })
            .catch(error => {
                console.error('Error:', error);
                displayResult({
                    success: false,
                    message: 'Lỗi kết nối máy chủ'
                });
                stats.errors++;
                updateStats(false);
            });
        }

        /**
         * Handle scan errors (silent)
         */
        function onScanError(error) {
            // Ignore frame errors silently
        }

        /**
         * Display scan result
         */
        function displayResult(data) {
            const resultDiv = document.getElementById('scanResult');
            const resultCard = document.getElementById('resultCard');
            const titleEl = document.getElementById('resultTitle');
            const msgEl = document.getElementById('resultMessage');

            if (data.success) {
                resultCard.style.borderColor = '#10b981';
                resultCard.style.background = '#f0fdf4';
                titleEl.style.color = '#10b981';
                titleEl.innerHTML = '✅ Điểm danh thành công';
                msgEl.innerHTML = `<strong>${data.member_name || 'Thành viên'}</strong><br>${data.message || ''}`;
                
                confetti({
                    particleCount: 150,
                    spread: 70,
                    origin: { y: 0.7 },
                    colors: ['#C9993F', '#16A34A', '#FFFFFF']
                });
            } else {
                resultCard.style.borderColor = '#ef4444';
                resultCard.style.background = '#fef2f2';
                titleEl.style.color = '#ef4444';
                titleEl.innerHTML = '❌ Điểm danh thất bại';
                msgEl.innerHTML = data.message || 'Không đọc được mã QR này';
            }

            resultDiv.style.display = 'block';
        }


        /**
         * Update statistics
         */
        function updateStats(success) {
            if (success) stats.scanned++;
            else stats.errors++;

            document.getElementById('statsScanned').textContent = stats.scanned;
            document.getElementById('statsError').textContent = stats.errors;
        }

        /**
         * Reset scanner
         */
        function resetScanner() {
            document.getElementById('scanResult').style.display = 'none';
            
            if (scanner && scanner.getState() === 2) { // PAUSED state
                scanner.resume();
            }
        }

        /**
         * Initialize on page load
         */
        document.addEventListener('DOMContentLoaded', () => {
            initScanner();

            // Handle camera permission errors
            scanner.getRunningTrack = function() {
                try {
                    return this.qrBoxContainer.videoElement.srcObject?.getTracks()[0];
                } catch (e) {
                    showCameraError();
                    return null;
                }
            };
        });

        /**
         * Handle camera permission denied
         */
        function showCameraError() {
            document.getElementById('reader').innerHTML = `
                <div style="padding: 20px; text-align: center; color: #f44336;">
                    <p><strong>⚠️ Lỗi quyền truy cập camera</strong></p>
                    <p>Vui lòng cấp quyền camera để sử dụng tính năng quét QR.</p>
                    <p style="font-size: 12px; color: #999;">
                        Chrome/Edge: Nhấp vào 🔒 ở thanh địa chỉ > Quyền > Camera > Cho phép
                    </p>
                </div>
            `;
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (scanner) {
                scanner.clear().catch(error => console.log('Error clearing scanner:', error));
            }
        });
    </script>
</body>
</html>
