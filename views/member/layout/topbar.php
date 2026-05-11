<?php
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$ho_ten = $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? 'Hội viên';
$initial = mb_strtoupper(mb_substr($ho_ten, 0, 1));

function mgTab(string $href, string $icon, string $label, string $uri, bool $ai = false): string {
    $base   = parse_url($href, PHP_URL_PATH);
    $active = ($base && (rtrim($uri,'/') === rtrim($base,'/') || strpos($uri, $base.'/') === 0)) ? ' active' : '';
    $dot    = $ai ? '<span class="ai-dot"></span>' : '';
    $cls    = $ai ? ' ai-tab' : '';
    return "<a href=\"$href\" class=\"mg-nav-tab$cls$active\"><span class=\"tab-icon\">$icon</span>$dot<span>$label</span></a>";
}

function sdItem(string $href, string $icon, string $label, string $uri): string {
    $base   = parse_url($href, PHP_URL_PATH);
    $active = ($base && (rtrim($uri,'/') === rtrim($base,'/') || strpos($uri, $base.'/') === 0)) ? ' active' : '';
    return "<a href=\"$href\" class=\"sd-item$active\"><span class=\"si\">$icon</span>$label</a>";
}
?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/css/member-premium.css">

<!-- Desktop Sidebar (hidden on mobile via CSS) -->
<aside class="mg-sidebar-desktop">
    <div class="sd-logo">Monkey <span>Gym</span></div>
    
    <!-- Nút Check-in QR Ưu tiên trên Desktop -->
    <div style="padding: 10px 20px 20px 20px;">
        <button class="mg-qr-btn" onclick="openQrModal()" 
                style="width: 100%; height: 50px; justify-content: center; background: var(--gold); color: #fff; border: none; border-radius: 12px; cursor: pointer; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 16px rgba(201,153,63,0.25);">
            <span style="font-size: 22px;">🔳</span>
            <span style="font-weight: 900; letter-spacing: 0.5px;">QUÉT MÃ CHECK-IN</span>
        </button>
    </div>

    <nav class="sd-nav">
        <div class="sd-label">AI</div>
        <?= sdItem(SITE_URL.'/member/planner',          '🤖', 'Gym Planner AI', $uri) ?>
        <div class="sd-label">Tập luyện</div>
        <?= sdItem(SITE_URL.'/member/dashboard',         '🏠', 'Tổng quan', $uri) ?>
        <?= sdItem(SITE_URL.'/member/booking',           '🗓️', 'Đặt lịch PT', $uri) ?>
        <?= sdItem(SITE_URL.'/member/journal',           '📓', 'Nhật ký', $uri) ?>
        <div class="sd-label">Dịch vụ</div>
        <?= sdItem(SITE_URL.'/member/store',             '🛒', 'Cửa hàng', $uri) ?>
        <?= sdItem(SITE_URL.'/member/cart',              '🛍️', 'Giỏ hàng', $uri) ?>
        <?= sdItem(SITE_URL.'/member/locker',            '🔒', 'Tủ đồ', $uri) ?>
        <?= sdItem(SITE_URL.'/member/reviews',           '⭐', 'Đánh giá HLV', $uri) ?>
        <div class="sd-label">Tài khoản</div>
        <?= sdItem(SITE_URL.'/member/change-password',   '🔑', 'Đổi mật khẩu', $uri) ?>
    </nav>
    <div class="sd-footer">
        <div class="sd-user" style="display: flex; align-items: center; width: 100%; overflow: hidden;">
            <div class="sd-avatar" style="flex-shrink: 0;"><?= $initial ?></div>
            <div style="flex: 1; min-width: 0; margin: 0 10px;">
                <span class="sd-name" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; font-size: 13px; font-weight: 700; color: #fff;"><?= htmlspecialchars($ho_ten) ?></span>
                <span class="sd-role" style="font-size: 11px; color: #9CA3AF; display: block;">Hội viên</span>
            </div>
            <a href="<?= SITE_URL ?>/logout" class="sd-logout" title="Đăng xuất" style="flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: rgba(239, 68, 68, 0.15); color: #EF4444; border-radius: 8px; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
        </div>
    </div>
</aside>

<!-- Wrapper for desktop layout -->
<div class="mg-layout-wrapper">

<!-- Mobile Top Bar -->
<header class="mg-topbar">
    <div class="mg-topbar-logo">Monkey <span>Gym</span></div>
    <div class="mg-topbar-right">
        <!-- QR Button — nổi bật, 1 tap hiện mã -->
        <button class="mg-qr-btn" id="topbarQrBtn" onclick="openQrModal()" title="Thẻ QR check-in">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="3" height="3" rx="0.5"/>
                <rect x="19" y="14" width="2" height="2"/>
                <rect x="14" y="19" width="2" height="2"/>
                <rect x="18" y="18" width="3" height="3" rx="0.5"/>
            </svg>
            <span>Check-in</span>
        </button>
        <button class="mg-avatar-btn" onclick="document.getElementById('userMenu').classList.toggle('show')"><?= $initial ?></button>
    </div>
