<?php require_once __DIR__ . '/layout/topbar.php'; ?>

<?php
/* ── PHP data prep ─────────────────────────────────────────────── */
$workout = $diet = [];
$advice = $calsTarget = $proteinTarget = 0;
$totalSessions = $doneSessions = 0;
$activeWeek = 1;

if (!empty($plan)) {
    $workout = json_decode($plan['lich_tap'],  true) ?? [];
    $dietRaw = json_decode($plan['che_do_an'], true) ?? [];
    $diet    = $dietRaw['meals'] ?? (isset($dietRaw[0]['meal']) ? $dietRaw : []);
    $advice  = $dietRaw['advice'] ?? '';
    $calsTarget    = (int)($dietRaw['calories_target'] ?? array_sum(array_column($diet,'calories')));
    $proteinTarget = (int)($dietRaw['protein_g']       ?? array_sum(array_column($diet,'protein_g')));
    $bmi      = (float)($plan['bmi'] ?? 0);
    $bmiLabel = $bmi < 18.5 ? 'Thiếu cân' : ($bmi < 25 ? 'Bình thường' : ($bmi < 30 ? 'Thừa cân' : 'Béo phì'));
    $bmiColor = $bmi < 18.5 ? '#3B82F6' : ($bmi < 25 ? '#10B981' : ($bmi < 30 ? '#F59E0B' : '#EF4444'));

    foreach ($workout as $wk => $days) {
        if (!is_array($days)) continue;
        $wNum = (int)str_replace('week_','',$wk);
        $totalSessions += count($days);
        foreach ($days as $d) {
            if (in_array("{$wNum}_{$d['day']}_{$d['slot']}", $checkins)) $doneSessions++;
        }
    }
    $progress = $totalSessions > 0 ? round($doneSessions / $totalSessions * 100) : 0;

    for ($w = 1; $w <= 4; $w++) {
        $wk = $workout["week_{$w}"] ?? [];
        $allDone = !empty($wk);
        foreach ($wk as $d) { if (!in_array("{$w}_{$d['day']}_{$d['slot']}", $checkins)) { $allDone = false; break; } }
        if (!$allDone) { $activeWeek = $w; break; }
    }

    $mgIcons = ['ngực'=>'💪','vai'=>'🏋️','lưng'=>'🦺','chân'=>'🦵','mông'=>'🦵','bụng'=>'🔥','tay'=>'💪','tim'=>'❤️','cardio'=>'🏃','toàn'=>'⚡','active'=>'🧘'];
    function getMgIcon($mg, $map) { $mg = mb_strtolower($mg,'UTF-8'); foreach($map as $k=>$v) if(str_contains($mg,$k)) return $v; return '🏋️'; }
    $intColors = ['Thấp'=>'#10B981','Trung bình'=>'#F59E0B','Cao'=>'#EF4444','Rất cao'=>'#7C3AED'];
    $mealIcons = ['Sáng'=>'🌅','Trưa'=>'☀️','Snack'=>'🍌','Tối'=>'🌙'];

    // 1. Tính toán trạng thái hoàn thành từng tuần
    $weeksStatus = [];
    for ($w = 1; $w <= 4; $w++) {
        $wkDays = $workout["week_{$w}"] ?? [];
        if (empty($wkDays)) { $weeksStatus[$w] = true; continue; }
        $wkDoneCount = 0;
        foreach ($wkDays as $d) {
            if (in_array("{$w}_{$d['day']}_{$d['slot']}", $checkins)) $wkDoneCount++;
        }
        $weeksStatus[$w] = ($wkDoneCount === count($wkDays));
    }

    // 2. Chuẩn bị ngày bắt đầu (Thứ 2 của tuần tạo Plan)
    $planStart = new DateTime($plan['created_at']);
    $planStart->modify('monday this week');
    $dayMap = ['Thứ 2'=>0, 'Thứ 3'=>1, 'Thứ 4'=>2, 'Thứ 5'=>3, 'Thứ 6'=>4, 'Thứ 7'=>5, 'Chủ Nhật'=>6];
}

