<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$ho_ten  = $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? 'Trainer';

function isActiveTrainer(string $path, string $uri): bool {
    $base = parse_url($path, PHP_URL_PATH);
    return ($base && strpos($uri, $base) !== false);
}
function navATrainer(string $href, string $icon, string $label, string $uri): string {
    $cls  = isActiveTrainer($href, $uri) ? ' active' : '';
    return "<a href=\"$href\" class=\"$cls\"><span class=\"nav-icon\">$icon</span> $label</a>";
}
?>
<div class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon">M</div>
        <div class="logo-text-wrap">
            <div class="logo-name">Monkey Gym</div>
            <div class="logo-sub">Huấn Luyện Viên</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-item">
            <?= navATrainer(SITE_URL.'/trainer/dashboard', '📊', 'Tổng Quan', $uri) ?>
        </div>

        <div class="nav-item">
            <?= navATrainer(SITE_URL.'/trainer/schedule', '📅', 'Thời Khóa Biểu', $uri) ?>
        </div>

        <div class="nav-item">
            <?= navATrainer(SITE_URL.'/trainer/students', '👥', 'Học Viên', $uri) ?>
        </div>

        <div class="nav-item">
            <?= navATrainer(SITE_URL.'/trainer/my-payroll', '💰', 'Lịch Sử Thu Nhập', $uri) ?>
        </div>

        <div class="nav-item">
            <?= navATrainer(SITE_URL.'/trainer/profile', '👤', 'Hồ Sơ Cá Nhân', $uri) ?>
        </div>
    </nav>

    <!-- User info + logout -->
    <div class="nav-logout">
        <div style="padding: 8px 12px; margin-bottom: 4px; font-size: 12px;">
            <div style="font-weight:600; color: var(--text-primary);"><?= htmlspecialchars($ho_ten) ?></div>
            <div style="color: var(--text-muted)">Huấn Luyện Viên</div>
        </div>
        <a href="<?= SITE_URL ?>/logout">🚪 Đăng xuất</a>
    </div>
</div>
