<?php
$sourceDir = 'C:/Users/super/.gemini/antigravity/brain/d17b7eb7-baca-49f5-9e65-c8d32ab14016/';
$destDir = __DIR__ . '/../public/assets/img/landing/';

if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}

$files = [
    'gym_floor_modern_1778565636240.png' => 'facility-1.png',
    'gym_sauna_premium_1778565885725.png' => 'facility-2.png',
    'gym_pt_session_1778565906370.png' => 'facility-3.png'
];

foreach ($files as $src => $dest) {
    if (file_exists($sourceDir . $src)) {
        copy($sourceDir . $src, $destDir . $dest);
        echo "Copied $src to $dest<br>";
    } else {
        echo "Source file $src not found<br>";
    }
}
echo "Done.";
?>
