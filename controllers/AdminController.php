<?php
require_once __DIR__ . '/../utils/Middleware.php';
require_once __DIR__ . '/../includes/EmailService.php';

class AdminController {
    public function __construct() {
        Middleware::checkAdmin();
    }

    public function dashboard() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();

        $filter = $_GET['filter'] ?? 'month';
        $dateCondition = "";
        $groupBy = "DATE(created_at)";

        switch($filter) {
            case 'today':
                $dateCondition = "DATE(created_at) = CURDATE()";
                $groupBy = "HOUR(created_at)";
                break;
            case 'year':
                $dateCondition = "YEAR(created_at) = YEAR(CURDATE())";
                $groupBy = "MONTH(created_at)";
                break;
            case 'all':
                $dateCondition = "1=1";
                $groupBy = "CONCAT(MONTH(created_at), '/', YEAR(created_at))"; // Phan biet nam
                break;
            case 'month':
            default:
                $dateCondition = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
                $groupBy = "DATE(created_at)";
                break;
        }

        // ============================================
        // 1. DOANH THU & BIEU DO DOANH THU
        // ============================================
        $sqlRevenue = "
            SELECT label, SUM(val) as val, MIN(full_date) as order_col FROM (
                SELECT $groupBy as label, so_tien as val, created_at as full_date
                FROM THANH_TOAN WHERE trang_thai='success' AND $dateCondition
                UNION ALL
                SELECT " . str_replace('created_at', 'ngay_mua', $groupBy) . " as label, s.gia_tien as val, d.ngay_mua as full_date
                FROM DON_HANG_SP d JOIN SAN_PHAM s ON d.ma_sp=s.ma_sp 
                WHERE d.trang_thai='completed' AND " . str_replace('created_at', 'ngay_mua', $dateCondition) . "
            ) as T
            GROUP BY label ORDER BY order_col
        ";
        $revData = $db->query($sqlRevenue)->fetchAll();
        
        $totalRevenue = 0;
        $chartRevLabels = [];
        $chartRevVals = [];
        foreach($revData as $row) {
            $totalRevenue += $row['val'];
            $chartRevLabels[] = "".$row['label'];
            $chartRevVals[] = $row['val'];
        }

        // ============================================
        // 2. HOI VIEN MOI & BIEU DO XU HUONG
        // ============================================
        $totalMembers = $db->query("SELECT COUNT(*) FROM HOI_VIEN")->fetchColumn();
        
        $sqlMembers = "
            SELECT $groupBy as label, COUNT(*) as val, MIN(created_at) as order_col 
            FROM HOI_VIEN WHERE $dateCondition
            GROUP BY label ORDER BY order_col
        ";
        $memData = $db->query($sqlMembers)->fetchAll();
        $chartMemLabels = [];
        $chartMemVals = [];
        $newMembers = 0;
        foreach($memData as $row) {
            $newMembers += $row['val'];
            $chartMemLabels[] = "".$row['label'];
            $chartMemVals[] = $row['val'];
        }

        // ============================================
        // 3. DOANH SO GOI TAP (DOUGHNUT / BAR)
        // ============================================
        $sqlPackages = "
            SELECT g.ten_goi as label, SUM(t.so_tien) as doanh_thu, COUNT(*) as so_luong
            FROM DANG_KY_GOI d
            JOIN GOI_TAP g ON d.ma_goi = g.ma_goi
            JOIN THANH_TOAN t ON d.ma_dang_ky = t.ma_dang_ky
            WHERE t.trang_thai = 'success' AND " . str_replace('created_at', 't.created_at', $dateCondition) . "
            GROUP BY g.ten_goi
            ORDER BY doanh_thu DESC
        ";
        $pkgData = $db->query($sqlPackages)->fetchAll();
        $chartPkgLabels = [];
        $chartPkgRev = [];
        $chartPkgCount = [];
        foreach($pkgData as $row) {
            $chartPkgLabels[] = $row['label'];
            $chartPkgRev[] = $row['doanh_thu'];
            $chartPkgCount[] = $row['so_luong'];
        }

        // ============================================
        // 4. DOANH THU THEO NGUỒN (PIE/DOUGHNUT)
        // ============================================
        $membershipRev = $db->query("SELECT SUM(so_tien) FROM THANH_TOAN WHERE trang_thai='success' AND $dateCondition")->fetchColumn() ?: 0;
        $productRev = $db->query("
            SELECT SUM(s.gia_tien) 
            FROM DON_HANG_SP d JOIN SAN_PHAM s ON d.ma_sp=s.ma_sp 
            WHERE d.trang_thai='completed' AND " . str_replace('created_at', 'ngay_mua', $dateCondition)
        )->fetchColumn() ?: 0;

        // ============================================
        // 5. CHI PHÍ LƯƠNG (PAYROLL EXPENSES)
        // ============================================
        $payrollExpense = $db->prepare("SELECT SUM(tong_luong) FROM BANG_LUONG WHERE thang_nam = ?");
        $payrollExpense->execute([date('m/Y')]);
        $totalPayroll = $payrollExpense->fetchColumn() ?: 0;

        // ============================================
        // 6. TĂNG TRƯỞNG (MONTH OVER MONTH)
        // ============================================
        $lastMonthRev = $db->query("SELECT SUM(so_tien) FROM THANH_TOAN WHERE trang_thai='success' AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))")->fetchColumn() ?: 0;
        $revGrowth = ($lastMonthRev > 0) ? round((($membershipRev - $lastMonthRev) / $lastMonthRev) * 100, 1) : 100;

        // Peak hours
        $sqlPeak = "
            SELECT HOUR(thoi_gian_vao) as h, COUNT(*) as c 
            FROM LICH_SU_RA_VAO 
            WHERE " . str_replace('created_at', 'thoi_gian_vao', $dateCondition) . "
            GROUP BY HOUR(thoi_gian_vao)
            ORDER BY c DESC LIMIT 1
        ";
        $peakHourRow = $db->query($sqlPeak)->fetch();
        $peakHour = $peakHourRow ? $peakHourRow['h'] . ":00 - " . ($peakHourRow['h'] + 1) . ":00" : "N/A";

