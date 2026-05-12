<?php
shell_exec('git config --global --add safe.directory C:/wamp64/www/monkeygymnhom2 2>&1');
$output = shell_exec('git show HEAD:views/landing.php 2>&1');
echo $output;
?>
