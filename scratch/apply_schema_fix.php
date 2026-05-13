<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();

try {
    echo "Checking and updating database schema...\n";

    // Update YEU_CAU_THUE_TU
    $columns = $db->query("SHOW COLUMNS FROM YEU_CAU_THUE_TU")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('so_thang', $columns)) {
        $db->exec("ALTER TABLE YEU_CAU_THUE_TU ADD COLUMN so_thang INT DEFAULT 1");
        echo "- Added 'so_thang' to YEU_CAU_THUE_TU\n";
    }
    
    if (!in_array('loai_tu_mong_muon', $columns)) {
        $db->exec("ALTER TABLE YEU_CAU_THUE_TU ADD COLUMN loai_tu_mong_muon VARCHAR(10) DEFAULT 'M'");
        echo "- Added 'loai_tu_mong_muon' to YEU_CAU_THUE_TU\n";
    }

    // Update TU_DO
    $columnsTu = $db->query("SHOW COLUMNS FROM TU_DO")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('loai_tu', $columnsTu)) {
        $db->exec("ALTER TABLE TU_DO ADD COLUMN loai_tu VARCHAR(10) DEFAULT 'M'");
        echo "- Added 'loai_tu' to TU_DO\n";
    }

    echo "Database updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