        require __DIR__ . '/../views/admin/bang-dieu-khien.php';
    }

    public function packages() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM GOI_TAP ORDER BY ma_goi ASC");
        $packages = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/goi-tap.php';
    }

    public function members() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        
        $search = $_GET['search'] ?? '';
        $filter = $_GET['filter'] ?? 'all';
        $params = [];
        
        $sql = "
            SELECT h.ma_hoi_vien, u.ma_nguoi_dung, u.trang_thai as trang_thai_tk, h.ma_qr, 
                   u.ten_dang_nhap, u.email, COALESCE(u.ho_ten, u.ten_dang_nhap) as ho_ten, 
                   u.so_dien_thoai, h.chieu_cao, h.can_nang, h.ngay_het_han_goi,
                   CASE 
                       WHEN h.ngay_het_han_goi >= CURDATE() THEN 'active'
                       WHEN h.ngay_het_han_goi IS NULL THEN 'no_package'
                       ELSE 'expired'
                   END as trang_thai_goi
            FROM HOI_VIEN h 
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE 1=1
        ";

        if (!empty($search)) {
            $sql .= " AND (u.ho_ten LIKE ? OR u.ten_dang_nhap LIKE ? OR u.email LIKE ? OR u.so_dien_thoai LIKE ? OR h.ma_qr LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($filter === 'active') {
            $sql .= " AND h.ngay_het_han_goi >= CURDATE()";
        } elseif ($filter === 'expired') {
            $sql .= " AND h.ngay_het_han_goi < CURDATE()";
        } elseif ($filter === 'no_package') {
            $sql .= " AND h.ngay_het_han_goi IS NULL";
        }

        $sql .= " ORDER BY h.ma_hoi_vien DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $members = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/hoi-vien.php';
    }

    public function history() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $stmt = $db->query("SELECT l.thoi_gian_vao, u.ten_dang_nhap, h.ma_qr 
                            FROM LICH_SU_RA_VAO l 
                            JOIN HOI_VIEN h ON l.ma_hoi_vien = h.ma_hoi_vien 
                            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung 
                            ORDER BY l.thoi_gian_vao DESC LIMIT 100");
        $historyList = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/lich-su.php';
    }

    public function qrScanner() {
        require __DIR__ . '/../views/admin/quet-qr.php';
    }

    public function qrCheckin() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid Request']);
            return;
        }

        $qrCodeData = json_decode(file_get_contents('php://input'), true)['qr'] ?? '';
        
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();

        // Check if QR code belongs to a member
        $stmt = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_qr = :qr");
        $stmt->execute(['qr' => $qrCodeData]);
        $member = $stmt->fetch();

        // Fallback tìm theo SĐT nếu không thấy QR
        if (!$member) {
            $stmt = $db->prepare("
                SELECT hv.ma_hoi_vien 
                FROM HOI_VIEN hv 
                JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung 
                WHERE nd.so_dien_thoai = :phone
            ");
            $stmt->execute(['phone' => $qrCodeData]);
            $member = $stmt->fetch();
        }

        if ($member) {
            $memberId = $member['ma_hoi_vien'];
            // Kiem tra hom nay da diem danh chua
            $checkToday = $db->prepare("SELECT COUNT(*) FROM LICH_SU_RA_VAO WHERE ma_hoi_vien = ? AND DATE(thoi_gian_vao) = CURDATE()");
            $checkToday->execute([$memberId]);
            $alreadyToday = $checkToday->fetchColumn() > 0;

            $insert = $db->prepare("INSERT INTO LICH_SU_RA_VAO (ma_hoi_vien, ghi_chu) VALUES (:id, 'QR Scan')");
            $insert->execute(['id' => $memberId]);

            // Cong diem neu lan dau check-in trong ngay
            if (!$alreadyToday) {
                $db->prepare("INSERT INTO DIEM_TICH_LUY (ma_hoi_vien, so_diem) VALUES (?, 1) ON DUPLICATE KEY UPDATE so_diem = so_diem + 1")
                   ->execute([$memberId]);
                $db->prepare("INSERT INTO LICH_SU_DIEM (ma_hoi_vien, so_diem_thay_doi, ly_do) VALUES (?, 1, 'Diem danh hang ngay')")
                   ->execute([$memberId]);
            }

            echo json_encode(['success' => true, 'message' => 'Diem danh thanh cong!' . (!$alreadyToday ? ' +1 diem' : '')]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ma QR khong ton tai!']);
        }
    }
    public function addPackage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO GOI_TAP (ten_goi, loai_goi, thoi_han_thang, gia_tien, so_buoi_pt) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['ten_goi'], $_POST['loai_goi'], $_POST['thoi_han'], $_POST['gia_tien'], $_POST['so_pt']]);
            header("Location: " . SITE_URL . "/admin/packages?msg=" . urlencode("Đã thêm gói tập thành công!"));
        }
    }

    public function updatePackage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE GOI_TAP SET ten_goi=?, loai_goi=?, thoi_han_thang=?, gia_tien=?, so_buoi_pt=? WHERE ma_goi=?");
            $stmt->execute([$_POST['ten_goi'], $_POST['loai_goi'], $_POST['thoi_han'], $_POST['gia_tien'], $_POST['so_pt'], $_POST['ma_goi']]);
            header("Location: " . SITE_URL . "/admin/packages?msg=" . urlencode("Cập nhật thành công."));
        }
    }

    public function products() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM SAN_PHAM ORDER BY ma_sp DESC");
        $products = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/san-pham.php';
    }

    public function addProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $hinhAnh = null;
            
            if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
                $uploadDir = __DIR__ . '/../public/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $ext = pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
                // Validate extension
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array(strtolower($ext), $allowed)) {
                    setFlash("danger", "Định dạng ảnh không hợp lệ (chỉ cho phép jpg, png, gif, webp).");
                    header("Location: " . SITE_URL . "/admin/products");
                    return;
                }

                $fileName = uniqid('prod_') . '.' . $ext;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $targetPath)) {
                    $hinhAnh = '/uploads/products/' . $fileName;
                } else {
                    setFlash("danger", "Không thể lưu hình ảnh vào thư mục upload.");
                }
            }
            
            try {
                $stmt = $db->prepare("INSERT INTO SAN_PHAM (ten_sp, gia_tien, mo_ta, ton_kho, hinh_anh) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['ten_sp'], $_POST['gia_tien'], $_POST['mo_ta'], $_POST['ton_kho'], $hinhAnh]);
                setFlash("success", "Đã thêm sản phẩm thành công!");
            } catch (Exception $e) {
                setFlash("danger", "Lỗi CSDL: " . $e->getMessage());
            }
            header("Location: " . SITE_URL . "/admin/products");
        }
    }

    public function updateProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            
            $stmtP = $db->prepare("SELECT hinh_anh FROM SAN_PHAM WHERE ma_sp = ?");
            $stmtP->execute([$_POST['ma_sp']]);
            $hinhAnh = $stmtP->fetchColumn();

            if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
                $uploadDir = __DIR__ . '/../public/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $ext = pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array(strtolower($ext), $allowed)) {
                    $fileName = uniqid('prod_') . '.' . $ext;
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $targetPath)) {
                        // Xóa ảnh cũ nếu có
                        if ($hinhAnh && file_exists(__DIR__ . '/../public' . $hinhAnh)) {
                            @unlink(__DIR__ . '/../public' . $hinhAnh);
                        }
                        $hinhAnh = '/uploads/products/' . $fileName;
                    }
                }
            }

            try {
                $stmt = $db->prepare("UPDATE SAN_PHAM SET ten_sp=?, gia_tien=?, mo_ta=?, ton_kho=?, hinh_anh=? WHERE ma_sp=?");
                $stmt->execute([$_POST['ten_sp'], $_POST['gia_tien'], $_POST['mo_ta'], $_POST['ton_kho'], $hinhAnh, $_POST['ma_sp']]);
                setFlash("success", "Cập nhật sản phẩm thành công.");
            } catch (Exception $e) {
                setFlash("danger", "Lỗi khi cập nhật sản phẩm.");
            }
            header("Location: " . SITE_URL . "/admin/products");
        }
    }

    public function deleteProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            try {
                $stmt = $db->prepare("DELETE FROM SAN_PHAM WHERE ma_sp=?");
                $stmt->execute([$_POST['ma_sp']]);
                header("Location: " . SITE_URL . "/admin/products?msg=" . urlencode("Đã xóa san pham."));
            } catch (PDOException $e) {
                header("Location: " . SITE_URL . "/admin/products?error=" . urlencode("Không thể xóa. Có đơn hàng đang liên kết!"));
            }
        }
    }

    public function purchases() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        
        // 1. Lay hoa don goi tap
        $stmt1 = $db->query("
            SELECT d.ma_dang_ky as id, u.ten_dang_nhap as nguoi_mua, g.ten_goi as mon_hang, 
                   t.so_tien as gia_tien, d.created_at as ngay_mua, 'gói tập' as phan_loai, t.trang_thai as status
            FROM DANG_KY_GOI d
            JOIN HOI_VIEN h ON d.ma_hoi_vien = h.ma_hoi_vien
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            JOIN GOI_TAP g ON d.ma_goi = g.ma_goi
            JOIN THANH_TOAN t ON d.ma_dang_ky = t.ma_dang_ky
            ORDER BY d.created_at DESC LIMIT 50
        ");
        $packagePurchases = $stmt1->fetchAll();

        // 2. Lay hoa don San pham thuc pham
        $stmt2 = $db->query("
            SELECT d.ma_dh as id, u.ten_dang_nhap as nguoi_mua, s.ten_sp as mon_hang, 
                   s.gia_tien, d.ngay_mua, 'sản phẩm' as phan_loai, d.trang_thai as status, d.ma_giao_dich
            FROM DON_HANG_SP d
            JOIN HOI_VIEN h ON d.ma_hoi_vien = h.ma_hoi_vien
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            JOIN SAN_PHAM s ON d.ma_sp = s.ma_sp
            ORDER BY d.ngay_mua DESC LIMIT 50
        ");
        $productPurchases = $stmt2->fetchAll();

        // Merge array
        $allPurchases = array_merge($packagePurchases, $productPurchases);
        usort($allPurchases, function($a, $b) {
            return strtotime($b['ngay_mua']) - strtotime($a['ngay_mua']);
        });

        require __DIR__ . '/../views/admin/hoa-don.php';
    }

    public function updateOrderStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $db->prepare("UPDATE DON_HANG_SP SET trang_thai = ? WHERE ma_dh = ?")
               ->execute([$_POST['status'], $_POST['ma_dh']]);
            header("Location: " . SITE_URL . "/admin/packages?msg=" . urlencode("Đã nhận hàng cứng."));
        }
    }

    public function deletePackage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            
            // Check dependency
            $check = $db->prepare("SELECT COUNT(*) as c FROM DANG_KY_GOI WHERE ma_goi=?");
            $check->execute([$_POST['ma_goi']]);
            $count = $check->fetch()['c'];
            
            if ($count > 0) {
                header("Location: " . SITE_URL . "/admin/packages?error=" . urlencode("Không thể xóa! Gói tập này đang có hội viên sử dụng."));
            } else {
                $stmt = $db->prepare("DELETE FROM GOI_TAP WHERE ma_goi=?");
                $stmt->execute([$_POST['ma_goi']]);
                header("Location: " . SITE_URL . "/admin/packages?msg=" . urlencode("Đã xóa goi tap."));
            }
        }
    }

    public function banMember() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $isBan = ($_POST['action'] === 'ban');
            $status = $isBan ? 'banned' : 'active';
            $biBan  = $isBan ? 1 : 0;

            $db->prepare("UPDATE NGUOI_DUNG SET trang_thai=? WHERE ma_nguoi_dung=?")
               ->execute([$status, $_POST['ma_user']]);
            // Đồng bộ cờ bi_ban trong HOI_VIEN
            $db->prepare("UPDATE HOI_VIEN SET bi_ban=? WHERE ma_nguoi_dung=?")
               ->execute([$biBan, $_POST['ma_user']]);

            setFlash("success", $isBan ? "Đã khóa tài khoản hội viên." : "Đã mở khóa tài khoản hội viên.");
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? (SITE_URL . "/admin/members");
            header("Location: " . $redirectUrl);
        }
    }

    public function deleteMember() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM NGUOI_DUNG WHERE ma_nguoi_dung=?");
            $stmt->execute([$_POST['ma_user']]);
                setFlash("success", "Đã xóa vĩnh viễn tài khoản.");
                $redirectUrl = $_SERVER['HTTP_REFERER'] ?? (SITE_URL . "/admin/members");
                header("Location: " . $redirectUrl);
        }
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            require_once __DIR__ . '/../utils/SecurityHelper.php';
            $db = Database::getConnection();
            $defaultPassword = SecurityHelper::hashPassword('123456');
            $stmt = $db->prepare("UPDATE NGUOI_DUNG SET mat_khau=? WHERE ma_nguoi_dung=?");
            $stmt->execute([$defaultPassword, $_POST['ma_user']]);
            setFlash("success", "Đã cấp lại mật khẩu về mặc định (123456) thành công.");
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? (SITE_URL . "/admin/members");
            header("Location: " . $redirectUrl);
        }
    }

    public function memberDetail() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        
        $maHoiVien = $_GET['id'] ?? 0;
        
        // 1. Lấy thông tin cơ bản
        $stmt = $db->prepare("
            SELECT h.*, u.ten_dang_nhap, u.ho_ten, u.email, u.so_dien_thoai, u.trang_thai, u.created_at as ngay_tao
            FROM HOI_VIEN h
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE h.ma_hoi_vien = ?
        ");
        $stmt->execute([$maHoiVien]);
        $memberInfo = $stmt->fetch();
        
        if (!$memberInfo) {
            setFlash("danger", "Hội viên không tồn tại.");
            header("Location: " . SITE_URL . "/admin/members");
            exit;
        }

        // 2. Lịch sử chỉ số cơ thể - lấy từ HOI_VIEN trực tiếp (chỉ có 1 bản ghi hiện tại)
        // Tạo mảng giả để tương thích với view
        $bmiHistory = [];
        if (!empty($memberInfo['can_nang']) && !empty($memberInfo['chieu_cao'])) {
            $bmiHistory[] = [
                'can_nang'  => $memberInfo['can_nang'],
                'chieu_cao' => $memberInfo['chieu_cao'],
                'ngay_do'   => $memberInfo['ngay_tao'] ?? date('Y-m-d H:i:s'),
                'ma_nguoi_dung' => $memberInfo['ma_nguoi_dung'],
            ];
        }

        // 3. Lấy thông tin gói tập hiện tại
        $stmtPkg = $db->prepare("
            SELECT d.*, g.ten_goi, t.so_tien, t.trang_thai as trang_thai_tt
            FROM DANG_KY_GOI d
            JOIN GOI_TAP g ON d.ma_goi = g.ma_goi
            JOIN THANH_TOAN t ON d.ma_dang_ky = t.ma_dang_ky
            WHERE d.ma_hoi_vien = ?
            ORDER BY d.created_at DESC LIMIT 5
        ");
        $stmtPkg->execute([$maHoiVien]);
        $packages = $stmtPkg->fetchAll();

        // 4. Lịch sử điểm danh QR
        $checkinHistory = [];
        try {
            $stmtCheckin = $db->prepare("
                SELECT dd.ngay, dd.gio_diem_danh, dd.phuong_thuc, dd.ghi_chu
                FROM diem_danh dd
                WHERE dd.ma_hoi_vien = ?
                ORDER BY dd.ngay DESC, dd.gio_diem_danh DESC
                LIMIT 30
            ");
            $stmtCheckin->execute([$maHoiVien]);
            $checkinHistory = $stmtCheckin->fetchAll();
        } catch (Exception $e) {
            // Fallback: dùng LICH_SU_RA_VAO nếu bảng diem_danh chưa có
            try {
                $stmtCheckin = $db->prepare("
                    SELECT DATE(thoi_gian_vao) as ngay, TIME(thoi_gian_vao) as gio_diem_danh, 
                           'qr_code' as phuong_thuc, ghi_chu
                    FROM LICH_SU_RA_VAO
                    WHERE ma_hoi_vien = ?
                    ORDER BY thoi_gian_vao DESC
                    LIMIT 30
                ");
                $stmtCheckin->execute([$maHoiVien]);
                $checkinHistory = $stmtCheckin->fetchAll();
            } catch (Exception $e2) {}
        }

        require __DIR__ . '/../views/admin/chi-tiet-hoi-vien.php';
    }

    public function trainers() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $stmt = $db->query("SELECT t.ma_hlv, u.ma_nguoi_dung, u.ten_dang_nhap, u.trang_thai, t.chuyen_mon, t.mo_ta, t.anh_dai_dien,
                            COALESCE((SELECT AVG(so_sao) FROM DANH_GIA_HLV WHERE ma_hlv = t.ma_hlv AND trang_thai = 'approved'), 5) as avg_rating,
                            (SELECT COUNT(*) FROM DANH_GIA_HLV WHERE ma_hlv = t.ma_hlv AND trang_thai = 'approved') as review_count
                            FROM HUAN_LUYEN_VIEN t 
                            JOIN NGUOI_DUNG u ON t.ma_nguoi_dung = u.ma_nguoi_dung 
                            ORDER BY t.ma_hlv DESC");
        $trainers = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/hlv.php';
    }

    public function addTrainer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            require_once __DIR__ . '/../utils/SecurityHelper.php';
            $db = Database::getConnection();
            
            try {
                $db->beginTransaction();
                
                // 1. Tạo NGUOI_DUNG
                $stmt1 = $db->prepare("INSERT INTO NGUOI_DUNG (ten_dang_nhap, mat_khau, vai_tro) VALUES (?, ?, 'hlv')");
                $stmt1->execute([$_POST['email'], SecurityHelper::hashPassword($_POST['password'])]);
                $userId = $db->lastInsertId();
                
                // 2. Liên kết dữ liệu HUAN_LUYEN_VIEN
                $stmt2 = $db->prepare("INSERT INTO HUAN_LUYEN_VIEN (ma_nguoi_dung, chuyen_mon, mo_ta) VALUES (?, ?, ?)");
                $stmt2->execute([$userId, $_POST['chuyen_mon'], $_POST['mo_ta']]);
                
                $db->commit();
                header("Location: " . SITE_URL . "/admin/trainers?msg=" . urlencode("Da them Huan luyen vien thanh cong!"));
            } catch (PDOException $e) {
                $db->rollBack();
                // Email trùng lặp
                header("Location: " . SITE_URL . "/admin/trainers?error=" . urlencode("Email nay da duoc su dung!"));
            }
        }
    }

    public function updateTrainer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            
            try {
                $db->beginTransaction();
                
                // 1. Update NGUOI_DUNG
                $stmt1 = $db->prepare("UPDATE NGUOI_DUNG SET ten_dang_nhap = ?, trang_thai = ? WHERE ma_nguoi_dung = ?");
                $stmt1->execute([$_POST['email'], $_POST['trang_thai'], $_POST['ma_nguoi_dung']]);
                
                // 2. Update HUAN_LUYEN_VIEN
                $stmt2 = $db->prepare("UPDATE HUAN_LUYEN_VIEN SET chuyen_mon = ?, mo_ta = ? WHERE ma_nguoi_dung = ?");
                $stmt2->execute([$_POST['chuyen_mon'], $_POST['mo_ta'], $_POST['ma_nguoi_dung']]);
                
                $db->commit();
                setFlash("success", "Cập nhật thông tin HLV thành công!");
                header("Location: " . SITE_URL . "/admin/trainers");
            } catch (PDOException $e) {
                $db->rollBack();
                setFlash("danger", "Lỗi: Email có thể đã tồn tại hoặc dữ liệu không hợp lệ.");
                header("Location: " . SITE_URL . "/admin/trainers");
            }
        }
    }

    // ============================================================
    // ĐÁNH GIÁ HLV
    // ============================================================
    public function reviews() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $reviews = $db->query("
            SELECT r.*, u_hv.ten_dang_nhap as ten_hoi_vien, u_hlv.ten_dang_nhap as ten_hlv
            FROM DANH_GIA_HLV r
            JOIN HOI_VIEN hv ON r.ma_hoi_vien = hv.ma_hoi_vien
            JOIN NGUOI_DUNG u_hv ON hv.ma_nguoi_dung = u_hv.ma_nguoi_dung
            JOIN HUAN_LUYEN_VIEN hlv ON r.ma_hlv = hlv.ma_hlv
            JOIN NGUOI_DUNG u_hlv ON hlv.ma_nguoi_dung = u_hlv.ma_nguoi_dung
            ORDER BY CASE WHEN r.trang_thai='pending' THEN 0 WHEN r.trang_thai='reported' THEN 1 ELSE 2 END, r.created_at DESC
        ")->fetchAll();
        require __DIR__ . '/../views/admin/danh-gia.php';
    }

    public function approveReview() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $db->prepare("UPDATE DANH_GIA_HLV SET trang_thai='approved' WHERE ma_dg=?")
               ->execute([$_POST['ma_dg']]);
            header("Location: " . SITE_URL . "/admin/reviews?msg=" . urlencode("Da duyet danh gia!"));
        }
    }

    public function rejectReview() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $db->prepare("UPDATE DANH_GIA_HLV SET trang_thai='rejected' WHERE ma_dg=?")
               ->execute([$_POST['ma_dg']]);
            header("Location: " . SITE_URL . "/admin/reviews?msg=" . urlencode("Đã từ chối đánh giá."));
        }
    }

    // ============================================================
    // QUẢN LÝ TỦ ĐỒ
    // ============================================================
    public function lockers() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $pendingRequests = $db->query("
            SELECT y.*, u.ten_dang_nhap FROM YEU_CAU_THUE_TU y
            JOIN HOI_VIEN hv ON y.ma_hoi_vien = hv.ma_hoi_vien
            JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE y.trang_thai = 'pending' ORDER BY y.created_at ASC
        ")->fetchAll();

        $rentedLockers = $db->query("
            SELECT y.*, t.so_tu, u.ten_dang_nhap FROM YEU_CAU_THUE_TU y
            JOIN TU_DO t ON y.ma_tu = t.ma_tu
            JOIN HOI_VIEN hv ON y.ma_hoi_vien = hv.ma_hoi_vien
            JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE y.trang_thai = 'approved' ORDER BY y.ngay_bat_dau ASC
        ")->fetchAll();

        $availableLockers = $db->query("SELECT * FROM TU_DO WHERE trang_thai = 'trong' ORDER BY so_tu")->fetchAll();
        $allLockers = $db->query("SELECT * FROM TU_DO ORDER BY so_tu")->fetchAll();

        require __DIR__ . '/../views/admin/tu-do.php';
    }

    public function createLocker() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            try {
                $db->prepare("INSERT INTO TU_DO (so_tu, ghi_chu) VALUES (?, ?)")
                   ->execute([trim($_POST['so_tu']), $_POST['ghi_chu'] ?? '']);
                header("Location: " . SITE_URL . "/admin/lockers?msg=" . urlencode("Đã thêm tủ " . $_POST['so_tu']));
            } catch (PDOException $e) {
                header("Location: " . SITE_URL . "/admin/lockers?error=" . urlencode("Số tủ đã tồn tại!"));
            }
        }
    }

    public function approveLocker() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            try {
                $db->beginTransaction();
                
                // 1. LẤY SỐ THÁNG KHÁCH ĐÃ YÊU CẦU
                $stmtYc = $db->prepare("SELECT so_thang FROM YEU_CAU_THUE_TU WHERE ma_yc = ?");
                $stmtYc->execute([$_POST['ma_yc']]);
                $soThang = $stmtYc->fetchColumn() ?: 1;

                // 2. TÍNH TOÁN NGÀY THÁNG
                $ngayBatDau = date('Y-m-d');
                $ngayKetThuc = date('Y-m-d', strtotime("+$soThang month"));
                $maGD = 'TU' . strtoupper(substr(md5(uniqid()), 0, 8));

                $db->prepare("UPDATE YEU_CAU_THUE_TU SET trang_thai='approved', ma_tu=?, ngay_bat_dau=?, ngay_ket_thuc=?, ma_giao_dich_thue=? WHERE ma_yc=?")
                   ->execute([$_POST['ma_tu'], $ngayBatDau, $ngayKetThuc, $maGD, $_POST['ma_yc']]);
                
                $db->prepare("UPDATE TU_DO SET trang_thai='dang_thue' WHERE ma_tu=?")
                   ->execute([$_POST['ma_tu']]);
                
                $db->commit();
                
                // --- Gửi Email xác nhận tủ ---
                try {
                    $maYc = (int)$_POST['ma_yc'];
                    $lockerInfo = $db->prepare("
                        SELECT u.ho_ten, u.ten_dang_nhap, u.email, td.so_tu
                        FROM YEU_CAU_THUE_TU yc
                        JOIN HOI_VIEN hv ON yc.ma_hoi_vien = hv.ma_hoi_vien
                        JOIN NGUOI_DUNG u ON hv.ma_nguoi_dung = u.ma_nguoi_dung
                        JOIN TU_DO td ON yc.ma_tu = td.ma_tu
                        WHERE yc.ma_yc = ?
                    ");
                    $lockerInfo->execute([$maYc]);
                    $lInfo = $lockerInfo->fetch();
                    if ($lInfo) {
                        $toEmail = !empty($lInfo['email']) ? $lInfo['email'] : $lInfo['ten_dang_nhap'];
                        $toName  = !empty($lInfo['ho_ten']) ? $lInfo['ho_ten'] : $lInfo['ten_dang_nhap'];
                        if (filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                            $emailService = new EmailService();
                            $emailService->sendLockerConfirmation($toEmail, $toName, $lInfo['so_tu'], $ngayBatDau, $ngayKetThuc, 'approved');
                        }
                    }
                } catch (Exception $emailEx) {
                    error_log('Locker email error: ' . $emailEx->getMessage());
                }
                
                header("Location: " . SITE_URL . "/admin/lockers?msg=" . urlencode("Đã phân tủ thành công cho $soThang tháng! Ma GD: $maGD"));
            } catch (Exception $e) {
                $db->rollBack();
                header("Location: " . SITE_URL . "/admin/lockers?error=" . urlencode("Lỗi khi phân tủ."));
            }
        }
    }

    public function rejectLocker() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $db->prepare("UPDATE YEU_CAU_THUE_TU SET trang_thai='rejected' WHERE ma_yc=?")
               ->execute([$_POST['ma_yc']]);
            header("Location: " . SITE_URL . "/admin/lockers?msg=" . urlencode("Đã từ chối yêu cầu."));
        }
    }

    public function revokeLocker() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $db->beginTransaction();
            $stmtY = $db->prepare("SELECT ma_tu FROM YEU_CAU_THUE_TU WHERE ma_yc = ?");
            $stmtY->execute([$_POST['ma_yc']]);
            $maTu = $stmtY->fetchColumn();
            $db->prepare("UPDATE YEU_CAU_THUE_TU SET trang_thai='cancelled' WHERE ma_yc=?")->execute([$_POST['ma_yc']]);
            if ($maTu) $db->prepare("UPDATE TU_DO SET trang_thai='trong' WHERE ma_tu=?")->execute([$maTu]);
            $db->commit();
            header("Location: " . SITE_URL . "/admin/lockers?msg=" . urlencode("Đã thu hồi tủ."));
        }
    }

    // ============================================================
    // YÊU CẦU BAN HỘI VIÊN
    // ============================================================
    public function banRequests() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $pendingBans = $db->query("
            SELECT yb.*, u_hv.ten_dang_nhap as ten_hoi_vien, u_st.ten_dang_nhap as ten_staff
            FROM YEU_CAU_BAN yb
            JOIN HOI_VIEN hv ON yb.ma_hoi_vien = hv.ma_hoi_vien
            JOIN NGUOI_DUNG u_hv ON hv.ma_nguoi_dung = u_hv.ma_nguoi_dung
            JOIN NGUOI_DUNG u_st ON yb.ma_staff = u_st.ma_nguoi_dung
            WHERE yb.trang_thai = 'pending' ORDER BY yb.created_at ASC
        ")->fetchAll();

        $processedBans = $db->query("
            SELECT yb.*, u_hv.ten_dang_nhap as ten_hoi_vien, u_st.ten_dang_nhap as ten_staff
            FROM YEU_CAU_BAN yb
            JOIN HOI_VIEN hv ON yb.ma_hoi_vien = hv.ma_hoi_vien
            JOIN NGUOI_DUNG u_hv ON hv.ma_nguoi_dung = u_hv.ma_nguoi_dung
            JOIN NGUOI_DUNG u_st ON yb.ma_staff = u_st.ma_nguoi_dung
            WHERE yb.trang_thai != 'pending' ORDER BY yb.updated_at DESC LIMIT 30
        ")->fetchAll();

        require __DIR__ . '/../views/admin/yeu-cau-cam.php';
    }

    public function approveBanRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $db->beginTransaction();
            // 1. Cập nhật trạng thái yêu cầu
            $db->prepare("UPDATE YEU_CAU_BAN SET trang_thai='approved' WHERE ma_ycb=?")->execute([$_POST['ma_ycb']]);
            // 2. Đánh dấu bi_ban trong HOI_VIEN
            $db->prepare("UPDATE HOI_VIEN SET bi_ban=1 WHERE ma_hoi_vien=?")->execute([$_POST['ma_hoi_vien']]);
            // 3. Khóa tài khoản NGUOI_DUNG
            $db->prepare("
                UPDATE NGUOI_DUNG SET trang_thai='banned' 
                WHERE ma_nguoi_dung = (SELECT ma_nguoi_dung FROM HOI_VIEN WHERE ma_hoi_vien = ?)
            ")->execute([$_POST['ma_hoi_vien']]);
            $db->commit();
            setFlash("success", "Đã duyệt và khóa tài khoản hội viên.");
            header("Location: " . SITE_URL . "/admin/ban-requests");
        }
    }

    public function rejectBanRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $db->prepare("UPDATE YEU_CAU_BAN SET trang_thai='rejected' WHERE ma_ycb=?")->execute([$_POST['ma_ycb']]);
            header("Location: " . SITE_URL . "/admin/ban-requests?msg=" . urlencode("Đã từ chối yêu cầu ban."));
        }
    }

    // Staff gửi yêu cầu ban
    public function staffBanRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $staffId = $_SESSION['user_id'];
            $lyDo = trim($_POST['ly_do'] ?? '');
            $maHoiVien = $_POST['ma_hoi_vien'] ?? 0;

            if (empty($lyDo) || mb_strlen($lyDo) < 10) {
                setFlash("danger", "Lý do báo cáo phải có ít nhất 10 ký tự.");
                $redirectUrl = $_SERVER['HTTP_REFERER'] ?? (SITE_URL . "/admin/members");
                header("Location: " . $redirectUrl);
                return;
            }

            try {
                $db->prepare("INSERT INTO YEU_CAU_BAN (ma_hoi_vien, ma_staff, ly_do) VALUES (?, ?, ?)")
                   ->execute([$maHoiVien, $staffId, $lyDo]);
                setFlash("success", "Đã gửi yêu cầu ban lên Admin thành công.");
            } catch (PDOException $e) {
                setFlash("danger", "Lỗi khi gửi yêu cầu: " . $e->getMessage());
            }
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? (SITE_URL . "/admin/members");
            header("Location: " . $redirectUrl);
        }
    }

    // ============================================================
    // THÔNG BÁO HỆ THỐNG
    // ============================================================
    public function announcements() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403); die("Chỉ Admin mới có quyền này.");
        }
        $announcements = $db->query("SELECT * FROM THONG_BAO_HE_THONG ORDER BY created_at DESC")->fetchAll();
        require __DIR__ . '/../views/admin/thong-bao.php';
    }

    public function createAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();

            // Xử lý upload ảnh
            $hinhAnh = null;
            if (!empty($_FILES['hinh_anh']['name']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($_FILES['hinh_anh']['tmp_name']);
                if (in_array($fileType, $allowedTypes)) {
                    $ext = pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
                    $fileName = 'ann_' . time() . '_' . mt_rand(1000, 9999) . '.' . strtolower($ext);
                    $uploadDir = __DIR__ . '/../public/uploads/announcements/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $uploadDir . $fileName)) {
                        $hinhAnh = '/uploads/announcements/' . $fileName;
                    }
                }
            }

            $db->prepare("INSERT INTO THONG_BAO_HE_THONG (tieu_de, noi_dung, hinh_anh) VALUES (?, ?, ?)")
               ->execute([$_POST['tieu_de'], $_POST['noi_dung'], $hinhAnh]);
            header("Location: " . SITE_URL . "/admin/announcements?msg=" . urlencode("Da dang thong bao moi!"));
        }
    }

    public function toggleAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            if ($_POST['trang_thai'] === 'active') {
                // Tắt các cái cũ trước (Đã loại bỏ để cho phép nhiều thông báo)
                // $db->query("UPDATE THONG_BAO_HE_THONG SET trang_thai='inactive'");
            }
            $db->prepare("UPDATE THONG_BAO_HE_THONG SET trang_thai=? WHERE ma_tb=?")
               ->execute([$_POST['trang_thai'], $_POST['ma_tb']]);
            header("Location: " . SITE_URL . "/admin/announcements?msg=OK");
        }
    }

    public function deleteAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            // Xóa file ảnh trước khi xóa record
            $stmt = $db->prepare("SELECT hinh_anh FROM THONG_BAO_HE_THONG WHERE ma_tb=?");
            $stmt->execute([$_POST['ma_tb']]);
            $row = $stmt->fetch();
            if ($row && !empty($row['hinh_anh'])) {
                $filePath = __DIR__ . '/../public' . $row['hinh_anh'];
                if (file_exists($filePath)) unlink($filePath);
            }
            $db->prepare("DELETE FROM THONG_BAO_HE_THONG WHERE ma_tb=?")->execute([$_POST['ma_tb']]);
            header("Location: " . SITE_URL . "/admin/announcements?msg=" . urlencode("Đã xóa thong bao."));
        }
    }

    public function updateAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();

            $maTb = $_POST['ma_tb'];
            $tieuDe = $_POST['tieu_de'];
            $noiDung = $_POST['noi_dung'];

            // Lấy ảnh cũ
            $stmt = $db->prepare("SELECT hinh_anh FROM THONG_BAO_HE_THONG WHERE ma_tb=?");
            $stmt->execute([$maTb]);
            $old = $stmt->fetch();
            $hinhAnh = $old['hinh_anh'] ?? null;

            // Xử lý xóa ảnh cũ nếu người dùng yêu cầu
            if (!empty($_POST['xoa_anh']) && $hinhAnh) {
                $filePath = __DIR__ . '/../public' . $hinhAnh;
                if (file_exists($filePath)) unlink($filePath);
                $hinhAnh = null;
            }

            // Xử lý upload ảnh mới (thay thế ảnh cũ nếu có)
            if (!empty($_FILES['hinh_anh']['name']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($_FILES['hinh_anh']['tmp_name']);
                if (in_array($fileType, $allowedTypes)) {
                    // Xóa ảnh cũ trước
                    if ($hinhAnh) {
                        $oldPath = __DIR__ . '/../public' . $hinhAnh;
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    $ext = pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
                    $fileName = 'ann_' . time() . '_' . mt_rand(1000, 9999) . '.' . strtolower($ext);
                    $uploadDir = __DIR__ . '/../public/uploads/announcements/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $uploadDir . $fileName)) {
                        $hinhAnh = '/uploads/announcements/' . $fileName;
                    }
                }
            }

            // Cập nhật CHỈ tiêu đề, nội dung, hình ảnh — KHÔNG thay đổi created_at
            $db->prepare("UPDATE THONG_BAO_HE_THONG SET tieu_de=?, noi_dung=?, hinh_anh=? WHERE ma_tb=?")
               ->execute([$tieuDe, $noiDung, $hinhAnh, $maTb]);

            setFlash('success', 'Đã cập nhật thông báo thành công!');
            header("Location: " . SITE_URL . "/admin/announcements");
        }
    }

    // =========================================================================
    // NHÂN VIÊN - Merged từ MonkeyGym_Full
    // =========================================================================

    public function staffManagement() {
        $this->requireAdmin();
        $db = new Database();
        $staff = $db->select("
            SELECT nv.*, nd.ten_dang_nhap, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.trang_thai,
                   pb.ten_phong_ban
            FROM nhan_vien nv
            INNER JOIN nguoi_dung nd ON nv.ma_nguoi_dung = nd.ma_nguoi_dung
            LEFT JOIN phong_ban pb ON nv.ma_phong_ban = pb.ma_phong_ban
            ORDER BY nd.ho_ten ASC
        ") ?: [];
        $departments = $db->select("SELECT * FROM phong_ban ORDER BY ten_phong_ban") ?: [];
        $flash = getFlash();
        require_once __DIR__ . '/../views/admin/nhan-vien.php';
    }

    public function addStaff() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . SITE_URL . '/admin/staff'); exit; }
        $db = new Database();
        try {
            $hoTen      = trim($_POST['ho_ten'] ?? '');
            $tenDangNhap = trim($_POST['ten_dang_nhap'] ?? '');
            $sdt        = trim($_POST['so_dien_thoai'] ?? '');
            $chucVu     = trim($_POST['chuc_vu'] ?? 'Nhân viên');
            $maPhongBan = !empty($_POST['ma_phong_ban']) ? (int)$_POST['ma_phong_ban'] : null;
            $ngayVaoLam = !empty($_POST['ngay_vao_lam']) ? $_POST['ngay_vao_lam'] : null;
            $matKhau    = $_POST['mat_khau'] ?? '';

            if (!$hoTen || !$tenDangNhap || !$matKhau) throw new Exception('Vui lòng nhập đầy đủ thông tin bắt buộc');
            if ($db->selectOne("SELECT ma_nguoi_dung FROM NGUOI_DUNG WHERE ten_dang_nhap=?", [$tenDangNhap]))
                throw new Exception('Email đăng nhập đã tồn tại');

            $hash = password_hash($matKhau, PASSWORD_DEFAULT);
            $db->execute("INSERT INTO NGUOI_DUNG (ten_dang_nhap, mat_khau, vai_tro, ho_ten, so_dien_thoai) VALUES (?,?,?,?,?)",
                [$tenDangNhap, $hash, 'nhanvien', $hoTen, $sdt]);
            $maNguoiDung = $db->lastInsertId();
            if (!$maNguoiDung) $maNguoiDung = $db->selectOne("SELECT ma_nguoi_dung FROM NGUOI_DUNG WHERE ten_dang_nhap=?", [$tenDangNhap])['ma_nguoi_dung'];
            $db->execute("INSERT INTO nhan_vien (ma_nguoi_dung, ma_phong_ban, chuc_vu, ngay_vao_lam) VALUES (?,?,?,?)",
                [$maNguoiDung, $maPhongBan, $chucVu, $ngayVaoLam]);
            setFlash('success', 'Thêm nhân viên thành công');
        } catch (Exception $e) {
            setFlash('danger', $e->getMessage());
        }
        header('Location: ' . SITE_URL . '/admin/staff'); exit;
    }

    public function updateStaff() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . SITE_URL . '/admin/staff'); exit; }
        $db = new Database();
        try {
            $maNguoiDung = (int)($_POST['ma_nguoi_dung'] ?? 0);
            $maNhanVien  = (int)($_POST['ma_nhan_vien'] ?? 0);
            $hoTen       = trim($_POST['ho_ten'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $sdt         = trim($_POST['so_dien_thoai'] ?? '');
            $chucVu      = trim($_POST['chuc_vu'] ?? 'Nhân viên');
            $maPhongBan  = !empty($_POST['ma_phong_ban']) ? (int)$_POST['ma_phong_ban'] : null;
            $ngayVaoLam  = !empty($_POST['ngay_vao_lam']) ? $_POST['ngay_vao_lam'] : null;
            $trangThai   = (int)($_POST['trang_thai'] ?? 1);
            $trangThaiND = $trangThai ? 'active' : 'banned';

            if (!$maNguoiDung || !$maNhanVien) throw new Exception('Thiếu mã nhân viên');
            $db->execute("UPDATE NGUOI_DUNG SET ho_ten=?, email=?, so_dien_thoai=?, trang_thai=? WHERE ma_nguoi_dung=?",
                [$hoTen, $email, $sdt, $trangThaiND, $maNguoiDung]);
            $db->execute("UPDATE nhan_vien SET ma_phong_ban=?, chuc_vu=?, ngay_vao_lam=? WHERE ma_nhan_vien=?",
                [$maPhongBan, $chucVu, $ngayVaoLam, $maNhanVien]);
            setFlash('success', 'Cập nhật nhân viên thành công');
        } catch (Exception $e) {
            setFlash('danger', $e->getMessage());
        }
        header('Location: ' . SITE_URL . '/admin/staff'); exit;
    }

    public function deleteStaff() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . SITE_URL . '/admin/staff'); exit; }
        $db = new Database();
        $maNguoiDung = (int)($_POST['ma_nguoi_dung'] ?? 0);
        if ($maNguoiDung) {
            $db->execute("UPDATE NGUOI_DUNG SET trang_thai='banned' WHERE ma_nguoi_dung=?", [$maNguoiDung]);
            setFlash('success', 'Đã ngừng hoạt động nhân viên');
        }
        header('Location: ' . SITE_URL . '/admin/staff'); exit;
    }

    // =========================================================================
    // BÁO CÁO - Merged từ MonkeyGym_Full
    // =========================================================================

    public function reports() {
        $this->requireAdmin();
        $db = new Database();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate   = $_GET['end_date'] ?? date('Y-m-d');

        // Doanh thu tổng
        $revenueStats = $db->selectOne("
            SELECT COALESCE(SUM(so_tien),0) as tong_doanh_thu, COUNT(*) as so_giao_dich, COALESCE(AVG(so_tien),0) as doanh_thu_trung_binh
            FROM THANH_TOAN WHERE trang_thai='success' AND DATE(created_at) BETWEEN ? AND ?
        ", [$startDate, $endDate]) ?: ['tong_doanh_thu'=>0,'so_giao_dich'=>0,'doanh_thu_trung_binh'=>0];

        // Theo phương thức
        $revenueByMethod = $db->select("
            SELECT phuong_thuc, COUNT(*) as so_luong, SUM(so_tien) as tong_tien
            FROM THANH_TOAN WHERE trang_thai='success' AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY phuong_thuc
        ", [$startDate, $endDate]) ?: [];

        // Hội viên
        $memberStats = $db->selectOne("
            SELECT COUNT(*) as tong_hoi_vien, SUM(CASE WHEN nd.created_at >= ? THEN 1 ELSE 0 END) as hoi_vien_moi
            FROM HOI_VIEN hv INNER JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung=nd.ma_nguoi_dung
        ", [$startDate]) ?: ['tong_hoi_vien'=>0,'hoi_vien_moi'=>0];

        // Gói tập
        $packageStats = $db->select("
            SELECT gt.ten_goi, COUNT(*) as so_luong, SUM(gt.gia_tien) as doanh_thu
            FROM DANG_KY_GOI dk INNER JOIN GOI_TAP gt ON dk.ma_goi=gt.ma_goi
            WHERE DATE(dk.created_at) BETWEEN ? AND ?
            GROUP BY gt.ma_goi ORDER BY so_luong DESC
        ", [$startDate, $endDate]) ?: [];

        // Doanh thu theo ngày
        $dailyRevenue = $db->select("
            SELECT DATE(created_at) as ngay, SUM(so_tien) as doanh_thu
            FROM THANH_TOAN WHERE trang_thai='success' AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at) ORDER BY ngay ASC
        ", [$startDate, $endDate]) ?: [];

        // Điểm danh (từ bảng diem_danh nếu có, fallback lich_su_ra_vao)
        try {
            $checkinStats = $db->selectOne("
                SELECT COUNT(*) as tong_diem_danh, COUNT(DISTINCT ma_hoi_vien) as hoi_vien_diem_danh
                FROM diem_danh WHERE ngay BETWEEN ? AND ?
            ", [$startDate, $endDate]) ?: ['tong_diem_danh'=>0,'hoi_vien_diem_danh'=>0];
        } catch (Exception $e) {
            $checkinStats = ['tong_diem_danh'=>0,'hoi_vien_diem_danh'=>0];
        }

        require_once __DIR__ . '/../views/admin/bao-cao.php';
    }

    // =========================================================================
    // ĐIỂM DANH (nâng cao) - Merged từ MonkeyGym_Full
    // =========================================================================

    public function checkin() {
        $this->requireStaffOrAdmin();
        $db = new Database();
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate   = $_GET['end_date'] ?? date('Y-m-d');

        $todayStats = ['total' => 0];
        try {
            $todayStats = $db->selectOne("SELECT COUNT(*) as total FROM diem_danh WHERE ngay=CURDATE()") ?: ['total'=>0];
        } catch (Exception $e) {}

        $recentCheckins = [];
        try {
            $recentCheckins = $db->select("
                SELECT dd.*, COALESCE(nd.ho_ten, nd.ten_dang_nhap) as ho_ten, nd.so_dien_thoai,
                       GROUP_CONCAT(gt.ten_goi SEPARATOR ', ') as ten_goi
                FROM diem_danh dd
                INNER JOIN HOI_VIEN hv ON dd.ma_hoi_vien=hv.ma_hoi_vien
                INNER JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung=nd.ma_nguoi_dung
                LEFT JOIN DANG_KY_GOI dk ON hv.ma_hoi_vien=dk.ma_hoi_vien AND dk.trang_thai='active'
                LEFT JOIN GOI_TAP gt ON dk.ma_goi=gt.ma_goi
                WHERE dd.ngay BETWEEN ? AND ?
                GROUP BY dd.ma_diem_danh
                ORDER BY dd.ngay DESC, dd.gio_diem_danh DESC LIMIT 50
            ", [$startDate, $endDate]) ?: [];
        } catch (Exception $e) {}

        require_once __DIR__ . '/../views/admin/diem-danh.php';
    }

    public function doCheckin() {
        ob_start(); // Chặn PHP warning/error từ WAMP xuất ra trước JSON
        ini_set('display_errors', 0);

        ob_clean();
        header('Content-Type: application/json; charset=UTF-8');

        $this->requireStaffOrAdmin();
        $db = new Database();

        // Nhận cả JSON lẫn form-urlencoded
        $input  = json_decode(file_get_contents('php://input'), true) ?? [];
        $qrCode = trim($input['qr_code'] ?? $_POST['qr_code'] ?? '');

        if (!$qrCode) {
            ob_end_clean();
            echo json_encode(['success'=>false,'message'=>'Mã QR không hợp lệ']); exit;
        }

        // Hỗ trợ QR động DQRC_ (HMAC) + QR tĩnh MEMBER_ + ma_qr DB
        $memberId = null;
        if (strpos($qrCode, 'DQRC_') === 0) {
            if (function_exists('verifyDynamicQR')) {
                $mid = 0;
                if (!verifyDynamicQR($qrCode, $mid)) {
                    ob_end_clean();
                    echo json_encode(['success'=>false,'message'=>'❌ QR đã hết hạn (>60s). Yêu cầu hội viên refresh.']); exit;
                }
                $memberId = $mid;
            } else {
                $parts = explode('_', $qrCode);
                $memberId = isset($parts[1]) ? (int)$parts[1] : null;
            }
        } elseif (strpos($qrCode, 'MEMBER_') === 0) {
            $parts    = explode('_', $qrCode);
            $memberId = isset($parts[1]) ? (int)$parts[1] : null;
        }

        try {
            // Tìm member
            if ($memberId) {
                $member = $db->selectOne("
                    SELECT hv.ma_hoi_vien, COALESCE(nd.ho_ten,nd.ten_dang_nhap) as ho_ten,
                           nd.email, nd.so_dien_thoai
                    FROM HOI_VIEN hv INNER JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung=nd.ma_nguoi_dung
                    WHERE hv.ma_hoi_vien=?
                ", [$memberId]);
            } else {
                // Thử tìm theo mã QR trước
                $member = $db->selectOne("
                    SELECT hv.ma_hoi_vien, COALESCE(nd.ho_ten,nd.ten_dang_nhap) as ho_ten,
                           nd.email, nd.so_dien_thoai
                    FROM HOI_VIEN hv INNER JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung=nd.ma_nguoi_dung
                    WHERE hv.ma_qr=?
                ", [$qrCode]);

                // Nếu không thấy mã QR, thử tìm theo Số điện thoại
                if (!$member) {
                    $member = $db->selectOne("
                        SELECT hv.ma_hoi_vien, COALESCE(nd.ho_ten,nd.ten_dang_nhap) as ho_ten,
                               nd.email, nd.so_dien_thoai
                        FROM HOI_VIEN hv INNER JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung=nd.ma_nguoi_dung
                        WHERE nd.so_dien_thoai=?
                    ", [$qrCode]);
                }
            }

            if (!$member) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Không tìm thấy hội viên với mã QR này!']); exit;
            }

            $package = $db->selectOne("
                SELECT dk.*, gt.ten_goi FROM DANG_KY_GOI dk INNER JOIN GOI_TAP gt ON dk.ma_goi=gt.ma_goi
                WHERE dk.ma_hoi_vien=? AND dk.trang_thai='active' AND dk.ngay_ket_thuc>=CURDATE()
                ORDER BY dk.ngay_ket_thuc DESC LIMIT 1
            ", [$member['ma_hoi_vien']]);
            if (!$package) { ob_end_clean(); echo json_encode(['success'=>false,'message'=>'Hội viên chưa có gói tập hoặc đã hết hạn!','data'=>['member'=>$member]]); exit; }

            // Kiểm tra đã check-in hôm nay chưa (dùng bảng diem_danh)
            try {
                $checkedToday = $db->selectOne("SELECT * FROM diem_danh WHERE ma_hoi_vien=? AND ngay=CURDATE()", [$member['ma_hoi_vien']]);
                if ($checkedToday) {
                    ob_end_clean(); echo json_encode(['success'=>false,'message'=>'Hội viên đã check-in hôm nay lúc ' . date('H:i', strtotime($checkedToday['gio_diem_danh']))]); exit;
                }
                $db->execute("INSERT INTO diem_danh (ma_hoi_vien, ngay, gio_diem_danh, phuong_thuc, ghi_chu) VALUES (?,CURDATE(),CURTIME(),'qr_code',?)",
                    [$member['ma_hoi_vien'], 'Check-in bởi ' . ($_SESSION['ho_ten'] ?? 'Staff')]);

                // ✅ Cộng +1 điểm check-in hàng ngày
                $db->execute(
                    "INSERT INTO DIEM_TICH_LUY (ma_hoi_vien, so_diem) VALUES (?,1)
                     ON DUPLICATE KEY UPDATE so_diem = so_diem + 1",
                    [$member['ma_hoi_vien']]
                );
                $db->execute(
                    "INSERT INTO LICH_SU_DIEM (ma_hoi_vien, so_diem_thay_doi, ly_do) VALUES (?,1,'Điểm danh hàng ngày')",
                    [$member['ma_hoi_vien']]
                );
                // Cũng ghi vào LICH_SU_RA_VAO để tương thích với màn lịch sử cũ
                $db->execute("INSERT INTO LICH_SU_RA_VAO (ma_hoi_vien, ghi_chu) VALUES (?,?)",
                    [$member['ma_hoi_vien'], 'QR Check-in (diem_danh)']);
            } catch (Exception $e) {
                // Fallback: dùng LICH_SU_RA_VAO nếu bảng diem_danh chưa có
                $db->execute("INSERT INTO LICH_SU_RA_VAO (ma_hoi_vien, ghi_chu) VALUES (?,?)",
                    [$member['ma_hoi_vien'], 'QR Check-in']);
            }

            ob_end_clean(); echo json_encode(['success'=>true,'message'=>'Check-in thành công!','data'=>['member'=>$member,'package'=>$package,'time'=>date('H:i:s')]]);
        } catch (Exception $e) {
            ob_end_clean(); echo json_encode(['success'=>false,'message'=>'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    // =========================================================================
    // LỊCH SỬ ĐIỂM DANH - Xem tất cả lịch sử ra vào của hội viên
    // =========================================================================

    public function attendanceHistory() {
        $this->requireStaffOrAdmin();
        $db = new Database();

        // Bộ lọc
        $startDate  = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate    = $_GET['end_date'] ?? date('Y-m-d');
        $searchName = trim($_GET['search'] ?? '');

        // Thống kê tổng
        $totalCheckins = 0;
        $uniqueMembers = 0;
        try {
            $statsRow = $db->selectOne("
                SELECT COUNT(*) as tong, COUNT(DISTINCT dd.ma_hoi_vien) as so_hv
                FROM diem_danh dd
                WHERE dd.ngay BETWEEN ? AND ?
            ", [$startDate, $endDate]);
            $totalCheckins = $statsRow['tong'] ?? 0;
            $uniqueMembers = $statsRow['so_hv'] ?? 0;
        } catch (Exception $e) {}

        // Lấy danh sách điểm danh
        $attendanceList = [];
        try {
            $searchCondition = '';
            $params = [$startDate, $endDate];
            if ($searchName !== '') {
                $searchCondition = " AND (nd.ho_ten LIKE ? OR nd.ten_dang_nhap LIKE ?)";
                $params[] = "%{$searchName}%";
                $params[] = "%{$searchName}%";
            }

            $attendanceList = $db->select("
                SELECT dd.ma_diem_danh, dd.ngay, dd.gio_diem_danh, dd.phuong_thuc, dd.ghi_chu,
                       COALESCE(nd.ho_ten, nd.ten_dang_nhap) as ho_ten, nd.ten_dang_nhap,
                       nd.so_dien_thoai, hv.ma_qr, hv.ma_hoi_vien,
                       GROUP_CONCAT(gt.ten_goi SEPARATOR ', ') as ten_goi
                FROM diem_danh dd
                INNER JOIN HOI_VIEN hv ON dd.ma_hoi_vien = hv.ma_hoi_vien
                INNER JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
                LEFT JOIN DANG_KY_GOI dk ON hv.ma_hoi_vien = dk.ma_hoi_vien AND dk.trang_thai = 'active'
                LEFT JOIN GOI_TAP gt ON dk.ma_goi = gt.ma_goi
                WHERE dd.ngay BETWEEN ? AND ? {$searchCondition}
                GROUP BY dd.ma_diem_danh
                ORDER BY dd.ngay DESC, dd.gio_diem_danh DESC
                LIMIT 200
            ", $params) ?: [];
        } catch (Exception $e) {
            // Fallback: dùng LICH_SU_RA_VAO
            try {
                $searchConditionLegacy = '';
                $paramsLegacy = [$startDate, $endDate];
                if ($searchName !== '') {
                    $searchConditionLegacy = " AND (nd.ho_ten LIKE ? OR nd.ten_dang_nhap LIKE ?)";
                    $paramsLegacy[] = "%{$searchName}%";
                    $paramsLegacy[] = "%{$searchName}%";
                }
                $attendanceList = $db->select("
                    SELECT l.ma_lich_su as ma_diem_danh, DATE(l.thoi_gian_vao) as ngay, 
                           TIME(l.thoi_gian_vao) as gio_diem_danh, 'qr_code' as phuong_thuc, l.ghi_chu,
                           COALESCE(nd.ho_ten, nd.ten_dang_nhap) as ho_ten, nd.ten_dang_nhap,
                           nd.so_dien_thoai, hv.ma_qr, hv.ma_hoi_vien,
                           NULL as ten_goi
                    FROM LICH_SU_RA_VAO l
                    INNER JOIN HOI_VIEN hv ON l.ma_hoi_vien = hv.ma_hoi_vien
                    INNER JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
                    WHERE DATE(l.thoi_gian_vao) BETWEEN ? AND ? {$searchConditionLegacy}
                    ORDER BY l.thoi_gian_vao DESC
                    LIMIT 200
                ", $paramsLegacy) ?: [];
            } catch (Exception $e2) {}
        }

        require_once __DIR__ . '/../views/admin/lich-su-diem-danh.php';
    }

    // =========================================================================
    // QUẢN LÝ THANH TOÁN TRỰC TIẾP - Merged từ MonkeyGym_Full
    // =========================================================================

    public function paymentManagement() {
        $this->requireStaffOrAdmin();
        $db = new Database();

        $pendingPayments = $db->select("
            SELECT dk.ma_dang_ky, dk.created_at, gt.ten_goi, gt.gia_tien,
                   nd.ho_ten, nd.ten_dang_nhap, dk.gia_thanh_toan,
                   dk.ngay_kich_hoat, dk.ngay_ket_thuc
            FROM DANG_KY_GOI dk
            INNER JOIN HOI_VIEN hv ON dk.ma_hoi_vien=hv.ma_hoi_vien
            INNER JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung=nd.ma_nguoi_dung
            INNER JOIN GOI_TAP gt ON dk.ma_goi=gt.ma_goi
            WHERE dk.trang_thai='pending'
            ORDER BY dk.created_at DESC
        ") ?: [];

        $recentPayments = $db->select("
            SELECT tt.*, gt.ten_goi, nd.ho_ten, nd2.ho_ten as nguoi_thu_ten
            FROM THANH_TOAN tt
            INNER JOIN DANG_KY_GOI dk ON tt.ma_dang_ky=dk.ma_dang_ky
            INNER JOIN HOI_VIEN hv ON dk.ma_hoi_vien=hv.ma_hoi_vien
            INNER JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung=nd.ma_nguoi_dung
            INNER JOIN GOI_TAP gt ON dk.ma_goi=gt.ma_goi
            LEFT JOIN NGUOI_DUNG nd2 ON tt.nguoi_thu=nd2.ma_nguoi_dung
            ORDER BY tt.created_at DESC LIMIT 30
        ") ?: [];

        $flash = getFlash();
        require_once __DIR__ . '/../views/admin/quan-ly-thanh-toan.php';
    }

    public function processPayment() {
        $this->requireStaffOrAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . SITE_URL . '/?page=admin&action=paymentManagement'); exit; }
        $db = new Database();
        try {
            $maDangKy   = (int)($_POST['ma_dang_ky'] ?? 0);
            $soTien     = (float)($_POST['so_tien'] ?? 0);
            $phuongThuc = $_POST['phuong_thuc'] ?? 'tien_mat';
            $ghiChu     = $_POST['ghi_chu'] ?? '';
            $nguoiThu   = $_SESSION['user_id'] ?? null;

            if (!$maDangKy) throw new Exception('Thiếu mã đăng ký');

            // Kiểm tra trạng thái trước khi xử lý — chống nhấn nhiều lần
            $dangKy = $db->selectOne("SELECT trang_thai FROM DANG_KY_GOI WHERE ma_dang_ky = ?", [$maDangKy]);
            if (!$dangKy) throw new Exception('Không tìm thấy đăng ký gói.');
            if ($dangKy['trang_thai'] !== 'pending') {
                setFlash('warning', 'Giao dịch này đã được xử lý trước đó.');
                header('Location: ' . SITE_URL . '/admin/payment-management'); exit;
            }

            $db->beginTransaction();

            // Lấy thông tin gói tập để tính ngày hết hạn và số buổi PT
            $info = $db->selectOne("
                SELECT dk.ma_hoi_vien, gt.thoi_han_thang, gt.so_buoi_pt
                FROM DANG_KY_GOI dk
                JOIN GOI_TAP g ON dk.ma_goi = g.ma_goi
                JOIN GOI_TAP gt ON dk.ma_goi = gt.ma_goi
                WHERE dk.ma_dang_ky = ?
            ", [$maDangKy]);

            $thoiHanThang = (int)($info['thoi_han_thang'] ?? 1);
            $soBuoiPT = (int)($info['so_buoi_pt'] ?? 0);
            $maHoiVien = $info['ma_hoi_vien'];
            
            $ngayKichHoat = date('Y-m-d');
            $ngayKetThuc = date('Y-m-d', strtotime("+$thoiHanThang month", strtotime($ngayKichHoat)));

            $txnRef = 'CASH_' . time() . '_' . $maDangKy;
            $db->execute("INSERT INTO THANH_TOAN (ma_dang_ky, vnp_TxnRef, so_tien, phuong_thuc, trang_thai, nguoi_thu, ghi_chu, ngay_thanh_toan)
                VALUES (?,?,?,?,'success',?,?,NOW())",
                [$maDangKy, $txnRef, $soTien, $phuongThuc, $nguoiThu, $ghiChu]);

            $db->execute("UPDATE DANG_KY_GOI SET trang_thai='active', ngay_kich_hoat=?, ngay_ket_thuc=? WHERE ma_dang_ky=?", 
                [$ngayKichHoat, $ngayKetThuc, $maDangKy]);

            // Cập nhật ngày hết hạn và CỘNG DỒN số buổi PT
            $db->execute("UPDATE HOI_VIEN SET ngay_het_han_goi = ?, so_buoi_pt_con_lai = so_buoi_pt_con_lai + ? WHERE ma_hoi_vien = ?", 
                [$ngayKetThuc, $soBuoiPT, $maHoiVien]);

            $db->commit();
            
            // --- Gửi email biên lai thanh toán tiền mặt ---
            try {
                $payInfo = $db->selectOne("
                    SELECT nd.ho_ten, nd.ten_dang_nhap, nd.email, gt.ten_goi, dk.gia_thanh_toan
                    FROM DANG_KY_GOI dk
                    JOIN HOI_VIEN hv ON dk.ma_hoi_vien = hv.ma_hoi_vien
                    JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
                    JOIN GOI_TAP gt ON dk.ma_goi = gt.ma_goi
                    WHERE dk.ma_dang_ky = ?
                ", [$maDangKy]);
                if ($payInfo) {
                    $toEmail = !empty($payInfo['email']) ? $payInfo['email'] : $payInfo['ten_dang_nhap'];
                    $toName  = !empty($payInfo['ho_ten']) ? $payInfo['ho_ten'] : $payInfo['ten_dang_nhap'];
                    if (filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                        $emailService = new EmailService();
                        $emailService->sendPaymentConfirmation(
                            $toEmail, $toName,
                            (float)($payInfo['gia_thanh_toan'] ?? $soTien),
                            $payInfo['ten_goi'],
                            $txnRef
                        );
                    }
                }
            } catch (Exception $emailEx) {
                error_log('Payment email error: ' . $emailEx->getMessage());
            }
            
            setFlash('success', 'Thanh toán thành công!');
        } catch (Exception $e) {
            $db->rollBack();
            setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }
        header('Location: ' . SITE_URL . '/admin/payment-management'); exit;
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    private function requireAdmin() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            redirect('/login'); exit;
        }
    }

    // =========================================================================
    // NHẮC NHỞ TỰ ĐỘNG QUA EMAIL (15 ngày vắng + 3 ngày hết hạn)
    // =========================================================================
    public function sendReminders() {
        $this->requireStaffOrAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . SITE_URL . '/admin/dashboard'); exit;
        }
        if (!verifyCsrf('send_reminders')) {
            setFlash('danger', 'Phiên bảo mật hết hạn. Vui lòng thử lại.');
            header('Location: ' . SITE_URL . '/admin/dashboard'); exit;
        }
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $emailService = new EmailService();
        $sent = 0;
        $errors = 0;

        // --- 1. NHẮC NHỞ VẮNG MẶT (chính xác 15 ngày, gửi 1 lần duy nhất) ---
        // Logic: lần điểm danh gần nhất cách hôm nay đúng 15 ngày (tới bất kế giờ)
        try {
            $stmtAbsent = $db->prepare("
                SELECT hv.ma_hoi_vien, nd.ho_ten, nd.ten_dang_nhap, nd.email,
                       MAX(l.thoi_gian_vao) as last_checkin
                FROM HOI_VIEN hv
                JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
                LEFT JOIN LICH_SU_RA_VAO l ON hv.ma_hoi_vien = l.ma_hoi_vien
                WHERE hv.ngay_het_han_goi >= CURDATE()
                GROUP BY hv.ma_hoi_vien, nd.ho_ten, nd.ten_dang_nhap, nd.email
                HAVING DATEDIFF(CURDATE(), DATE(last_checkin)) = 15
                   OR (last_checkin IS NULL AND DATEDIFF(CURDATE(), DATE(nd.created_at)) = 15)
            ");
            $stmtAbsent->execute();
            $absentMembers = $stmtAbsent->fetchAll();

            foreach ($absentMembers as $m) {
                $toEmail = !empty($m['email']) ? $m['email'] : $m['ten_dang_nhap'];
                $toName  = !empty($m['ho_ten']) ? $m['ho_ten'] : $m['ten_dang_nhap'];
                if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) continue;
                try {
                    $emailService->sendAbsenceReminder($toEmail, $toName, 15);
                    $sent++;
                } catch (Exception $e) {
                    $errors++;
                    error_log('Absence email failed for ' . $toEmail . ': ' . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log('Absence reminder query error: ' . $e->getMessage());
        }

        // --- 2. NHẮC NHỞ HẾT HẠN GÓI TẬP (còn đúng 3 ngày) ---
        try {
            $stmtExpire = $db->prepare("
                SELECT hv.ma_hoi_vien, nd.ho_ten, nd.ten_dang_nhap, nd.email,
                       hv.ngay_het_han_goi,
                       gt.ten_goi
                FROM HOI_VIEN hv
                JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
                LEFT JOIN DANG_KY_GOI dk ON hv.ma_hoi_vien = dk.ma_hoi_vien AND dk.trang_thai = 'active'
                LEFT JOIN GOI_TAP gt ON dk.ma_goi = gt.ma_goi
                WHERE DATEDIFF(hv.ngay_het_han_goi, CURDATE()) = 3
                GROUP BY hv.ma_hoi_vien
            ");
            $stmtExpire->execute();
            $expiringMembers = $stmtExpire->fetchAll();

            foreach ($expiringMembers as $m) {
                $toEmail = !empty($m['email']) ? $m['email'] : $m['ten_dang_nhap'];
                $toName  = !empty($m['ho_ten']) ? $m['ho_ten'] : $m['ten_dang_nhap'];
                if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) continue;
                try {
                    $emailService->sendExpirationReminder($toEmail, $toName, 3, $m['ten_goi'] ?? 'Gói tập hiện tại');
                    $sent++;
                } catch (Exception $e) {
                    $errors++;
                    error_log('Expiration email failed for ' . $toEmail . ': ' . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log('Expiration reminder query error: ' . $e->getMessage());
        }

        // --- 3. NHẮC NHỞ HẾT HẠN TỦ ĐỒ (còn đúng 3 ngày) ---
        try {
            $stmtLocker = $db->prepare("
                SELECT yc.ma_yc, nd.ho_ten, nd.ten_dang_nhap, nd.email, td.so_tu, yc.ngay_ket_thuc
                FROM YEU_CAU_THUE_TU yc
                JOIN HOI_VIEN hv ON yc.ma_hoi_vien = hv.ma_hoi_vien
                JOIN NGUOI_DUNG nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
                JOIN TU_DO td ON yc.ma_tu = td.ma_tu
                WHERE yc.trang_thai = 'approved' AND DATEDIFF(yc.ngay_ket_thuc, CURDATE()) = 3
            ");
            $stmtLocker->execute();
            $expiringLockers = $stmtLocker->fetchAll();

            foreach ($expiringLockers as $l) {
                $toEmail = !empty($l['email']) ? $l['email'] : $l['ten_dang_nhap'];
                $toName  = !empty($l['ho_ten']) ? $l['ho_ten'] : $l['ten_dang_nhap'];
                if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) continue;
                try {
                    $emailService->sendLockerExpirationReminder($toEmail, $toName, $l['so_tu'], 3);
                    $sent++;
                } catch (Exception $e) {
                    $errors++;
                    error_log('Locker expiration email failed for ' . $toEmail . ': ' . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log('Locker expiration reminder query error: ' . $e->getMessage());
        }

        setFlash('success', "✅ Đã gửi $sent email nhắc nhở! (Lỗi: $errors)");
        header('Location: ' . SITE_URL . '/admin/dashboard'); exit;
    }

    private function requireStaffOrAdmin() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'nhanvien'])) {
            redirect('/login'); exit;
        }
    }

    // ============================================================
    // QUẢN LÝ MÃ GIẢM GIÁ
    // ============================================================
    public function promotions() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $promotions = $db->query("SELECT * FROM MA_GIAM_GIA ORDER BY created_at DESC")->fetchAll();
        require __DIR__ . '/../views/admin/ma-giam-gia.php';
    }

    public function createPromotion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            try {
                $phan_tram = $_POST['loai_giam_gia'] === 'phan_tram' ? (int)$_POST['phan_tram_giam'] : 0;
                $so_tien = $_POST['loai_giam_gia'] === 'so_tien' ? (float)$_POST['so_tien_giam'] : 0;

                $db->prepare("INSERT INTO MA_GIAM_GIA (code, phan_tram_giam, so_tien_giam, loai_ap_dung, so_luong_con, ngay_het_han, mo_ta) VALUES (?, ?, ?, ?, ?, ?, ?)")
                   ->execute([
                       strtoupper(trim($_POST['code'])),
                       $phan_tram,
                       $so_tien,
                       $_POST['loai_ap_dung'],
                       $_POST['so_luong_con'],
                       $_POST['ngay_het_han'],
                       $_POST['mo_ta'] ?? ''
                   ]);
                setFlash('success', 'Đã thêm mã giảm giá mới!');
            } catch (PDOException $e) {
                setFlash('danger', 'Lỗi: Mã giảm giá đã tồn tại hoặc dữ liệu không hợp lệ.');
            }
            redirect('/admin/promotions');
        }
    }

    public function deletePromotion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $db->prepare("DELETE FROM MA_GIAM_GIA WHERE ma_giam_gia = ?")->execute([$_POST['ma_giam_gia']]);
            setFlash('success', 'Đã xóa mã giảm giá.');
            redirect('/admin/promotions');
        }
    }

    // Hiển thị giao diện Kanban CRM
    public function leads() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        
        // Lấy toàn bộ danh sách khách hàng đăng ký tập thử
        $stmt = $db->query("SELECT * FROM DANG_KY_TAP_THU ORDER BY ngay_dang_ky DESC");
        $leads = $stmt ? $stmt->fetchAll() : [];
        
        // Phân loại data vào 5 cột
        $board = [
            'chua_goi' => [],
            'da_goi' => [],
            'dang_cho' => [],
            'da_chot' => [],
            'that_bai' => []
        ];
        
        foreach ($leads as $lead) {
            $board[$lead['trang_thai']][] = $lead;
        }
        
        require __DIR__ . '/../views/admin/quan-ly-khach-tiem-nang.php';
    }

    // API Cập nhật trạng thái bằng AJAX khi kéo thả
    public function updateLeadStatus() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $status = $_POST['status'] ?? '';
            
            if ($id && $status) {
                try {
                    $db = Database::getConnection();
                    $stmt = $db->prepare("UPDATE DANG_KY_TAP_THU SET trang_thai = ? WHERE id = ?");
                    $stmt->execute([$status, $id]);
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL.']);
                }
            }
        }
    }
    // Hiển thị trang xếp lịch Group X
    public function schedule() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        
        $classes = $db->query("SELECT * FROM LOP_HOC_NHOM")->fetchAll();
        $trainers = $db->query("
            SELECT h.ma_hlv, u.ho_ten, u.ten_dang_nhap 
            FROM HUAN_LUYEN_VIEN h 
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
        ")->fetchAll();
        
        $schedules = $db->query("
            SELECT lh.*, l.ten_lop, l.loai_lop, COALESCE(u.ho_ten, u.ten_dang_nhap) as ten_hlv
            FROM LICH_HOC_NHOM lh
            JOIN LOP_HOC_NHOM l ON lh.ma_lop = l.ma_lop
            JOIN HUAN_LUYEN_VIEN h ON lh.ma_hlv = h.ma_hlv
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            ORDER BY lh.ngay_hoc ASC, lh.gio_bat_dau ASC
        ")->fetchAll();
        
        require __DIR__ . '/../views/admin/quan-ly-lich-hoc.php';
    }

    public function createSchedule() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../database/config.php';
            $db = Database::getConnection();
            $maHlv = $_POST['ma_hlv'];
            $ngay = $_POST['ngay_hoc'];
            $batDau = $_POST['gio_bat_dau'];
            $ngayGioPT = $ngay . ' ' . $batDau;

            // --- KIỂM TRA TRÙNG LỊCH HLV ---
            
            // 1. Kiểm tra trong LICH_HOC_NHOM (Trùng với lớp nhóm khác)
            $stmtConflictGroup = $db->prepare("
                SELECT COUNT(*) FROM LICH_HOC_NHOM 
                WHERE ma_hlv = ? AND ngay_hoc = ? AND gio_bat_dau = ?
            ");
            $stmtConflictGroup->execute([$maHlv, $ngay, $batDau]);
            if ($stmtConflictGroup->fetchColumn() > 0) {
                header('Location: ' . SITE_URL . '/admin/schedule?error=' . urlencode("HLV đã có lịch dạy lớp nhóm khác vào khung giờ này!"));
                exit;
            }

            // 2. Kiểm tra trong LICH_DAT_PT (Trùng với khách PT)
            $stmtConflictPT = $db->prepare("
                SELECT COUNT(*) FROM LICH_DAT_PT 
                WHERE ma_hlv = ? AND ngay_gio_tap = ? AND trang_thai != 'cancelled'
            ");
            $stmtConflictPT->execute([$maHlv, $ngayGioPT]);
            if ($stmtConflictPT->fetchColumn() > 0) {
                header('Location: ' . SITE_URL . '/admin/schedule?error=' . urlencode("HLV bận dạy PT cho hội viên vào khung giờ này!"));
                exit;
            }
            // --- KẾT THÚC KIỂM TRA ---

            $stmt = $db->prepare("INSERT INTO LICH_HOC_NHOM (ma_lop, ma_hlv, ngay_hoc, gio_bat_dau, gio_ket_thuc, so_luong_toi_da) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['ma_lop'], $maHlv, $ngay, $batDau, $_POST['gio_ket_thuc'], $_POST['so_luong']]);
            header('Location: ' . SITE_URL . '/admin/schedule?success=1');
            exit;
        }
    }

    public function payroll() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $month = $_GET['month'] ?? date('m/Y'); 
        
        // Sửa lỗi: Sử dụng prepare thay vì query trực tiếp để tránh lỗi injection và sai cú pháp PDO
        $stmt = $db->prepare("
            SELECT b.*, COALESCE(u.ho_ten, u.ten_dang_nhap) as ho_ten, u.ten_dang_nhap 
            FROM BANG_LUONG b 
            JOIN HUAN_LUYEN_VIEN h ON b.ma_hlv = h.ma_hlv 
            JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung
            WHERE b.thang_nam = ?
        ");
        $stmt->execute([$month]);
        $payrolls = $stmt->fetchAll();
        
        require __DIR__ . '/../views/admin/quan-ly-luong.php';
    }

    public function calculatePayroll() {
        require_once __DIR__ . '/../database/config.php';
        $db = Database::getConnection();
        $month = date('m/Y');
        $dbMonth = date('Y-m'); 

        try {
            $trainers = $db->query("SELECT * FROM HUAN_LUYEN_VIEN")->fetchAll();
            
            foreach ($trainers as $t) {
                // 1. KIỂM TRA: HLV này đã được thanh toán lương tháng này chưa?
                $stmtCheck = $db->prepare("SELECT ma_luong, trang_thai FROM BANG_LUONG WHERE ma_hlv = ? AND thang_nam = ? ORDER BY ma_luong DESC LIMIT 1");
                $stmtCheck->execute([$t['ma_hlv'], $month]);
                $existing = $stmtCheck->fetch();

                // Nếu ĐÃ THANH TOÁN -> Bỏ qua, tuyệt đối không tính lại để tránh nhân đôi lương cứng
                if ($existing && $existing['trang_thai'] === 'da_thanh_toan') {
                    continue; 
                }

                // Nếu có bản nháp (CHƯA THANH TOÁN) -> Xóa đi để tính lại bản mới (Cập nhật số buổi mới nhất)
                if ($existing && $existing['trang_thai'] === 'chua_thanh_toan') {
                    $db->prepare("DELETE FROM BANG_LUONG WHERE ma_luong = ?")->execute([$existing['ma_luong']]);
                }

                // 2. ĐẾM SỐ BUỔI DẠY (Lớp Nhóm + PT)
                $stmtCountGroup = $db->prepare("SELECT COUNT(*) as tong_buoi FROM LICH_HOC_NHOM WHERE ma_hlv = ? AND DATE_FORMAT(ngay_hoc, '%Y-%m') = ?");
                $stmtCountGroup->execute([$t['ma_hlv'], $dbMonth]);
                $so_buoi_nhom = $stmtCountGroup->fetch()['tong_buoi'] ?? 0;
                
                $stmtCountPT = $db->prepare("
                    SELECT COUNT(*) as tong_buoi 
                    FROM LICH_DAT_PT 
                    WHERE ma_hlv = ? 
                      AND DATE_FORMAT(ngay_gio_tap, '%Y-%m') = ?
                      AND trang_thai IN ('confirmed', 'completed', 'attended', 'cancel_rejected')
                ");
                $stmtCountPT->execute([$t['ma_hlv'], $dbMonth]);
                $so_buoi_pt = $stmtCountPT->fetch()['tong_buoi'] ?? 0;

                $so_buoi = $so_buoi_nhom + $so_buoi_pt;
                
                // 3. TÍNH TIỀN VÀ LƯU VÀO DB
                $thuong = $so_buoi * ($t['gia_buoi_day'] ?? 150000);
                $tong = ($t['luong_cung'] ?? 5000000) + $thuong;
                
                $db->prepare("INSERT INTO BANG_LUONG (ma_hlv, thang_nam, luong_cung, so_buoi_day, thuong_hoa_hong, tong_luong) VALUES (?, ?, ?, ?, ?, ?)")
                   ->execute([$t['ma_hlv'], $month, $t['luong_cung'], $so_buoi, $thuong, $tong]);
            }
            header('Location: ' . SITE_URL . '/admin/payroll?success=1');
            exit;
        } catch (Exception $e) { die("Lỗi: " . $e->getMessage()); }
    }

    public function paySalary() {
        $db = Database::getConnection();
        if (isset($_GET['id'])) {
            $db->prepare("UPDATE BANG_LUONG SET trang_thai = 'da_thanh_toan' WHERE ma_luong = ?")->execute([$_GET['id']]);
            header('Location: ' . SITE_URL . '/admin/payroll?paid=1');
            exit;
        }
    }
}

// ============================================================
// HELPER: Gửi email xác nhận thanh toán (global function)
// ============================================================
function _adminSendPaymentEmail(
    string $toEmail, string $toName, string $tenGoi,
    float $soTien, string $phuongThuc,
    string $ngayBatDau, string $ngayKetThuc
): void {
    $subject  = '✅ Xác nhận thanh toán gói tập — Monkey Gym';
    $amountFmt = number_format($soTien, 0, ',', '.') . 'đ';
    $startFmt  = date('d/m/Y', strtotime($ngayBatDau));
    $endFmt    = date('d/m/Y', strtotime($ngayKetThuc));
    $siteUrl   = defined('SITE_URL') ? SITE_URL : '';

    $methodMap = [
        'tien_mat'     => 'Tiền mặt',
        'chuyen_khoan' => 'Chuyển khoản',
        'the'          => 'Thẻ',
        'cash'         => 'Tiền mặt',
    ];
    $methodLabel = $methodMap[$phuongThuc] ?? ucfirst($phuongThuc);

    $body = "<!DOCTYPE html>
<html lang='vi'><head><meta charset='UTF-8'></head>
<body style='font-family:Inter,sans-serif;background:#F8F7F4;margin:0;padding:24px'>
<div style='max-width:520px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.08)'>
  <div style='background:linear-gradient(135deg,#C9993F,#8B6914);padding:28px 32px;text-align:center'>
    <h1 style='color:#fff;margin:0;font-size:22px'>🏋️ Monkey Gym</h1>
    <p style='color:rgba(255,255,255,.85);margin:6px 0 0;font-size:14px'>Xác nhận thanh toán thành công</p>
  </div>
  <div style='padding:32px'>
    <p style='font-size:15px;color:#1C1917;margin-bottom:16px'>Xin chào <strong>" . htmlspecialchars($toName) . "</strong>,</p>
    <p style='color:#6B7280;font-size:14px;margin-bottom:20px'>Gói tập của bạn đã được thanh toán và kích hoạt thành công!</p>
    <div style='background:#FDF8EE;border:1px solid #E8D5A0;border-radius:10px;padding:20px;margin-bottom:24px'>
      <table style='width:100%;border-collapse:collapse;font-size:14px'>
        <tr><td style='padding:7px 0;color:#6B7280;width:45%'>Gói tập:</td>
            <td style='padding:7px 0;font-weight:600'>" . htmlspecialchars($tenGoi) . "</td></tr>
        <tr><td style='padding:7px 0;color:#6B7280'>Số tiền:</td>
            <td style='padding:7px 0;color:#C9993F;font-weight:700;font-size:16px'>$amountFmt</td></tr>
        <tr><td style='padding:7px 0;color:#6B7280'>Phương thức:</td>
            <td style='padding:7px 0'>$methodLabel</td></tr>
        <tr><td style='padding:7px 0;color:#6B7280'>Ngày bắt đầu:</td>
            <td style='padding:7px 0;font-weight:600'>$startFmt</td></tr>
        <tr><td style='padding:7px 0;color:#6B7280'>Có hiệu lực đến:</td>
            <td style='padding:7px 0;font-weight:600;color:#16A34A'>$endFmt</td></tr>
      </table>
    </div>
    <div style='text-align:center;margin-bottom:24px'>
      <a href='{$siteUrl}/member/dashboard'
         style='display:inline-block;background:#C9993F;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px'>
        Xem Dashboard của tôi
      </a>
    </div>
    <p style='font-size:12px;color:#9CA3AF;text-align:center;margin:0'>
      © " . date('Y') . " Monkey Gym. Mọi thắc mắc vui lòng liên hệ quầy lễ tân.
    </p>
  </div>
</div>
</body></html>";

    require_once __DIR__ . '/../includes/EmailService.php';
    $emailService = new EmailService();
    $emailService->send($toEmail, $subject, $body);
}