<?php
/**
 * Test Page for QR Scanner
 * Simple test to verify configuration without database
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

startSession();

// Check if logged in
$isLoggedIn = isLoggedIn();
$userRole = $isLoggedIn ? ($_SESSION['user_role'] ?? $_SESSION['vai_tro'] ?? 'guest') : 'guest';
$isAdmin = $isLoggedIn && hasRole('admin');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test QR Scanner | Monkey Gym</title>
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        .test-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 15px;
            border: 1px solid var(--border);
        }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .test-pass {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .test-fail {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid #f44336;
        }
        .test-warn {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        .test-icon {
            font-size: 20px;
            min-width: 30px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="test-container">
            <h1 style="text-align: center; margin-bottom: 30px;">✅ QR Scanner Verification</h1>

            <!-- Auth Status -->
            <div class="test-item <?= $isLoggedIn ? 'test-pass' : 'test-fail' ?>">
                <span class="test-icon"><?= $isLoggedIn ? '✓' : '✗' ?></span>
                <span>
                    <strong>Authentication:</strong> 
                    <?= $isLoggedIn ? "Logged in as <strong>$userRole</strong>" : "Not logged in" ?>
                </span>
            </div>

            <!-- Admin Role -->
            <div class="test-item <?= $isAdmin ? 'test-pass' : 'test-fail' ?>">
                <span class="test-icon"><?= $isAdmin ? '✓' : '✗' ?></span>
                <span>
                    <strong>Admin Role:</strong> 
                    <?= $isAdmin ? "Yes - Access granted" : "No - Admin access required" ?>
                </span>
            </div>

            <!-- Config Status -->
            <div class="test-item test-pass">
                <span class="test-icon">✓</span>
                <span>
                    <strong>Configuration:</strong> Loaded successfully
                </span>
            </div>

            <!-- Database Connection -->
            <div class="test-item <?php
                try {
                    $db = new \Database();
                    echo 'test-pass';
                } catch (Exception $e) {
                    echo 'test-fail';
                }
            ?>">
                <span class="test-icon">
                    <?php
                    try {
                        $db = new \Database();
                        echo '✓';
                    } catch (Exception $e) {
                        echo '✗';
                    }
                    ?>
                </span>
                <span>
                    <strong>Database Connection:</strong> 
                    <?php
                    try {
                        $testDb = new \Database();
                        echo "Connected successfully";
                    } catch (Exception $e) {
                        echo "Failed: " . $e->getMessage();
                    }
                    ?>
                </span>
            </div>

            <!-- API Endpoint -->
            <div class="test-item test-warn">
                <span class="test-icon">ℹ</span>
                <span>
                    <strong>API Endpoint:</strong> /public/api/qr-checkin.php
                </span>
            </div>

            <!-- Requirements -->
            <h3 style="margin-top: 30px; margin-bottom: 15px; color: var(--primary);">📋 Required Database Tables:</h3>
            <ul style="list-style: none; padding: 0; color: var(--text-secondary);">
                <li>✓ HOI_VIEN (ma_hoi_vien, ma_qr)</li>
                <li>✓ NGUOI_DUNG (ma_nguoi_dung, ten_dang_nhap)</li>
                <li>✓ DIEM_DANH (ma_hoi_vien, ngay_gio_diem_danh)</li>
            </ul>

            <!-- Action Buttons -->
            <div style="margin-top: 30px; display: flex; gap: 10px; justify-content: center;">
                <?php if ($isAdmin): ?>
                    <a href="/admin/quet-qr.php" class="btn btn-primary">
                        🔄 Go to QR Scanner
                    </a>
                <?php else: ?>
                    <div style="color: var(--text-secondary); text-align: center; width: 100%;">
                        Please log in as admin to access QR scanner
                    </div>
                <?php endif; ?>
                <a href="/" class="btn btn-secondary">Home</a>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh config status every 5 seconds
        setTimeout(() => {
            location.reload();
        }, 5000);
    </script>
</body>
</html>
