<?php
require_once __DIR__ . '/../utils/Middleware.php';
require_once __DIR__ . '/../includes/EmailService.php';

class MemberController {
    public function __construct() {
        Middleware::checkAuth();
    }

    public function dashboard() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        
        $userId = $_SESSION['user_id'];
        
        $stmt = $db->prepare("SELECT u.ten_dang_nhap, COALESCE(u.ho_ten, u.ten_dang_nhap) as ho_ten, 
                                     u.email, u.so_dien_thoai,
                                     h.ma_hoi_vien, h.ma_qr, h.chieu_cao, h.can_nang, h.ngay_het_han_goi, h.so_buoi_pt_con_lai 
                              FROM NGUOI_DUNG u 
                              JOIN HOI_VIEN h ON u.ma_nguoi_dung = h.ma_nguoi_dung 
                              WHERE u.ma_nguoi_dung = :id");
        $stmt->execute(['id' => $userId]);
        $memberInfo = $stmt->fetch();

        $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmtM->execute([$userId]);
        $memberId = $stmtM->fetchColumn();

        $productOrders = [];
        if ($memberId) {
            $stmtProd = $db->prepare("
                SELECT d.ma_dh, d.ngay_mua, d.trang_thai, s.ten_sp, s.gia_tien, d.ma_giao_dich 
                FROM DON_HANG_SP d
                JOIN SAN_PHAM s ON d.ma_sp = s.ma_sp
                WHERE d.ma_hoi_vien = ?
                ORDER BY d.ngay_mua DESC
            ");
            $stmtProd->execute([$memberId]);
            $productOrders = $stmtProd->fetchAll();
            foreach($productOrders as &$po) $po['type'] = 'product';

            // Lấy đăng ký gói tập
            $stmtPack = $db->prepare("
                SELECT dk.ma_dang_ky, t.ngay_thanh_toan as ngay_mua, 'completed' as trang_thai, 
                       g.ten_goi as ten_sp, t.so_tien as gia_tien, t.vnp_TxnRef as ma_giao_dich, 'package' as type
                FROM DANG_KY_GOI dk 
                JOIN GOI_TAP g ON dk.ma_goi = g.ma_goi 
                JOIN THANH_TOAN t ON dk.ma_dang_ky = t.ma_dang_ky 
                WHERE dk.ma_hoi_vien = ?
                ORDER BY t.ngay_thanh_toan DESC
            ");
            $stmtPack->execute([$memberId]);
            $packageOrders = $stmtPack->fetchAll();

            // Lấy thuê tủ đồ
            $stmtLock = $db->prepare("
                SELECT t.ma_thanh_toan, t.created_at as ngay_mua, t.trang_thai, 
                       t.ghi_chu as ten_sp, t.so_tien as gia_tien, t.vnp_TxnRef as ma_giao_dich, 'locker' as type
                FROM THANH_TOAN t
                JOIN YEU_CAU_THUE_TU yc ON t.ma_yc_thue = yc.ma_yc
                WHERE yc.ma_hoi_vien = ? AND t.ma_yc_thue IS NOT NULL
                ORDER BY t.created_at DESC
            ");
            $stmtLock->execute([$memberId]);
            $lockerOrders = $stmtLock->fetchAll();

            $transactions = array_merge($productOrders, $packageOrders, $lockerOrders);
            usort($transactions, function($a, $b) {
                $tA = isset($a['ngay_mua']) ? strtotime($a['ngay_mua']) : 0;
                $tB = isset($b['ngay_mua']) ? strtotime($b['ngay_mua']) : 0;
                return $tB <=> $tA;
            });
            $transactions = array_slice($transactions, 0, 10);
        } else {
            $transactions = [];
        }

        // 3. Lấy thông báo hệ thống
        $announcements = $db->query("SELECT * FROM THONG_BAO_HE_THONG WHERE trang_thai = 'active' ORDER BY created_at DESC")->fetchAll();

        // 4. Lấy lớp học Group X sắp diễn ra nhất của hội viên này
        $nextClass = null;
        if ($memberId) {
            $stmtNext = $db->prepare("
                SELECT lh.*, l.ten_lop, l.hinh_anh, COALESCE(u.ho_ten, u.ten_dang_nhap) as ten_hlv
                FROM DAT_CHO_LOP_HOC dc
                JOIN LICH_HOC_NHOM lh ON dc.ma_lich = lh.ma_lich
                JOIN LOP_HOC_NHOM l ON lh.ma_lop = l.ma_lop
                JOIN HUAN_LUYEN_VIEN h ON lh.ma_hlv = h.ma_hlv
                JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
                WHERE dc.ma_hoi_vien = ? AND dc.trang_thai = 'thanh_cong' 
                  AND (lh.ngay_hoc > CURDATE() OR (lh.ngay_hoc = CURDATE() AND lh.gio_bat_dau > CURTIME()))
                ORDER BY lh.ngay_hoc ASC, lh.gio_bat_dau ASC
                LIMIT 1
            ");
            $stmtNext->execute([$memberId]);
            $nextClass = $stmtNext->fetch();
        }

        require __DIR__ . '/../views/member/bang-dieu-khien.php';
    }

    public function history() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmtM->execute([$userId]);
        $memberId = $stmtM->fetchColumn();

        $transactions = [];
        if ($memberId) {
            // Lấy đơn hàng sản phẩm
            $stmtProd = $db->prepare("
                SELECT d.ma_dh, d.ngay_mua, d.trang_thai, s.ten_sp, d.gia_thanh_toan as gia_tien, d.ma_giao_dich, 'product' as type
                FROM DON_HANG_SP d
                JOIN SAN_PHAM s ON d.ma_sp = s.ma_sp
                WHERE d.ma_hoi_vien = ?
                ORDER BY d.ngay_mua DESC
            ");
            $stmtProd->execute([$memberId]);
            $productOrders = $stmtProd->fetchAll();

            // Lấy đăng ký gói tập
            $stmtPack = $db->prepare("
                SELECT dk.ma_dang_ky, t.ngay_thanh_toan as ngay_mua, t.trang_thai, 
                       g.ten_goi as ten_sp, t.so_tien as gia_tien, t.vnp_TxnRef as ma_giao_dich, 'package' as type
                FROM DANG_KY_GOI dk 
                JOIN GOI_TAP g ON dk.ma_goi = g.ma_goi 
                JOIN THANH_TOAN t ON dk.ma_dang_ky = t.ma_dang_ky 
                WHERE dk.ma_hoi_vien = ?
                ORDER BY t.ngay_thanh_toan DESC
            ");
            $stmtPack->execute([$memberId]);
            $packageOrders = $stmtPack->fetchAll();

            // Lấy thuê tủ đồ
            $stmtLock = $db->prepare("
                SELECT t.ma_thanh_toan, t.created_at as ngay_mua, t.trang_thai, 
                       t.ghi_chu as ten_sp, t.so_tien as gia_tien, t.vnp_TxnRef as ma_giao_dich, 'locker' as type
                FROM THANH_TOAN t
                JOIN YEU_CAU_THUE_TU yc ON t.ma_yc_thue = yc.ma_yc
                WHERE yc.ma_hoi_vien = ? AND t.ma_yc_thue IS NOT NULL
                ORDER BY t.created_at DESC
            ");
            $stmtLock->execute([$memberId]);
            $lockerOrders = $stmtLock->fetchAll();

            $transactions = array_merge($productOrders, $packageOrders, $lockerOrders);
            usort($transactions, function($a, $b) {
                $da = $a['ngay_mua'] ? strtotime($a['ngay_mua']) : 0;
                $db = $b['ngay_mua'] ? strtotime($b['ngay_mua']) : 0;
                return $db <=> $da;
            });
        }

        require __DIR__ . '/../views/member/lich-su.php';
    }

    public function store() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        
        // 1. Phân Loại Gói Tập Hội Viên
        $stmt_mem = $db->query("SELECT * FROM GOI_TAP WHERE loai_goi = 'membership' ORDER BY gia_tien ASC");
        $packages = $stmt_mem ? $stmt_mem->fetchAll() : [];
        
        // 2. Phân Loại Gói Buổi PT
        $stmt_pt = $db->query("SELECT * FROM GOI_TAP WHERE loai_goi = 'pt' ORDER BY gia_tien ASC");
        $ptPackages = $stmt_pt ? $stmt_pt->fetchAll() : [];
        
        // 3. Phân Loại Sản Phẩm TPCN
        $products = [];
        try {
            // Thử query - tương thích schema cũ (gia_tien/ton_kho) và mới (gia_ban/so_luong_ton)
            $cols = $db->query("SHOW COLUMNS FROM SAN_PHAM")->fetchAll(PDO::FETCH_COLUMN);
            $hasMaSP  = in_array('ma_san_pham', $cols) ? 'ma_san_pham' : (in_array('ma_sp', $cols) ? 'ma_sp' : 'ma_san_pham');
            $hasGia   = in_array('gia_ban', $cols) ? 'gia_ban' : 'gia_tien';
            $hasTon   = in_array('so_luong_ton', $cols) ? 'so_luong_ton' : 'ton_kho';
            $hasAnh   = in_array('anh_sp', $cols) ? 'anh_sp' : (in_array('hinh_anh', $cols) ? 'hinh_anh' : 'anh_sp');
            $hasTen   = in_array('ten_sp', $cols) ? 'ten_sp' : 'ten_san_pham';
            $hasMoTa  = in_array('mo_ta', $cols) ? 'mo_ta' : "'' AS mo_ta";
            $hasTrangThai = in_array('trang_thai', $cols) ? "AND trang_thai = 'active'" : '';
            $sql = "SELECT *, $hasMaSP as _ma_sp, $hasGia as _gia_ban, $hasTon as _ton_kho,
                           $hasAnh as _anh_sp, $hasTen as _ten_sp, $hasMoTa as _mo_ta
                    FROM SAN_PHAM
                    WHERE $hasTon > 0 $hasTrangThai
                    ORDER BY $hasMaSP DESC";
            $stmt_prod = $db->query($sql);
            if ($stmt_prod) {
                $products = $stmt_prod->fetchAll();
            }
        } catch (Exception $e) {
            $products = [];
        }

        // Check thẻ member
        $stmt = $db->prepare("SELECT trang_thai FROM NGUOI_DUNG WHERE ma_nguoi_dung = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $memberStatus = $stmt->fetchColumn();

        // Khuyen Mai
        $stmtPromoQ = $db->query("SELECT * FROM MA_GIAM_GIA WHERE so_luong_con > 0 AND ngay_het_han > NOW() ORDER BY created_at DESC");
        $stmtPromo = $stmtPromoQ ?: null;
        $promotions = $stmtPromo->fetchAll();

        require __DIR__ . '/../views/member/cua-hang.php';
    }

    public function buyProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];
            $maSp = $_POST['ma_sp'];

            // Lọc Member ID
            $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $memberId = $stmtM->fetchColumn();

            // Rút Tồn Kho && Rút Giá Điền
            $stmtP = $db->prepare("SELECT gia_tien, ton_kho FROM SAN_PHAM WHERE ma_sp = ?");
            $stmtP->execute([$maSp]);
            $product = $stmtP->fetch();

            if($product['ton_kho'] < 1) {
                header("Location: " . SITE_URL . "/member/store?error=" . urlencode("San pham nay da het hang."));
                exit;
            }

            try {
                $db->beginTransaction();

                // Check ma giam gia
                $maGiamGiaId = null;
                $giaThanhToan = $product['gia_tien'];
                if (!empty($_POST['ma_giam_gia'])) {
                    $code = strtoupper(trim($_POST['ma_giam_gia']));
                    $stmtPromo = $db->prepare("SELECT ma_giam_gia, phan_tram_giam, so_tien_giam FROM MA_GIAM_GIA WHERE code = ? AND so_luong_con > 0 AND ngay_het_han > NOW() AND loai_ap_dung IN ('all', 'product')");
                    $stmtPromo->execute([$code]);
                    $promo = $stmtPromo->fetch();
                    if ($promo) {
                        $maGiamGiaId = $promo['ma_giam_gia'];
                        if ($promo['phan_tram_giam'] > 0) {
                            $giaThanhToan = max(0, $product['gia_tien'] * (1 - $promo['phan_tram_giam'] / 100));
                        } else {
                            $giaThanhToan = max(0, $product['gia_tien'] - $promo['so_tien_giam']);
                        }
                        // Tru luot dung
                        $db->prepare("UPDATE MA_GIAM_GIA SET so_luong_con = so_luong_con - 1 WHERE ma_giam_gia = ?")->execute([$maGiamGiaId]);
                    }
                }

                // Tru Ton Kho
                $db->prepare("UPDATE SAN_PHAM SET ton_kho = ton_kho - 1 WHERE ma_sp = ?")->execute([$maSp]);

                // Record Don Hang (Sinh Ma Duy Nhat)
                $maGD = "MG" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
                $stmtOrder = $db->prepare("INSERT INTO DON_HANG_SP (ma_hoi_vien, ma_sp, ma_giao_dich, trang_thai, gia_thanh_toan) VALUES (?, ?, ?, 'pending', ?)");
                $stmtOrder->execute([$memberId, $maSp, $maGD, $giaThanhToan]);

                $db->commit();

                // Chuyen toi giao dien Bien Lai
                header("Location: " . SITE_URL . "/member/receipt?ma_gd=" . $maGD);
            } catch (Exception $e) {
                $db->rollBack();
                header("Location: " . SITE_URL . "/member/store?error=" . urlencode("Giao dịch lỗi. Thử lại sau."));
            }
        }
    }

    public function receipt() {
        if(!isset($_GET['ma_gd'])) header("Location: " . SITE_URL . "/member/store");
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT d.ma_giao_dich, d.ngay_mua, d.trang_thai, s.ten_sp, s.gia_tien, d.gia_thanh_toan
            FROM DON_HANG_SP d
            JOIN SAN_PHAM s ON d.ma_sp = s.ma_sp
            JOIN HOI_VIEN h ON d.ma_hoi_vien = h.ma_hoi_vien
            WHERE d.ma_giao_dich = ? AND h.ma_nguoi_dung = ?
        ");
        $stmt->execute([$_GET['ma_gd'], $_SESSION['user_id']]);
        $receipt = $stmt->fetch();

        if(!$receipt) {
            echo "Bien lai khong hop le hoac khong thuoc ve ban!";
            exit;
        }
        
        require __DIR__ . '/../views/member/bien-lai.php';
    }

    public function buyPackage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/member/store');
        }

        $db          = new Database();
        $userId      = (int)$_SESSION['user_id'];
        $maGoi       = (int)($_POST['ma_goi'] ?? 0);
        $phuongThuc  = $_POST['phuong_thuc'] ?? 'tien_mat'; // tien_mat | chuyen_khoan

        if (!$maGoi) {
            setFlash('danger', 'Gói tập không hợp lệ.');
            redirect('/member/store');
        }

        $package = $db->selectOne("SELECT * FROM GOI_TAP WHERE ma_goi = ?", [$maGoi]);
        if (!$package) {
            setFlash('danger', 'Gói tập không tồn tại.');
            redirect('/member/store');
        }

        $member = $db->selectOne(
            "SELECT hv.ma_hoi_vien, hv.ngay_het_han_goi, hv.so_buoi_pt_con_lai
             FROM HOI_VIEN hv WHERE hv.ma_nguoi_dung = ?", [$userId]
        );
        if (!$member) {
            setFlash('danger', 'Không tìm thấy thông tin hội viên.');
            redirect('/member/store');
        }

        // Tính ngày hết hạn cộng dồn
        $today   = date('Y-m-d');
        $curExp  = $member['ngay_het_han_goi'];
        $base    = ($curExp && $curExp >= $today) ? $curExp : $today;
        $thang   = (int)$package['thoi_han_thang'];
        $newExp  = $thang > 0
            ? date('Y-m-d', strtotime("$base + $thang months"))
            : $base;

        try {
            $db->beginTransaction();

            $giaThanhToan = $package['gia_tien'];
            // Check promo code
            if (!empty($_POST['ma_giam_gia'])) {
                $code = strtoupper(trim($_POST['ma_giam_gia']));
                $promo = $db->selectOne("SELECT ma_giam_gia, phan_tram_giam, so_tien_giam FROM MA_GIAM_GIA WHERE code = ? AND so_luong_con > 0 AND ngay_het_han > NOW() AND loai_ap_dung IN ('all', 'package')", [$code]);
                if ($promo) {
                    if ($promo['phan_tram_giam'] > 0) {
                        $giaThanhToan = max(0, $package['gia_tien'] * (1 - $promo['phan_tram_giam'] / 100));
                    } else {
                        $giaThanhToan = max(0, $package['gia_tien'] - $promo['so_tien_giam']);
                    }
                    $db->execute("UPDATE MA_GIAM_GIA SET so_luong_con = so_luong_con - 1 WHERE ma_giam_gia = ?", [$promo['ma_giam_gia']]);
                }
            }

            $maDangKy = $db->insert(
                "INSERT INTO DANG_KY_GOI (ma_hoi_vien, ma_goi, ngay_kich_hoat, ngay_ket_thuc, trang_thai, gia_thanh_toan)
                 VALUES (?,?,?,?,'pending',?)",
                [$member['ma_hoi_vien'], $maGoi, $today, $newExp, $giaThanhToan]
            );
            if (!$maDangKy) throw new Exception('Lỗi tạo đăng ký gói tập');

            $txnRef = 'MBR_' . $maDangKy . '_' . time();
            $db->execute(
                "INSERT INTO THANH_TOAN (ma_dang_ky, vnp_TxnRef, so_tien, phuong_thuc, trang_thai)
                 VALUES (?,?,?,?,'pending')",
                [$maDangKy, $txnRef, $giaThanhToan, $phuongThuc]
            );

            $db->commit();

            // Cộng điểm đăng ký gói: 10 điểm
            $hvRow = $db->selectOne("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung=?", [$userId]);
            if ($hvRow) addPoints(new Database(), $hvRow['ma_hoi_vien'], 10, 'Đăng ký gói tập: '.$package['ten_goi']);

            // Nếu chọn chuyển khoản -> redirect VNPay
            if ($phuongThuc === 'chuyen_khoan') {
                // Lưu vào session để vnpay-return xử lý kích hoạt
                $_SESSION['pending_payment'] = [
                    'ma_dang_ky'  => $maDangKy,
                    'ma_goi'      => $maGoi,
                    'ma_hoi_vien' => $member['ma_hoi_vien'],
                    'so_tien'     => $package['gia_tien'],
                    'ten_goi'     => $package['ten_goi'],
                    'new_expire'  => $newExp,
                    'so_buoi_pt'  => (int)$package['so_buoi_pt'],
                    'txn_ref'     => $txnRef,
                ];

                // Tạo URL VNPay
                require_once __DIR__ . '/../includes/VNPayGateway.php';
                try {
                    $vnpay = new VNPayGateway();
                    $payUrl = $vnpay->createPaymentUrl(
                        $txnRef,
                        $giaThanhToan,
                        'Thanh toan goi tap: ' . $package['ten_goi']
                    );
                    header('Location: ' . $payUrl); exit;
                } catch (Exception $vnpe) {
                    // VNPay chưa config → giả lập
                    header('Location: ' . SITE_URL . '/member/payment-simulate?ma_dang_ky=' . $maDangKy); exit;
                }
            }

            // Tiền mặt -> thông báo đến quầy
            setFlash('success',
                '✅ Đã đăng ký gói <strong>' . htmlspecialchars($package['ten_goi']) . '</strong>! ' .
                '💵 Vui lòng đến <strong>quầy lễ tân</strong> thanh toán tiền mặt. Gói sẽ được kích hoạt ngay sau đó.'
            );

        } catch (Exception $e) {
            $db->rollBack();
            error_log('buyPackage error: ' . $e->getMessage());
            setFlash('danger', 'Lỗi đăng ký: ' . $e->getMessage());
        }

        redirect('/member/dashboard');
    }
    public function ptBooking() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];
        
