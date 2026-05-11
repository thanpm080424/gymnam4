<?php
$target = __DIR__ . '/controllers/PlannerController.php';
if (file_exists($target)) {
    if (unlink($target)) {
        echo "KILLED: Da xoa file loi thanh cong.";
    } else {
        echo "FAIL: Khong the xoa file.";
    }
} else {
    echo "NOT_FOUND: File khong ton tai.";
}
?>
