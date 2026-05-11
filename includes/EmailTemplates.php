<?php
/**
 * Email Templates Class
 * Các mẫu email HTML đẹp và responsive
 * 
 * MonkeyGym Email System
 * Author: Claude AI
 * Date: 2025-12-11
 */

class EmailTemplates {
    
    /**
     * Base CSS cho tất cả email
     */
    private static function getBaseStyle() {
        return "
        <style>
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .email-container {
                max-width: 600px;
                margin: 20px auto;
                background-color: #ffffff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .email-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .email-header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: bold;
            }
            .email-body {
                padding: 30px;
            }
            .credentials-box {
                background-color: #f8f9fa;
                border-left: 4px solid #667eea;
                padding: 20px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .credentials-box h3 {
                color: #667eea;
                margin-top: 0;
                font-size: 18px;
            }
            .credentials-box p {
                margin: 12px 0;
                font-size: 14px;
            }
            .credential-value {
                color: #dc3545;
                font-size: 18px;
                font-weight: bold;
                font-family: 'Courier New', monospace;
                background: #fff;
                padding: 8px 12px;
                border-radius: 4px;
                display: inline-block;
                margin-left: 10px;
            }
            .button {
                display: inline-block;
                padding: 14px 32px;
                background-color: #667eea;
                color: white !important;
                text-decoration: none;
                border-radius: 6px;
                margin: 20px 0;
                font-weight: bold;
                font-size: 16px;
                transition: background 0.3s;
            }
            .button:hover {
                background-color: #5568d3;
            }
            .warning-box {
                background-color: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .success-box {
                background-color: #d4edda;
                border-left: 4px solid #28a745;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .info-box {
                background-color: #d1ecf1;
                border-left: 4px solid #17a2b8;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .email-footer {
                background-color: #f8f9fa;
                text-align: center;
                padding: 20px;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #e0e0e0;
            }
            .emoji {
                font-size: 32px;
                display: inline-block;
                margin-bottom: 10px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
            }
            td {
                padding: 12px;
                border-bottom: 1px solid #eee;
            }
            td:first-child {
                font-weight: bold;
                width: 40%;
            }
            .highlight {
                background-color: #fffacd;
                padding: 2px 6px;
                border-radius: 3px;
            }
            @media only screen and (max-width: 600px) {
                .email-body {
                    padding: 20px;
                }
                .email-header h1 {
                    font-size: 24px;
                }
            }
        </style>
        ";
    }
    
    /**
     * Template 1: Email chào mừng hội viên mới
     * 
     * @param array $data - ['ho_ten', 'username', 'password']
     * @return string HTML email
     */
    public static function welcomeMember($data) {
        $name = htmlspecialchars($data['ho_ten']);
        $username = htmlspecialchars($data['username']);
        $password = htmlspecialchars($data['password']);
        $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost/MonkeyGym_Full';
        
        return "
<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Chào mừng đến với Monkey Gym</title>
    " . self::getBaseStyle() . "
</head>
<body>
    <div class='email-container'>
        <div class='email-header'>
            <div class='emoji'>🐵</div>
            <h1>Chào mừng đến với Monkey Gym!</h1>
        </div>
        
        <div class='email-body'>
            <p style='font-size: 16px;'>Xin chào <strong>$name</strong>,</p>
            
            <p>Chúc mừng bạn đã trở thành thành viên của <strong>Monkey Gym</strong>! 🎉</p>
            
            <p>Chúng tôi rất vui mừng được đồng hành cùng bạn trên hành trình rèn luyện sức khỏe và thể lực.</p>
            
            <div class='credentials-box'>
                <h3>🔐 Thông tin đăng nhập của bạn</h3>
                <p>
                    <strong>Website:</strong> 
                    <a href='$siteUrl' style='color: #667eea;'>$siteUrl</a>
                </p>
                <p>
                    <strong>Username:</strong>
                    <span class='credential-value'>$username</span>
                </p>
                <p>
                    <strong>Password:</strong>
                    <span class='credential-value'>$password</span>
                </p>
            </div>
            
            <div class='warning-box'>
                <strong>⚠️ Lưu ý bảo mật:</strong>
                <ul style='margin: 10px 0 0 0; padding-left: 20px;'>
                    <li>Vui lòng <strong>đổi mật khẩu</strong> sau lần đăng nhập đầu tiên</li>
                    <li>Không chia sẻ thông tin đăng nhập với người khác</li>
                    <li>Giữ email này cẩn thận hoặc xóa sau khi đã lưu thông tin</li>
                </ul>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='$siteUrl/public/login.php' class='button'>
                    🚀 Đăng nhập ngay
                </a>
            </div>
            
            <div class='info-box'>
                <strong>📋 Bước tiếp theo:</strong>
                <ol style='margin: 10px 0 0 0; padding-left: 20px;'>
                    <li>Đăng nhập vào hệ thống</li>
                    <li>Hoàn thiện hồ sơ cá nhân của bạn</li>
                    <li>Xem mã QR điểm danh</li>
                    <li>Bắt đầu hành trình tập luyện!</li>
                </ol>
            </div>
            
            <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;'>
            
            <p><strong>📞 Liên hệ với chúng tôi:</strong></p>
            <table>
                <tr>
                    <td>Hotline</td>
                    <td><strong>1900 xxxx</strong></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><strong>support@monkeygym.com</strong></td>
                </tr>
                <tr>
                    <td>Địa chỉ</td>
                    <td><strong>123 Đường ABC, Quận XYZ, TP.HCM</strong></td>
                </tr>
            </table>
            
            <p style='margin-top: 30px;'>Chúc bạn có những trải nghiệm tập luyện tuyệt vời! 💪</p>
            
            <p style='color: #667eea; font-weight: bold;'>Đội ngũ Monkey Gym</p>
        </div>
        
        <div class='email-footer'>
            <p style='margin: 5px 0;'><strong>© 2025 Monkey Gym. All rights reserved.</strong></p>
            <p style='margin: 5px 0;'>Email này được gửi tự động từ hệ thống. Vui lòng không reply.</p>
            <p style='margin: 5px 0; color: #999;'>Nếu bạn nhận nhầm email này, vui lòng bỏ qua.</p>
        </div>
    </div>
</body>
</html>
        ";
    }
    
    /**
     * Template 2: Xác nhận thanh toán thành công
     * 
     * @param array $data - ['ho_ten', 'ten_goi', 'so_tien', 'ngay_bat_dau', 'ngay_ket_thuc', 'phuong_thuc']
     * @return string HTML email
     */
    public static function paymentConfirmation($data) {
        $name = htmlspecialchars($data['ho_ten']);
        $packageName = htmlspecialchars($data['ten_goi']);
        $amount = number_format($data['so_tien'], 0, ',', '.');
        $startDate = date('d/m/Y', strtotime($data['ngay_bat_dau']));
        $endDate = date('d/m/Y', strtotime($data['ngay_ket_thuc']));
        $method = $data['phuong_thuc'] == 'tien_mat' ? 'Tiền mặt' : 
                 ($data['phuong_thuc'] == 'chuyen_khoan' ? 'Chuyển khoản' : 'Thẻ');
        
        return "
<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Xác nhận thanh toán</title>
    " . self::getBaseStyle() . "
    <style>
        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .amount-highlight {
            font-size: 32px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='email-header success-header'>
            <div class='emoji'>✅</div>
            <h1>Thanh toán thành công!</h1>
        </div>
        
        <div class='email-body'>
            <p>Xin chào <strong>$name</strong>,</p>
            
            <div class='success-box'>
                <p style='margin: 0; font-size: 16px;'>
                    🎊 <strong>Cảm ơn bạn đã thanh toán!</strong><br>
                    Gói tập của bạn đã được kích hoạt và sẵn sàng sử dụng.
                </p>
            </div>
            
            <div class='amount-highlight'>
                $amount đ
            </div>
            
            <div class='credentials-box'>
                <h3>📋 Thông tin thanh toán</h3>
                <table>
                    <tr>
                        <td>Gói tập</td>
                        <td><strong>$packageName</strong></td>
                    </tr>
                    <tr>
                        <td>Số tiền</td>
                        <td><strong>$amount đ</strong></td>
                    </tr>
                    <tr>
                        <td>Phương thức</td>
                        <td><strong>$method</strong></td>
                    </tr>
                    <tr>
                        <td>Ngày bắt đầu</td>
                        <td><strong>$startDate</strong></td>
                    </tr>
                    <tr>
                        <td>Ngày kết thúc</td>
                        <td><strong>$endDate</strong></td>
                    </tr>
                </table>
            </div>
            
            <div class='info-box'>
                <strong>🎯 Bạn có thể:</strong>
                <ul style='margin: 10px 0 0 0; padding-left: 20px;'>
                    <li>Bắt đầu tập luyện ngay hôm nay</li>
                    <li>Sử dụng mã QR để điểm danh</li>
                    <li>Đặt lịch tập với huấn luyện viên (nếu có)</li>
                    <li>Tham gia các lớp học nhóm</li>
                </ul>
            </div>
            
            <p style='margin-top: 30px;'>Chúc bạn có những buổi tập hiệu quả và đạt được mục tiêu của mình! 💪</p>
            
            <p style='color: #28a745; font-weight: bold;'>Đội ngũ Monkey Gym</p>
        </div>
        
        <div class='email-footer'>
            <p><strong>© 2025 Monkey Gym</strong></p>
            <p>Hotline: 1900 xxxx | Email: support@monkeygym.com</p>
        </div>
    </div>
</body>
</html>
        ";
    }
    
    /**
     * Template 3: Nhắc nhở gói tập sắp hết hạn
     * 
     * @param array $data - ['ho_ten', 'ten_goi', 'ngay_ket_thuc', 'days_left']
     * @return string HTML email
     */
    public static function expiryReminder($data) {
        $name = htmlspecialchars($data['ho_ten']);
        $packageName = htmlspecialchars($data['ten_goi']);
        $expiryDate = date('d/m/Y', strtotime($data['ngay_ket_thuc']));
        $daysLeft = $data['days_left'];
        
        return "
<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Gói tập sắp hết hạn</title>
    " . self::getBaseStyle() . "
    <style>
        .warning-header {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #333;
        }
        .countdown {
            text-align: center;
            padding: 30px;
            background: #fff3cd;
            border-radius: 10px;
            margin: 20px 0;
        }
        .countdown-number {
            font-size: 72px;
            font-weight: bold;
            color: #dc3545;
            line-height: 1;
        }
        .countdown-text {
            font-size: 24px;
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='email-header warning-header'>
            <div class='emoji'>⏰</div>
            <h1>Gói tập sắp hết hạn!</h1>
        </div>
        
        <div class='email-body'>
            <p>Xin chào <strong>$name</strong>,</p>
            
            <div class='countdown'>
                <div class='countdown-number'>$daysLeft</div>
                <div class='countdown-text'>ngày còn lại</div>
            </div>
            
            <div class='warning-box'>
                <p style='margin: 0; font-size: 16px;'>
                    Gói tập <strong class='highlight'>$packageName</strong> của bạn sẽ hết hạn vào 
                    <strong class='highlight'>$expiryDate</strong>
                </p>
            </div>
            
            <p style='font-size: 16px;'>
                <strong>Đừng để gián đoạn hành trình rèn luyện của bạn!</strong> 🏋️‍♀️
            </p>
            
            <p>Gia hạn ngay hôm nay để:</p>
            <ul>
                <li>✨ Tiếp tục tập luyện không bị gián đoạn</li>
                <li>🎁 Nhận ưu đãi đặc biệt cho khách hàng thân thiết</li>
                <li>💪 Duy trì thói quen tập luyện đã hình thành</li>
                <li>🎯 Đạt được mục tiêu sức khỏe của bạn</li>
            </ul>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . (defined('SITE_URL') ? SITE_URL : '') . "/admin/goi-tap.php' class='button'>
                    🔄 Gia hạn ngay
                </a>
            </div>
            
            <div class='info-box'>
                <strong>💡 Mẹo:</strong> Gia hạn sớm để được tư vấn các gói tập phù hợp nhất với mục tiêu của bạn!
            </div>
            
            <p style='margin-top: 30px;'>Mọi thắc mắc xin liên hệ hotline <strong>1900 xxxx</strong></p>
            
            <p style='color: #ffc107; font-weight: bold;'>Đội ngũ Monkey Gym</p>
        </div>
        
        <div class='email-footer'>
            <p><strong>© 2025 Monkey Gym</strong></p>
            <p>Email: support@monkeygym.com | Địa chỉ: 123 Đường ABC, Quận XYZ</p>
        </div>
    </div>
</body>
</html>
        ";
    }
    
    /**
     * Template 4: Email khuyến mãi (Promotion)
     * 
     * @param array $data - ['title', 'content', 'discount', 'valid_until']
     * @return string HTML email
     */
    public static function promotion($data) {
        $title = htmlspecialchars($data['title']);
        $content = $data['content']; // Already HTML
        $discount = htmlspecialchars($data['discount']);
        $validUntil = date('d/m/Y', strtotime($data['valid_until']));
        
        return "
<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>$title</title>
    " . self::getBaseStyle() . "
    <style>
        .promo-header {
            background: linear-gradient(135deg, #e91e63 0%, #ff5722 100%);
        }
        .discount-badge {
            background: #dc3545;
            color: white;
            font-size: 48px;
            font-weight: bold;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px auto;
            max-width: 200px;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='email-header promo-header'>
            <div class='emoji'>🎉</div>
            <h1>$title</h1>
        </div>
        
        <div class='email-body'>
            <div class='discount-badge'>
                $discount% OFF
            </div>
            
            <div style='text-align: center; font-size: 18px; margin: 20px 0;'>
                $content
            </div>
            
            <div class='warning-box'>
                <p style='margin: 0;'>
                    ⏰ <strong>Ưu đãi có hiệu lực đến: $validUntil</strong>
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . (defined('SITE_URL') ? SITE_URL : '') . "' class='button'>
                    🎁 Nhận ưu đãi ngay
                </a>
            </div>
            
            <p style='text-align: center; color: #999; font-size: 12px;'>
                * Áp dụng cho tất cả các gói tập. Không áp dụng đồng thời với các chương trình khác.
            </p>
        </div>
        
        <div class='email-footer'>
            <p><strong>© 2025 Monkey Gym</strong></p>
        </div>
    </div>
</body>
</html>
        ";
    }
}
