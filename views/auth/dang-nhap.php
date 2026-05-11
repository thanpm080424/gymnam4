<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Monkey Gym</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-circle">🏋️</div>
            <h1>Monkey Gym</h1>
            <p>Vượt qua giới hạn, kiến tạo bản thân</p>
        </div>

        <h2>Đăng nhập</h2>

        <?php $__f = getFlash(); if ($__f): ?>
            <div class="alert alert-<?= htmlspecialchars($__f['type']) ?>">
                <?= htmlspecialchars($__f['message']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">✅ Đăng ký thành công! Vui lòng đăng nhập.</div>
        <?php endif; ?>

        <form method="POST" action="<?= SITE_URL ?>/login">
            <div class="form-group">
                <label for="email">Email / Tên đăng nhập</label>
                <input type="text" id="email" name="email" class="form-control"
                       required placeholder="admin"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control"
                       required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary w-full" style="margin-top:8px;padding:11px;">
                Đăng nhập
            </button>
        </form>

        <!-- Divider -->
        <div style="display:flex;align-items:center;gap:12px;margin:20px 0 16px;">
            <div style="flex:1;height:1px;background:var(--border-color,#e5e7eb);"></div>
            <span style="font-size:12px;color:var(--text-muted,#9ca3af);white-space:nowrap;">hoặc</span>
            <div style="flex:1;height:1px;background:var(--border-color,#e5e7eb);"></div>
        </div>

        <!-- Google Login -->
        <?php
        if (!class_exists('GoogleOAuth')) {
            @require_once __DIR__ . '/../../includes/GoogleOAuth.php';
        }
        $googleUrl = '';
        try { $googleUrl = getGoogleOAuthUrl(); } catch (Exception $e) {}
        ?>
        <?php if ($googleUrl): ?>
        <a href="<?= htmlspecialchars($googleUrl) ?>"
           style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px;border:1px solid var(--border-color,#d1d5db);border-radius:10px;background:#fff;color:#1f2937;font-weight:600;font-size:14px;text-decoration:none;transition:all .2s;cursor:pointer;"
           onmouseover="this.style.background='#f9fafb';this.style.boxShadow='0 2px 8px rgba(0,0,0,.08)'"
           onmouseout="this.style.background='#fff';this.style.boxShadow='none'">
            <svg width="20" height="20" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Đăng nhập bằng Google
        </a>
        <?php endif; ?>

        <p class="text-center" style="margin-top:20px;font-size:13px;color:var(--text-muted);">
            Chưa có tài khoản?
            <a href="<?= SITE_URL ?>/register" style="color:var(--gold);font-weight:600;">Đăng ký ngay</a>
        </p>

        <p class="text-center" style="margin-top:8px;">
            <a href="<?= SITE_URL ?>/" style="font-size:13px;color:var(--text-muted);text-decoration:none;">
                ← Về trang chủ
            </a>
        </p>
    </div>
</div>
</body>
</html>
