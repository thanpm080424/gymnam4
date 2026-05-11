<?php
/**
 * Router & Entry Point - Monkey Gym
 * Auto-detect base path - hoạt động với bất kỳ tên folder nào
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';

startSession();

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/MemberController.php';
require_once __DIR__ . '/../controllers/TrainerController.php';
require_once __DIR__ . '/../controllers/LandingController.php';
require_once __DIR__ . '/../controllers/AiController.php';
require_once __DIR__ . '/../controllers/PlannerController.php';

// Auto-detect base path - hoạt động cho mọi tên folder và mọi URL
$scriptName  = $_SERVER['SCRIPT_NAME'] ?? '/public/index.php';
$publicPath  = rtrim(dirname($scriptName), '/');       // /monkey-gym-v3/public
$projectPath = rtrim(dirname($publicPath), '/');       // /monkey-gym-v3

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = $requestUri;

// Thử strip /project/public trước, rồi /project
foreach ([$publicPath, $projectPath] as $strip) {
    if ($strip && $strip !== '/' && strpos($uri, $strip) === 0) {
        $uri = substr($uri, strlen($strip));
        break;
    }
}
$uri = '/' . trim($uri, '/');
if ($uri === '' || $uri === '//') $uri = '/';

try {
    switch ($uri) {

        case '/': case '/index.php': case '':
            (new LandingController())->index();
            break;

        // AUTH
        case '/login': case '/dang-nhap':
            (new AuthController())->login(); break;
        case '/register': case '/dang-ky':
            (new AuthController())->register(); break;
        case '/logout':
            (new AuthController())->logout(); break;
        case '/dang-nhap-google':
            require __DIR__ . '/dang-nhap-google.php'; break;
        case '/vnpay-return':
            require __DIR__ . '/vnpay-return.php'; break;
        case '/vnpay-notify':
            require __DIR__ . '/vnpay-notify.php'; break;

        // ADMIN
        case '/admin/dashboard':   (new AdminController())->dashboard(); break;
        case '/admin/members':     (new AdminController())->members(); break;
        case '/admin/packages':    (new AdminController())->packages(); break;
        case '/admin/packages/create': (new AdminController())->addPackage(); break;
        case '/admin/packages/update': (new AdminController())->updatePackage(); break;
        case '/admin/packages/delete': (new AdminController())->deletePackage(); break;
        case '/admin/history':     (new AdminController())->history(); break;
        case '/admin/trainers':    (new AdminController())->trainers(); break;
        case '/admin/trainers/create': (new AdminController())->addTrainer(); break;
        case '/admin/trainers/update': (new AdminController())->updateTrainer(); break;
        case '/admin/members/ban': (new AdminController())->banMember(); break;
        case '/admin/members/delete': (new AdminController())->deleteMember(); break;
        case '/admin/members/detail': (new AdminController())->memberDetail(); break;
        case '/admin/members/reset-password': (new AdminController())->resetPassword(); break;
        case '/admin/members/ban-request': (new AdminController())->staffBanRequest(); break;
        case '/admin/products':    (new AdminController())->products(); break;
        case '/admin/products/create': (new AdminController())->addProduct(); break;
        case '/admin/products/update': (new AdminController())->updateProduct(); break;
        case '/admin/products/delete': (new AdminController())->deleteProduct(); break;
        case '/admin/promotions':      (new AdminController())->promotions(); break;
        case '/admin/promotions/create': (new AdminController())->createPromotion(); break;
        case '/admin/promotions/delete': (new AdminController())->deletePromotion(); break;
        case '/admin/purchases':   (new AdminController())->purchases(); break;
        case '/admin/purchases/update': (new AdminController())->updateOrderStatus(); break;
        case '/admin/qr-scanner':  (new AdminController())->qrScanner(); break;
        case '/admin/qr-checkin':  (new AdminController())->qrCheckin(); break;
        case '/admin/checkin':     (new AdminController())->checkin(); break;
        case '/admin/checkin/do':  (new AdminController())->doCheckin(); break;
        case '/admin/attendance-history': (new AdminController())->attendanceHistory(); break;
        case '/admin/payment-management': (new AdminController())->paymentManagement(); break;
        case '/admin/payment-management/process': (new AdminController())->processPayment(); break;
        case '/admin/reviews':     (new AdminController())->reviews(); break;
        case '/admin/reviews/approve': (new AdminController())->approveReview(); break;
        case '/admin/reviews/reject':  (new AdminController())->rejectReview(); break;
        case '/admin/lockers':     (new AdminController())->lockers(); break;
        case '/admin/lockers/create':  (new AdminController())->createLocker(); break;
        case '/admin/lockers/approve': (new AdminController())->approveLocker(); break;
        case '/admin/lockers/reject':  (new AdminController())->rejectLocker(); break;
        case '/admin/lockers/revoke':  (new AdminController())->revokeLocker(); break;
        case '/admin/ban-requests': (new AdminController())->banRequests(); break;
        case '/admin/ban-requests/approve': (new AdminController())->approveBanRequest(); break;
        case '/admin/ban-requests/reject':  (new AdminController())->rejectBanRequest(); break;
        case '/admin/announcements': (new AdminController())->announcements(); break;
        case '/admin/announcements/create': (new AdminController())->createAnnouncement(); break;
        case '/admin/announcements/toggle': (new AdminController())->toggleAnnouncement(); break;
        case '/admin/announcements/delete': (new AdminController())->deleteAnnouncement(); break;
        case '/admin/announcements/update': (new AdminController())->updateAnnouncement(); break;
        case '/admin/staff':       (new AdminController())->staffManagement(); break;
        case '/admin/staff/add':   (new AdminController())->addStaff(); break;
        case '/admin/staff/update':(new AdminController())->updateStaff(); break;
        case '/admin/staff/delete':(new AdminController())->deleteStaff(); break;
        case '/admin/reports':     (new AdminController())->reports(); break;
        case '/admin/send-reminders': (new AdminController())->sendReminders(); break;

        // MEMBER
        case '/member/dashboard':  (new MemberController())->dashboard(); break;
        case '/member/payment-simulate':
            require __DIR__ . '/../views/member/payment-simulate.php';
            break;

        case '/member/store':      (new MemberController())->store(); break;
        case '/member/buy':        (new MemberController())->buyPackage(); break;
        case '/member/store/buy-product':    (new MemberController())->buyProduct(); break;
        case '/member/store/buy-with-points':(new MemberController())->buyWithPoints(); break;
        case '/member/receipt':    (new MemberController())->receipt(); break;
        case '/member/booking':    (new MemberController())->ptBooking(); break;
        case '/member/booking/create': (new MemberController())->createBooking(); break;
        case '/member/booking/cancel': (new MemberController())->requestCancelBooking(); break;
        case '/member/dashboard/update-bmi': (new MemberController())->updateBmi(); break;
        case '/member/dashboard/update-profile': (new MemberController())->updateProfile(); break;
        case '/member/journal':    (new MemberController())->journal(); break;
        case '/member/journal/create': (new MemberController())->journalCreate(); break;
        case '/member/journal/delete': (new MemberController())->journalDelete(); break;
        case '/member/locker':     (new MemberController())->locker(); break;
        case '/member/locker/request': (new MemberController())->lockerRequest(); break;
        case '/member/reviews':    (new MemberController())->reviews(); break;
        case '/member/reviews/submit': (new MemberController())->submitReview(); break;
        case '/member/reviews/delete': (new MemberController())->deleteReview(); break;
        case '/member/change-password': (new MemberController())->changePassword(); break;
        case '/member/cart':            (new MemberController())->cartView(); break;
        case '/member/cart/add':        (new MemberController())->cartAdd(); break;
        case '/member/cart/remove':     (new MemberController())->cartRemove(); break;
        case '/member/cart/checkout':   (new MemberController())->cartCheckout(); break;
        case '/member/history':         (new MemberController())->history(); break;

        // PLANNER
        case '/member/planner':           (new PlannerController())->index(); break;
        case '/member/planner/generate':  (new PlannerController())->generate(); break;
        case '/member/planner/checkin':   (new PlannerController())->checkin(); break;

        // API PLANNER (Đồng bộ với giao diện mới)
        case '/api/member/toggle-checkin':   (new PlannerController())->checkin(); break;
        case '/api/member/update-busy-slots': (new PlannerController())->updateBusySlots(); break;

        // TRAINER
        case '/trainer/dashboard': (new TrainerController())->dashboard(); break;
        case '/trainer/students':  (new TrainerController())->students(); break;
        case '/trainer/booking/resolve': (new TrainerController())->resolveBooking(); break;
        case '/trainer/cancel/resolve':  (new TrainerController())->resolveCancel(); break;
        case '/trainer/profile':         (new TrainerController())->profile(); break;
        case '/trainer/profile/update':  (new TrainerController())->updateProfile(); break;
        case '/trainer/note/save':       (new TrainerController())->saveNote(); break;
        case '/trainer/reviews/report':  (new TrainerController())->reportReview(); break;
        case '/trainer/schedule':        (new TrainerController())->schedule(); break;
        case '/trainer/schedule/update': (new TrainerController())->scheduleUpdate(); break;
        case '/trainer/schedule/delete': (new TrainerController())->scheduleDeleteSlot(); break;
        case '/trainer/schedule/api':    TrainerController::getScheduleApi(); break;
        case '/api/qr-token':
            require __DIR__ . '/api/qr-token.php'; break;

        case '/api/chat':
            (new AiController())->chat();
            break;

        default:
            $file = __DIR__ . $uri;
            if (file_exists($file) && is_file($file)) {
                require $file;
            } else {
                http_response_code(404);
                echo "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>404</title>
                <style>body{font-family:sans-serif;text-align:center;padding-top:80px;background:#0f172a;color:#94a3b8}
                h1{font-size:4rem;color:#8b5cf6;margin:0}a{color:#10b981;text-decoration:none}</style></head>
                <body><h1>404</h1><p>Trang không tồn tại.</p>
                <p><a href='" . SITE_URL . "'>← Về trang chủ</a></p></body></html>";
            }
    }

} catch (Exception $e) {
    error_log("[monkey-gym] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo "<pre style='background:#1e1e1e;color:#d4d4d4;padding:20px;text-align:left'>"
            . "<b>Error:</b> " . htmlspecialchars($e->getMessage()) . "\n\n"
            . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h2 style='text-align:center;margin-top:100px'>Lỗi hệ thống. Vui lòng thử lại.</h2>";
    }
}