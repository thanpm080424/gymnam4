<?php
require_once __DIR__ . '/../utils/Middleware.php';

class TrainerController {
    public function __construct() {
        Middleware::checkTrainer();
    }

    public function dashboard() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        // 1. Lấy thông tin HLV
        $stmtH = $db->prepare("SELECT * FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtH->execute([$userId]);
        $trainer = $stmtH->fetch();
        $ma_hlv = $trainer['ma_hlv'] ?? 0;

        // 2. Lấy lịch dạy LỚP NHÓM sắp tới
        $stmtS = $db->prepare("
            SELECT lh.*, l.ten_lop, l.hinh_anh, l.loai_lop
            FROM LICH_HOC_NHOM lh
            JOIN LOP_HOC_NHOM l ON lh.ma_lop = l.ma_lop
            WHERE lh.ma_hlv = ? AND lh.ngay_hoc >= CURDATE()
            ORDER BY lh.ngay_hoc ASC, lh.gio_bat_dau ASC
        ");
        $stmtS->execute([$ma_hlv]);
        $schedules = $stmtS->fetchAll();

        // 3. Lấy lịch đặt PT cá nhân từ hội viên
        $stmtPT = $db->prepare("
            SELECT l.*, u.ho_ten, u.ten_dang_nhap,
                   DATE(l.ngay_gio_tap) as ngay_tap,
                   TIME(l.ngay_gio_tap) as gio_tap
            FROM LICH_DAT_PT l
            JOIN HOI_VIEN hv ON l.ma_hoi_vien = hv.ma_hoi_vien
            JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE l.ma_hlv = ? AND DATE(l.ngay_gio_tap) >= CURDATE()
              AND l.trang_thai IN ('pending', 'confirmed')
            ORDER BY l.ngay_gio_tap ASC
        ");
        $stmtPT->execute([$ma_hlv]);
        $ptBookings = $stmtPT->fetchAll();

        // 4. Tính toán thu nhập dự kiến tháng này (group + PT)
        $stmtP = $db->prepare("
            SELECT COUNT(*) as so_buoi 
            FROM LICH_HOC_NHOM 
            WHERE ma_hlv = ? AND MONTH(ngay_hoc) = MONTH(CURDATE()) AND YEAR(ngay_hoc) = YEAR(CURDATE())
        ");
        $stmtP->execute([$ma_hlv]);
        $so_buoi_nhom = $stmtP->fetch()['so_buoi'] ?? 0;

        $stmtPTCount = $db->prepare("
            SELECT COUNT(*) as so_buoi 
            FROM LICH_DAT_PT 
            WHERE ma_hlv = ? AND MONTH(ngay_gio_tap) = MONTH(CURDATE()) AND YEAR(ngay_gio_tap) = YEAR(CURDATE())
              AND trang_thai IN ('pending', 'confirmed', 'cancel_rejected')
        ");
        $stmtPTCount->execute([$ma_hlv]);
        $so_buoi_pt = $stmtPTCount->fetch()['so_buoi'] ?? 0;

        $so_buoi = $so_buoi_nhom + $so_buoi_pt;
        
        $luong_cung = $trainer['luong_cung'] ?? 5000000;
        $gia_buoi = $trainer['gia_buoi_day'] ?? 150000;
        $du_kien = $luong_cung + ($so_buoi * $gia_buoi);

        // 5. Đánh giá trung bình
        $stmtAvg = $db->prepare("SELECT AVG(so_sao) as avg_star FROM DANH_GIA_HLV WHERE ma_hlv = ? AND trang_thai = 'approved'");
        $stmtAvg->execute([$ma_hlv]);
        $avgStar = round($stmtAvg->fetchColumn() ?: 0, 1);

        $bookings = $ptBookings; 
        require __DIR__ . '/../views/trainer/bang-dieu-khien.php';
    }

    public function myPayroll() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtH = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtH->execute([$userId]);
        $ma_hlv = $stmtH->fetchColumn();

        // Lấy lịch sử bảng lương
        $stmtPay = $db->prepare("
            SELECT * FROM BANG_LUONG 
            WHERE ma_hlv = ? 
            ORDER BY ma_luong DESC
        ");
        $stmtPay->execute([$ma_hlv]);
        $payrolls = $stmtPay->fetchAll();

        require __DIR__ . '/../views/trainer/lich-su-luong.php';
    }

    /**
     * API: Trả về lịch đã đặt của HLV (dùng cho trang đặt lịch PT của member)
     * Static vì member cũng gọi được (không qua middleware checkTrainer)
     */
    public static function getScheduleApi() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();

        $ma_hlv = (int)($_GET['ma_hlv'] ?? 0);
        if (!$ma_hlv) {
            echo json_encode(['bookings' => []]);
            return;
        }

        // Lấy các lịch đã được xác nhận/đang chờ của HLV (14 ngày tới)
        $stmt = $db->prepare("
            SELECT DATE(ngay_gio_tap) as booking_date, 
                   DATE_FORMAT(ngay_gio_tap, '%H:%i') as booking_time
            FROM LICH_DAT_PT 
            WHERE ma_hlv = ? 
              AND ngay_gio_tap >= CURDATE()
              AND trang_thai IN ('pending', 'confirmed', 'cancel_rejected')
            ORDER BY ngay_gio_tap ASC
        ");
        $stmt->execute([$ma_hlv]);
        $bookings = $stmt->fetchAll();

        echo json_encode(['bookings' => $bookings]);
    }

    /**
     * View: Thời Khóa Biểu Tuần (Tự động từ LICH_DAT_PT)
     */
    public function schedule() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        // 1. Lấy ma_hlv
        $stmtH = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtH->execute([$userId]);
        $ma_hlv = $stmtH->fetchColumn();

        // 2. Xử lý tuần (Week Offset)
        $weekOffset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
        
        // Tìm ngày Thứ 2 của tuần được chọn
        $monday = new DateTime();
        $monday->setISODate((int)date('Y'), (int)date('W')); 
        if ($weekOffset != 0) {
            $monday->modify(($weekOffset * 7) . " days");
        }
        
        $startDate = $monday->format('Y-m-d');
        $endDate = clone $monday;
        $endDate->modify('+6 days');
        $endDateStr = $endDate->format('Y-m-d');

        $weekLabel = "Tuần " . $monday->format('d/m') . " - " . $endDate->format('d/m/Y');

        // 3. Tạo mảng các ngày trong tuần (Header)
        $weekDays = [];
        $tempDate = clone $monday;
        $thuVn = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];
        for ($i = 0; $i < 7; $i++) {
            $dateStr = $tempDate->format('Y-m-d');
            $weekDays[] = [
                'date'     => $dateStr,
                'label'    => $tempDate->format('d/m'),
                'thu_vn'   => $thuVn[$i],
                'is_today' => ($dateStr === date('Y-m-d'))
            ];
            $tempDate->modify('+1 day');
        }

        // 4. Lấy khung giờ từ config (GYM_TIME_SLOTS)
        // Chuyển đổi sang format view cần
        $timeSlots = [];
        if (defined('GYM_TIME_SLOTS')) {
            foreach (GYM_TIME_SLOTS as $slot) {
                $timeSlots[] = [
                    'label'    => substr($slot['bat_dau'], 0, 5) . ' - ' . substr($slot['ket_thuc'], 0, 5),
                    'bat_dau'  => $slot['bat_dau'],
                    'ket_thuc' => $slot['ket_thuc'],
                    'is_break' => $slot['is_break'] ?? false
                ];
            }
        }

        // 5. Lấy danh sách lịch đặt PT (LICH_DAT_PT)
        $stmtPT = $db->prepare("
            SELECT l.*, u.ho_ten as ten_hoi_vien,
                   DATE_FORMAT(l.ngay_gio_tap, '%H:%i') as gio_hien,
                   'PT' as kieu_lich
            FROM LICH_DAT_PT l
            JOIN HOI_VIEN hv ON l.ma_hoi_vien = hv.ma_hoi_vien
            JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE l.ma_hlv = ? 
              AND l.ngay_gio_tap >= ? 
              AND l.ngay_gio_tap <= ?
            ORDER BY l.ngay_gio_tap ASC
        ");
        $stmtPT->execute([$ma_hlv, $startDate . ' 00:00:00', $endDateStr . ' 23:59:59']);
        $ptBookings = $stmtPT->fetchAll();

        // 6. Lấy lịch dạy lớp nhóm (LICH_HOC_NHOM)
        $stmtS = $db->prepare("
            SELECT lh.*, l.ten_lop as ten_hoi_vien, 
                   CONCAT(lh.ngay_hoc, ' ', lh.gio_bat_dau) as ngay_gio_tap,
                   DATE_FORMAT(CONCAT(lh.ngay_hoc, ' ', lh.gio_bat_dau), '%H:%i') as gio_hien,
                   'GROUP' as kieu_lich,
                   'confirmed' as trang_thai,
                   l.loai_lop as ghi_chu
            FROM LICH_HOC_NHOM lh
            JOIN LOP_HOC_NHOM l ON lh.ma_lop = l.ma_lop
            WHERE lh.ma_hlv = ? 
              AND lh.ngay_hoc >= ? 
              AND lh.ngay_hoc <= ?
        ");
        $stmtS->execute([$ma_hlv, $startDate, $endDateStr]);
        $groupBookings = $stmtS->fetchAll();

        // 7. Gộp và Nhóm theo ngày
        $allBookings = array_merge($ptBookings, $groupBookings);
        $bookingByDate = [];
        foreach ($allBookings as $b) {
            $date = isset($b['ngay_gio_tap']) ? substr($b['ngay_gio_tap'], 0, 10) : $b['ngay_hoc'];
            $bookingByDate[$date][] = $b;
        }

        require __DIR__ . '/../views/trainer/lich-day.php';
    }

    /**
     * Chức năng: Xóa/Hủy lịch PT
     */
    public function scheduleDeleteSlot() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];
        $ma_lich = (int)($_GET['id'] ?? 0);

        if (!$ma_lich) {
            header('Location: ' . SITE_URL . '/trainer/schedule?msg=ID không hợp lệ');
            return;
        }

        // Lấy ma_hlv để bảo mật (chỉ xóa lịch của mình)
        $stmtH = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtH->execute([$userId]);
        $ma_hlv = $stmtH->fetchColumn();

        // Cập nhật trạng thái thành 'cancelled' thay vì xóa cứng (để lưu vết)
        $stmt = $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancelled' WHERE ma_lich = ? AND ma_hlv = ?");
        $stmt->execute([$ma_lich, $ma_hlv]);

        header('Location: ' . SITE_URL . '/trainer/schedule?msg=Đã hủy lịch hẹn thành công');
    }

    /**
     * Chức năng: Chấp nhận hoặc Từ chối lịch hẹn từ TKB
     */
    public function scheduleResolve() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];
        $ma_lich = (int)($_GET['id'] ?? 0);
        $action = $_GET['action'] ?? ''; // 'confirm' hoặc 'cancel'

        if (!$ma_lich || !in_array($action, ['confirm', 'cancel'])) {
            header('Location: ' . SITE_URL . '/trainer/schedule?msg=Yêu cầu không hợp lệ');
            return;
        }

        // Lấy ma_hlv để bảo mật
        $stmtH = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtH->execute([$userId]);
        $ma_hlv = $stmtH->fetchColumn();

        if ($action === 'confirm') {
            $stmt = $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'confirmed' WHERE ma_lich = ? AND ma_hlv = ?");
            $msg = "Đã xác nhận lịch hẹn";
            $stmt->execute([$ma_lich, $ma_hlv]);
        } else {
            // Khi HLV hủy hoặc từ chối -> Phải hoàn lại buổi tập cho hội viên
            $db->beginTransaction();
            try {
                // 1. Lấy ma_hoi_vien của lịch này
                $stmtGet = $db->prepare("SELECT ma_hoi_vien FROM LICH_DAT_PT WHERE ma_lich = ? AND ma_hlv = ?");
                $stmtGet->execute([$ma_lich, $ma_hlv]);
                $ma_hv = $stmtGet->fetchColumn();

                if ($ma_hv) {
                    // 2. Cập nhật trạng thái và lý do hủy
                    $stmt = $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancelled', ly_do_huy = 'HLV báo bận' WHERE ma_lich = ? AND ma_hlv = ?");
                    $stmt->execute([$ma_lich, $ma_hlv]);

                    // 3. Hoàn lại 1 buổi PT cho hội viên
                    $db->prepare("UPDATE HOI_VIEN SET so_buoi_pt_con_lai = so_buoi_pt_con_lai + 1 WHERE ma_hoi_vien = ?")
                       ->execute([$ma_hv]);
                    
                    $db->commit();
                    $msg = "Đã hủy/từ chối lịch hẹn và hoàn lại 1 buổi tập cho hội viên";
                } else {
                    $db->rollBack();
                    $msg = "Không tìm thấy thông tin lịch hẹn để hủy";
                }
            } catch (Exception $e) {
                $db->rollBack();
                $msg = "Lỗi khi xử lý hủy lịch: " . $e->getMessage();
            }
        }
        header('Location: ' . SITE_URL . '/trainer/schedule?msg=' . urlencode($msg));
    }

