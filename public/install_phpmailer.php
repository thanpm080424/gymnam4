<?php
$baseUrl = 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/';
$files = ['Exception.php', 'PHPMailer.php', 'SMTP.php'];
$dir = __DIR__ . '/../includes/PHPMailer/src/';

if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

foreach ($files as $file) {
    $content = file_get_contents($baseUrl . $file);
    if ($content !== false) {
        file_put_contents($dir . $file, $content);
        echo "Downloaded $file<br>";
    } else {
        echo "Failed to download $file<br>";
    }
}
echo "Done.";
?>
