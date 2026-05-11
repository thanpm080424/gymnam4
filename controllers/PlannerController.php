<?php
require_once __DIR__ . '/../database/config.php';

class PlannerController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hoi_vien') {
            header('Location: ' . SITE_URL . '/login'); exit;
        }

        $db     = Database::getConnection();
        $userId = (int)$_SESSION['user_id'];

        $stmt = $db->prepare("SELECT ma_hoi_vien, chieu_cao, can_nang FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmt->execute([$userId]);
        $member = $stmt->fetch();
        if (!$member) die("Không tìm thấy thông tin hội viên.");
        $maHoiVien = $member['ma_hoi_vien'];

        if (isset($_GET['reset'])) {
            $db->prepare("DELETE FROM PLAN_CA_NHAN WHERE ma_hoi_vien = ?")->execute([$maHoiVien]);
            header('Location: ' . SITE_URL . '/member/planner'); exit;
        }

        $stmtPlan = $db->prepare("SELECT * FROM PLAN_CA_NHAN WHERE ma_hoi_vien = ? ORDER BY created_at DESC LIMIT 1");
        $stmtPlan->execute([$maHoiVien]);
        $plan = $stmtPlan->fetch();

        $busySchedule = [];
        $stmtBusy = $db->prepare("SELECT lich_ban_json FROM LICH_BAN_HOI_VIEN WHERE ma_hoi_vien = ?");
        $stmtBusy->execute([$maHoiVien]);
        $busyRow = $stmtBusy->fetch();
        if ($busyRow) $busySchedule = json_decode($busyRow['lich_ban_json'], true) ?? [];

        $checkins = [];
        if ($plan) {
            $stmtC = $db->prepare("SELECT week, day_name, slot FROM PLAN_CHECKIN WHERE ma_plan = ?");
            $stmtC->execute([$plan['ma_plan']]);
            foreach ($stmtC->fetchAll() as $row) {
                $checkins[] = "{$row['week']}_{$row['day_name']}_{$row['slot']}";
            }
        }

        require __DIR__ . '/../views/member/planner.php';
    }

    public function generate() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=UTF-8');
        if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }

        $db     = Database::getConnection();
        $userId = (int)$_SESSION['user_id'];
        $input  = json_decode(file_get_contents('php://input'), true) ?? [];

        $height = max(100, min(250, (float)($input['height'] ?? 170)));
        $weight = max(30,  min(300, (float)($input['weight'] ?? 65)));
        $age    = max(10,  min(100, (int)  ($input['age']    ?? 22)));
        $goal   = $input['goal']             ?? 'Duy trì sức khỏe';
        $level  = $input['level']            ?? 'Trung bình';
        $sessWk = max(2, min(6, (int)($input['sessions_per_week'] ?? 4)));
        $busy   = $input['busy_schedule']    ?? [];

        $bmi = round($weight / (($height / 100) ** 2), 1);

        $stmtM = $db->prepare("SELECT ma_hoi_vien FROM HOI_VIEN WHERE ma_nguoi_dung = ?");
        $stmtM->execute([$userId]);
        $maHoiVien = $stmtM->fetchColumn();

        $db->prepare("UPDATE HOI_VIEN SET chieu_cao=?, can_nang=? WHERE ma_hoi_vien=?")
           ->execute([$height, $weight, $maHoiVien]);

        $db->prepare("INSERT INTO LICH_BAN_HOI_VIEN (ma_hoi_vien, lich_ban_json)
                      VALUES (?, ?) ON DUPLICATE KEY UPDATE lich_ban_json=VALUES(lich_ban_json), updated_at=NOW()")
           ->execute([$maHoiVien, json_encode($busy, JSON_UNESCAPED_UNICODE)]);

        $planData = $this->_callGemini($height, $weight, $age, $goal, $level, $sessWk, $bmi, $busy);
        if (!$planData) $planData = $this->_fallback($goal, $sessWk, $busy);

        $db->prepare("DELETE FROM PLAN_CA_NHAN WHERE ma_hoi_vien=?")->execute([$maHoiVien]);
        $db->prepare("INSERT INTO PLAN_CA_NHAN (ma_hoi_vien,muc_tieu,chieu_cao,can_nang,bmi,lich_tap,che_do_an) VALUES(?,?,?,?,?,?,?)")
           ->execute([$maHoiVien, $goal, $height, $weight, $bmi,
                      json_encode($planData['workout_plan'], JSON_UNESCAPED_UNICODE),
                      json_encode($planData['diet_plan'],    JSON_UNESCAPED_UNICODE)]);

        echo json_encode(['success' => true]);
    }

    public function checkin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=UTF-8');
        if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }

        $input   = json_decode(file_get_contents('php://input'), true) ?? [];
        $maPlan  = (int)($input['ma_plan'] ?? 0);
        $week    = (int)($input['week']    ?? 1);
        $dayName = trim($input['day']  ?? '');
        $slot    = trim($input['slot'] ?? '');
        if (!$maPlan || !$dayName || !$slot) { echo json_encode(['success'=>false]); exit; }

        $db     = Database::getConnection();
        $userId = (int)$_SESSION['user_id'];

        $own = $db->prepare("SELECT p.ma_plan FROM PLAN_CA_NHAN p JOIN HOI_VIEN hv ON p.ma_hoi_vien=hv.ma_hoi_vien WHERE p.ma_plan=? AND hv.ma_nguoi_dung=?");
        $own->execute([$maPlan, $userId]);
        if (!$own->fetch()) { echo json_encode(['success'=>false,'message'=>'Không có quyền']); exit; }

        $ex = $db->prepare("SELECT ma_checkin FROM PLAN_CHECKIN WHERE ma_plan=? AND week=? AND day_name=? AND slot=?");
        $ex->execute([$maPlan, $week, $dayName, $slot]);
        $row = $ex->fetch();

        if ($row) {
            $db->prepare("DELETE FROM PLAN_CHECKIN WHERE ma_checkin=?")->execute([$row['ma_checkin']]);
            echo json_encode(['success'=>true,'done'=>false]);
        } else {
            $db->prepare("INSERT INTO PLAN_CHECKIN (ma_plan,week,day_name,slot) VALUES(?,?,?,?)")
               ->execute([$maPlan, $week, $dayName, $slot]);
            echo json_encode(['success'=>true,'done'=>true]);
        }
    }

    private function _callGemini($h, $w, $age, $goal, $level, $sessWk, $bmi, $busy) {
        $key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : null;
        if (!$key) return null;

        $bmiNote   = $bmi < 18.5 ? 'Thiếu cân' : ($bmi < 25 ? 'Bình thường' : ($bmi < 30 ? 'Thừa cân' : 'Béo phì'));
        $dayNames  = ['2'=>'Thứ 2','3'=>'Thứ 3','4'=>'Thứ 4','5'=>'Thứ 5','6'=>'Thứ 6','7'=>'Thứ 7','cn'=>'Chủ Nhật'];
        $slotNames = ['sang'=>'Sáng','chieu'=>'Chiều','toi'=>'Tối'];

        $freeLines = [];
        foreach ($dayNames as $dk => $dn) {
            $busySlots = array_map(fn($s) => $slotNames[$s] ?? $s, $busy[$dk] ?? []);
            $free = array_diff(['Sáng','Chiều','Tối'], $busySlots);
            if ($free) $freeLines[] = "- $dn rảnh: " . implode(', ', $free);
        }
        $freeDesc = implode("\n", $freeLines);

        // Sử dụng chuỗi thông thường để tránh lỗi Heredoc
        $prompt = "Bạn là chuyên gia thể hình. Tạo lộ trình 4 tuần. "
                . "Thông tin: Tuổi $age, Cao $h cm, Nặng $w kg, BMI $bmi ($bmiNote). "
                . "Mục tiêu: $goal, Trình độ: $level, Tần suất: $sessWk buổi/tuần. "
                . "Lịch rảnh: $freeDesc. "
                . "QUY TẮC: 1. MỖI NGÀY TỐI ĐA 1 BUỔI TẬP. 2. Dàn trải đều các ngày. "
                . "3. Lịch chia PPL/Upper-Lower. 4. Chi tiết có SetxRep, Rest, Tempo. "
                . "5. Tuần 1 thích nghi, Tuần 2-3 tăng dần, Tuần 4 Deload. "
                . "6. Thực đơn 4 bữa đa dạng món. Trả JSON hợp lệ (workout_plan, diet_plan, advice).";

        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'maxOutputTokens' => 4096,
                'temperature' => 0.5
            ]
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $key;
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 45,
        ]);
        $res = curl_exec($ch); curl_close($ch);

        if ($res) {
            $data   = json_decode($res, true);
            $text   = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($text) {
                $parsed = json_decode(trim($text), true);
                if (json_last_error() === JSON_ERROR_NONE && isset($parsed['workout_plan'])) return $parsed;
            }
        }
        return null;
    }

    private function _fallback($goal, $sessWk, $busy) {
        $isLoss    = str_contains($goal, 'Giảm');
        $dayNames  = ['2'=>'Thứ 2','3'=>'Thứ 3','4'=>'Thứ 4','5'=>'Thứ 5','6'=>'Thứ 6','7'=>'Thứ 7','cn'=>'Chủ Nhật'];
        $slotNames = ['sang'=>'Sáng','chieu'=>'Chiều','toi'=>'Tối'];

        $pool = [];
        foreach ($dayNames as $dk => $dn) {
            $free = array_diff(['sang','chieu','toi'], $busy[$dk] ?? []);
            if (!empty($free)) {
                $sk = reset($free); 
                $pool[] = [$dn, $slotNames[$sk]];
            }
        }
        
        $tpls = [
            ['Ngực & Vai (Push)','Sức mạnh','Bench Press 4x10, Shoulder Press 3x12, Rest 90s',60,'Cao'],
            ['Lưng & Tay trước (Pull)','Kháng lực','Pull-up 4x8, Barbell Row 3x10, Bicep Curl 3x15, Rest 90s',60,'Cao'],
            ['Chân & Mông (Legs)','Nặng','Squat 4x10, Leg Press 3x12, Plank 3x60s, Rest 2m',65,'Rất cao'],
            ['Tim mạch & Core','Cardio','HIIT 20p, Mountain Climber 3x20, Rest 60s',45,'Trung bình'],
            ['Toàn thân','Fullbody','Deadlift 3x8, Push-up 3x20, Lunges 3x12, Rest 2m',60,'Cao'],
        ];

        $workout = [];
        for ($wn = 1; $wn <= 4; $wn++) {
            $week = []; $cnt = 0;
            $finalPool = $pool;
            shuffle($finalPool);
            foreach ($finalPool as $s) {
                if ($cnt >= $sessWk) break;
                $tpl = $tpls[$cnt % count($tpls)];
                $week[] = ['day'=>$s[0],'slot'=>$s[1],'muscle_group'=>$tpl[0],'activity'=>$tpl[1],'details'=>$tpl[2],'duration_min'=>$tpl[3],'intensity'=>$tpl[4]];
                $cnt++;
            }
            usort($week, function($a, $b) use ($dayNames) {
                $da = array_search($a['day'], $dayNames);
                $db = array_search($b['day'], $dayNames);
                return $da <=> $db;
            });
            $workout["week_{$wn}"] = $week;
        }

        $meals = $isLoss
            ? [['meal'=>'Sáng','time'=>'07:00','food'=>'Trứng luộc + Salad ức gà','calories'=>350,'protein_g'=>30],
               ['meal'=>'Trưa','time'=>'12:30','food'=>'Bún gạo lứt + Thịt bò xào rau','calories'=>500,'protein_g'=>35],
               ['meal'=>'Snack','time'=>'16:00','food'=>'Sữa yogurt Hy Lạp + Quả mọng','calories'=>180,'protein_g'=>10],
               ['meal'=>'Tối','time'=>'19:30','food'=>'Cá hồi nướng + Bông cải xanh','calories'=>400,'protein_g'=>40]]
            : [['meal'=>'Sáng','time'=>'07:00','food'=>'Bún bò / Phở gà lớn','calories'=>600,'protein_g'=>35],
               ['meal'=>'Trưa','time'=>'12:30','food'=>'Cơm trắng + Sườn xào chua ngọt + Canh','calories'=>800,'protein_g'=>45],
               ['meal'=>'Snack','time'=>'16:00','food'=>'Sinh tố chuối bơ + Whey','calories'=>400,'protein_g'=>30],
               ['meal'=>'Tối','time'=>'19:30','food'=>'Bít tết + Khoai tây nghiền','calories'=>700,'protein_g'=>50]];

        return [
            'workout_plan' => $workout,
            'diet_plan'    => ['calories_target'=>array_sum(array_column($meals,'calories')),'protein_g'=>array_sum(array_column($meals,'protein_g')),'meals'=>$meals],
            'advice'       => 'Tập trung vào form chuẩn thay vì mức tạ quá nặng để tránh chấn thương.',
        ];
    }
}
