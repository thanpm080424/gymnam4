<?php
// Script hỗ trợ import database tự động
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // 1. Kết nối MySQL (chưa chọn database)
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Ket noi MySQL thanh cong!\n";

    // 2. Đọc file schema.sql
    $sqlFile = __DIR__ . '/database/schema.sql';
    if (!file_exists($sqlFile)) {
        die("Không tìm thấy file schema.sql tại: $sqlFile\n");
    }
    
    $sqlContent = file_get_contents($sqlFile);
    
    // 3. Thực thi SQL
    $pdo->exec($sqlContent);
    echo "Tạo database và import các bảng thành công!\n";
    echo "Bạn đã có thể bắt đầu chạy dự án.\n";

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "Hãy chắc chắn rằng phần mềm WAMP của bạn đang bật (biểu tượng WAMP màu xanh lá).\n";
    echo "Nếu MySQL của bạn có mật khẩu cho tài khoản 'root', vui lòng sửa DB_PASS trong file này.\n";
}