        // Lay thong tin member balance
        $stmtM = $db->prepare("SELECT ma_hoi_vien, so_buoi_pt_con_lai FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmtM->execute([$userId]);
        $member = $stmtM->fetch();

        // Danh sach HLV
        $stmtT = $db->query("SELECT h.ma_hlv, u.ten_dang_nhap, h.chuyen_mon FROM HUAN_LUYEN_VIEN h JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung");
        $trainers = $stmtT->fetchAll();

        // Danh sach lich dat cua toi
        $stmtB = $db->prepare("
            SELECT l.*, u.ten_dang_nhap 
            FROM LICH_DAT_PT l 
            JOIN HUAN_LUYEN_VIEN h ON l.ma_hlv = h.ma_hlv
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE l.ma_hoi_vien = ?
            ORDER BY l.ngay_gio_tap DESC
        ");
        $stmtB->execute([$member['ma_hoi_vien']]);
        $bookings = $stmtB->fetchAll();

        require __DIR__ . '/../views/member/dat-lich.php';
    }

    public function createBooking() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];

            $stmtM = $db->prepare("SELECT ma_hoi_vien, so_buoi_pt_con_lai FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $member = $stmtM->fetch();

            if ($member['so_buoi_pt_con_lai'] < 1) {
                setFlash('danger', 'Bạn đã dùng hết thẻ buổi tập PT!');
                header("Location: " . SITE_URL . "/member/booking");
                exit;
            }

            $ngayTap = $_POST['ngay_tap'] ?? date('Y-m-d');
            $gioTap = $_POST['gio_tap'] ?? '00:00:00';
            $ngayGio = date('Y-m-d H:i:s', strtotime("$ngayTap $gioTap"));

            // Chặn đặt lịch trong quá khứ
            if (strtotime($ngayGio) < (time() - 300)) { // Cho phép sai số 5 phút
                setFlash('danger', 'Khung giờ này đã HẾT HẠN! Vui lòng chọn thời gian khác.');
                header("Location: " . SITE_URL . "/member/booking");
                exit;
            }

            // --- KIỂM TRA TRÙNG LỊCH HLV ---
            $hlvId = (int)($_POST['ma_hlv'] ?? 0);
            $checkDate = $ngayTap;
            // Đảm bảo định dạng HH:MM:SS để khớp với cột TIME trong Database
            $checkTime = (strlen($gioTap) === 5) ? $gioTap . ':00' : $gioTap;

            // 1. Kiểm tra trong LICH_DAT_PT (Trùng với khách PT khác)
            $stmtConflictPT = $db->prepare("
                SELECT COUNT(*) FROM LICH_DAT_PT 
                WHERE ma_hlv = ? AND ngay_gio_tap = ? AND trang_thai != 'cancelled'
            ");
            $stmtConflictPT->execute([$hlvId, $ngayGio]);
            if ($stmtConflictPT->fetchColumn() > 0) {
                setFlash('danger', 'HLV đã có lịch dạy PT khác vào khung giờ này. Vui lòng chọn giờ khác.');
                header("Location: " . SITE_URL . "/member/booking");
                exit;
            }

            // 2. Kiểm tra trong LICH_HOC_NHOM (Trùng với lớp Group X)
            $stmtConflictGroup = $db->prepare("
                SELECT COUNT(*) FROM LICH_HOC_NHOM 
                WHERE ma_hlv = ? AND ngay_hoc = ? AND gio_bat_dau = ?
            ");
            $stmtConflictGroup->execute([$hlvId, $checkDate, $checkTime]);
            if ($stmtConflictGroup->fetchColumn() > 0) {
                setFlash('danger', 'HLV bận dạy lớp nhóm (Group X) vào khung giờ này. Vui lòng chọn giờ khác.');
                header("Location: " . SITE_URL . "/member/booking");
                exit;
            }
            // --- KẾT THÚC KIỂM TRA ---

            // Check trùng lịch của chính hội viên (chặn khoảng thời gian 60 phút)
            $stmtOverlap = $db->prepare("SELECT COUNT(*) FROM LICH_DAT_PT WHERE ma_hoi_vien = ? AND ABS(TIMESTAMPDIFF(MINUTE, ngay_gio_tap, ?)) < 60 AND trang_thai != 'cancelled'");
            $stmtOverlap->execute([$member['ma_hoi_vien'], $ngayGio]);
            if ($stmtOverlap->fetchColumn() > 0) {
                setFlash('danger', 'Bạn đã có một lịch tập trong khoảng thời gian này! Vui lòng chọn khung giờ cách ít nhất 1 tiếng.');
                header("Location: " . SITE_URL . "/member/booking");
                exit;
            }

            // Check xem HLV đã có người khác đặt CHỐT vào khung giờ này chưa (chặn khoảng thời gian 60 phút)
            $stmtTrainerOverlap = $db->prepare("SELECT COUNT(*) FROM LICH_DAT_PT WHERE ma_hlv = ? AND ABS(TIMESTAMPDIFF(MINUTE, ngay_gio_tap, ?)) < 60 AND trang_thai IN ('confirmed', 'completed', 'cancel_rejected')");
            $stmtTrainerOverlap->execute([$_POST['ma_hlv'], $ngayGio]);
            if ($stmtTrainerOverlap->fetchColumn() > 0) {
                setFlash('danger', 'Huấn luyện viên này đã kín lịch trong khung giờ trên (hoặc bị trùng sát giờ). Vui lòng chọn giờ khác!');
                header("Location: " . SITE_URL . "/member/booking");
                exit;
            }

            try {
                $db->beginTransaction();
                // Tru buoi PT
                $db->prepare("UPDATE HOI_VIEN SET so_buoi_pt_con_lai = so_buoi_pt_con_lai - 1 WHERE ma_hoi_vien = ?")
                   ->execute([$member['ma_hoi_vien']]);
                
                $ghiChu = trim($_POST['ghi_chu'] ?? '');

                // Dat lich (Tự động xác nhận để thuận tiện nếu lịch trống)
                $db->prepare("INSERT INTO LICH_DAT_PT (ma_hoi_vien, ma_hlv, loai_pt, ngay_gio_tap, ghi_chu, trang_thai) VALUES (?, ?, ?, ?, ?, 'confirmed')")
                   ->execute([$member['ma_hoi_vien'], $_POST['ma_hlv'], $_POST['loai_pt'], $ngayGio, $ghiChu]);
                
                $db->commit();
                
                // --- Gửi Email xác nhận đặt lịch ---
                try {
                    $stmtInfo = $db->prepare("
                        SELECT u_mem.ho_ten as member_name, u_mem.email as member_email, u_mem.ten_dang_nhap as member_username,
                               u_hlv.ho_ten as trainer_name, u_hlv.ten_dang_nhap as trainer_username
                        FROM HOI_VIEN hv
                        JOIN NGUOI_DUNG u_mem ON hv.ma_nguoi_dung = u_mem.ma_nguoi_dung
                        CROSS JOIN HUAN_LUYEN_VIEN hlv 
                        JOIN NGUOI_DUNG u_hlv ON hlv.ma_nguoi_dung = u_hlv.ma_nguoi_dung
                        WHERE hv.ma_hoi_vien = ? AND hlv.ma_hlv = ?
                    ");
                    $stmtInfo->execute([$member['ma_hoi_vien'], $_POST['ma_hlv']]);
                    $info = $stmtInfo->fetch();
                    
                    if ($info) {
                        $emailService = new EmailService();
                        $memEmail = !empty($info['member_email']) ? $info['member_email'] : $info['member_username'];
                        $memName = !empty($info['member_name']) ? $info['member_name'] : $info['member_username'];
                        $trainerName = !empty($info['trainer_name']) ? $info['trainer_name'] : $info['trainer_username'];
                        
                        $emailService->sendBookingConfirmation(
                            $memEmail, 
                            $memName, 
                            $trainerName, 
                            date('d/m/Y', strtotime($ngayTap)), 
                            date('H:i', strtotime($gioTap))
                        );
                    }
                } catch (Exception $e) {
                    error_log('Failed to send booking email: ' . $e->getMessage());
                }
                
                setFlash('success', '✅ Đã đặt lịch PT thành công! Lịch đã được tự động xác nhận.');
                header("Location: " . SITE_URL . "/member/booking");
                exit;
            } catch(PDOException $e) {
                $db->rollBack();
                setFlash('danger', 'Lỗi hệ thống đặt lịch: ' . $e->getMessage());
                header("Location: " . SITE_URL . "/member/booking");
                exit;
            }
        }
    }

    public function requestCancelBooking() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $maLich = $_POST['ma_lich'];
            $lyDo = trim($_POST['ly_do'] ?? 'Hội viên tự hủy');
            
            $stmt = $db->prepare("SELECT ma_hoi_vien, ngay_gio_tap, trang_thai FROM LICH_DAT_PT WHERE ma_lich = ?");
            $stmt->execute([$maLich]);
            $lich = $stmt->fetch();
            
            if (!$lich) {
                setFlash('danger', 'Không tìm thấy lịch tập!');
                header("Location: " . SITE_URL . "/member/booking");
                exit;
            }
            
            $now = time();
            $tapTime = strtotime($lich['ngay_gio_tap']);
            $diffHours = ($tapTime - $now) / 3600;
            
            if ($diffHours > 12) {
                try {
                    $db->beginTransaction();
                    $db->prepare("UPDATE LICH_DAT_PT SET trang_thai = 'cancelled', ly_do_huy = ? WHERE ma_lich = ?")
                       ->execute([$lyDo, $maLich]);
                    $db->prepare("UPDATE HOI_VIEN SET so_buoi_pt_con_lai = so_buoi_pt_con_lai + 1 WHERE ma_hoi_vien = ?")
                       ->execute([$lich['ma_hoi_vien']]);
                    $db->commit();
                    setFlash('success', 'Đã hủy lịch thành công và hệ thống đã hoàn lại 1 buổi tập!');
                } catch (Exception $e) {
                    $db->rollBack();
                    setFlash('danger', 'Lỗi hệ thống khi hủy lịch.');
                }
            } else {
                setFlash('danger', 'Chỉ được phép hủy lịch trước 12 tiếng so với giờ tập. Bạn không thể hủy lịch này!');
            }
            
            header("Location: " . SITE_URL . "/member/booking");
            exit;
        }
    }

