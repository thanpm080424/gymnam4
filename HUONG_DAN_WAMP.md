# Hướng dẫn cài đặt trên WAMP

## Yêu cầu
- WAMP Server 3.x (Apache 2.4+, PHP 8.1+, MySQL 9.x)
- Module Apache: mod_rewrite phải được BẬT

## Bước 1: Bật mod_rewrite trong WAMP
1. Click icon WAMP dưới taskbar → Apache → Apache modules
2. Tích chọn **rewrite_module**
3. Restart All Services

## Bước 2: Copy project vào WAMP
```
C:\wamp64\www\monkey-gym\
```
Cấu trúc phải là:
```
C:\wamp64\www\monkey-gym\
    ├── .htaccess           ← file này ở ROOT
    ├── config\
    ├── public\
    │   ├── .htaccess       ← file này trong PUBLIC
    │   ├── index.php       ← entry point
    │   ├── css\
    │   └── js\
    ├── controllers\
    ├── views\
    └── ...
```

## Bước 3: Import Database
1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Import file `database/monkey_gym.sql` (file SQL của bạn)
3. Import tiếp `database/migration_v3.sql` để thêm bảng mới

## Bước 4: Cấu hình (nếu cần)
Mở `config/config.php`, kiểm tra:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // password WAMP của bạn (thường để trống)
define('DB_NAME', 'monkey_gym');
define('SITE_URL', 'http://localhost/monkey-gym');
```

## Bước 5: Truy cập
- Trang chủ:  http://localhost/monkey-gym/public/
- Đăng nhập:  http://localhost/monkey-gym/public/login

## Tài khoản mặc định (từ file SQL)
| Role | Username | Password |
|------|----------|----------|
| Admin | admin | (bcrypt hash) |
| Hội viên | hoivien@gmail.com | ... |
| HLV | hlv1@gmail.com | ... |
| Nhân viên | staff | ... |

> Password trong DB đã được hash bcrypt. Nếu quên, tạo tài khoản mới qua trang đăng ký hoặc update trực tiếp trong phpMyAdmin.

## Lỗi thường gặp

### "404 Not Found" khi truy cập bất kỳ trang nào
→ mod_rewrite chưa bật. Làm theo Bước 1.

### "403 Forbidden"
→ AllowOverride chưa được cấu hình. Mở `C:\wamp64\bin\apache\apache2.x.x\conf\httpd.conf`:
```apache
<Directory "c:/wamp64/www/">
    AllowOverride All    ← đổi từ None thành All
    ...
</Directory>
```

### Trang trắng / không load gì
→ Bật hiển thị lỗi PHP: trong `config/config.php` đảm bảo `ENVIRONMENT = 'development'`

### "Cannot connect to database"
→ Kiểm tra MySQL đang chạy trong WAMP, kiểm tra DB_NAME = 'monkey_gym' đúng chưa

## Cấu trúc URL
Tất cả URL đều qua `/monkey-gym/public/index.php`:
- Login: `http://localhost/monkey-gym/public/login`
- Admin: `http://localhost/monkey-gym/public/admin/dashboard`
- Member: `http://localhost/monkey-gym/public/member/dashboard`
- Trainer: `http://localhost/monkey-gym/public/trainer/dashboard`
