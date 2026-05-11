<?php
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$ho_ten = $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? 'HLV';
function tnavA(string $href, string $icon, string $label, string $uri): string {
    $base = parse_url($href, PHP_URL_PATH);
    $cls  = ($base && strpos($uri, $base) !== false) ? ' active' : '';
    return "<a href=\"$href\" class=\"$cls\">$icon $label</a>";
}
?>
<header class="member-topbar">
    <div class="member-topbar-inner">
        <a href="<?= SITE_URL ?>/trainer/dashboard" class="member-logo">
            Monkey <span>Gym</span>
            <span style="font-size:11px;color:var(--text-muted);font-weight:400;margin-left:6px;">HLV</span>
        </a>
        <nav class="member-nav">
            <?= tnavA(SITE_URL.'/trainer/dashboard', '🗓', 'Lịch Hẹn', $uri) ?>
            <?= tnavA(SITE_URL.'/trainer/schedule',  '📅', 'Thời Khóa Biểu', $uri) ?>
            <?= tnavA(SITE_URL.'/trainer/profile',   '👤', 'Hồ Sơ', $uri) ?>
        </nav>
        <div class="member-actions">
            <span class="member-greeting">Xin chào, <strong><?= htmlspecialchars($ho_ten) ?></strong></span>
            <a href="<?= SITE_URL ?>/logout" class="btn btn-secondary btn-sm">Đăng xuất</a>
        </div>
    </div>
</header>
