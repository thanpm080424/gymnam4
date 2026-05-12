<?php
require 'database/config.php';
$db = Database::getConnection();
$res = $db->query('DESCRIBE LICH_HOC_NHOM')->fetchAll();
foreach($res as $r) echo $r['Field'] . "\n";
?>
