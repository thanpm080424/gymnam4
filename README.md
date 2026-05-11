# 🐒 Monkey Gym - Management System v2.0

Tài liệu hướng dẫn kỹ thuật và vận hành hệ thống Quản lý Phòng tập Monkey Gym.

---

## � LINKS CHẠY ĐƯỢC

### 🏠 **Trang Chủ & Đăng Nhập**
- **Trang chủ:** http://localhost/monkey-gym
- **Đăng nhập:** http://localhost/monkey-gym/public/dang-nhap.php
- **Đăng ký:** http://localhost/monkey-gym/public/dang-ky.php
- **Đăng nhập Google OAuth:** http://localhost/monkey-gym/public/dang-nhap-google.php
- **Test - Kiểm tra cấu hình:** http://localhost/monkey-gym/public/test-qr.php

### 👥 **Admin Dashboard**
- **Admin Dashboard:** http://localhost/monkey-gym/admin/bang-dieu-khien.php
- **Quét QR Điểm danh:** http://localhost/monkey-gym/admin/quet-qr.php
- **Quản lý Hội viên:** http://localhost/monkey-gym/admin/hoi-vien.php
- **Quản lý Gói tập:** http://localhost/monkey-gym/admin/goi-tap.php
- **Quản lý HLV:** http://localhost/monkey-gym/admin/hlv.php
- **Quản lý Sản phẩm:** http://localhost/monkey-gym/admin/san-pham.php
- **Duyệt Đánh giá:** http://localhost/monkey-gym/admin/danh-gia.php
- **Hóa đơn/Thanh toán:** http://localhost/monkey-gym/admin/hoa-don.php
- **Lịch sử hoạt động:** http://localhost/monkey-gym/admin/lich-su.php
- **Thông báo hệ thống:** http://localhost/monkey-gym/admin/thong-bao.php
- **Yêu cầu cấm hội viên:** http://localhost/monkey-gym/admin/yeu-cau-cam.php
- **Tự do (Locker):** http://localhost/monkey-gym/admin/tu-do.php

### 👤 **Member Dashboard**
- **Member Dashboard:** http://localhost/monkey-gym/member/bang-dieu-khien.php
- **Đặt lịch PT:** http://localhost/monkey-gym/member/dat-lich.php
- **Cửa hàng mua gói tập:** http://localhost/monkey-gym/member/cua-hang.php
- **Nhật ký tập luyện:** http://localhost/monkey-gym/member/nhat-ky.php
- **Biên lai/Lịch sử:** http://localhost/monkey-gym/member/bien-lai.php
- **Tự do (Locker):** http://localhost/monkey-gym/member/tu-do.php
- **Đánh giá HLV:** http://localhost/monkey-gym/member/danh-gia.php

### 🏋️ **Trainer Dashboard**
- **Trainer Dashboard:** http://localhost/monkey-gym/trainer/bang-dieu-khien.php
- **Hồ sơ HLV:** http://localhost/monkey-gym/trainer/ho-so.php
- **Quản lý học viên:** http://localhost/monkey-gym/trainer/hoc-vien.php

### 🔌 **API Endpoints**
- **Kiểm tra Voucher:** http://localhost/monkey-gym/public/api/check-voucher.php (POST)
- **Tạo thanh toán VNPay:** http://localhost/monkey-gym/public/api/create-payment.php (POST)
- **QR Code Điểm danh:** http://localhost/monkey-gym/public/api/qr-checkin.php (POST)

### 💳 **Thanh Toán & Callback**
- **VNPay Return:** http://localhost/monkey-gym/public/vnpay-return.php
- **VNPay Notify (IPN):** http://localhost/monkey-gym/public/vnpay-notify.php
- **Google OAuth Callback:** http://localhost/monkey-gym/public/dang-nhap-google.php

---

## �📝 Giới thiệu
**Monkey Gym** là một nền tảng quản lý phòng gym hiện đại, hỗ trợ đa nền tảng (Responsive Web) giúp kết nối Hội viên, Huấn luyện viên (PT) và Đội ngũ quản lý (Admin/Staff). Hệ thống không chỉ dừng lại ở việc quản lý ra vào mà còn tích hợp các tính năng mạng xã hội thu nhỏ như Nhật ký tập luyện, Đánh giá HLV và Hệ thống tích điểm đổi quà.

---

