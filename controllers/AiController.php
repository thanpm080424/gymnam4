<?php
require_once __DIR__ . '/../database/config.php';

/**
 * AiController - Monkey Gym Chatbot
 * Nâng cấp: Tăng độ "thông minh" và cá tính cho AI (Monkey Persona)
 * Loại bỏ JSON Mode gò bó, sử dụng Natural Language + Metadata block
 */
class AiController {

    public function chat() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=UTF-8');

        $input   = json_decode(file_get_contents('php://input'), true) ?? [];
        $message = trim($input['message'] ?? '');
        $history = $input['history'] ?? [];

        if (empty($message)) {
            echo json_encode(['reply' => 'Bạn muốn hỏi gì ạ?', 'action' => null, 'suggestions' => []]);
            exit;
        }

        $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : null;
        $db = Database::getConnection();

        // ── Fetch Data ────────────────────────────────────────────────────────
        $packages = $db->query("SELECT ten_goi, gia_tien, thoi_han_thang, so_buoi_pt FROM GOI_TAP ORDER BY gia_tien")->fetchAll();
        $trainers = $db->query("SELECT u.ho_ten, u.ten_dang_nhap, h.chuyen_mon, h.nam_kinh_nghiem FROM HUAN_LUYEN_VIEN h JOIN NGUOI_DUNG u ON h.ma_nguoi_dung = u.ma_nguoi_dung WHERE u.trang_thai = 'active'")->fetchAll();
        $promos   = $db->query("SELECT code, phan_tram_giam, so_tien_giam, so_luong_con, ngay_het_han FROM MA_GIAM_GIA WHERE so_luong_con > 0 AND ngay_het_han > NOW() ORDER BY created_at DESC LIMIT 5")->fetchAll();

        $isLoggedIn = !empty($_SESSION['user_id']);
        $memberCtx  = '';

        if ($isLoggedIn) {
            $userId   = (int)$_SESSION['user_id'];
            $userName = $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? 'bạn';

            $stmtPkg = $db->prepare("SELECT g.ten_goi, dk.ngay_ket_thuc, hv.so_buoi_pt_con_lai FROM DANG_KY_GOI dk JOIN HOI_VIEN hv ON dk.ma_hoi_vien = hv.ma_hoi_vien JOIN GOI_TAP g ON dk.ma_goi = g.ma_goi WHERE hv.ma_nguoi_dung = ? AND dk.trang_thai IN ('active', 'dang_hoat_dong') ORDER BY dk.ngay_ket_thuc DESC LIMIT 1");
            $stmtPkg->execute([$userId]);
            $pkg = $stmtPkg->fetch();

            $stmtPts = $db->prepare("SELECT d.so_diem FROM DIEM_TICH_LUY d JOIN HOI_VIEN hv ON d.ma_hoi_vien = hv.ma_hoi_vien WHERE hv.ma_nguoi_dung = ?");
            $stmtPts->execute([$userId]);
            $pts = $stmtPts->fetch();

            $stmtBookings = $db->prepare("SELECT ldp.ngay_gio_tap, u.ho_ten AS ten_hlv, ldp.trang_thai FROM LICH_DAT_PT ldp JOIN HOI_VIEN hv ON ldp.ma_hoi_vien = hv.ma_hoi_vien JOIN HUAN_LUYEN_VIEN hlv ON ldp.ma_hlv = hlv.ma_hlv JOIN NGUOI_DUNG u ON hlv.ma_nguoi_dung = u.ma_nguoi_dung WHERE hv.ma_nguoi_dung = ? AND ldp.ngay_gio_tap >= NOW() AND ldp.trang_thai IN ('pending','confirmed', 'attended') ORDER BY ldp.ngay_gio_tap ASC LIMIT 3");
            $stmtBookings->execute([$userId]);
            $bookings = $stmtBookings->fetchAll();

            $stmtLocker = $db->prepare("SELECT td.so_tu FROM YEU_CAU_THUE_TU yt JOIN HOI_VIEN hv ON yt.ma_hoi_vien = hv.ma_hoi_vien JOIN TU_DO td ON yt.ma_tu = td.ma_tu WHERE hv.ma_nguoi_dung = ? AND yt.trang_thai = 'approved' ORDER BY yt.created_at DESC LIMIT 1");
            $stmtLocker->execute([$userId]);
            $locker = $stmtLocker->fetch();

            $memberCtx = "\n─── CONTEXT CÁ NHÂN ($userName) ───\n";
            if ($pkg) {
                $daysLeft = max(0, (int)((strtotime($pkg['ngay_ket_thuc']) - time()) / 86400));
                $memberCtx .= "Gói: {$pkg['ten_goi']} (còn $daysLeft ngày). Buổi PT: {$pkg['so_buoi_pt_con_lai']}.\n";
            }
            if ($pts) $memberCtx .= "Điểm: {$pts['so_diem']}.\n";
            if (!empty($bookings)) {
                foreach ($bookings as $b) {
                    $status = ($b['trang_thai'] === 'confirmed' || $b['trang_thai'] === 'attended') ? '✓' : '⏳';
                    $memberCtx .= "Lịch PT: " . date('d/m H:i', strtotime($b['ngay_gio_tap'])) . " với {$b['ten_hlv']} ($status).\n";
                }
            }
            if ($locker) $memberCtx .= "Tủ đồ: {$locker['so_tu']}.\n";
        }

