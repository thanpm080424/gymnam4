<?php
require_once __DIR__ . '/../utils/Middleware.php';

// Danh sách khung giờ chuẩn của gym
if (!defined('GYM_TIME_SLOTS')) {
    define('GYM_TIME_SLOTS', [
        ['bat_dau' => '05:30:00', 'ket_thuc' => '06:50:00', 'label' => '5h30–6h50'],
        ['bat_dau' => '07:00:00', 'ket_thuc' => '08:20:00', 'label' => '7h00–8h20'],
        ['bat_dau' => '08:30:00', 'ket_thuc' => '09:50:00', 'label' => '8h30–9h50'],
        ['bat_dau' => '10:00:00', 'ket_thuc' => '11:20:00', 'label' => '10h00–11h20'],
        ['bat_dau' => '11:30:00', 'ket_thuc' => '13:30:00', 'label' => '🔸 11h30–13h30 (NGHỈ)', 'is_break' => true],
        ['bat_dau' => '13:30:00', 'ket_thuc' => '14:50:00', 'label' => '13h30–14h50'],
        ['bat_dau' => '15:00:00', 'ket_thuc' => '16:20:00', 'label' => '15h00–16h20'],
        ['bat_dau' => '17:00:00', 'ket_thuc' => '18:20:00', 'label' => '17h00–18h20'],
        ['bat_dau' => '18:30:00', 'ket_thuc' => '19:50:00', 'label' => '18h30–19h50'],
        ['bat_dau' => '20:00:00', 'ket_thuc' => '21:20:00', 'label' => '20h00–21h20'],
    ]);
}

