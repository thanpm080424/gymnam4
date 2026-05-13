<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();

function checkTable($db, $tableName) {
    echo "\nTable: $tableName\n";
    try {
        $stmt = $db->query("DESCRIBE $tableName");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo " - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

checkTable($db, 'YEU_CAU_THUE_TU');
checkTable($db, 'TU_DO');