        if (empty($geminiApiKey)) {
            $reply = $this->_keywordFallback($message, $packages, $trainers, $promos);
            echo json_encode(['reply' => $reply, 'action' => null, 'suggestions' => []]);
            exit;
        }

        // ── System Prompt (Monkey Persona) ──────────────────────────────────────
        $displayName = $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? 'khách vãng lai';
        $loginLabel  = $isLoggedIn ? "đã đăng nhập" : "chưa đăng nhập";

        $sysPrompt = <<<PROMPT
Bạn là Monkey 🐒 — Linh vật và trợ lý AI đầy năng lượng của phòng gym Monkey Gym.

CÁ TÍNH:
- Thân thiện, hài hước, đôi khi dùng teencode nhẹ (vd: k, ko, thui, nè...) để tạo cảm giác gần gũi.
- Rất am hiểu về gym, fitness và sức khỏe.
- Coi hội viên như những người anh em/bạn bè trong đại gia đình Monkey Gym.
- Sử dụng emoji linh hoạt (🐒, 💪, 🔥, 🍌, ✨).

QUY TẮC PHẢN HỒI:
1. Trả lời tự nhiên, KHÔNG máy móc. Đừng liệt kê cứng nhắc trừ khi người dùng yêu cầu.
2. Ưu tiên giải quyết vấn đề của hội viên bằng dữ liệu thực tế được cung cấp.
3. Nếu người dùng hỏi ngoài lề (đời sống, triết lý), hãy trả lời thông minh nhưng luôn hướng họ về việc tập luyện (vd: "Buồn thì đi tập gym cho khỏe bạn ơi!").
4. CẤU TRÚC ĐẦU RA BẮT BUỘC:
   - Phần 1: Câu trả lời tự nhiên cho người dùng.
   - Phần 2: Luôn kết thúc bằng chuỗi phân cách `---METADATA---`
   - Phần 3: Một khối JSON chứa `action` và `suggestions`.

ĐỊNH DẠNG METADATA JSON:
{
  "action": "open_store" | "open_booking" | "open_journal" | "open_locker" | "open_dashboard" | "open_login" | null,
  "suggestions": ["câu hỏi 1", "câu hỏi 2", "câu hỏi 3"]
}

DỮ LIỆU PHÒNG TẬP:
$memberCtx
Gói tập: (liệt kê cho bạn biết)
PROMPT;
        foreach($packages as $p) $sysPrompt .= "- {$p['ten_goi']}: " . number_format($p['gia_tien']) . "đ\n";
        $sysPrompt .= "\nHLV:\n";
        foreach($trainers as $t) $sysPrompt .= "- " . ($t['ho_ten'] ?: $t['ten_dang_nhap']) . " ({$t['chuyen_mon']})\n";
        $sysPrompt .= "\nKhuyến mãi: " . json_encode($promos) . "\n";
        $sysPrompt .= "\nNgười dùng: $displayName ($loginLabel)";

        // ── Call Gemini ─────────────────────────────────────────────────────────
        $contents = [];
        foreach (array_slice($history, -10) as $h) {
            $contents[] = ['role' => ($h['role'] === 'user' ? 'user' : 'model'), 'parts' => [['text' => $h['text']]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $geminiApiKey;
        $payload = json_encode([
            'systemInstruction' => ['parts' => [['text' => $sysPrompt]]],
            'contents'          => $contents,
            'generationConfig'  => [
                'maxOutputTokens'  => 1000,
                'temperature'      => 0.85,
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if (!$curlErr && $response) {
            $data = json_decode($response, true);
            $fullText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if ($fullText) {
                // Tách text và metadata
                $parts = explode('---METADATA---', $fullText);
                $reply = trim($parts[0]);
                $metaRaw = isset($parts[1]) ? trim($parts[1]) : '{}';
                
                // Parse JSON metadata
                $meta = json_decode($metaRaw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Fallback nếu AI trả JSON lỗi (regex tìm JSON)
                    if (preg_match('/\{.*\}/s', $metaRaw, $matches)) {
                        $meta = json_decode($matches[0], true);
                    }
                }

                echo json_encode([
                    'reply'       => $reply,
                    'action'      => $meta['action'] ?? null,
                    'suggestions' => $meta['suggestions'] ?? []
                ]);
                exit;
            }
        }

        // ── Fallback ────────────────────────────────────────────────────────────
        $reply = $this->_keywordFallback($message, $packages, $trainers, $promos);
        echo json_encode(['reply' => $reply, 'action' => null, 'suggestions' => []]);
    }

    private function _keywordFallback($msg, $pkgs, $hlvs, $promos) {
        $m = mb_strtolower($msg, 'UTF-8');
        if (strpos($m, 'chào') !== false) return 'Chào bạn nha! Mình là Monkey 🐒. Đang có hứng thú tập tành gì không nè?';
        if (strpos($m, 'giá') !== false || strpos($m, 'gói') !== false) return 'Bạn xem các gói tập trong mục Cửa Hàng nhé, nhiều ưu đãi lắm đó! 💪';
        return 'Monkey chưa hiểu ý bạn lắm, nhưng mà thôi đi tập gym đi cho đời nó tươi! 🐒🔥';
    }
}