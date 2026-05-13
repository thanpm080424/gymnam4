<?php
require_once __DIR__ . '/../database/config.php';
$dbInstance = Database::getConnection();
$db = $dbInstance->conn; // Get the PDO instance

try {
    echo "Updating THANH_TOAN table schema...\n";

    // 1. Make ma_dang_ky nullable
    $db->exec("ALTER TABLE THANH_TOAN MODIFY ma_dang_ky INT NULL");
    echo "- Modified 'ma_dang_ky' to be NULLable\n";

    // 2. Add ma_yc_thue column if not exists
    $stmt = $db->query("SHOW COLUMNS FROM THANH_TOAN");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('ma_yc_thue', $columns)) {
        $db->exec("ALTER TABLE THANH_TOAN ADD COLUMN ma_yc_thue INT NULL");
        echo "- Added 'ma_yc_thue' column\n";
    }

    // 3. Add foreign key if not exists
    $dbname = defined('DB_NAME') ? DB_NAME : 'monkey_gym';
    $checkFK = $db->prepare("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = ? 
        AND TABLE_NAME = 'THANH_TOAN' 
        AND CONSTRAINT_NAME = 'fk_thanh_toan_tu'
    ");
    $checkFK->execute([$dbname]);
    if (!$checkFK->fetch()) {
        $db->exec("ALTER TABLE THANH_TOAN ADD CONSTRAINT fk_thanh_toan_tu FOREIGN KEY (ma_yc_thue) REFERENCES YEU_CAU_THUE_TU(ma_yc) ON DELETE CASCADE");
        echo "- Added foreign key 'fk_thanh_toan_tu'\n";
    }

    echo "Database updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
