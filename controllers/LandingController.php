<?php
require_once __DIR__ . '/../database/config.php';

class LandingController {
    public function index() {
        // Neu da dang nhap, redirect luon
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/../utils/Middleware.php';
            Middleware::redirectToDashboard();
        }

        $db = Database::getConnection();

        // Lay danh sach trainers + rating
        $stmtT = $db->query("
            SELECT t.ma_hlv, u.ten_dang_nhap, u.ho_ten, t.chuyen_mon, t.anh_dai_dien, t.gioi_thieu, t.nam_kinh_nghiem, t.mo_ta,
                   COALESCE(AVG(d.so_sao), 5) as avg_rating, 
                   COUNT(d.ma_dg) as review_count
            FROM HUAN_LUYEN_VIEN t
            JOIN NGUOI_DUNG u ON t.ma_nguoi_dung = u.ma_nguoi_dung
            LEFT JOIN DANH_GIA_HLV d ON t.ma_hlv = d.ma_hlv AND d.trang_thai = 'approved'
            GROUP BY t.ma_hlv, u.ten_dang_nhap, u.ho_ten, t.chuyen_mon, t.anh_dai_dien, t.gioi_thieu, t.nam_kinh_nghiem, t.mo_ta
            ORDER BY review_count DESC, avg_rating DESC
        ");
        $trainers = $stmtT ? $stmtT->fetchAll() : [];

        // Lay danh gia approved (dung cho modal chi tiet) - Lay tat ca roi group trong PHP de toi uu
        $stmtAllR = $db->query("
            SELECT r.ma_hlv, r.so_sao, r.noi_dung, r.created_at, COALESCE(u.ho_ten, u.ten_dang_nhap) as ten_hoi_vien
            FROM DANH_GIA_HLV r
            JOIN HOI_VIEN hv ON r.ma_hoi_vien = hv.ma_hoi_vien
            JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE r.trang_thai = 'approved'
            ORDER BY r.created_at DESC
        ");
        $allReviews = $stmtAllR ? $stmtAllR->fetchAll() : [];
        $trainerReviews = [];
        foreach ($allReviews as $rev) {
            $trainerReviews[$rev['ma_hlv']][] = $rev;
        }

        // Lay goi tap membership
        $stmtP = $db->query("SELECT * FROM GOI_TAP WHERE loai_goi = 'membership' ORDER BY gia_tien ASC LIMIT 4");
        $packages = $stmtP ? $stmtP->fetchAll() : [];

        // Lay san pham
        $stmtSP = $db->query("SELECT * FROM SAN_PHAM WHERE ton_kho > 0 ORDER BY ma_sp DESC LIMIT 6");
        $products = $stmtSP ? $stmtSP->fetchAll() : [];

        // Thong bao he thong
        $stmtAnn = $db->query("SELECT * FROM THONG_BAO_HE_THONG WHERE trang_thai = 'active' ORDER BY created_at DESC");
        $announcements_list = $stmtAnn ? $stmtAnn->fetchAll() : [];

        // Khuyen Mai (Promotions)
        $stmtPromo = $db->query("SELECT * FROM MA_GIAM_GIA WHERE so_luong_con > 0 AND ngay_het_han > NOW() ORDER BY created_at DESC");
        $promotions = $stmtPromo ? $stmtPromo->fetchAll() : [];

        // Lớp học (Classes)
        $stmtClasses = $db->query("SELECT * FROM LOP_HOC_NHOM WHERE trang_thai = 'active' LIMIT 3");
        $classes = $stmtClasses ? $stmtClasses->fetchAll() : [];

        require __DIR__ . '/../views/landing.php';
    }

    // Xử lý API Đăng ký tập thử (AJAX)
    public function dangKyTapThu() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ho_ten = trim($_POST['ho_ten'] ?? '');
            $sdt = trim($_POST['sdt'] ?? '');
            
            if (empty($ho_ten) || empty($sdt)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đủ Họ tên và Số điện thoại!']);
                return;
            }
            
            try {
                $db = Database::getConnection();
                $stmt = $db->prepare("INSERT INTO DANG_KY_TAP_THU (ho_ten, so_dien_thoai) VALUES (?, ?)");
                $stmt->execute([$ho_ten, $sdt]);
                
                echo json_encode(['success' => true, 'message' => '🎉 Tuyệt vời! Bộ phận CSKH sẽ gọi cho bạn trong ít phút nữa.']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: Không thể lưu đăng ký.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
        }
    }
}
