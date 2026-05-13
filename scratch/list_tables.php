<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection()->conn;

$stmt = $db->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "\n";
}