$busyJson = json_encode($busySchedule ?? [], JSON_UNESCAPED_UNICODE);
?>

<style>
/* ════════════════════════════════════════════════
   MONKEY AI PLANNER — PC & MOBILE OPTIMIZED
   ════════════════════════════════════════════════ */
:root {
  --g:   #C9993F;
  --gd:  #8B6914;
  --gg:  rgba(201,153,63,0.15);
  --ok:  #10B981;
  --err: #EF4444;
  --bg:  #F4F7FA;
  --card:#FFFFFF;
  --bd:  #E8EDF2;
  --tx:  #0F172A;
  --mu:  #64748B;
  --r:   24px;
}

body { background: var(--bg); }

/* ── Shell ─────────────────────────────────────── */
.pl { max-width: 1200px; margin: 0 auto; padding: 40px 20px 80px; font-family: 'Inter', sans-serif; }
.setup-pl { max-width: 860px; } /* Tăng chiều rộng để dàn hàng ngang đẹp hơn */

/* ── Loading ───────────────────────────────────── */
#aiLoading {
  display:none; position:fixed; inset:0;
  background:rgba(255,255,255,0.98); backdrop-filter:blur(10px);
  z-index:9999; flex-direction:column;
  align-items:center; justify-content:center; gap:20px;
}
#aiLoading.on { display:flex; }
.spin { width:60px; height:60px; border:4px solid var(--bd); border-top-color:var(--g); border-radius:50%; animation:sp .8s linear infinite; }
@keyframes sp { to{transform:rotate(360deg)} }