    /**
     * View: Danh sách học viên của HLV
     */
    public function students() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtH = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtH->execute([$userId]);
        $ma_hlv = $stmtH->fetchColumn();

        // Lấy danh sách học viên (hội viên có ít nhất 1 lịch đặt với HLV này)
        $stmtS = $db->prepare("
            SELECT DISTINCT hv.*, u.ho_ten, u.email, u.so_dien_thoai,
                   (SELECT COUNT(*) FROM LICH_DAT_PT WHERE ma_hoi_vien = hv.ma_hoi_vien AND ma_hlv = ? AND trang_thai = 'completed') as so_buoi_da_tap
            FROM HOI_VIEN hv
            JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung
            JOIN LICH_DAT_PT l ON hv.ma_hoi_vien = l.ma_hoi_vien
            WHERE l.ma_hlv = ?
        ");
        $stmtS->execute([$ma_hlv, $ma_hlv]);
        $students = $stmtS->fetchAll();

        require __DIR__ . '/../views/trainer/hoc-vien.php';
    }

    /**
     * View: Thông tin cá nhân HLV & Đánh giá
     */
    public function profile() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtH = $db->prepare("SELECT h.*, u.ten_dang_nhap, u.ho_ten FROM HUAN_LUYEN_VIEN h JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung WHERE h.ma_nguoi_dung = ?");
        $stmtH->execute([$userId]);
        $trainer = $stmtH->fetch();
        $ma_hlv = $trainer['ma_hlv'] ?? 0;