    public function updateBmi() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf('update_bmi')) {
                setFlash('danger', 'Lỗi bảo mật: Yêu cầu không hợp lệ.');
                header("Location: " . SITE_URL . "/member/dashboard");
                exit;
            }
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];
            $chieu_cao = $_POST['chieu_cao'];
            $can_nang = $_POST['can_nang'];

            $stmt = $db->prepare("UPDATE HOI_VIEN SET chieu_cao = ?, can_nang = ? WHERE ma_nguoi_dung = ?");
            if ($stmt->execute([$chieu_cao, $can_nang, $userId])) {
                setFlash('success', 'Đã cập nhật chỉ số cơ thể thành công!');
                header("Location: " . SITE_URL . "/member/dashboard");
            } else {
                setFlash('danger', 'Có lỗi xảy ra khi cập nhật.');
                header("Location: " . SITE_URL . "/member/dashboard");
            }
        }
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf('update_profile')) {
                setFlash('danger', 'Lỗi bảo mật: Yêu cầu không hợp lệ.');
                header("Location: " . SITE_URL . "/member/dashboard");
                exit;
            }
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];
            
            $ho_ten = trim($_POST['ho_ten'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $sdt = trim($_POST['so_dien_thoai'] ?? '');

            if (empty($ho_ten) || empty($email) || empty($sdt)) {
                setFlash('danger', 'Vui lòng điền đầy đủ Họ tên, Email và Số điện thoại!');
                header("Location: " . SITE_URL . "/member/dashboard");
                exit;
            }

            try {
                $db->beginTransaction();
                
                // Kiểm tra xem tên đăng nhập cũ có phải là email cũ không
                $stmtCurr = $db->prepare("SELECT ten_dang_nhap, email FROM NGUOI_DUNG WHERE ma_nguoi_dung = ?");
                $stmtCurr->execute([$userId]);
                $currUser = $stmtCurr->fetch();

                // Cập nhật bảng NGUOI_DUNG
                $stmt = $db->prepare("UPDATE NGUOI_DUNG SET ho_ten = ?, email = ?, so_dien_thoai = ? WHERE ma_nguoi_dung = ?");
                $stmt->execute([$ho_ten, $email, $sdt, $userId]);
                
                // Nếu Tên đăng nhập đang dùng chính là Email cũ, thì đồng bộ Tên đăng nhập thành Email mới luôn
                if ($currUser && $currUser['ten_dang_nhap'] === $currUser['email']) {
                    // Cần check xem email mới đã bị ai dùng làm ten_dang_nhap chưa
                    $checkDup = $db->prepare("SELECT COUNT(*) FROM NGUOI_DUNG WHERE ten_dang_nhap = ? AND ma_nguoi_dung != ?");
                    $checkDup->execute([$email, $userId]);
                    if ($checkDup->fetchColumn() == 0) {
                        $db->prepare("UPDATE NGUOI_DUNG SET ten_dang_nhap = ? WHERE ma_nguoi_dung = ?")
                           ->execute([$email, $userId]);
                    }
                }

                // Cập nhật session nếu cần
                $_SESSION['ho_ten'] = $ho_ten;

                $db->commit();
                setFlash('success', '✅ Cập nhật thông tin cá nhân thành công!');
            } catch (Exception $e) {
                $db->rollBack();
                setFlash('danger', 'Lỗi: ' . $e->getMessage());
            }
            
            header("Location: " . SITE_URL . "/member/dashboard");
            exit;
        }
    }

    // ============================================================
    // NHẬT KÝ TẬP LUYỆN
    // ============================================================
    public function journal() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmtM->execute([$userId]);
        $memberId = $stmtM->fetchColumn();

        $stmtE = $db->prepare("SELECT * FROM NHAT_KY_TAP WHERE ma_hoi_vien = ? ORDER BY created_at DESC");
        $stmtE->execute([$memberId]);
        $rawEntries = $stmtE->fetchAll();

        $entries = [];
        foreach ($rawEntries as $e) {
            $stmtMedia = $db->prepare("SELECT * FROM MEDIA_NHAT_KY WHERE ma_nk = ?");
            $stmtMedia->execute([$e['ma_nk']]);
            $e['media'] = $stmtMedia->fetchAll();
            $entries[] = $e;
        }

        require __DIR__ . '/../views/member/nhat-ky.php';
    }

    public function journalCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];

            $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $memberId = $stmtM->fetchColumn();

            if (empty($_POST['tieu_de'])) {
                setFlash("danger", "Tiêu đề không được để trống!");
                redirect("/member/journal");
                return;
            }

            $db->beginTransaction();
            $stmt = $db->prepare("INSERT INTO NHAT_KY_TAP (ma_hoi_vien, tieu_de, noi_dung) VALUES (?, ?, ?)");
            $stmt->execute([$memberId, $_POST['tieu_de'], $_POST['noi_dung'] ?? '']);
            $nkId = $db->lastInsertId();

            // Xử lý upload media (tối đa 3 file)
            if (!empty($_FILES['media']['name'][0])) {
                $uploadDir = __DIR__ . '/../public/uploads/journal/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $count = 0;
                foreach ($_FILES['media']['tmp_name'] as $i => $tmp) {
                    if ($count >= 3 || $_FILES['media']['error'][$i] !== 0) continue;
                    $ext = strtolower(pathinfo($_FILES['media']['name'][$i], PATHINFO_EXTENSION));
                    $loai = in_array($ext, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
                    $filename = '/uploads/journal/' . uniqid() . '.' . $ext;
                    $filename = str_replace('\\', '/', $filename);
                    move_uploaded_file($tmp, __DIR__ . '/../public' . $filename);
                    $db->prepare("INSERT INTO MEDIA_NHAT_KY (ma_nk, duong_dan, loai_media) VALUES (?, ?, ?)")
                       ->execute([$nkId, $filename, $loai]);
                    $count++;
                }
            }

            $db->commit();
            setFlash("success", "Đã lưu nhật ký! 📓");
            header("Location: " . SITE_URL . "/member/journal");
        }
    }

    public function journalDelete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];

            $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $memberId = $stmtM->fetchColumn();

            // Chỉ xóa entry của chính mình
            $db->prepare("DELETE FROM NHAT_KY_TAP WHERE ma_nk = ? AND ma_hoi_vien = ?")
               ->execute([$_POST['ma_nk'], $memberId]);
            setFlash("success", "Đã xóa entry.");
            header("Location: " . SITE_URL . "/member/journal");
        }
    }

    // ============================================================
    // THUÊ TỦ ĐỒ
    // ============================================================
    public function locker() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmtM->execute([$userId]);
        $memberId = $stmtM->fetchColumn();

        // Check nếu đang có yêu cầu/tủ hiện tại
        $stmtL = $db->prepare("
            SELECT y.*, t.so_tu FROM YEU_CAU_THUE_TU y
            LEFT JOIN TU_DO t ON y.ma_tu = t.ma_tu
            WHERE y.ma_hoi_vien = ? AND y.trang_thai IN ('pending', 'approved')
            ORDER BY y.created_at DESC LIMIT 1
        ");
        $stmtL->execute([$memberId]);
        $myLocker = $stmtL->fetch();

        $lockerCount = $db->query("SELECT COUNT(*) FROM TU_DO WHERE trang_thai = 'trong'")->fetchColumn();
        $rentedCount = $db->query("SELECT COUNT(*) FROM TU_DO WHERE trang_thai = 'dang_thue'")->fetchColumn();
        $maintenanceCount = $db->query("SELECT COUNT(*) FROM TU_DO WHERE trang_thai = 'bao_tri'")->fetchColumn();

        require __DIR__ . '/../views/member/tu-do.php';
    }

    public function lockerRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];

            // Lấy số tháng và loại tủ từ form
            $soThang = isset($_POST['so_thang']) ? (int)$_POST['so_thang'] : 1;
            $loaiTu = $_POST['loai_tu'] ?? 'M';

            $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $memberId = $stmtM->fetchColumn();

            // Check trùng: Không cho phép gửi thêm yêu cầu nếu đang có yêu cầu chờ hoặc đang sử dụng tủ
            $stmtC = $db->prepare("SELECT COUNT(*) FROM YEU_CAU_THUE_TU WHERE ma_hoi_vien = ? AND trang_thai IN ('pending','approved')");
            $stmtC->execute([$memberId]);
            if ($stmtC->fetchColumn() > 0) {
                setFlash('danger', 'Bạn đã có một yêu cầu thuê tủ đang xử lý hoặc đang sử dụng tủ đồ!');
                header("Location: " . SITE_URL . "/member/locker");
                return;
            }

            // LUỒNG CHUỒN: Mọi yêu cầu đều phải ở trạng thái PENDING để Staff kiểm tra và gán tủ
            try {
                $db->prepare("INSERT INTO YEU_CAU_THUE_TU (ma_hoi_vien, trang_thai, so_thang, loai_tu_mong_muon) VALUES (?, 'pending', ?, ?)")
                   ->execute([$memberId, $soThang, $loaiTu]);
                
                setFlash('success', "Gửi yêu cầu thuê tủ (Size $loaiTu - $soThang tháng) thành công! Vui lòng liên hệ quầy lễ tân để hoàn tất thanh toán và nhận số tủ.");
                header("Location: " . SITE_URL . "/member/locker");
            } catch (Exception $e) {
                setFlash('danger', 'Lỗi hệ thống khi gửi yêu cầu. Vui lòng thử lại sau.');
                header("Location: " . SITE_URL . "/member/locker");
            }
        }
    }

    // ============================================================
    // ĐÁNH GIÁ HLV
    // ============================================================
    public function reviews() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmtM->execute([$userId]);
        $memberId = $stmtM->fetchColumn();

        // Lấy danh sách HLV đã có lịch confirmed/completed
        $stmtT = $db->prepare("
            SELECT DISTINCT h.ma_hlv, u.ten_dang_nhap, h.chuyen_mon, h.anh_dai_dien
            FROM LICH_DAT_PT l
            JOIN HUAN_LUYEN_VIEN h ON l.ma_hlv = h.ma_hlv
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE l.ma_hoi_vien = ? AND l.trang_thai IN ('confirmed', 'completed', 'cancelled', 'cancel_rejected')
        ");
        $stmtT->execute([$memberId]);
        $rawTrainers = $stmtT->fetchAll();

        $trainedBy = [];
        foreach ($rawTrainers as $t) {
            $stmtR = $db->prepare("SELECT * FROM DANH_GIA_HLV WHERE ma_hoi_vien = ? AND ma_hlv = ?");
            $stmtR->execute([$memberId, $t['ma_hlv']]);
            $t['existing_review'] = $stmtR->fetch() ?: null;
            $trainedBy[] = $t;
        }

        // [SMART SUGGESTION] Tìm buổi tập gần nhất chưa đánh giá
        $stmtSuggest = $db->prepare("
            SELECT l.ma_hlv, COALESCE(u.ho_ten, u.ten_dang_nhap) as ten_hlv, l.ngay_gio_tap 
            FROM LICH_DAT_PT l
            JOIN HUAN_LUYEN_VIEN h ON l.ma_hlv = h.ma_hlv
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE l.ma_hoi_vien = ? 
              AND l.ngay_gio_tap < NOW() 
              AND l.trang_thai IN ('confirmed', 'completed', 'attended') 
              AND NOT EXISTS (
                  SELECT 1 FROM DANH_GIA_HLV d 
                  WHERE d.ma_hoi_vien = l.ma_hoi_vien 
                    AND d.ma_hlv = l.ma_hlv 
                    AND d.created_at >= l.ngay_gio_tap 
              )
            ORDER BY l.ngay_gio_tap DESC LIMIT 1
        ");
        $stmtSuggest->execute([$memberId]);
        $suggestedSession = $stmtSuggest->fetch();

        require __DIR__ . '/../views/member/danh-gia.php';
    }

    public function submitReview() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];

            $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $memberId = $stmtM->fetchColumn();

            $maHlv = (int)$_POST['ma_hlv'];
            $soSao = max(1, min(5, (int)$_POST['so_sao']));
            $noiDung = trim($_POST['noi_dung'] ?? '');

            // Check xem đã review chưa (UPSERT)
            $stmtC = $db->prepare("SELECT ma_dg FROM DANH_GIA_HLV WHERE ma_hoi_vien = ? AND ma_hlv = ?");
            $stmtC->execute([$memberId, $maHlv]);
            $existing = $stmtC->fetchColumn();

            if ($existing) {
                // Cập nhật lại (Nếu >= 4 sao thì tự động duyệt luôn cho thuận tiện)
                $newStatus = ($soSao >= 4) ? 'approved' : 'pending';
                $db->prepare("UPDATE DANH_GIA_HLV SET so_sao = ?, noi_dung = ?, trang_thai = ? WHERE ma_dg = ?")
                   ->execute([$soSao, $noiDung, $newStatus, $existing]);
                
                $msg = ($newStatus === 'approved') ? "Đánh giá đã được cập nhật và hiển thị!" : "Đánh giá đã được cập nhật, chờ Staff duyệt.";
            } else {
                // Thêm mới (Nếu >= 4 sao thì tự động duyệt)
                $newStatus = ($soSao >= 4) ? 'approved' : 'pending';
                $db->prepare("INSERT INTO DANH_GIA_HLV (ma_hoi_vien, ma_hlv, so_sao, noi_dung, trang_thai) VALUES (?, ?, ?, ?, ?)")
                   ->execute([$memberId, $maHlv, $soSao, $noiDung, $newStatus]);
                
                $msg = ($newStatus === 'approved') ? "Cảm ơn bạn đã đánh giá tích cực! Đánh giá đã được hiển thị." : "Đánh giá đã được gửi, chờ Staff duyệt.";
            }

            header("Location: " . SITE_URL . "/member/reviews?msg=" . urlencode($msg));
        }
    }

    public function deleteReview() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];

            $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $memberId = $stmtM->fetchColumn();

            $maDg = (int)($_POST['ma_dg'] ?? 0);

            // Kiểm tra và xóa
            $db->prepare("DELETE FROM DANH_GIA_HLV WHERE ma_dg = ? AND ma_hoi_vien = ?")
               ->execute([$maDg, $memberId]);

            header("Location: " . SITE_URL . "/member/reviews?msg=" . urlencode("Đã xóa đánh giá thành công."));
            exit;
        }
    }

    // ============================================================
    // MUA SẢN PHẨM BẰNG ĐIỂM
    // ============================================================
    public function buyWithPoints() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];
            $maSp = (int)$_POST['ma_sp'];

            $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $memberId = $stmtM->fetchColumn();

            $stmtP = $db->prepare("SELECT * FROM SAN_PHAM WHERE ma_sp = ?");
            $stmtP->execute([$maSp]);
            $product = $stmtP->fetch();

            if (!$product || $product['ton_kho'] < 1) {
                header("Location: " . SITE_URL . "/member/store?error=" . urlencode("Sản phẩm đã hết hàng."));
                return;
            }
            if (is_null($product['gia_diem'])) {
                header("Location: " . SITE_URL . "/member/store?error=" . urlencode("Sản phẩm này không hỗ trợ mua bằng điểm."));
                return;
            }

            $stmtD = $db->prepare("SELECT so_diem FROM DIEM_TICH_LUY WHERE ma_hoi_vien = ?");
            $stmtD->execute([$memberId]);
            $currentPoints = $stmtD->fetchColumn() ?: 0;

            if ($currentPoints < $product['gia_diem']) {
                header("Location: " . SITE_URL . "/member/store?error=" . urlencode("Bạn không đủ điểm để mua sản phẩm này. Can " . $product['gia_diem'] . " diem."));
                return;
            }

            try {
                $db->beginTransaction();
                // Trừ tồn kho
                $db->prepare("UPDATE SAN_PHAM SET ton_kho = ton_kho - 1 WHERE ma_sp = ?")->execute([$maSp]);
                // Tạo đơn hàng
                $maGD = "PT" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
                $db->prepare("INSERT INTO DON_HANG_SP (ma_hoi_vien, ma_sp, ma_giao_dich, trang_thai) VALUES (?, ?, ?, 'pending')")
                   ->execute([$memberId, $maSp, $maGD]);
                // Trừ điểm
                $db->prepare("UPDATE DIEM_TICH_LUY SET so_diem = so_diem - ? WHERE ma_hoi_vien = ?")
                   ->execute([$product['gia_diem'], $memberId]);
                // Log điểm
                $db->prepare("INSERT INTO LICH_SU_DIEM (ma_hoi_vien, so_diem_thay_doi, ly_do) VALUES (?, ?, ?)")
                   ->execute([$memberId, -$product['gia_diem'], 'Mua sản phẩm: ' . $product['ten_sp']]);
                $db->commit();
                header("Location: " . SITE_URL . "/member/receipt?ma_gd=" . $maGD);
            } catch (Exception $e) {
                $db->rollBack();
                header("Location: " . SITE_URL . "/member/store?error=" . urlencode("Giao dich loi."));
            }
        }
    }

    // ============================================================
    // ĐỔI MẬT KHẨU
    // ============================================================
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf('change_password')) {
                setFlash('danger', 'Phiên đã hết hạn. Vui lòng thử lại.');
                redirect('/member/change-password'); return;
            }
            require_once __DIR__ . '/../database/config.php';
            $db  = Database::getConnection();
            $uid = (int)$_SESSION['user_id'];
            $s   = $db->prepare("SELECT mat_khau FROM NGUOI_DUNG WHERE ma_nguoi_dung=?");
            $s->execute([$uid]);
            $user = $s->fetch();

            if (!verifyPassword($_POST['old_password'], $user['mat_khau'])) {
                setFlash('danger', '❌ Mật khẩu hiện tại không đúng!');
                redirect('/member/change-password'); return;
            }
            $np = $_POST['new_password'] ?? '';
            if (strlen($np) < 8) {
                setFlash('danger', 'Mật khẩu mới phải có ít nhất 8 ký tự!');
                redirect('/member/change-password'); return;
            }
            if ($np !== ($_POST['confirm_password'] ?? '')) {
                setFlash('danger', '❌ Xác nhận mật khẩu không khớp!');
                redirect('/member/change-password'); return;
            }
            $db->prepare("UPDATE NGUOI_DUNG SET mat_khau=? WHERE ma_nguoi_dung=?")
               ->execute([hashPassword($np), $uid]);
            setFlash('success', '✅ Đổi mật khẩu thành công!');
            redirect('/member/dashboard');
        } else {
            require __DIR__ . '/../views/member/doi-mat-khau.php';
        }
    }

    // ============================================================
    // GIỎ HÀNG PHIÊN
    // ============================================================
    public function cartAdd() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/member/store'); return; }
        require_once __DIR__ . '/../database/config.php';
        $db   = Database::getConnection();
        $maSP = (int)($_POST['ma_san_pham'] ?? 0);
        $qty  = max(1, (int)($_POST['so_luong'] ?? 1));

        // Tự detect tên cột thực tế trong DB
        $cols   = $db->query("SHOW COLUMNS FROM SAN_PHAM")->fetchAll(PDO::FETCH_COLUMN);
        $colId  = in_array('ma_san_pham', $cols) ? 'ma_san_pham' : 'ma_sp';
        $colGia = in_array('gia_ban', $cols)      ? 'gia_ban'     : 'gia_tien';
        $colTon = in_array('so_luong_ton', $cols)  ? 'so_luong_ton': 'ton_kho';
        $colAnh = in_array('anh_sp', $cols)        ? 'anh_sp'      : (in_array('hinh_anh',$cols)?'hinh_anh':"''");
        $colTen = in_array('ten_sp', $cols)        ? 'ten_sp'      : 'ten_san_pham';
        $colMoTa= in_array('mo_ta', $cols)         ? 'mo_ta'       : "''";

        $s = $db->prepare("SELECT *, $colId as _id, $colGia as _gia, $colTon as _ton,
                                    $colAnh as _anh, $colTen as _ten, $colMoTa as _mo_ta
                           FROM SAN_PHAM WHERE $colId = ?");
        if (!$s) { setFlash('danger','Lỗi query sản phẩm.'); redirect('/member/store'); return; }
        $s->execute([$maSP]);
        $prod = $s->fetch();

        if (!$prod || (int)$prod['_ton'] < 1) {
            setFlash('danger','Sản phẩm không tồn tại hoặc đã hết hàng.');
            redirect('/member/store'); return;
        }

        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$maSP])) {
            $_SESSION['cart'][$maSP]['qty'] = min($_SESSION['cart'][$maSP]['qty'] + $qty, (int)$prod['_ton']);
        } else {
            $_SESSION['cart'][$maSP] = [
                'ma_san_pham' => $maSP,
                'ten_sp'      => $prod['_ten'],
                'gia_ban'     => (float)$prod['_gia'],
                'qty'         => $qty,
                'anh_sp'      => $prod['_anh'],
            ];
        }
        setFlash('success', '🛒 Đã thêm <strong>'.htmlspecialchars($prod['_ten']).'</strong> vào giỏ hàng!');
        redirect('/member/cart');
    }

    public function cartView() {
        $cart  = $_SESSION['cart'] ?? [];
        $total = array_sum(array_map(fn($i) => $i['gia_ban']*$i['qty'], $cart));
        require __DIR__ . '/../views/member/gio-hang.php';
    }

    public function cartRemove() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/member/cart'); return; }
        $maSP = (int)($_POST['ma_san_pham'] ?? 0);
        unset($_SESSION['cart'][$maSP]);
        setFlash('success','Đã xóa khỏi giỏ hàng.');
        redirect('/member/cart');
    }

    public function cartCheckout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/member/cart'); return; }
        if (!verifyCsrf('cart_checkout')) { setFlash('danger','Phiên lỗi.'); redirect('/member/cart'); return; }
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) { setFlash('danger','Giỏ hàng trống!'); redirect('/member/store'); return; }
        
        require_once __DIR__ . '/../database/config.php';
        $db  = Database::getConnection();
        $uid = (int)$_SESSION['user_id'];
        
        // Detect tên cột thực tế
        $spCols  = $db->query("SHOW COLUMNS FROM SAN_PHAM")->fetchAll(PDO::FETCH_COLUMN);
        $colId   = in_array('ma_san_pham', $spCols) ? 'ma_san_pham' : 'ma_sp';
        $colGia  = in_array('gia_ban', $spCols)     ? 'gia_ban'     : 'gia_tien';
        $colTon  = in_array('so_luong_ton', $spCols) ? 'so_luong_ton': 'ton_kho';

        // Detect cột DON_HANG_SP
        $dhCols  = [];
        try { $dhCols = $db->query("SHOW COLUMNS FROM DON_HANG_SP")->fetchAll(PDO::FETCH_COLUMN); } catch(Exception $e){}
        $dhColSP  = in_array('ma_sp', $dhCols) ? 'ma_sp' : (in_array('ma_san_pham',$dhCols) ? 'ma_san_pham' : 'ma_sp');
        $dhColUID = in_array('ma_hoi_vien', $dhCols) ? 'ma_hoi_vien' : (in_array('ma_nguoi_dung',$dhCols) ? 'ma_nguoi_dung' : 'ma_hoi_vien');

        // Lấy ma_hoi_vien
        $maHoiVien = null;
        try {
            $hvStmt = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung=?");
            $hvStmt->execute([$uid]);
            $hvRow = $hvStmt->fetch();
            $maHoiVien = $hvRow ? (int)$hvRow['ma_hoi_vien'] : null;
        } catch(Exception $e) {}
        
        $insertUID = ($dhColUID === 'ma_hoi_vien') ? $maHoiVien : $uid;
        if (!$insertUID) { setFlash('danger','Không tìm thấy tài khoản hội viên.'); redirect('/member/cart'); return; }

        $phuongThuc = $_POST['phuong_thuc'] ?? 'tien_mat';
        $maGDTotal  = 'CRT' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
        $totalAmount = 0;

        try {
            $db->beginTransaction();
            foreach ($cart as $item) {
                $maSPItem = (int)($item['ma_san_pham'] ?? $item['_id'] ?? 0);
                $s = $db->prepare("SELECT $colGia as _gia, $colTon as _ton FROM SAN_PHAM WHERE $colId = ?");
                if (!$s) throw new Exception("Lỗi query sản phẩm.");
                $s->execute([$maSPItem]);
                $prod = $s->fetch();
                
                if (!$prod || (int)$prod['_ton'] < $item['qty']) {
                    throw new Exception(htmlspecialchars($item['ten_sp']).' không đủ hàng!');
                }
                
                $tongTien = (float)$prod['_gia'] * $item['qty'];
                $totalAmount += $tongTien;

                // Insert đơn hàng
                $ins = $db->prepare("INSERT INTO DON_HANG_SP ($dhColUID, $dhColSP, so_luong, gia_tien, gia_thanh_toan, trang_thai, ma_giao_dich) VALUES (?,?,?,?,?,?,?)");
                if (!$ins) throw new Exception("Lỗi cấu trúc bảng đơn hàng.");
                
                $ins->execute([$insertUID, $maSPItem, $item['qty'], $prod['_gia'], $tongTien, 'pending', $maGDTotal]);

                // Nếu là TIỀN MẶT -> trừ kho ngay và cộng điểm ngay
                if ($phuongThuc === 'tien_mat') {
                    $upd = $db->prepare("UPDATE SAN_PHAM SET $colTon=$colTon-? WHERE $colId=?");
                    if ($upd) $upd->execute([$item['qty'], $maSPItem]);

                    $diem = max(1, (int)floor($tongTien / 50000));
                    if ($maHoiVien) addPoints($db, $maHoiVien, $diem, 'Mua sản phẩm: '.$item['ten_sp']);
                }
            }
            $db->commit();

            if ($phuongThuc === 'chuyen_khoan') {
                require_once __DIR__ . '/../includes/VNPayGateway.php';
                try {
                    $vnpay = new VNPayGateway();
                    $payUrl = $vnpay->createPaymentUrl(
                        $maGDTotal,
                        $totalAmount,
                        'Thanh toan gio hang: ' . count($cart) . ' san pham'
                    );
                    
                    $_SESSION['pending_payment'] = [
                        'type'        => 'cart',
                        'ma_giao_dich' => $maGDTotal,
                        'ma_hoi_vien' => $maHoiVien,
                        'so_tien'     => $totalAmount,
                        'cart_data'   => $cart
                    ];

                    header('Location: ' . $payUrl); exit;
                } catch (Exception $vnpe) {
                    header('Location: ' . SITE_URL . '/member/payment-simulate?type=cart&ma_gd=' . $maGDTotal); exit;
                }
            }

            $_SESSION['cart'] = [];
            setFlash('success', '✅ Đặt hàng thành công! Vui lòng thanh toán tại quầy khi nhận hàng.');
            redirect('/member/dashboard');
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            setFlash('danger', 'Lỗi: ' . $e->getMessage());
            redirect('/member/cart');
        }
    }
    // Hiển thị danh sách Lớp học Group X (Member View)
    public function groupX() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        
        $userId = $_SESSION['user_id'];
        
        $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmtM->execute([$userId]);
        $memberId = $stmtM->fetchColumn();

        // Lấy danh sách lịch học từ hôm nay trở đi, kèm theo số lượng đã đặt
        $query = "
            SELECT lh.*, l.ten_lop, l.mo_ta, l.hinh_anh, l.loai_lop, COALESCE(u.ho_ten, u.ten_dang_nhap) as ten_hlv,
                   (SELECT COUNT(*) FROM DAT_CHO_LOP_HOC WHERE ma_lich = lh.ma_lich) as so_nguoi_da_dat,
                   (SELECT COUNT(*) FROM DAT_CHO_LOP_HOC WHERE ma_lich = lh.ma_lich AND ma_hoi_vien = ?) as toi_da_dat
            FROM LICH_HOC_NHOM lh
            JOIN LOP_HOC_NHOM l ON lh.ma_lop = l.ma_lop
            JOIN HUAN_LUYEN_VIEN h ON lh.ma_hlv = h.ma_hlv
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE lh.ngay_hoc >= CURDATE()
            ORDER BY lh.ngay_hoc ASC, lh.gio_bat_dau ASC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$memberId]);
        $schedules = $stmt->fetchAll();
        
        require __DIR__ . '/../views/member/lop-hoc.php';
    }

    // API Xử lý Đặt chỗ (AJAX)
    public function bookClass() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];
            
            $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $memberId = $stmtM->fetchColumn();
            
            $ma_lich = $_POST['ma_lich'] ?? '';

            if ($ma_lich && $memberId) {
                try {
                    // 1. Kiểm tra xem lớp đã đầy chưa
                    $stmt = $db->prepare("SELECT so_luong_toi_da, (SELECT COUNT(*) FROM DAT_CHO_LOP_HOC WHERE ma_lich = ?) as dang_co FROM LICH_HOC_NHOM WHERE ma_lich = ?");
                    $stmt->execute([$ma_lich, $ma_lich]);
                    $classInfo = $stmt->fetch();

                    if ($classInfo['dang_co'] >= $classInfo['so_luong_toi_da']) {
                        echo json_encode(['success' => false, 'message' => 'Rất tiếc, lớp học đã kín chỗ!']);
                        return;
                    }

                    // 2. Tiến hành đặt chỗ
                    $stmt = $db->prepare("INSERT INTO DAT_CHO_LOP_HOC (ma_lich, ma_hoi_vien) VALUES (?, ?)");
                    $stmt->execute([$ma_lich, $memberId]);
                    
                    echo json_encode(['success' => true, 'message' => 'Đặt chỗ thành công! Hẹn gặp bạn tại phòng tập.']);
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        echo json_encode(['success' => false, 'message' => 'Bạn đã đặt chỗ cho lớp học này rồi.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
                    }
                }
            }
        }
    }
    // Trang Lịch sử lớp học của tôi
    public function myClasses() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $userId = $_SESSION['user_id'];

        $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmtM->execute([$userId]);
        $memberId = $stmtM->fetchColumn();

        // Lấy danh sách lớp đã đặt
        $query = "
            SELECT dc.*, lh.ngay_hoc, lh.gio_bat_dau, lh.gio_ket_thuc, l.ten_lop, l.loai_lop, l.hinh_anh,
                   COALESCE(u.ho_ten, u.ten_dang_nhap) as ten_hlv
            FROM DAT_CHO_LOP_HOC dc
            JOIN LICH_HOC_NHOM lh ON dc.ma_lich = lh.ma_lich
            JOIN LOP_HOC_NHOM l ON lh.ma_lop = l.ma_lop
            JOIN HUAN_LUYEN_VIEN h ON lh.ma_hlv = h.ma_hlv
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE dc.ma_hoi_vien = ?
            ORDER BY lh.ngay_hoc DESC, lh.gio_bat_dau DESC
        ";
        $stmt = $db->prepare($query);
        $stmt->execute([$memberId]);
        $allBookings = $stmt->fetchAll();

        // Phân loại Sắp diễn ra vs Lịch sử
        $upcoming = [];
        $history = [];
        $now = date('Y-m-d H:i:s');

        foreach ($allBookings as $b) {
            $sessionTime = $b['ngay_hoc'] . ' ' . $b['gio_bat_dau'];
            if ($sessionTime > $now && $b['trang_thai'] === 'thanh_cong') {
                $upcoming[] = $b;
            } else {
                $history[] = $b;
            }
        }

        require __DIR__ . '/../views/member/lop-hoc-cua-toi.php';
    }

    // API Hủy đặt chỗ (AJAX)
    public function cancelClass() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $userId = $_SESSION['user_id'];
            
            $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
            $stmtM->execute([$userId]);
            $memberId = $stmtM->fetchColumn();
            
            $ma_dat_cho = $_POST['ma_dat_cho'] ?? '';

            if ($ma_dat_cho && $memberId) {
                try {
                    // Kiểm tra xem có đúng là của mình không và lớp chưa diễn ra
                    $stmt = $db->prepare("
                        SELECT dc.ma_dat_cho, lh.ngay_hoc, lh.gio_bat_dau 
                        FROM DAT_CHO_LOP_HOC dc
                        JOIN LICH_HOC_NHOM lh ON dc.ma_lich = lh.ma_lich
                        WHERE dc.ma_dat_cho = ? AND dc.ma_hoi_vien = ?
                    ");
                    $stmt->execute([$ma_dat_cho, $memberId]);
                    $booking = $stmt->fetch();

                    if (!$booking) {
                        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin đặt chỗ!']);
                        return;
                    }

                    $sessionTime = $booking['ngay_hoc'] . ' ' . $booking['gio_bat_dau'];
                    if ($sessionTime < date('Y-m-d H:i:s')) {
                        echo json_encode(['success' => false, 'message' => 'Lớp học đã diễn ra, không thể hủy!']);
                        return;
                    }

                    // Tiến hành hủy
                    $stmt = $db->prepare("UPDATE DAT_CHO_LOP_HOC SET trang_thai = 'da_huy' WHERE ma_dat_cho = ?");
                    $stmt->execute([$ma_dat_cho]);
                    
                    echo json_encode(['success' => true, 'message' => 'Đã hủy đặt chỗ thành công!']);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
                }
            }
        }
    }
}