<?php
require_once __DIR__ . '/../database/config.php';
$db = Database::getConnection();

// 1. Thêm lớp học mẫu
$classes = [
    ['ma_lop' => 'YOGA01', 'ten_lop' => 'Yoga Zen Flow', 'loai_lop' => 'Yoga', 'trang_thai' => 'active'],
    ['ma_lop' => 'CARDIO01', 'ten_lop' => 'Cardio Blast', 'loai_lop' => 'Cardio', 'trang_thai' => 'active'],
    ['ma_lop' => 'ZUMBA01', 'ten_lop' => 'Zumba Party', 'loai_lop' => 'Zumba', 'trang_thai' => 'active']
];

foreach ($classes as $c) {
    $db->query("INSERT IGNORE INTO LOP_HOC_NHOM (ma_lop, ten_lop, loai_lop, trang_thai) VALUES (?, ?, ?, ?)", [
        $c['ma_lop'], $c['ten_lop'], $c['loai_lop'], $c['trang_thai']
    ]);
}

// 2. Thêm HLV mẫu nếu chưa có
$checkHLV = $db->query("SELECT * FROM HUAN_LUYEN_VIEN")->fetchAll();
if (empty($checkHLV)) {
    // Giả định có user ma_nguoi_dung = 2 là HLV
    $db->query("INSERT IGNORE INTO HUAN_LUYEN_VIEN (ma_nguoi_dung, chuyen_mon) VALUES (2, 'Huan Luyen Vien Mac Dinh')");
}

echo "Dữ liệu mẫu đã được nạp thành công!";
?>