        // Lấy danh sách đánh giá
        $stmtR = $db->prepare("
            SELECT d.*, u.ho_ten as ten_hoi_vien 
            FROM DANH_GIA_HLV d 
            JOIN HOI_VIEN hv ON d.ma_hoi_vien = hv.ma_hoi_vien 
            JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung 
            WHERE d.ma_hlv = ? AND d.trang_thai != 'rejected' 
            ORDER BY d.created_at DESC
        ");
        $stmtR->execute([$ma_hlv]);
        $reviews = $stmtR->fetchAll();

        // Tính đánh giá trung bình
        $stmtAvg = $db->prepare("SELECT AVG(so_sao) as avg_star, COUNT(*) as r_count FROM DANH_GIA_HLV WHERE ma_hlv = ? AND trang_thai = 'approved'");
        $stmtAvg->execute([$ma_hlv]);
        $avgData = $stmtAvg->fetch();
        $avgRating = $avgData['avg_star'] ?? 5;
        $reviewCount = $avgData['r_count'] ?? 0;

        require __DIR__ . '/../views/trainer/ho-so.php';
    }

    /**
     * Chức năng: Cập nhật thông tin cá nhân
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];

            $ho_ten = $_POST['ho_ten'] ?? '';
            $chuyen_mon = $_POST['chuyen_mon'] ?? '';
            $kinh_nghiem = $_POST['nam_kinh_nghiem'] ?? 0;
            $gioi_thieu = $_POST['gioi_thieu'] ?? '';

            // Update NGUOI_DUNG
            $stmtU = $db->prepare("UPDATE NGUOI_DUNG SET ho_ten = ? WHERE ma_nguoi_dung = ?");
            $stmtU->execute([$ho_ten, $userId]);

            // Update HUAN_LUYEN_VIEN
            $sqlH = "UPDATE HUAN_LUYEN_VIEN SET chuyen_mon = ?, nam_kinh_nghiem = ?, gioi_thieu = ?";
            $params = [$chuyen_mon, $kinh_nghiem, $gioi_thieu];

            if (!empty($_FILES['anh_dai_dien']['name'])) {
                $uploadDir = __DIR__ . '/../public/uploads/trainers/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($_FILES['anh_dai_dien']['name'], PATHINFO_EXTENSION);
                $filename = 'hlv_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['anh_dai_dien']['tmp_name'], $uploadDir . $filename)) {
                    $sqlH .= ", anh_dai_dien = ?";
                    $params[] = '/uploads/trainers/' . $filename;
                }
            }

            $sqlH .= " WHERE ma_nguoi_dung = ?";
            $params[] = $userId;

            $stmtH = $db->prepare($sqlH);
            $stmtH->execute($params);

            setFlash('success', 'Cập nhật hồ sơ thành công!');
            header('Location: ' . SITE_URL . '/trainer/profile');
        }
    }

    /**
     * Chức năng: Báo cáo đánh giá xấu
     */
    public function reportReview() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $ma_dg = $_POST['ma_dg'] ?? 0;
            
