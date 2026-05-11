<?php
/**
 * Scratch: Chạy Migration v5 - Nâng cấp bảng lich_lam_viec_hlv
 * Truy cập: http://localhost/monkey-gym-v8/run_migration_v5.php
 */
require_once __DIR__ . '/config/config.php';

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "<h2>Migration v5 - Monkey Gym</h2><pre>";

// Helper: kiem tra cot co ton tai khong
function colExists(PDO $pdo, string $table, string $col): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
    $stmt->execute([$table, $col]);
    return (int)$stmt->fetchColumn() > 0;
}
// Helper: kiem tra index co ton tai khong
function idxExists(PDO $pdo, string $table, string $idx): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=?");
    $stmt->execute([$table, $idx]);
    return (int)$stmt->fetchColumn() > 0;
}

// 1. Them cot muc_tieu
if (!colExists($pdo, 'lich_lam_viec_hlv', 'muc_tieu')) {
    $pdo->exec("ALTER TABLE `lich_lam_viec_hlv` ADD COLUMN `muc_tieu` VARCHAR(100) DEFAULT NULL");
    echo "OK: Added column muc_tieu\n";
} else {
    echo "SKIP: Column muc_tieu already exists\n";
}

// 2. Them cot trang_thai
if (!colExists($pdo, 'lich_lam_viec_hlv', 'trang_thai')) {
    $pdo->exec("ALTER TABLE `lich_lam_viec_hlv` ADD COLUMN `trang_thai` ENUM('trong','da_dat') NOT NULL DEFAULT 'trong'");
    echo "OK: Added column trang_thai\n";
} else {
    echo "SKIP: Column trang_thai already exists\n";
}

// 3. Xoa unique index cu neu co
if (idxExists($pdo, 'lich_lam_viec_hlv', 'unique_hlv_slot')) {
    $pdo->exec("ALTER TABLE `lich_lam_viec_hlv` DROP INDEX `unique_hlv_slot`");
    echo "OK: Dropped old unique_hlv_slot index\n";
}

// 4. Them unique key
try {
    $pdo->exec("ALTER TABLE `lich_lam_viec_hlv` ADD UNIQUE KEY `unique_hlv_slot` (`ma_hlv`, `ngay_trong_tuan`, `gio_bat_dau`)");
    echo "OK: Added unique_hlv_slot index\n";
} catch (Exception $e) {
    echo "WARN: " . $e->getMessage() . "\n";
}

echo "\nHoan tat Migration v5!</pre>";