/* ── Steps ─────────────────────────────────────── */
.steps { 
    display:flex; margin-bottom:32px; background:var(--card); 
    border-radius:18px; overflow:hidden; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.04); 
    border: 1px solid var(--bd);
}
.step-tab { 
    flex:1; padding:22px 10px; text-align:center; font-size:14px; font-weight:700; 
    color:var(--mu); border:none; background:none; cursor:pointer; 
    position:relative; transition:.3s; display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.step-tab.active { color:var(--g); background: linear-gradient(to bottom, #fff, #fdfbf7); }
.step-tab.done   { color:var(--ok); }
.step-tab::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:var(--g); opacity:0; transition:.3s; }
.step-tab.active::after { opacity:1; }
.step-num { display:block; font-size:18px; }

.step-panel { opacity: 0; visibility: hidden; height: 0; overflow: hidden; transition: opacity 0.3s ease; }
.step-panel.on { opacity: 1; visibility: visible; height: auto; overflow: visible; }

/* ── Cards ─────────────────────────────────────── */
.card { background:var(--card); border:1.5px solid var(--bd); border-radius:var(--r); box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
.card-body { padding:40px; }
.setup-pl .card-body { min-height: 580px; position: relative; } /* Chỉ áp dụng cho Wizard */
.card-title { font-size:20px; font-weight:900; color:var(--tx); margin-bottom:24px; display:flex; align-items:center; gap:12px; }

/* ── Form ──────────────────────────────────────── */
.field { margin-bottom:24px; }
.label { display:block; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:1.5px; color:var(--mu); margin-bottom:10px; }
.inp {
  width:100%; padding:14px 18px; border-radius:14px;
  border:2px solid var(--bd); font-size:16px; font-weight:600;
  color:var(--tx); background:#F8FAFC; outline:none; transition:all .2s;
}
.inp:focus { border-color:var(--g); background:#fff; box-shadow: 0 0 0 4px var(--gg); }

.row2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.row3 { display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; }

/* Goal square buttons */
.goals { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.goal-pill { 
    padding:30px 10px; border-radius:20px; border:2px solid var(--bd); background:#fff;
    cursor:pointer; text-align:center; transition:.3s; 
}
.goal-pill .gi { font-size:40px; display:block; margin-bottom:12px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); }
.goal-pill .gl { font-size:14px; font-weight:800; color:var(--tx); }
.goal-pill.on { border-color:var(--g); background: #fdfaf3; transform: scale(1.02); box-shadow: 0 10px 25px var(--gg); }

/* Level capsules */
.levels { display:flex; gap:12px; flex-wrap:wrap; }
.lv-pill { 
    padding:12px 24px; border-radius:30px; border:1.5px solid var(--bd); font-size:14px;
    font-weight:700; cursor:pointer; background:#fff; transition:.2s; color:var(--mu);
}
.lv-pill.on { border-color:var(--g); background:var(--g); color:#fff; box-shadow: 0 6px 15px var(--gg); }

/* BMI Box — Matches image */
.bmi-box { 
    display:flex; align-items:center; gap:20px; padding:24px; 
    background: #F8FAFC; border:1.5px solid var(--bd); border-radius:20px; 
}
.bmi-val { font-size:36px; font-weight:900; color: var(--err); line-height: 1; }
.bmi-cat { font-size:18px; font-weight:800; color: var(--err); }
.bmi-info-lbl { font-size:12px; font-weight:800; color:var(--mu); letter-spacing:1px; margin-bottom:4px; }

/* Busy grid */
.busy-grid { overflow-x:auto; background: #fff; padding: 12px; border-radius: 16px; border: 1px solid var(--bd); }
.busy-table { width:100%; border-collapse:separate; border-spacing:8px; }
.busy-table th { font-size:11px; font-weight:900; color:var(--mu); padding:10px; letter-spacing:1px; }
.slot-cell {
  width:100%; padding:12px 2px; border-radius:10px; font-size:13px; /* Thu nhỏ để tiết kiệm diện tích */
  font-weight:700; text-align:center; cursor:pointer;
  border:2px solid var(--bd); background:#F8FAFC; color:var(--mu); transition:.2s;
}
.slot-cell.busy { background:#FEE2E2; border-color:#FCA5A5; color:#B91C1C; transform: scale(0.96); }

/* Buttons */
.btn { 
    padding:18px 30px; border-radius:18px; font-size:16px; font-weight:900;
    cursor:pointer; border:none; transition:.3s; display:flex; align-items:center; justify-content:center; gap:12px;
}
.btn-primary { background: linear-gradient(135deg, #C9993F, #8B6914); color:#fff; box-shadow: 0 10px 25px rgba(201,153,63,0.25); width:100%; }
.btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(201,153,63,0.35); }

/* Plan View */
.plan-hdr h2 { font-size:32px; font-weight:900; letter-spacing:-1px; }
.stats { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:32px; }
.stat { background:#fff; border:1px solid var(--bd); border-radius:24px; padding:24px; }
.stat-val { font-size:26px; font-weight:900; }

/* Week tabs */
.week-tabs { display:flex; gap:8px; margin-bottom:20px; overflow-x:auto; padding-bottom:4px; }
.wtab { padding:12px 24px; border-radius:24px; font-size:14px; font-weight:700; cursor:pointer;
  border:1.5px solid var(--bd); background:var(--card); color:var(--mu); transition:.2s;
  white-space:nowrap; font-family:inherit; }
.wtab.on { background:var(--g); border-color:var(--g); color:#fff; box-shadow: 0 4px 12px var(--gg); }

/* Workout cards */
.wcard { background:var(--card); border:1.5px solid var(--bd); border-radius:var(--r);
  padding:24px; margin-bottom:16px; display:flex; gap:20px; align-items:flex-start; transition:.2s; position: relative; }
.wcard::after { content:''; position:absolute; left:0; top:20%; bottom:20%; width:4px; background:var(--g); border-radius:0 4px 4px 0; opacity:0; transition:.2s; }
.wcard:hover::after { opacity:1; }
.wcard:hover { box-shadow:0 12px 40px rgba(0,0,0,.08); transform:translateX(4px); }
.wcard.done { background:#F0FDF4; border-color:#BBF7D0; }
.wcard.done::after { background: var(--ok); opacity:1; }

.wcard-day { width:52px; height:52px; border-radius:14px; background:var(--bg); border:1px solid var(--bd);
  display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0; }
.wcard-day .dn { font-size:20px; font-weight:900; color:var(--g); }

.wcard-body { flex:1; min-width:0; }
.wcard-meta { font-size:11px; font-weight:800; color:var(--mu); text-transform:uppercase; letter-spacing:1px; }
.wcard-title { font-size:18px; font-weight:900; color:var(--tx); margin:4px 0 6px; }
.wcard-det { font-size:14px; color:var(--mu); line-height:1.6; }
.wcard-action { flex-shrink:0; }

.btn-ci { padding:10px 20px; border-radius:12px; border:2px solid var(--g);
  background:transparent; color:var(--g); font-size:13px; font-weight:800;
  cursor:pointer; white-space:nowrap; transition:.2s; min-width:110px; }
.btn-ci:hover { background:var(--g); color:#fff; }
.btn-ci.done { background:var(--ok); border-color:var(--ok); color:#fff; cursor:default; }

/* Diet section */
.diet-header { padding:20px 24px; border-bottom:1px solid var(--bd); display:flex; justify-content:space-between; align-items:center; }
.diet-header h3 { font-size:16px; font-weight:800; margin:0; }
.diet-macro { text-align:right; }
.dm-kcal { font-size:18px; font-weight:900; color:var(--g); }
.meal-row { display:flex; gap:16px; align-items:center; padding:16px 24px; border-bottom:1px solid var(--bd); }
.meal-row:last-child { border-bottom:none; }
.meal-body { flex:1; min-width:0; }
.meal-name { font-size:11px; font-weight:800; color:var(--ok); text-transform:uppercase; letter-spacing:1px; }
.meal-food { font-size:14px; font-weight:700; color:var(--tx); line-height:1.5; }
.meal-cal { font-size:14px; font-weight:900; color:var(--g); white-space:nowrap; flex-shrink:0; text-align:right; }

/* Advice */
.advice { padding:20px; background:var(--gg); border:1px solid rgba(201,153,63,.2); border-radius:var(--r); font-size:14px; line-height:1.7; }

@media (min-width: 992px) {
    .plan-grid { display: grid; grid-template-columns: 1fr 400px; gap: 32px; align-items: start; }
}

@media (max-width: 768px) {
    .card-body { padding: 24px; }
    .goals { grid-template-columns: 1fr; }
    .stats { grid-template-columns: repeat(2,1fr); }
    .row3 { grid-template-columns: 1fr; }
}
</style>

<!-- Loading -->
<div id="aiLoading">
  <div class="spin"></div>
  <div style="font-size:20px;font-weight:900;color:var(--tx)">🤖 Monkey AI is thinking...</div>
  <div style="font-size:15px;color:var(--mu)">Đang phân tích chỉ số và lịch bận của bạn</div>
</div>

<main class="mg-main">
<div class="pl <?= empty($plan) ? 'setup-pl' : '' ?>">

<?php if (empty($plan)): ?>
<!-- ════════ SETUP WIZARD (Matches Image) ════════ -->

<div style="text-align:center;margin-bottom:40px">
  <img src="https://cdn-icons-png.flaticon.com/512/4712/4712035.png" width="80" style="margin-bottom:16px; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1))">
  <h1 style="font-size:36px;font-weight:900;margin:0;letter-spacing:-1.5px">Monkey <span style="color:var(--g)">AI</span> Planner</h1>
  <p style="font-size:16px;color:var(--mu);margin:10px 0 0">Giải pháp tập luyện thông minh, tự động tránh lịch bận của bạn</p>
</div>

<!-- Tabs -->
<div class="steps" id="stepTabs">
  <button class="step-tab active" id="st1" onclick="goStep(1)">
    <span class="step-num">📋</span><strong>01 Thông tin</strong>
  </button>
  <button class="step-tab" id="st2" onclick="goStep(2)">
    <span class="step-num">📅</span><strong>02 Lịch bận</strong>
  </button>
  <button class="step-tab" id="st3" onclick="goStep(3)">
    <span class="step-num">✨</span><strong>03 Xác nhận</strong>
  </button>
</div>

<!-- ── STEP 1: Thông tin ─────────────── -->
<div class="step-panel on card" id="panel1">
<div class="card-body">
  <div class="card-title">🎯 Mục tiêu & Chỉ số cơ thể</div>

  <div class="field">
    <span class="label">Mục tiêu ưu tiên</span>
    <div class="goals" id="goalGrid">
      <button class="goal-pill on" data-val="Giảm cân" onclick="selectGoal(this)">
        <span class="gi">🔥</span><span class="gl">Giảm cân</span>
      </button>
      <button class="goal-pill" data-val="Tăng cơ" onclick="selectGoal(this)">
        <span class="gi">💪</span><span class="gl">Tăng cơ</span>
      </button>
      <button class="goal-pill" data-val="Duy trì sức khỏe" onclick="selectGoal(this)">
        <span class="gi">🧘</span><span class="gl">Sức khỏe</span>
      </button>
    </div>
  </div>

  <div class="field">
    <span class="label">Trình độ hiện tại</span>
    <div class="levels" id="levelGrid">
      <button class="lv-pill" data-val="Mới bắt đầu" onclick="selectLevel(this)">🌱 Mới bắt đầu</button>
      <button class="lv-pill on" data-val="Trung bình" onclick="selectLevel(this)">⚡ Trung bình</button>
      <button class="lv-pill" data-val="Nâng cao" onclick="selectLevel(this)">🔥 Nâng cao</button>
    </div>
  </div>

  <div class="row3">
    <div class="field">
      <label class="label">Tuổi của bạn</label>
      <input type="number" id="sAge" class="inp" value="22">
    </div>
    <div class="field">
      <label class="label">Chiều cao (cm)</label>
      <input type="number" id="sHeight" class="inp" value="<?= (int)($member['chieu_cao'] ?: 170) ?>">
    </div>
    <div class="field">
      <label class="label">Cân nặng (kg)</label>
      <input type="number" id="sWeight" class="inp" value="<?= (int)($member['can_nang'] ?: 65) ?>">
    </div>
  </div>

  <!-- BMI Box (Matches Image) -->
  <div class="bmi-box" style="margin-bottom:30px">
    <img src="https://cdn-icons-png.flaticon.com/512/1611/1611179.png" width="44">
    <div>
      <div class="bmi-info-lbl">CHỈ SỐ BMI HIỆN TẠI</div>
      <div style="display:flex; align-items:baseline; gap:12px">
        <div class="bmi-val" id="bmiVal">--</div>
        <div class="bmi-cat" id="bmiCat">--</div>
      </div>
    </div>
  </div>

  <div class="field">
    <span class="label">Số buổi tập mong muốn / tuần: <span id="sessLbl" style="color:var(--g)">4</span></span>
    <div style="display:flex; align-items:center; gap:20px; background:#F8FAFC; padding:15px 25px; border-radius:15px">
      <span style="font-size:14px;font-weight:800;color:var(--mu)">2</span>
      <input type="range" id="sSess" min="2" max="6" value="4" oninput="document.getElementById('sessLbl').textContent=this.value" style="flex:1; accent-color:var(--g)">
      <span style="font-size:14px;font-weight:800;color:var(--mu)">6</span>
    </div>
  </div>

  <button class="btn btn-primary" onclick="goStep(2)">Tiếp theo: Thiết lập lịch bận →</button>
</div>
</div>

<!-- ── STEP 2 & 3: Keep existing logic ──────── -->
<div class="step-panel card" id="panel2">
<div class="card-body">
  <div class="card-title">📅 Thời gian biểu của bạn</div>
  <p style="color:var(--mu); font-size:15px; margin-bottom:20px">Đánh dấu đỏ 🔴 vào các khung giờ bạn bận. AI sẽ không xếp lịch tập vào đó.</p>
  <div class="busy-grid">
    <table class="busy-table" id="busyTable">
      <thead><tr><th></th><th>T2</th><th>T3</th><th>T4</th><th>T5</th><th>T6</th><th>T7</th><th>CN</th></tr></thead>
      <tbody>
        <?php foreach ([['sang','Sáng','🌅'],['chieu','Chiều','🌞'],['toi','Tối','🌙']] as [$sk,$sn,$si]): ?>
        <tr>
          <td style="font-size:13px;font-weight:800;color:var(--mu);text-align:right;padding-right:15px"><?= $si ?> <?= $sn ?></td>
          <?php foreach (['2','3','4','5','6','7','cn'] as $dk): ?>
          <td><div class="slot-cell <?= in_array($sk, $busySchedule[$dk]??[]) ? 'busy' : '' ?>" data-day="<?= $dk ?>" data-slot="<?= $sk ?>" onclick="toggleSlot(this)"><?= in_array($sk, $busySchedule[$dk]??[]) ? '🔴' : '✓' ?></div></td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="display:flex; gap:15px; margin-top:30px">
    <button class="btn btn-outline" style="flex:1" onclick="goStep(1)">← Quay lại</button>
    <button class="btn btn-primary" style="flex:1" onclick="goStep(3)">Tiếp theo →</button>
  </div>
</div>
</div>

<div class="step-panel card" id="panel3">
<div class="card-body">
  <div class="card-title">✨ Xác nhận lộ trình</div>
  <div style="background:#F8FAFC; padding:25px; border-radius:20px; border:1px solid var(--bd); margin-bottom:25px">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; font-size:16px">
      <div><span style="color:var(--mu)">Mục tiêu:</span> <strong id="sum_goal">—</strong></div>
      <div><span style="color:var(--mu)">Trình độ:</span> <strong id="sum_level">—</strong></div>
      <div><span style="color:var(--mu)">BMI:</span> <strong id="sum_bmi">—</strong></div>
      <div><span style="color:var(--mu)">Tần suất:</span> <strong id="sum_sess">—</strong></div>
    </div>
  </div>
  <button class="btn btn-primary" onclick="generatePlan()" id="btnGen">🚀 Kích hoạt Monkey AI Planner</button>
</div>
</div>

<?php else: ?>
<!-- ════════ ACTIVE PLAN (Responsive) ════════ -->

<div class="plan-container">
    <div class="plan-hdr" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px">
      <div>
        <h2>🚀 Lộ trình cá nhân hóa</h2>
        <p style="color:var(--mu)">Cập nhật: <?= date('d/m/Y', strtotime($plan['created_at'])) ?> · BMI: <?= $bmi ?></p>
      </div>
      <button class="btn btn-outline" style="padding:10px 20px" onclick="if(confirm('Reset?'))location.href='?reset=1'">🔄 Reset</button>
    </div>

    <div class="stats">
      <div class="stat"><div class="stat-icon">⚖️</div><div class="stat-lbl">BMI</div><div class="stat-val" style="color:<?= $bmiColor ?>"><?= $bmi ?></div></div>
      <div class="stat"><div class="stat-icon">📈</div><div class="stat-lbl">Tiến độ</div><div class="stat-val"><?= $progress ?>%</div></div>
      <div class="stat"><div class="stat-icon">📅</div><div class="stat-lbl">Giai đoạn</div><div class="stat-val">Tuần <?= $activeWeek ?></div></div>
      <div class="stat"><div class="stat-icon">🔥</div><div class="stat-lbl">Mục tiêu</div><div class="stat-val"><?= number_format($calsTarget) ?> <span style="font-size:12px">KCAL</span></div></div>
    </div>

    <div class="plan-grid">
        <div id="panelWorkout">
          <div class="card" style="margin-bottom:20px">
            <div class="card-body" style="padding:15px">
              <div class="week-tabs">
                <?php for($w=1;$w<=4;$w++): ?>
                <button class="wtab <?= $w==$activeWeek?'on':'' ?>" onclick="switchWeek(<?= $w ?>)" data-week="<?= $w ?>">Tuần <?= $w ?></button>
                <?php endfor; ?>
              </div>
            </div>
          </div>

          <?php for($w=1;$w<=4;$w++): $wkDays = $workout["week_$w"]??[]; ?>
          <div class="week-pnl" id="wpnl<?= $w ?>" style="<?= $w!=$activeWeek?'display:none':'' ?>">
            <?php foreach($wkDays as $idx => $day): 
              $isDone = in_array("{$w}_{$day['day']}_{$day['slot']}", $checkins);
              
              // Tính ngày thực tế
              $dOff = $dayMap[$day['day']] ?? 0;
              $dObj = clone $planStart;
              $dObj->modify("+".(($w-1)*7 + $dOff)." days");
              $realDate = $dObj->format('d/m');

              // Kiểm tra ngày thực tế (Chưa tới ngày thì kh cho check-in)
              $today = new DateTime('today');
              $isFuture = ($dObj > $today);

              // Kiểm tra xem có được phép check-in không (tuần trước phải xong)
              $isLocked = ($w > 1 && !$weeksStatus[$w-1]);
            ?>
            <div class="wcard <?= $isDone?'done':'' ?> <?= ($isLocked || $isFuture)?'locked':'' ?>" id="wc-<?= $w ?>-<?= $idx ?>">
              <div class="wcard-day">
                <div class="dn"><?= str_replace('Thứ ','T',$day['day']) ?></div>
                <div style="font-size:10px; font-weight:800; color:var(--mu)"><?= $realDate ?></div>
              </div>
              <div class="wcard-body">
                <div class="wcard-meta"><?= $day['slot'] ?> · <?= $day['muscle_group']??'' ?></div>
                <div class="wcard-title"><?= $day['activity'] ?></div>
                <div class="wcard-det"><?= $day['details'] ?></div>
              </div>
              <div class="wcard-action">
                <?php if ($isDone): ?>
                  <button class="btn-ci done" disabled>✅ Xong</button>
                <?php elseif ($isLocked): ?>
                  <button class="btn-ci" disabled title="Hoàn thành tuần trước để mở khóa" style="opacity:0.5; cursor:not-allowed">🔒 Khóa tuần</button>
                <?php elseif ($isFuture): ?>
                  <button class="btn-ci" disabled title="Chưa tới ngày tập" style="opacity:0.5; cursor:not-allowed; background:#f1f5f9; border-color:#cbd5e1; color:#94a3b8">🔒 Chưa tới ngày</button>
                <?php else: ?>
                  <button class="btn-ci" onclick="doCheckin(this,<?= $plan['ma_plan'] ?>,<?= $w ?>,'<?= addslashes($day['day']) ?>','<?= addslashes($day['slot']) ?>',<?= $idx ?>)">🎯 Tập</button>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endfor; ?>
        </div>

        <div class="diet-sidebar">
          <div class="card">
            <div class="diet-header"><h3>🥗 Dinh dưỡng</h3><div class="dm-kcal"><?= number_format($calsTarget) ?> kcal</div></div>
            <?php foreach($diet as $m): ?>
            <div class="meal-row">
              <div class="meal-body"><div class="meal-name"><?= $m['meal'] ?></div><div class="meal-food"><?= $m['food'] ?></div></div>
              <div class="meal-cal"><?= $m['calories'] ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if(!empty($advice)): ?><div class="advice" style="margin-top:20px"><?= $advice ?></div><?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

</div>
</main>

<script>
let selGoal = 'Giảm cân', selLevel = 'Trung bình', busyData = <?= $busyJson ?>;
function selectGoal(b){ document.querySelectorAll('.goal-pill').forEach(x=>x.classList.remove('on')); b.classList.add('on'); selGoal=b.dataset.val; }
function selectLevel(b){ document.querySelectorAll('.lv-pill').forEach(x=>x.classList.remove('on')); b.classList.add('on'); selLevel=b.dataset.val; }
function updateBmi(){
  const h = parseFloat(document.getElementById('sHeight')?.value), w = parseFloat(document.getElementById('sWeight')?.value);
  if(!h||!w) return; const bmi = (w/((h/100)**2)).toFixed(1);
  const el=document.getElementById('bmiVal'), cat=document.getElementById('bmiCat');
  if(el){ el.textContent=bmi; const [l,c] = bmi<18.5?['Thiếu cân','#3B82F6']:bmi<25?['Bình thường','#10B981']:bmi<30?['Thừa cân','#F59E0B']:['Béo phì','#EF4444']; cat.textContent=l; cat.style.color=c; el.style.color=c; }
}
document.getElementById('sHeight')?.addEventListener('input', updateBmi); document.getElementById('sWeight')?.addEventListener('input', updateBmi); updateBmi();
function toggleSlot(el){
  const d=el.dataset.day, s=el.dataset.slot; if(!busyData[d]) busyData[d]=[];
  const i=busyData[d].indexOf(s); if(i===-1){ busyData[d].push(s); el.classList.add('busy'); el.textContent='🔴'; }
  else { busyData[d].splice(i,1); el.classList.remove('busy'); el.textContent='✓'; }
}
function goStep(n){
  [1,2,3].forEach(i=>{ document.getElementById('panel'+i)?.classList.remove('on'); document.getElementById('st'+i)?.classList.remove('active'); });
  document.getElementById('panel'+n)?.classList.add('on'); document.getElementById('st'+n)?.classList.add('active');
  if(n===3){ document.getElementById('sum_goal').textContent=selGoal; document.getElementById('sum_level').textContent=selLevel; document.getElementById('sum_bmi').textContent=document.getElementById('bmiVal').textContent; document.getElementById('sum_sess').textContent=document.getElementById('sSess').value+' buổi'; }
}
async function generatePlan(){
  document.getElementById('aiLoading').classList.add('on');
  const res = await fetch('<?= SITE_URL ?>/member/planner/generate', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ height:parseFloat(document.getElementById('sHeight').value), weight:parseFloat(document.getElementById('sWeight').value), age:parseInt(document.getElementById('sAge').value), goal:selGoal, level:selLevel, sessions_per_week:parseInt(document.getElementById('sSess').value), busy_schedule:busyData })
  });
  if((await res.json()).success) window.location.reload(); else alert('Lỗi');
}
function switchWeek(w){ document.querySelectorAll('.week-pnl').forEach(p=>p.style.display='none'); document.querySelectorAll('[data-week]').forEach(t=>t.classList.remove('on')); document.getElementById('wpnl'+w).style.display='block'; document.querySelector('[data-week="'+w+'"]').classList.add('on'); }
async function doCheckin(btn,ma,w,d,s,idx){
  const res = await fetch('<?= SITE_URL ?>/member/planner/checkin', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ma_plan:ma,week:w,day:d,slot:s}) });
  const data = await res.json(); if(data.success) { btn.classList.add('done'); btn.textContent='✅ Xong'; document.getElementById('wc-'+w+'-'+idx).classList.add('done'); }
}
</script>