## 🛠 Công nghệ sử dụng
- **Backend:** PHP 8.x (Vanilla PHP - Kiến trúc MVC tự xây dựng).
- **Frontend:** HTML5, CSS3 (Vanilla), Javascript (ES6).
- **Cơ sở dữ liệu:** MySQL 8.0.
- **Thư viện bên thứ 3:** 
  - [Chart.js](https://www.chartjs.org/) (Đồ thị thống kê).
  - [QR Scanner Library](https://github.com/mebjas/html5-qrcode) (Quét mã QR).

---

## 📂 Cấu trúc thư mục
```bash
/monkey-gym
├── /controllers        # Xử lý Logic nghiệp vụ (Admin, Member, Trainer, Auth...)
├── /models             # Tương tác với Cơ sở dữ liệu
├── /views              # Giao diện người dùng (phân theo vai trò)
│   ├── /admin          # Dashboard Admin/Staff & Quản lý
│   ├── /member         # Dashboard & Tính năng cho Hội viên
│   ├── /trainer        # Hồ sơ & Quản lý học viên cho HLV
│   ├── /auth           # Login & Register
│   └── landing.php     # Trang chủ giới thiệu
├── /public             # Thư mục gốc công khai (Entry Point)
│   ├── index.php       # Tệp định tuyến (Router) chính
│   ├── /css            # Stylesheets (styles.css, landing.css...)
│   ├── /uploads        # Lưu trữ ảnh (Sản phẩm, Nhật ký, HLV...)
│   └── favicon.png     # Logo hệ thống
├── /database           # Cấu hình & Scripts SQL
│   ├── config.php      # Kết nối PDO MySQL
│   ├── schema.sql      # Schema khởi tạo (v1)
│   └── migration_v2.sql # Script nâng cấp (v2)
├── /utils              # Tiện ích bổ sung
│   ├── Middleware.php  # Phân quyền (Check Auth, Admin, Trainer...)
│   └── SecurityHelper.php # Bảo mật (Hash mật khẩu, XSS Filter)
└── README.md           # Tài liệu này
```

---

## 🛡️ Hệ thống Phân quyền (Roles)
Hệ thống sử dụng ENUM `vai_tro` để phân cấp người dùng:
1.  **Admin:** Toàn quyền quản trị hệ thống, gói tập, doanh thu, thông báo và duyệt cấm hội viên.
2.  **Staff (`nhanvien`):** Quản lý vận hành hàng ngày (Locker, duyệt Review, quét QR điểm danh, bán hàng tại quầy).
3.  **HLV (`hlv`):** Quản lý học viên, cập nhật hồ sơ cá nhân và theo dõi đánh giá.
4.  **Hội viên (`hoi_vien`):** Mua gói tập, đặt lịch PT, viết nhật ký, thuê tủ đồ, tích điểm.

---

## ✨ Tính năng chính

### 1. Đối với Hội viên (Members)
- **Ví điện tử & Gói tập:** Theo dõi hạn sử dụng gói tập và số buổi PT còn lại.
- **Nhật ký tập luyện:** Lưu lại hình ảnh/video và cảm nhận sau mỗi buổi tập (Riêng tư).
- **Thuê tủ đồ:** Gửi yêu cầu thuê tủ cố định hàng tháng trên hệ thống.
- **Hệ thống tích điểm:** Mỗi lần Check-in nhận 1 điểm. Dùng điểm đổi sản phẩm gym (Whey, phụ kiện).
- **Đánh giá HLV:** Đánh giá 1-5 sao và nhận xét cho PT sau khi hoàn thành khóa tập.

### 2. Đối với Huấn luyện viên (PT)
- **Profile chuyên nghiệp:** Cập nhật tiểu sử, số năm kinh nghiệm để thu hút học viên trên trang chủ.
- **Ghi chú học viên:** Lưu lại tình trạng sức khỏe, giáo án riêng cho từng học viên.
- **Quản lý lịch hẹn:** Xác nhận hoặc từ chối lịch đặt PT từ hội viên.

### 3. Đối với Admin & Staff
- **Analytics Hub:** Biểu đồ doanh thu thực tế (Tổng doanh thu, số khách mới, giờ cao điểm).
- **QR Check-in:** Quét mã QR code của hội viên tại quầy để điểm danh tự động.
- **Moderation:** Duyệt các đánh giá HLV bài trừ nội dung xấu; Duyệt/phân tủ đồ cho hội viên.
- **Thông báo hệ thống:** Đăng thông báo dạng Popup hiển thị cho toàn bộ người dùng khi truy cập trang chủ.
- **Cảnh báo tồn kho:** Tự động báo đỏ các sản phẩm dưới 20 món trong kho.

---

## 🗄️ Cấu trúc Cơ sở dữ liệu (v2.0)

Hệ thống bao gồm 20 bảng được phân nhóm theo nghiệp vụ:

### Nhóm 1: Người dùng & Phân quyền
- **`NGUOI_DUNG`**: Tài khoản đăng nhập (Email, Mật khẩu Bcrypt, Vai trò: `admin`, `hlv`, `hoi_vien`, `nhanvien`).
- **`HOI_VIEN`**: Thông tin cá nhân hội viên (Mã QR, Chiều cao, Cân nặng, Ngày hết hạn gói, Trạng thái `bi_ban`).
- **`HUAN_LUYEN_VIEN`**: Hồ sơ HLV (Chuyên môn, Kinh nghiệm, Giới thiệu, Ảnh đại diện).

### Nhóm 2: Gói tập & Thanh toán
- **`GOI_TAP`**: Danh mục gói tập (Tên, Giá, Thời hạn, Số buổi PT đi kèm).
- **`DANG_KY_GOI`**: Lịch sử đăng ký gói của hội viên (Ngày kích hoạt, Ngày hết hạn, Trạng thái).
- **`THANH_TOAN`**: Chi tiết giao dịch (Mã VNPay/Tiền mặt, Số tiền, Trạng thái thành công/thất bại).

### Nhóm 3: Cửa hàng & Sản phẩm
- **`SAN_PHAM`**: Danh mục vật phẩm (Whey, phụ kiện, Giá tiền, **Giá điểm**, Tồn kho).
- **`DON_HANG_SP`**: Đơn hàng sản phẩm (Mã giao dịch, Trạng thái nhận hàng tại quầy).

### Nhóm 4: Lịch trình & Hoạt động
- **`LICH_SU_RA_VAO`**: Log check-in điểm danh qua mã QR.
- **`LICH_DAT_PT`**: Lịch tập 1-kèm-1 giữa hội viên và HLV.
- **`NHAT_KY_TAP`**: Bài viết nhật ký cá nhân của hội viên.
- **`MEDIA_NHAT_KY`**: Lưu trữ đường dẫn ảnh/video của nhật ký.

### Nhóm 5: Hệ thống Tích điểm (Mới v2.0)
- **`DIEM_TICH_LUY`**: Tổng điểm hiện tại của hội viên.
- **`LICH_SU_DIEM`**: Chi tiết cộng/trừ điểm (Lý do, số điểm thay đổi).

### Nhóm 6: Tiện ích & Tương tác (Mới v2.0)
- **`DANH_GIA_HLV`**: Đánh giá 5 sao & Nhận xét cho HLV (Kèm trạng thái kiểm duyệt của Staff).
- **`TU_DO`**: Danh sách tủ đồ trong phòng tập (Số tủ, Vị trí, Trạng thái trống/thuê/bào trì).
- **`YEU_CAU_THUE_TU`**: Đơn đăng ký thuê tủ của hội viên.
- **`GHI_CHU_HLV`**: Ghi chú riêng tư của HLV dành cho từng học viên.

### Nhóm 7: Quản trị & Staff (Mới v2.0)
- **`THONG_BAO_HE_THONG`**: Quản lý các thông báo Popup hiển thị trên trang chủ.
- **`YEU_CAU_BAN`**: Staff gửi yêu cầu khóa tài khoản hội viên lên Admin xét duyệt.
- **`KHUYEN_MAI`**: Quản lý Voucher và mã giảm giá.

---

## 🚀 Hướng dẫn cài đặt
1.  **Clone source code** vào thư mục Web Server (WAMP/XAMPP).
2.  **Import Database:** 
    - Chạy file `database/schema.sql` trước.
    - Sau đó chạy `database/migration_v2.sql` để cập nhật tính năng mới.
3.  **Cấu hình kết nối:** Sửa thông số DB trong `database/config.php`.
4.  **Tài khoản đăng nhập mặc định:**
    - Admin: `admin` / `123`
    - Staff: `staff` / `123`
    - Hội viên: `hoivien@gmail.com` / `123`

---
*Phát triển bởi Đội ngũ Monkey Gym Dev Team - Version 2.0 (2024)*
