<?php
$old = __DIR__ . '/controllers/PlannerController.php';
$fix = __DIR__ . '/controllers/PlannerController_fix.php';

if (file_exists($old)) {
    unlink($old);
}

if (file_exists($fix)) {
    if (rename($fix, $old)) {
        echo "Thành công: Đã đổi tên PlannerController_fix.php thành PlannerController.php";
    } else {
        echo "Lỗi: Không thể đổi tên file.";
    }
} else {
    echo "Lỗi: Không tìm thấy file PlannerController_fix.php";
}
?>
