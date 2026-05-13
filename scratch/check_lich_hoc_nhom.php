<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection()->conn;

$stmt = $db->query("DESCRIBE lich_hoc_nhom");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