</header>

<!-- User Dropdown (mobile) -->
<div id="userMenu" style="display:none; position:fixed; top:56px; right:12px; z-index:500; background:#fff; border:1px solid var(--border); border-radius:14px; box-shadow:0 8px 24px rgba(0,0,0,0.12); min-width:220px; padding:8px 0;">
    <div style="padding:12px 16px; border-bottom:1px solid var(--border);">
        <div style="font-weight:700; font-size:14px;"><?= htmlspecialchars($ho_ten) ?></div>
        <div style="font-size:12px; color:var(--text-muted);">Hội viên Monkey Gym</div>
    </div>
    <div style="padding:4px 12px 2px; font-size:10px; font-weight:700; color:var(--text-muted); letter-spacing:0.5px; text-transform:uppercase; margin-top:6px;">Tập luyện</div>
    <a href="<?= SITE_URL ?>/member/dashboard"         style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:var(--text);">🏠 Tổng quan</a>
    <a href="<?= SITE_URL ?>/member/booking"            style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:var(--text);">🗓️ Đặt lịch PT</a>
    <a href="<?= SITE_URL ?>/member/journal"             style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:var(--text);">📓 Nhật ký</a>
    <a href="<?= SITE_URL ?>/member/planner"             style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:var(--text);">🤖 AI Planner</a>
    <div style="height:1px;background:var(--border);margin:4px 12px;"></div>
    <div style="padding:4px 12px 2px; font-size:10px; font-weight:700; color:var(--text-muted); letter-spacing:0.5px; text-transform:uppercase;">Dịch vụ</div>
    <a href="<?= SITE_URL ?>/member/store"               style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:var(--text);">🛒 Cửa hàng</a>
    <a href="<?= SITE_URL ?>/member/cart"                 style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:var(--text);">🛍️ Giỏ hàng</a>
    <a href="<?= SITE_URL ?>/member/locker"               style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:var(--text);">🔒 Tủ đồ</a>
    <a href="<?= SITE_URL ?>/member/reviews"              style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:var(--text);">⭐ Đánh giá HLV</a>
    <div style="height:1px;background:var(--border);margin:4px 12px;"></div>
    <div style="padding:4px 12px 2px; font-size:10px; font-weight:700; color:var(--text-muted); letter-spacing:0.5px; text-transform:uppercase;">Tài khoản</div>
    <a href="<?= SITE_URL ?>/member/change-password"      style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:var(--text);">🔑 Đổi mật khẩu</a>
    <div style="height:1px;background:var(--border);margin:4px 0;"></div>
    <a href="<?= SITE_URL ?>/logout" style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:13px;color:#EF4444;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        Đăng xuất
    </a>
</div>

<!-- AI CHATBOT -->
<?php require_once __DIR__ . '/../../components/chatbot.php'; ?>

<!-- ═══ GLOBAL QR MODAL (hiện được từ mọi trang) ═══ -->
<?php
    // Lấy thông tin QR từ session/database
    $qrMemberId  = $_SESSION['user_id'] ?? 0;
    $qrHoten     = $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? 'Hội viên';
    // Lấy mã QR thực từ DB nếu có
    $qrCode = '';
    try {
        $dbQR = new Database();
        $hvQR = $dbQR->selectOne(
            "SELECT hv.ma_hoi_vien, hv.ma_qr FROM HOI_VIEN hv WHERE hv.ma_nguoi_dung = ?",
            [$qrMemberId]
        );
        if ($hvQR) {
            $qrCode     = $hvQR['ma_qr'];
            $qrMemberId = $hvQR['ma_hoi_vien'];
        }
    } catch (Exception $e) { $qrCode = 'MG_' . $qrMemberId; }
?>
<div id="globalQrModal" class="mg-qr-overlay" onclick="if(event.target===this) closeQrModal()">
    <div class="mg-qr-sheet">
        <div class="mg-qr-handle"></div>
        <div class="mg-qr-header">
            <div>
                <div class="mg-qr-title">📷 Thẻ Check-in</div>
                <div class="mg-qr-sub"><?= htmlspecialchars($qrHoten) ?></div>
            </div>
            <button class="mg-qr-close" onclick="closeQrModal()">✕</button>
        </div>
        <div class="mg-qr-body">
            <div class="mg-qr-box">
                <img id="globalQrImg"
                     src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=12&data=<?= rawurlencode($qrCode) ?>"
                     alt="QR Check-in" width="260" height="260">
                <div class="mg-qr-countdown">Tự đổi sau <strong id="globalQrTimer">60</strong>s</div>
            </div>
            <div class="mg-qr-code-text">Mã HV: <?= htmlspecialchars($qrMemberId) ?></div>
            <p class="mg-qr-hint">📍 Quét tại quầy lễ tân để điểm danh hoặc mở cổng</p>
        </div>
    </div>
</div>