class TrainerController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '';
        if (!isset($_SESSION['user_id']) || $role !== 'hlv') {
            header("Location: " . SITE_URL . "/login");
            exit;
        }
    }

    public function dashboard() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];
        
        $stmtT = $db->prepare("SELECT ma_hlv, chuyen_mon FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtT->execute([$userId]);
        $trainer = $stmtT->fetch();

        $stmtB = $db->prepare("
            SELECT l.*, COALESCE(u.ho_ten, u.ten_dang_nhap) as ho_ten, u.ten_dang_nhap, h.chieu_cao, h.can_nang
            FROM LICH_DAT_PT l
            JOIN HOI_VIEN h ON l.ma_hoi_vien = h.ma_hoi_vien
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE l.ma_hlv = ?
            ORDER BY CASE WHEN l.trang_thai IN ('pending', 'cancel_requested') THEN 1 ELSE 2 END, l.ngay_gio_tap DESC
        ");
        $stmtB->execute([$trainer['ma_hlv']]);
        $bookings = $stmtB->fetchAll();

        require __DIR__ . '/../views/trainer/bang-dieu-khien.php';
    }

    public function students() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtT = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtT->execute([$userId]);
        $trainer = $stmtT->fetch();

        if (!$trainer) {
            header("Location: " . SITE_URL . "/login"); exit;
        }

        // Lấy danh sách học viên duy nhất đã từng đặt lịch hoặc đang học với HLV này
        $stmtS = $db->prepare("
            SELECT DISTINCT h.ma_hoi_vien, COALESCE(u.ho_ten, u.ten_dang_nhap) as ho_ten, u.email, u.so_dien_thoai,
                   h.chieu_cao, h.can_nang, h.so_buoi_pt_con_lai,
                   (SELECT COUNT(*) FROM LICH_DAT_PT WHERE ma_hoi_vien = h.ma_hoi_vien AND ma_hlv = ? AND trang_thai = 'attended') as so_buoi_da_tap
            FROM LICH_DAT_PT l
            JOIN HOI_VIEN h ON l.ma_hoi_vien = h.ma_hoi_vien
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE l.ma_hlv = ?
            ORDER BY u.ho_ten ASC
        ");
        $stmtS->execute([$trainer['ma_hlv'], $trainer['ma_hlv']]);
        $students = $stmtS->fetchAll();

        require __DIR__ . '/../views/trainer/hoc-vien.php';
    }

    public function resolveBooking() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $action = $_POST['action'];
            $maLich = $_POST['ma_lich'];
            
            // Lấy thông tin lịch + khách
            $stmtL = $db->prepare("SELECT ma_hoi_vien, trang_thai FROM LICH_DAT_PT WHERE ma_lich = ?");
            $stmtL->execute([$maLich]);
            $lich = $stmtL->fetch();

            if ($lich['trang_thai'] === 'pending') {
                if ($action === 'accept') {
                    $db->beginTransaction();
                    $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'confirmed' WHERE ma_lich = ?")->execute([$maLich]);
                    
                    // Reject other pending requests for the same slot
                    $stmtSlot = $db->prepare("SELECT ma_hlv, ngay_gio_tap FROM LICH_DAT_PT WHERE ma_lich = ?");
                    $stmtSlot->execute([$maLich]);
                    $slot = $stmtSlot->fetch();
                    
                    $stmtOthers = $db->prepare("SELECT ma_lich, ma_hoi_vien FROM LICH_DAT_PT WHERE ma_hlv = ? AND ngay_gio_tap = ? AND trang_thai = 'pending' AND ma_lich != ?");
                    $stmtOthers->execute([$slot['ma_hlv'], $slot['ngay_gio_tap'], $maLich]);
                    $others = $stmtOthers->fetchAll();
                    
                    foreach($others as $other) {
                        $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancelled', ly_do_huy = 'HLV đã nhận học viên khác' WHERE ma_lich = ?")->execute([$other['ma_lich']]);
                        $db->prepare("UPDATE HOI_VIEN SET so_buoi_pt_con_lai = so_buoi_pt_con_lai + 1 WHERE ma_hoi_vien = ?")->execute([$other['ma_hoi_vien']]);
                    }
                    
                    $db->commit();
                    $msg = "Đã nhận lịch hẹn báo với học viên.";
                } elseif ($action === 'reject') {
                    $db->beginTransaction();
                    $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancelled' WHERE ma_lich = ?")->execute([$maLich]);
                    // Refund 1 PT session
                    $db->prepare("UPDATE HOI_VIEN SET so_buoi_pt_con_lai = so_buoi_pt_con_lai + 1 WHERE ma_hoi_vien = ?")->execute([$lich['ma_hoi_vien']]);
                    $db->commit();
                    $msg = "Đã từ chối lịch và hoàn trả buổi PT cho học viên.";
                }
            }
            
            header("Location: " . SITE_URL . "/trainer/dashboard?msg=" . urlencode($msg ?? "Thao tac thanh cong."));
        }
    }

    public function resolveCancel() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $action = $_POST['action'];
            $maLich = $_POST['ma_lich'];

            $stmtL = $db->prepare("SELECT ma_hoi_vien, trang_thai FROM LICH_DAT_PT WHERE ma_lich = ?");
            $stmtL->execute([$maLich]);
            $lich = $stmtL->fetch();

            if ($lich['trang_thai'] === 'cancel_requested') {
                if ($action === 'accept_cancel') {
                    $db->beginTransaction();
                    $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancelled' WHERE ma_lich = ?")->execute([$maLich]);
                    // Refund 1 PT session because cancel is accepted
                    $db->prepare("UPDATE HOI_VIEN SET so_buoi_pt_con_lai = so_buoi_pt_con_lai + 1 WHERE ma_hoi_vien = ?")->execute([$lich['ma_hoi_vien']]);
                    $db->commit();
                    $msg = "Đã chấp nhận đơn xin huỷ. Buổi tập đã hoàn lại cho khách.";
                } elseif ($action === 'reject_cancel') {
                    $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancel_rejected' WHERE ma_lich = ?")->execute([$maLich]);
                    $msg = "Đã BÁC BỎ lý do huỷ của khách. Khách hàng BỊ TRỪ buổi tập này.";
                }
            }
            header("Location: " . SITE_URL . "/trainer/dashboard?msg=" . urlencode($msg ?? "Da xu ly xong."));
        }
    }

    // ============================================================
    // HỒ SƠ CÁ NHÂN HLV
    // ============================================================
    public function profile() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtT = $db->prepare("
            SELECT t.*, u.ten_dang_nhap, u.ho_ten FROM HUAN_LUYEN_VIEN t
            JOIN NGUOI_DUNG u ON t.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE t.ma_nguoi_dung = ?
        ");
        $stmtT->execute([$userId]);
        $trainer = $stmtT->fetch();

        $stmtR = $db->prepare("
            SELECT r.*, u.ten_dang_nhap as ten_hoi_vien FROM DANH_GIA_HLV r
            JOIN HOI_VIEN hv ON r.ma_hoi_vien = hv.ma_hoi_vien
            JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE r.ma_hlv = ? AND r.trang_thai = 'approved'
            ORDER BY r.created_at DESC
        ");
        $stmtR->execute([$trainer['ma_hlv']]);
        $reviews = $stmtR->fetchAll();

        $stmtAvg = $db->prepare("SELECT AVG(so_sao) as avg, COUNT(*) as cnt FROM DANH_GIA_HLV WHERE ma_hlv = ? AND trang_thai = 'approved'");
        $stmtAvg->execute([$trainer['ma_hlv']]);
        $ratingInfo = $stmtAvg->fetch();
        $avgRating = $ratingInfo['avg'];
        $reviewCount = $ratingInfo['cnt'];

        require __DIR__ . '/../views/trainer/ho-so.php';
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];

            $stmtT = $db->prepare("SELECT ma_hlv, anh_dai_dien FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
            $stmtT->execute([$userId]);
            $trainer = $stmtT->fetch();

            $anhDaiDien = $trainer['anh_dai_dien'];
            if (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] == 0) {
                $uploadDir = __DIR__ . '/../public/uploads/trainers/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($_FILES['anh_dai_dien']['name'], PATHINFO_EXTENSION);
                $anhDaiDien = '/uploads/trainers/' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['anh_dai_dien']['tmp_name'], __DIR__ . '/../public' . $anhDaiDien);
            }

            $db->prepare("UPDATE HUAN_LUYEN_VIEN SET chuyen_mon=?, nam_kinh_nghiem=?, gioi_thieu=?, anh_dai_dien=? WHERE ma_hlv=?")
               ->execute([$_POST['chuyen_mon'], $_POST['nam_kinh_nghiem'], $_POST['gioi_thieu'], $anhDaiDien, $trainer['ma_hlv']]);

            // Cập nhật họ tên trong bảng NGUOI_DUNG
            $hoTen = trim($_POST['ho_ten'] ?? '');
            if ($hoTen !== '') {
                $db->prepare("UPDATE NGUOI_DUNG SET ho_ten=? WHERE ma_nguoi_dung=?")
                   ->execute([$hoTen, $userId]);
            }

            header("Location: " . SITE_URL . "/trainer/profile?msg=" . urlencode("Da cap nhat ho so!"));
        }
    }

    public function saveNote() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];

            $stmtT = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
            $stmtT->execute([$userId]);
            $maHlv = $stmtT->fetchColumn();

            $db->prepare("INSERT INTO GHI_CHU_HLV (ma_hlv, ma_hoi_vien, noi_dung) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE noi_dung=?")
               ->execute([$maHlv, $_POST['ma_hoi_vien'], $_POST['noi_dung'], $_POST['noi_dung']]);

            header("Location: " . SITE_URL . "/trainer/dashboard?msg=" . urlencode("Da luu ghi chu."));
        }
    }

    public function reportReview() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $db->prepare("UPDATE DANH_GIA_HLV SET trang_thai='reported' WHERE ma_dg=?")
               ->execute([$_POST['ma_dg']]);
            header("Location: " . SITE_URL . "/trainer/profile?msg=" . urlencode("Da bao cao bai danh gia. Staff se xem xet."));
        }
    }

    // ============================================================
    // THỜI KHÓA BIỂU HLV
    // ============================================================

    /**
     * GET /trainer/schedule
     * HLV xem & quản lý thời khóa biểu tuần của mình
     * ?week=0 (tuần này), ?week=-1 (tuần trước), ?week=1 (tuần sau)
     */
    public function schedule() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtT = $db->prepare("SELECT ma_hlv, chuyen_mon FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtT->execute([$userId]);
        $trainer = $stmtT->fetch();
        if (!$trainer) { header("Location: " . SITE_URL . "/trainer/dashboard"); exit; }

        $weekOffset = (int)($_GET['week'] ?? 0);
        $monday = new DateTime();
        $monday->modify('monday this week');
        $monday->modify($weekOffset . ' weeks');
        $sunday = clone $monday;
        $sunday->modify('+6 days');

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $d = clone $monday;
            $d->modify("$i days");
            $weekDays[] = [
                'date'   => $d->format('Y-m-d'),
                'label'  => $d->format('d/m'),
                'thu_vn' => ['', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'][(int)$d->format('N')],
                'is_today' => ($d->format('Y-m-d') === date('Y-m-d')),
            ];
        }

        // TKB = tất cả lịch hẹn thực tế trong tuần (tự động, không cần nhập thủ công)
        $stmtB = $db->prepare("
            SELECT l.ma_lich, l.ngay_gio_tap, l.loai_pt, l.ghi_chu, l.trang_thai,
                   DATE(l.ngay_gio_tap) as ngay,
                   TIME_FORMAT(l.ngay_gio_tap, '%H:%i') as gio_hien,
                   COALESCE(u.ho_ten, u.ten_dang_nhap) as ten_hoi_vien
            FROM LICH_DAT_PT l
            JOIN HOI_VIEN h ON l.ma_hoi_vien = h.ma_hoi_vien
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE l.ma_hlv = ?
            AND l.ngay_gio_tap BETWEEN ? AND ?
            AND l.trang_thai NOT IN ('cancelled', 'cancel_rejected')
            ORDER BY l.ngay_gio_tap ASC
        ");
        $stmtB->execute([
            $trainer['ma_hlv'],
            $monday->format('Y-m-d') . ' 00:00:00',
            $sunday->format('Y-m-d') . ' 23:59:59'
        ]);
        $rawBookings = $stmtB->fetchAll();

        // Gom nhóm theo ngày: $bookingByDate['2026-04-25'] = [booking1, booking2, ...]
        $bookingByDate = [];
        foreach ($rawBookings as $b) {
            $bookingByDate[$b['ngay']][] = $b;
        }

        $timeSlots = GYM_TIME_SLOTS;
        $weekLabel = 'Tuần ' . $monday->format('d/m') . ' – ' . $sunday->format('d/m/Y');

        require __DIR__ . '/../views/trainer/lich-day.php';
    }

    /**
     * POST /trainer/schedule/update
     * HLV thêm hoặc cập nhật 1 slot vào thời khóa biểu
     */
    public function scheduleUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . SITE_URL . "/trainer/schedule"); exit;
        }
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtT = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtT->execute([$userId]);
        $maHlv = $stmtT->fetchColumn();

        $ngayTrongTuan = (int)$_POST['ngay_trong_tuan'];  // 0=CN..6=T7
        $gioBatDau     = $_POST['gio_bat_dau'];
        $gioKetThuc    = $_POST['gio_ket_thuc'];
        $mucTieu       = trim($_POST['muc_tieu'] ?? '');
        $trangThai     = in_array($_POST['trang_thai'] ?? '', ['trong', 'da_dat']) ? $_POST['trang_thai'] : 'trong';
        $maLlv         = !empty($_POST['ma_llv']) ? (int)$_POST['ma_llv'] : null;

        if ($maLlv) {
            // Cập nhật slot đã tồn tại
            $db->prepare("
                UPDATE lich_lam_viec_hlv
                SET ngay_trong_tuan=?, gio_bat_dau=?, gio_ket_thuc=?, muc_tieu=?, trang_thai=?
                WHERE ma_llv=? AND ma_hlv=?
            ")->execute([$ngayTrongTuan, $gioBatDau, $gioKetThuc, $mucTieu, $trangThai, $maLlv, $maHlv]);
        } else {
            // Thêm slot mới (INSERT hoặc cập nhật nếu trùng)
            $db->prepare("
                INSERT INTO lich_lam_viec_hlv (ma_hlv, ngay_trong_tuan, gio_bat_dau, gio_ket_thuc, muc_tieu, trang_thai)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE gio_ket_thuc=VALUES(gio_ket_thuc), muc_tieu=VALUES(muc_tieu), trang_thai=VALUES(trang_thai)
            ")->execute([$maHlv, $ngayTrongTuan, $gioBatDau, $gioKetThuc, $mucTieu, $trangThai]);
        }

        $week = (int)($_POST['week_offset'] ?? 0);
        header("Location: " . SITE_URL . "/trainer/schedule?week=$week&msg=" . urlencode("Da luu slot lich day!"));
        exit;
    }

    /**
     * POST /trainer/schedule/delete
     * HLV xóa 1 slot khỏi thời khóa biểu
     */
    public function scheduleDeleteSlot() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . SITE_URL . "/trainer/schedule"); exit;
        }
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtT = $db->prepare("SELECT ma_hlv FROM HUAN_LUYEN_VIEN WHERE ma_nguoi_dung = ?");
        $stmtT->execute([$userId]);
        $maHlv = $stmtT->fetchColumn();

        $maLlv = (int)($_POST['ma_llv'] ?? 0);
        if ($maLlv) {
            $db->prepare("DELETE FROM lich_lam_viec_hlv WHERE ma_llv = ? AND ma_hlv = ?")
               ->execute([$maLlv, $maHlv]);
        }

        $week = (int)($_POST['week_offset'] ?? 0);
        header("Location: " . SITE_URL . "/trainer/schedule?week=$week&msg=" . urlencode("Da xoa slot."));
        exit;
    }

    /**
     * GET /trainer/schedule/api?ma_hlv=X
     * API JSON công khai (không yêu cầu đăng nhập HLV) — cho hội viên xem lịch
     * Gọi từ member booking page
     */
    public static function getScheduleApi() {
        header('Content-Type: application/json; charset=utf-8');
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();

        $maHlv = (int)($_GET['ma_hlv'] ?? 0);
        if (!$maHlv) { echo json_encode(['error' => 'Thi\u1ebfu ma_hlv']); exit; }

        // Tr\u1ea3 l\u1ecbch h\u1eb9n th\u1ef1c t\u1ebf tu\u1ea7n hi\u1ec7n t\u1ea1i
        $monday = new DateTime();
        $monday->modify('monday this week');
        $sunday = clone $monday;
        $sunday->modify('+6 days');

        $stmt = $db->prepare("
            SELECT
                DATE(l.ngay_gio_tap)                    AS booking_date,
                TIME_FORMAT(l.ngay_gio_tap, '%H:%i')    AS booking_time,
                DAYOFWEEK(l.ngay_gio_tap)               AS mysql_dow,
                l.loai_pt, l.trang_thai
            FROM LICH_DAT_PT l
            WHERE l.ma_hlv = ?
              AND l.ngay_gio_tap BETWEEN ? AND ?
              AND l.trang_thai != 'cancelled'
        ");
        $stmt->execute([
            $maHlv,
            $monday->format('Y-m-d') . ' 00:00:00',
            $sunday->format('Y-m-d') . ' 23:59:59'
        ]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'bookings'   => $bookings,
            'time_slots' => GYM_TIME_SLOTS,
            'week_start' => $monday->format('Y-m-d'),
        ]);
        exit;
    }
}

