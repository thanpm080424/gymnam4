<?php
require 'database/config.php';
$db = Database::getConnection();
$res = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach($res as $r) echo $r . "\n";
?>
