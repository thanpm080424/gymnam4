<?php
$uri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isAdmin = ($_SESSION['role'] ?? $_SESSION['vai_tro'] ?? '') === 'admin';
$ho_ten  = $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? 'Admin';
$role    = $isAdmin ? 'Quản Trị Viên' : 'Nhân Viên';

function isActive(array $paths, string $uri): bool {
    foreach ($paths as $p) {
        $base = parse_url($p, PHP_URL_PATH);
        if ($base && strpos($uri, $base) !== false) return true;
    }
    return false;
}
function navA(string $href, string $icon, string $label, string $uri): string {
    $base = parse_url($href, PHP_URL_PATH);
    $cls  = ($base && strpos($uri, $base) !== false) ? ' active' : '';
    return "<a href=\"$href\" class=\"$cls\"><span class=\"nav-icon\">$icon</span> $label</a>";
}

// Xác định group nào đang active để tự mở
$groupOpen = [
    'hoivien'   => isActive([SITE_URL.'/admin/members', SITE_URL.'/admin/leads', SITE_URL.'/admin/ban-requests'], $uri),
    'doanhthu'  => isActive([SITE_URL.'/admin/purchases', SITE_URL.'/admin/payment-management', SITE_URL.'/admin/reports', SITE_URL.'/admin/payroll'], $uri),
    'dichvu'    => isActive([SITE_URL.'/admin/packages', SITE_URL.'/admin/products', SITE_URL.'/admin/promotions', SITE_URL.'/admin/schedule'], $uri),
    'nhansu'    => isActive([SITE_URL.'/admin/trainers', SITE_URL.'/admin/staff'], $uri),
    'checkin'   => isActive([SITE_URL.'/admin/checkin', SITE_URL.'/admin/attendance-history'], $uri),
    'noidung'   => isActive([SITE_URL.'/admin/reviews', SITE_URL.'/admin/lockers', SITE_URL.'/admin/announcements'], $uri),
];
?>
<div class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon">M</div>
        <div class="logo-text-wrap">
            <div class="logo-name">Monkey Gym</div>
            <div class="logo-sub"><?= $role ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <div class="nav-item">
            <?= navA(SITE_URL.'/admin/dashboard', '📊', 'Tổng Quan', $uri) ?>
        </div>

        <div class="nav-divider"></div>

        <!-- Hội Viên -->
        <div class="nav-group <?= $groupOpen['hoivien'] ? 'open' : '' ?>">
            <div class="nav-group-label" onclick="toggleGroup(this)">
                <span class="group-title">👥 Hội Viên</span>
                <span class="arrow">▶</span>
            </div>
            <div class="nav-sub">
                <?= navA(SITE_URL.'/admin/members', '•', 'Danh Sách', $uri) ?>
                <?= navA(SITE_URL.'/admin/leads', '•', 'Khách Tiềm Năng', $uri) ?>
                <?php if ($isAdmin): ?>
                <?= navA(SITE_URL.'/admin/ban-requests', '•', 'Yêu Cầu Ban', $uri) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Doanh Thu -->
        <div class="nav-group <?= $groupOpen['doanhthu'] ? 'open' : '' ?>">
            <div class="nav-group-label" onclick="toggleGroup(this)">
                <span class="group-title">💰 Doanh Thu</span>
                <span class="arrow">▶</span>
            </div>
            <div class="nav-sub">

                <?= navA(SITE_URL.'/admin/purchases', '•', 'Giao Dịch', $uri) ?>
                <?= navA(SITE_URL.'/admin/payment-management', '•', 'Thanh Toán TT', $uri) ?>
                <?php if ($isAdmin): ?>
                <?= navA(SITE_URL.'/admin/reports', '•', 'Báo Cáo', $uri) ?>
                <?= navA(SITE_URL.'/admin/payroll', '•', 'Bảng Lương', $uri) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nhân Sự -->
        <?php if ($isAdmin): ?>
        <div class="nav-group <?= $groupOpen['nhansu'] ? 'open' : '' ?>">
            <div class="nav-group-label" onclick="toggleGroup(this)">
                <span class="group-title">🧑‍💼 Nhân Sự</span>
                <span class="arrow">▶</span>
            </div>
            <div class="nav-sub">
                <?= navA(SITE_URL.'/admin/trainers', '•', 'Huấn Luyện Viên', $uri) ?>
                <?= navA(SITE_URL.'/admin/staff', '•', 'Nhân Viên', $uri) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dịch Vụ -->
        <?php if ($isAdmin): ?>
        <div class="nav-group <?= $groupOpen['dichvu'] ? 'open' : '' ?>">
            <div class="nav-group-label" onclick="toggleGroup(this)">
                <span class="group-title">🏷️ Dịch Vụ</span>
                <span class="arrow">▶</span>
            </div>
            <div class="nav-sub">
                <?= navA(SITE_URL.'/admin/packages', '•', 'Gói Tập', $uri) ?>
                <?= navA(SITE_URL.'/admin/products', '•', 'Sản Phẩm', $uri) ?>
                <?= navA(SITE_URL.'/admin/schedule', '•', 'Xếp Lịch Học', $uri) ?>
                <?= navA(SITE_URL.'/admin/promotions', '•', 'Khuyến Mãi', $uri) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Điểm Danh -->
        <div class="nav-group <?= $groupOpen['checkin'] ? 'open' : '' ?>">
            <div class="nav-group-label" onclick="toggleGroup(this)">
                <span class="group-title">📷 Điểm Danh</span>
                <span class="arrow">▶</span>
            </div>
            <div class="nav-sub">
                <?= navA(SITE_URL.'/admin/checkin', '•', 'Quét QR', $uri) ?>
                <?= navA(SITE_URL.'/admin/attendance-history', '•', 'Lịch Sử Ra Vào', $uri) ?>
            </div>
        </div>

        <!-- Nội Dung -->
        <div class="nav-group <?= $groupOpen['noidung'] ? 'open' : '' ?>">
            <div class="nav-group-label" onclick="toggleGroup(this)">
                <span class="group-title">⚙️ Quản Lý</span>
                <span class="arrow">▶</span>
            </div>
            <div class="nav-sub">
                <?= navA(SITE_URL.'/admin/reviews', '•', 'Đánh Giá HLV', $uri) ?>
                <?= navA(SITE_URL.'/admin/lockers', '•', 'Tủ Đồ', $uri) ?>
                <?php if ($isAdmin): ?>
                <?= navA(SITE_URL.'/admin/announcements', '•', 'Thông Báo', $uri) ?>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- User info + logout -->
    <div class="nav-logout">
        <div style="padding: 8px 12px; margin-bottom: 4px; font-size: 12px;">
            <div style="font-weight:600; color: var(--text-primary); truncate"><?= htmlspecialchars($ho_ten) ?></div>
            <div style="color: var(--text-muted)"><?= $role ?></div>
        </div>
        <a href="<?= SITE_URL ?>/logout">🚪 Đăng xuất</a>
    </div>
</div>

<script>
function toggleGroup(el) {
    el.parentElement.classList.toggle('open');
}
</script>
