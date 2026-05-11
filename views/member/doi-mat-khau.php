<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi Mật Khẩu | Monkey Gym</title>
    <link rel="icon" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
<?php require __DIR__ . '/layout/topbar.php'; ?>
<main class="mg-main">
    <div class="mg-page-header">
        <h1>Đổi mật khẩu</h1>
        <p>Bảo mật tài khoản của bạn.</p>
    </div>    <div class="db-grid-desktop">
        <!-- CỘT TRÁI: HƯỚNG DẪN BẢO MẬT -->
        <div class="db-side-col">
            <div class="mg-card" style="background: linear-gradient(135deg, #F8FAFC, #EFF6FF); border-color: #DBEAFE;">
                <div class="mg-card-title">🛡️ Bảo mật tài khoản</div>
                <p style="font-size:13px; color:var(--text-muted); line-height:1.6; margin-bottom:15px;">
                    Việc đổi mật khẩu định kỳ giúp tài khoản của bạn luôn an toàn trước các truy cập trái phép.
                </p>
                <div style="font-size:12px; color:var(--text); font-weight:700; margin-bottom:10px;">Lưu ý:</div>
                <ul style="font-size:12px; color:var(--text-muted); padding-left:18px; line-height:1.8;">
                    <li>Nên dùng ít nhất 8 ký tự</li>
                    <li>Kết hợp chữ cái, số và ký tự đặc biệt</li>
                    <li>Không nên trùng với mật khẩu cũ</li>
                    <li>Không chia sẻ mật khẩu cho người khác</li>
                </ul>
            </div>
        </div>

        <!-- CỘT PHẢI: FORM ĐỔI MẬT KHẨU -->
        <div class="db-main-col">
            <div class="mg-card">
                <div class="mg-card-title">🔑 Nhập thông tin mật khẩu mới</div>
                <form method="POST" action="<?= SITE_URL ?>/member/change-password" id="pwForm">
                    <?= csrfField('change_password') ?>

                    <div style="margin-bottom:20px;">
                        <div class="mg-form-label">Mật khẩu hiện tại</div>
                        <input type="password" name="old_password" class="mg-input" required placeholder="Nhập mật khẩu đang dùng">
                    </div>

                    <div style="margin-bottom:20px;">
                        <div class="mg-form-label">Mật khẩu mới</div>
                        <input type="password" name="new_password" id="newPw" class="mg-input" required
                               placeholder="Tối thiểu 8 ký tự" oninput="checkStrength(this.value)">
                        
                        <!-- Thanh cường độ -->
                        <div style="margin-top:12px; height:6px; border-radius:3px; background:#F1F5F9; overflow:hidden">
                            <div id="strengthBar" style="height:100%; width:0%; transition:all .3s; border-radius:3px"></div>
                        </div>
                        <div id="strengthLabel" style="font-size:12px; margin-top:6px; font-weight:700;"></div>
                        
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:15px; background:#F8FAFC; padding:15px; border-radius:12px;">
                            <div id="r-len" style="font-size:11px; color:var(--text-muted);">● Ít nhất 8 ký tự</div>
                            <div id="r-upper" style="font-size:11px; color:var(--text-muted);">● Có chữ hoa (A-Z)</div>
                            <div id="r-num" style="font-size:11px; color:var(--text-muted);">● Có chữ số (0-9)</div>
                            <div id="r-special" style="font-size:11px; color:var(--text-muted);">● Có ký tự đặc biệt</div>
                        </div>
                    </div>

                    <div style="margin-bottom:25px;">
                        <div class="mg-form-label">Xác nhận mật khẩu mới</div>
                        <input type="password" name="confirm_password" id="confirmPw" class="mg-input" required
                               placeholder="Nhập lại mật khẩu mới" oninput="checkMatch()">
                        <div id="matchMsg" style="font-size:12px; margin-top:6px; font-weight:700;"></div>
                    </div>

                    <button type="submit" class="mg-btn mg-btn-primary mg-btn-block" id="submitBtn" disabled>Cập nhật mật khẩu ngay ✨</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function checkStrength(pw) {
    const rules = {
        'r-len':     pw.length >= 8,
        'r-upper':   /[A-Z]/.test(pw),
        'r-num':     /[0-9]/.test(pw),
        'r-special': /[!@#$%^&*(),.?":{}|<>]/.test(pw),
    };
    const score = Object.values(rules).filter(Boolean).length;
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    const colors = ['','#ef4444','#f97316','#eab308','#22c55e'];
    const labels = ['','Rất yếu','Yếu','Trung bình','Mạnh'];
    bar.style.width   = (score * 25) + '%';
    bar.style.background = colors[score] || '#ef4444';
    label.textContent = labels[score] || '';
    label.style.color = colors[score] || '#ef4444';
    Object.entries(rules).forEach(([id, ok]) => {
        const el = document.getElementById(id);
        if (el) { el.style.color = ok ? 'var(--success)' : 'var(--text-muted)';
                  el.style.fontWeight = ok ? '600' : '400'; }
    });
    document.getElementById('submitBtn').disabled = score < 3;
}
function checkMatch() {
    const pw  = document.getElementById('newPw').value;
    const cfm = document.getElementById('confirmPw').value;
    const el  = document.getElementById('matchMsg');
    if (!cfm) { el.textContent=''; return; }
    if (pw === cfm) { el.textContent='✅ Khớp'; el.style.color='var(--success)'; }
    else            { el.textContent='❌ Chưa khớp'; el.style.color='var(--danger)'; }
}
</script>
</body>
</html>