            $db->prepare("UPDATE DANH_GIA_HLV SET trang_thai = 'reported' WHERE ma_dg = ?")->execute([$ma_dg]);
            setFlash('success', 'Đã báo cáo bài đánh giá này cho Quản trị viên.');
            header('Location: ' . SITE_URL . '/trainer/profile');
        }
    }

    /**
     * Chức năng: Lưu ghi chú về học viên
     */
    public function saveNote() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $ma_hv = $_POST['ma_hoi_vien'] ?? 0;
            $ghi_chu = $_POST['ghi_chu'] ?? '';
            
            // Có thể mở rộng bảng ghi chú, hiện tại giả định lưu vào một bảng riêng hoặc cột trong LICH_DAT_PT
            // Demo: Cập nhật ghi chú vào lịch gần nhất của học viên này với HLV này
            $stmt = $db->prepare("UPDATE LICH_DAT_PT SET ghi_chu = ? WHERE ma_hoi_vien = ? ORDER BY ngay_gio_tap DESC LIMIT 1");
            $stmt->execute([$ghi_chu, $ma_hv]);
            
            setFlash('success', 'Đã lưu ghi chú về học viên.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * CHỮA LỖI 1: Xử lý Nhận / Từ chối lịch hẹn mới (từ trang Dashboard)
     */
    public function resolveBooking() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];
            $ma_lich = (int)($_POST['ma_lich'] ?? 0);
            $action = $_POST['action'] ?? ''; // 'accept' hoặc 'reject'

            // Lấy ma_hlv để bảo mật
            $stmtH = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
            $stmtH->execute([$userId]);
            $ma_hlv = $stmtH->fetchColumn();

            if ($action === 'accept') {
                $stmt = $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'confirmed' WHERE ma_lich = ? AND ma_hlv = ?");
                $stmt->execute([$ma_lich, $ma_hlv]);
                setFlash('success', 'Đã xác nhận nhận lịch dạy thành công!');
            } elseif ($action === 'reject') {
                $db->beginTransaction();
                try {
                    // 1. Lấy mã hội viên của lịch này
                    $stmtGet = $db->prepare("SELECT ma_hoi_vien FROM LICH_DAT_PT WHERE ma_lich = ? AND ma_hlv = ?");
                    $stmtGet->execute([$ma_lich, $ma_hlv]);
                    $ma_hv = $stmtGet->fetchColumn();

                    if ($ma_hv) {
                        // 2. Hủy lịch
                        $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancelled', ly_do_huy = 'HLV từ chối nhận lịch' WHERE ma_lich = ? AND ma_hlv = ?")
                           ->execute([$ma_lich, $ma_hlv]);

                        // 3. Hoàn lại buổi tập cho khách
                        $db->prepare("UPDATE HOI_VIEN SET so_buoi_pt_con_lai = so_buoi_pt_con_lai + 1 WHERE ma_hoi_vien = ?")
                           ->execute([$ma_hv]);

                        $db->commit();
                        setFlash('warning', 'Đã từ chối lịch. Hệ thống đã hoàn lại buổi tập cho học viên.');
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    setFlash('danger', 'Lỗi khi từ chối lịch.');
                }
            }
            header('Location: ' . SITE_URL . '/trainer/dashboard');
        }
    }

    /**
     * CHỮA LỖI 2: Xử lý Chấp nhận / Phạt khi khách xin hủy lịch
     */
    public function resolveCancel() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];
            $ma_lich = (int)($_POST['ma_lich'] ?? 0);
            $action = $_POST['action'] ?? ''; // 'accept_cancel' hoặc 'reject_cancel'

            $stmtH = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
            $stmtH->execute([$userId]);
            $ma_hlv = $stmtH->fetchColumn();

            if ($action === 'accept_cancel') {
                $db->beginTransaction();
                try {
                    $stmtGet = $db->prepare("SELECT ma_hoi_vien FROM LICH_DAT_PT WHERE ma_lich = ? AND ma_hlv = ?");
                    $stmtGet->execute([$ma_lich, $ma_hlv]);
                    $ma_hv = $stmtGet->fetchColumn();

                    if ($ma_hv) {
                        // Đổi trạng thái thành đã hủy
                        $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancelled' WHERE ma_lich = ? AND ma_hlv = ?")
                           ->execute([$ma_lich, $ma_hlv]);

                        // Hoàn buổi tập
                        $db->prepare("UPDATE HOI_VIEN SET so_buoi_pt_con_lai = so_buoi_pt_con_lai + 1 WHERE ma_hoi_vien = ?")
                           ->execute([$ma_hv]);

                        $db->commit();
                        setFlash('success', 'Đã chấp nhận yêu cầu hủy. Học viên không bị trừ buổi.');
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    setFlash('danger', 'Lỗi hệ thống.');
                }
            } elseif ($action === 'reject_cancel') {
                // Bác bỏ lý do hủy -> Khách bị mất buổi (Phạt)
                $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancel_rejected' WHERE ma_lich = ? AND ma_hlv = ?")
                   ->execute([$ma_lich, $ma_hlv]);
                setFlash('danger', 'Đã bác bỏ yêu cầu hủy. Học viên sẽ bị trừ mất buổi tập này!');
            }
            header('Location: ' . SITE_URL . '/trainer/dashboard');
        }
    }

    /**
     * Chức năng: Cập nhật giờ hoặc ghi chú cho lịch đã xếp
     */
    public function scheduleUpdate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];
            
            $ma_lich = (int)($_POST['ma_lich'] ?? 0);
            $ghi_chu = $_POST['ghi_chu'] ?? '';
            $gio_tap_moi = $_POST['gio_tap'] ?? ''; // Nếu cho phép đổi giờ

            // Lấy ma_hlv để đảm bảo chỉ sửa lịch của mình
            $stmtH = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
            $stmtH->execute([$userId]);
            $ma_hlv = $stmtH->fetchColumn();

            if ($ma_lich && $ma_hlv) {
                try {
                    $sql = "UPDATE LICH_DAT_PT SET ghi_chu = ?";
                    $params = [$ghi_chu];
                    
                    if (!empty($gio_tap_moi)) {
                        // Cập nhật lại phần giờ trong ngay_gio_tap
                        $sql .= ", ngay_gio_tap = CONCAT(DATE(ngay_gio_tap), ' ', ?)";
                        $params[] = $gio_tap_moi . ':00';
                    }
                    
                    $sql .= " WHERE ma_lich = ? AND ma_hlv = ?";
                    $params[] = $ma_lich;
                    $params[] = $ma_hlv;

                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    
                    setFlash('success', 'Đã cập nhật lịch hẹn thành công!');
                } catch (Exception $e) {
                    setFlash('danger', 'Lỗi khi cập nhật lịch.');
                }
            }
            header('Location: ' . SITE_URL . '/trainer/schedule');
        }
    }
}
