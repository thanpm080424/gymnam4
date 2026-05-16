<?php
/**
 * CRONJOB: TỰ ĐỘNG GỬI MAIL NHẮC NHỞ & XIN ĐÁNH GIÁ PT
 * Cài đặt chạy mỗi 15 phút hoặc 30 phút trên Server.
 */
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/EmailService.php';

try {
    $db = Database::getConnection();
    $emailService = new EmailService();

    // 1. TÌM CÁC BUỔI TẬP SẼ DIỄN RA TRONG 1 GIỜ TỚI (Nhắc nhở)
    $stmtReminder = $db->query("
        SELECT l.ma_lich, l.ngay_gio_tap, u.email, u.ho_ten, u_hlv.ho_ten as ten_hlv 
        FROM LICH_DAT_PT l 
        JOIN HOI_VIEN hv ON l.ma_hoi_vien = hv.ma_hoi_vien 
        JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung 
        JOIN HUAN_LUYEN_VIEN hlv ON l.ma_hlv = hlv.ma_hlv 
        JOIN NGUOI_DUNG u_hlv ON hlv.ma_nguoi_dung = u_hlv.ma_nguoi_dung 
        WHERE l.trang_thai = 'confirmed' 
        AND l.ngay_gio_tap BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 HOUR)
        AND l.ma_lich NOT IN (SELECT ma_lich FROM EMAIL_BOOKING_LOG WHERE trang_thai_gui = 'sent')
    ");
    $reminders = $stmtReminder->fetchAll();

    foreach ($reminders as $r) {
        if ($r['email'] && filter_var($r['email'], FILTER_VALIDATE_EMAIL)) {
            $emailService->sendPTReminderOrReview($r['email'], $r['ho_ten'], $r['ten_hlv'], $r['ngay_gio_tap'], 'reminder');
            $db->prepare("INSERT INTO EMAIL_BOOKING_LOG (ma_lich, email_gui_den) VALUES (?, ?)")->execute([$r['ma_lich'], $r['email']]);
        }
    }

    // 2. TỰ ĐỘNG CHUYỂN TRẠNG THÁI THÀNH 'completed' NẾU ĐÃ QUA GIỜ TẬP 1 TIẾNG
    // Điều này giúp hệ thống tự động chốt lương cho HLV
    $db->query("UPDATE LICH_DAT_PT SET trang_thai = 'completed' WHERE trang_thai = 'confirmed' AND ngay_gio_tap < DATE_SUB(NOW(), INTERVAL 1 HOUR)");

    // 3. TÌM CÁC BUỔI TẬP VỪA KẾT THÚC (Xin Review)
    // Gửi mail xin review cho các buổi tập đã hoàn thành trong vòng 24h qua và chưa gửi mail review
    $stmtReview = $db->query("
        SELECT l.ma_lich, l.ngay_gio_tap, u.email, u.ho_ten, u_hlv.ho_ten as ten_hlv 
        FROM LICH_DAT_PT l 
        JOIN HOI_VIEN hv ON l.ma_hoi_vien = hv.ma_hoi_vien 
        JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung 
        JOIN HUAN_LUYEN_VIEN hlv ON l.ma_hlv = hlv.ma_hlv 
        JOIN NGUOI_DUNG u_hlv ON hlv.ma_nguoi_dung = u_hlv.ma_nguoi_dung 
        WHERE l.trang_thai = 'completed' 
        AND l.ngay_gio_tap BETWEEN DATE_SUB(NOW(), INTERVAL 24 HOUR) AND NOW()
        AND l.ma_lich NOT IN (SELECT ma_lich FROM EMAIL_BOOKING_LOG WHERE trang_thai_gui = 'review_sent')
    ");
    $reviews = $stmtReview->fetchAll();

    foreach ($reviews as $rev) {
        if ($rev['email'] && filter_var($rev['email'], FILTER_VALIDATE_EMAIL)) {
            $emailService->sendPTReminderOrReview($rev['email'], $rev['ho_ten'], $rev['ten_hlv'], $rev['ngay_gio_tap'], 'review');
            $db->prepare("INSERT INTO EMAIL_BOOKING_LOG (ma_lich, email_gui_den, trang_thai_gui) VALUES (?, ?, 'review_sent')")
               ->execute([$rev['ma_lich'], $rev['email']]);
        }
    }

    echo "Cronjob PT Automation chạy thành công:\n";
    echo "- Đã gửi " . count($reminders) . " email nhắc nhở.\n";
    echo "- Đã gửi " . count($reviews) . " email xin đánh giá.\n";
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
