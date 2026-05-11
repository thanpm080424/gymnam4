<?php
require 'c:/wamp64/www/monkey-gym/database/config.php';
$db = Database::getConnection();
try {
    $db->exec('ALTER TABLE SAN_PHAM ADD COLUMN hinh_anh VARCHAR(255) DEFAULT NULL;');
    echo "Thanh cong";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Cot da ton tai";
    } else {
        echo "Loi: " . $e->getMessage();
    }
}