<!-- Bottom Nav (mobile) -->
<nav class="mg-bottom-nav">
    <?= mgTab(SITE_URL.'/member/dashboard', '🏠', 'Tổng quan',   $uri) ?>
    <?= mgTab(SITE_URL.'/member/planner',   '🤖', 'AI Planner',  $uri, true) ?>
    <?= mgTab(SITE_URL.'/member/booking',   '🗓️', 'Đặt lịch',    $uri) ?>
    <?= mgTab(SITE_URL.'/member/store',     '🛒', 'Cửa hàng',    $uri) ?>
    <?= mgTab(SITE_URL.'/member/journal',   '📓', 'Nhật ký',     $uri) ?>
</nav>

<script>
// ── Dropdown toggle ──────────────────────────────────────────────
document.addEventListener('click', function(e) {
    const menu = document.getElementById('userMenu');
    if (menu && !menu.contains(e.target) && !e.target.closest('.mg-avatar-btn')) {
        menu.style.display = 'none';
    }
});
document.querySelector('.mg-avatar-btn')?.addEventListener('click', function(e) {
    e.stopPropagation();
    const menu = document.getElementById('userMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
});

// ── Smooth page transition ───────────────────────────────────────
document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript"]):not([target])').forEach(a => {
    a.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || href.startsWith('mailto') || href.startsWith('tel')) return;
        if (this.closest('form')) return;
        e.preventDefault();
        document.body.style.opacity = '0';
        document.body.style.transition = 'opacity 0.18s ease';
        setTimeout(() => window.location.href = href, 180);
    });
});
document.body.style.opacity = '0';
requestAnimationFrame(() => {
    document.body.style.transition = 'opacity 0.22s ease';
    document.body.style.opacity = '1';
});

// ── Swipe navigation between bottom tabs ────────────────────────
(function() {
    const TABS = [
        '<?= SITE_URL ?>/member/dashboard',
        '<?= SITE_URL ?>/member/planner',
        '<?= SITE_URL ?>/member/booking',
        '<?= SITE_URL ?>/member/store',
        '<?= SITE_URL ?>/member/journal',
    ];

    // Find current tab index
    const cur = window.location.pathname;
    let curIdx = TABS.findIndex(t => {
        const p = t.replace(/^https?:\/\/[^/]+/, '');
        return cur === p || cur.startsWith(p + '/');
    });
    if (curIdx < 0) curIdx = 0;

    let startX = 0, startY = 0, startTime = 0;
    const SWIPE_MIN  = 60;   // minimum px for swipe
    const SWIPE_TIME = 350;  // max ms for swipe
    const RATIO_MAX  = 1.2;  // horizontal must dominate

    document.addEventListener('touchstart', e => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        startTime = Date.now();
    }, { passive: true });

    document.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - startX;
        const dy = e.changedTouches[0].clientY - startY;
        const dt = Date.now() - startTime;
        if (dt > SWIPE_TIME) return;
        if (Math.abs(dx) < SWIPE_MIN) return;
        if (Math.abs(dy) / Math.abs(dx) > RATIO_MAX) return; // more vertical than horizontal → scroll

        // Don't swipe if touching a scrollable element (like planner calendar)
        const el = document.elementFromPoint(startX, startY);
        if (el && el.closest('.no-swipe, [data-no-swipe]')) return;

        if (dx < 0 && curIdx < TABS.length - 1) {
            // Swipe left → next tab
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.18s ease';
            setTimeout(() => window.location.href = TABS[curIdx + 1], 180);
        } else if (dx > 0 && curIdx > 0) {
            // Swipe right → previous tab
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.18s ease';
            setTimeout(() => window.location.href = TABS[curIdx - 1], 180);
        }
    }, { passive: true });

    // Visual swipe hint on bottom nav (optional)
    const activeTab = document.querySelector('.mg-nav-tab.active');
    if (activeTab) activeTab.style.transform = 'scale(1.05)';
})();

// ── QR Modal ─────────────────────────────────────────────────────
const QR_MEMBER_ID = <?= (int)($qrMemberId ?? 0) ?>;

function openQrModal() {
    const modal = document.getElementById('globalQrModal');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeQrModal() {
    const modal = document.getElementById('globalQrModal');
    modal.classList.remove('open');
    document.body.style.overflow = '';
}

// Auto-refresh QR every 60 seconds
(function() {
    const timerEl = document.getElementById('globalQrTimer');
    const imgEl   = document.getElementById('globalQrImg');
    if (!timerEl || !imgEl) return;

    let s = 60 - (Math.floor(Date.now() / 1000) % 60);

    setInterval(function() {
        s--;
        if (s <= 0) {
            // Refresh QR token
            fetch('<?= SITE_URL ?>/api/qr-token?id=' + QR_MEMBER_ID)
                .then(r => r.json())
                .then(d => {
                    if (d.qr) {
                        imgEl.src = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=12&data=' + encodeURIComponent(d.qr) + '&t=' + Date.now();
                    }
                })
                .catch(() => {});
            s = 60;
        }
        if (timerEl) timerEl.textContent = s;
    }, 1000);
})();
</script>
