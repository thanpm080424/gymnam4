<?php
// views/landing.php
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monkey Gym | Phòng Tập Công Nghệ 4.0</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Outfit:wght@700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #09090b;
            --bg-card: #18181b;
            --primary: #84cc16; /* Monkey Green */
            --primary-glow: rgba(132, 204, 22, 0.3);
            --text-main: #f4f4f5;
            --text-muted: #a1a1aa;
            --border: rgba(255, 255, 255, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-main); color: var(--text-main); line-height: 1.6; overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; display: block; }

        /* Nút Bấm */
        .btn-neon { background: var(--primary); color: #000; padding: 12px 28px; border-radius: 8px; font-weight: 700; text-transform: uppercase; display: inline-block; transition: 0.3s; box-shadow: 0 0 15px var(--primary-glow); border: none; cursor: pointer; }
        .btn-neon:hover { background: #fff; transform: translateY(-3px); box-shadow: 0 0 25px rgba(255,255,255,0.4); }
        .btn-outline { border: 1px solid var(--border); padding: 12px 28px; border-radius: 8px; font-weight: 600; transition: 0.3s; display: inline-block; }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }

        /* Tiêu đề Chung */
        .sec-title { font-family: 'Outfit', sans-serif; font-size: 3rem; text-transform: uppercase; margin-bottom: 1rem; line-height: 1.1; }
        .sec-title span { color: var(--primary); }
        .sec-desc { color: var(--text-muted); font-size: 1.1rem; max-width: 600px; margin-bottom: 3rem; }
        .section { padding: 6rem 5%; max-width: 1400px; margin: 0 auto; }

        /* Navbar */
        .navbar { position: fixed; top: 0; width: 100%; padding: 1rem 5%; background: rgba(9,9,11,0.8); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .nav-logo { font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 900; display: flex; align-items: center; gap: 8px; }
        .nav-logo span { color: var(--primary); }
        .nav-links { display: flex; gap: 2rem; font-weight: 500; color: var(--text-muted); }
        .nav-links a:hover { color: var(--primary); }

        /* Hero */
        .hero { min-height: 100vh; display: flex; align-items: center; padding: 0 5%; background: linear-gradient(rgba(9,9,11,0.8), rgba(9,9,11,1)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1920') center/cover; position: relative; }
        .hero-content { max-width: 800px; z-index: 1; margin-top: 5rem; }
        .hero-badge { display: inline-block; padding: 5px 15px; border: 1px solid var(--primary); color: var(--primary); border-radius: 50px; font-weight: 600; margin-bottom: 1.5rem; text-transform: uppercase; font-size: 0.85rem; }
        .hero h1 { font-family: 'Outfit', sans-serif; font-size: clamp(3.5rem, 8vw, 6rem); line-height: 1; text-transform: uppercase; margin-bottom: 1.5rem; }
        .hero h1 span { color: transparent; -webkit-text-stroke: 1px var(--primary); }

        /* Lưới 3/4 Cột Chung */
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; }
        
        /* Card Thiết Kế Chung */
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 2rem; transition: 0.3s; }
        .card:hover { border-color: var(--primary); transform: translateY(-5px); }

        /* [MỚI] Form Đăng Ký Tập Thử */
        .trial-banner { background: linear-gradient(135deg, #18181b 0%, #064e3b 100%); border-radius: 20px; padding: 3rem; margin-top: -80px; position: relative; z-index: 10; border: 1px solid var(--primary); display: flex; flex-wrap: wrap; gap: 2rem; align-items: center; justify-content: space-between; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .trial-text h3 { font-family: 'Outfit'; font-size: 2.5rem; color: var(--primary); text-transform: uppercase; }
        .trial-form { display: flex; gap: 10px; flex: 1; min-width: 300px; }
        .trial-form input { flex: 1; padding: 15px; border-radius: 8px; border: 1px solid var(--border); background: #000; color: #fff; font-size: 1rem; outline: none; }
        .trial-form input:focus { border-color: var(--primary); }

        /* [MỚI] Công cụ BMI */
        .bmi-box { background: var(--bg-card); padding: 3rem; border-radius: 20px; border: 1px dashed var(--primary); text-align: center; }
        .bmi-input-group { display: flex; gap: 1rem; justify-content: center; margin: 2rem 0; }
        .bmi-input-group input { padding: 15px; width: 150px; background: #000; border: 1px solid var(--border); color: #fff; border-radius: 8px; text-align: center; font-size: 1.1rem; }
        #bmiResult { font-size: 2rem; font-family: 'Outfit'; color: var(--primary); margin-top: 1rem; display: none; }

        /* Gói Tập */
        .price-card.popular { border-color: var(--primary); transform: scale(1.05); background: linear-gradient(to bottom, rgba(132,204,22,0.1), var(--bg-card)); }
        .price-card ul { list-style: none; margin: 2rem 0; }
        .price-card ul li { margin-bottom: 10px; display: flex; gap: 10px; }
        .price-card ul li::before { content: '✓'; color: var(--primary); font-weight: bold; }

        /* HLV & Tin tức */
        .img-box { width: 100%; height: 250px; background: #27272a; border-radius: 12px; margin-bottom: 1.5rem; overflow: hidden; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(80%); transition: 0.3s; }
        .card:hover .img-box img { filter: grayscale(0%); }
        .badge { background: rgba(132,204,22,0.2); color: var(--primary); padding: 4px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; display: inline-block; }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .trial-form { flex-direction: column; }
            .price-card.popular { transform: none; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-logo">🐵 MONKEY<span>GYM</span></div>
        <div class="nav-links">
            <a href="#co-so">Cơ sở vật chất</a>
            <a href="#lop-hoc">Lớp học</a>
            <a href="#hlv">Huấn luyện viên</a>
            <a href="#goi-tap">Gói tập</a>
            <a href="#tin-tuc">Tin tức</a>
        </div>
        <div>
            <a href="<?= SITE_URL ?>/login" style="margin-right: 15px; font-weight: bold;">Đăng nhập</a>
            <a href="<?= SITE_URL ?>/register" class="btn-neon" style="padding: 10px 20px;">Gia nhập ngay</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">Phòng Tập Công Nghệ 4.0</div>
            <h1>Kiến Tạo <span>Vóc Dáng</span><br>Chinh Phục Đỉnh Cao</h1>
            <p class="sec-desc" style="font-size: 1.2rem;">Trải nghiệm hệ thống quản lý thông minh nhất 2026. Đội ngũ HLV tâm huyết, cơ sở vật chất 5 sao và cộng đồng gymers nhiệt huyết đang chờ đón bạn.</p>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="#goi-tap" class="btn-neon">Xem gói tập →</a>
                <a href="#co-so" class="btn-outline">Khám phá không gian</a>
            </div>
        </div>
    </section>

    <div style="padding: 0 5%; max-width: 1400px; margin: 0 auto;">
        <div class="trial-banner">
            <div class="trial-text">
                <h3>Trải Nghiệm 3 Ngày Miễn Phí</h3>
                <p>Nhập số điện thoại để nhận ngay mã tập thử tập Gym, Yoga & Xông hơi.</p>
            </div>
            <form class="trial-form" id="formTapThu">
                <input type="text" id="trialName" placeholder="Họ và tên của bạn" required>
                <input type="tel" id="trialPhone" placeholder="Số điện thoại" required>
                <button type="submit" class="btn-neon" id="btnSubmitTrial">Nhận Vé Ngay</button>
            </form>
            <div id="trialMessage" style="width: 100%; margin-top: 15px; font-weight: bold; display: none; text-align: center;"></div>
        </div>
    </div>

    <section id="co-so" class="section">
        <h2 class="sec-title">Hệ Thống <span>Không Gian</span></h2>
        <p class="sec-desc">Không gian tập luyện hiện đại với thiết kế Dark Mode & Neon độc bản.</p>
        <div class="grid-3">
            <div class="card">
                <div class="img-box"><img src="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=800" alt="Khu tập"></div>
                <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Khu Vực Tập Luyện Tập Trung</h3>
                <p style="color: var(--text-muted);">Hệ thống máy tập nhập khẩu 100%, đèn Neon động lực học giúp tăng hiệu suất.</p>
            </div>
            <div class="card">
                <div class="img-box"><img src="https://images.unsplash.com/photo-1576678927484-cc907957088c?q=80&w=800" alt="Sauna"></div>
                <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Xông Hơi & Thư Giãn</h3>
                <p style="color: var(--text-muted);">Phòng Sauna đá muối chuẩn Thụy Điển giúp phục hồi cơ bắp cực tốc sau tập.</p>
            </div>
            <div class="card">
                <div class="img-box"><img src="https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?q=80&w=800" alt="PT Room"></div>
                <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Khu Vực PT 1-1 Riêng Tư</h3>
                <p style="color: var(--text-muted);">Không gian tĩnh, máy móc chuyên biệt nơi các HLV đồng hành cùng lộ trình của bạn.</p>
            </div>
        </div>
    </section>

    <section id="lop-hoc" class="section" style="background: var(--bg-card); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
        <h2 class="sec-title">Lớp Học <span>Năng Lượng</span></h2>
        <p class="sec-desc">Đa dạng bộ môn giúp bạn đốt mỡ, xả stress và kết nối cộng đồng.</p>
        <div class="grid-3">
            <?php if(!empty($classes)): ?>
                <?php foreach($classes as $c): ?>
                <div class="card" style="background: #000;">
                    <div class="badge"><?= htmlspecialchars($c['loai_lop'] ?? 'Lớp Học') ?></div>
                    <h3 style="font-size: 1.8rem; font-family: 'Outfit';"><?= htmlspecialchars($c['ten_lop']) ?></h3>
                    <p style="color: var(--text-muted); margin: 15px 0;"><?= htmlspecialchars(mb_substr(strip_tags($c['mo_ta'] ?? ''), 0, 100)) ?>...</p>
                    <div style="color: var(--primary); font-weight: bold;"><a href="<?= SITE_URL ?>/login">Đăng nhập để xem lịch</a></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; color:var(--text-muted); grid-column: 1/-1;">Đang cập nhật danh sách lớp học.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="section">
        <div class="bmi-box">
            <h2 class="sec-title" style="font-size: 2.5rem;">Kiểm Tra <span>Chỉ Số BMI</span></h2>
            <p>Biết rõ cơ thể mình để chọn phương pháp tập luyện chính xác nhất.</p>
            
            <div class="bmi-input-group">
                <input type="number" id="bmiHeight" placeholder="Chiều cao (cm)" required>
                <input type="number" id="bmiWeight" placeholder="Cân nặng (kg)" required>
            </div>
            <button class="btn-neon" onclick="calculateBMI()">Phân Tích Cơ Thể</button>
            
            <div id="bmiResult"></div>
        </div>

        <script>
            function calculateBMI() {
                let h = document.getElementById('bmiHeight').value;
                let w = document.getElementById('bmiWeight').value;
                if(h > 0 && w > 0) {
                    let bmi = (w / ((h/100) * (h/100))).toFixed(1);
                    let status = bmi < 18.5 ? "Thiếu Cân (Cần tăng cơ)" : (bmi < 25 ? "Cân Đối (Giữ form)" : "Thừa Cân (Cần giảm mỡ)");
                    let resBox = document.getElementById('bmiResult');
                    resBox.style.display = 'block';
                    resBox.innerHTML = `BMI của bạn: ${bmi} - <span style="color: #fff;">${status}</span>`;
                } else {
                    alert("Vui lòng nhập số hợp lệ!");
                }
            }
        </script>
    </section>

    <section id="hlv" class="section">
        <h2 class="sec-title">Đội Ngũ <span>Chuyên Gia</span></h2>
        <p class="sec-desc">Những người thầy, người bạn đồng hành tin cậy trên hành trình của bạn.</p>
        <div class="grid-3">
            <?php if(!empty($trainers)): ?>
                <?php foreach(array_slice($trainers, 0, 3) as $t): ?>
                <div class="card" style="text-align: center;">
                    <div class="img-box">
                        <?php if(!empty($t['anh_dai_dien'])): ?>
                            <img src="<?= ASSET_URL . $t['anh_dai_dien'] ?>" alt="HLV">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=600" alt="HLV">
                        <?php endif; ?>
                    </div>
                    <div class="badge"><?= htmlspecialchars($t['chuyen_mon'] ?? 'Chuyên gia') ?></div>
                    <h3><?= htmlspecialchars($t['ho_ten'] ?? $t['ten_dang_nhap']) ?></h3>
                    <div style="color: #fbbf24; font-weight: bold; margin-top: 10px;">★★★★★ 5.0</div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; color:var(--text-muted); grid-column: 1/-1;">Chưa có dữ liệu HLV.</p>
            <?php endif; ?>
        </div>
    </section>

    <section id="goi-tap" class="section">
        <h2 class="sec-title" style="text-align: center;">Đầu Tư <span>Cho Sức Khỏe</span></h2>
        <p class="sec-desc" style="margin: 0 auto 3rem; text-align: center;">Khoản đầu tư thông minh nhất không bao giờ lỗ.</p>
        <div class="grid-3">
            <?php if(!empty($packages)): ?>
                <?php foreach($packages as $index => $pkg): 
                    $isPopular = ($index === 1); // Đánh dấu gói thứ 2 là phổ biến nhất
                ?>
                <div class="card price-card <?= $isPopular ? 'popular' : '' ?>" <?= $isPopular ? 'style="position: relative;"' : '' ?>>
                    <?php if($isPopular): ?>
                        <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--primary); color: #000; padding: 4px 15px; border-radius: 50px; font-weight: bold; font-size: 0.8rem;">BÁN CHẠY NHẤT</div>
                    <?php endif; ?>
                    
                    <h3 style="font-size: 1.5rem; color: <?= $isPopular ? '#fff' : 'var(--text-muted)' ?>;"><?= htmlspecialchars($pkg['ten_goi']) ?></h3>
                    <div style="font-size: <?= $isPopular ? '3rem' : '2.5rem' ?>; <?= $isPopular ? 'color: var(--primary);' : '' ?> font-family: 'Outfit'; font-weight: 900; margin: 10px 0;">
                        <?= number_format($pkg['gia_tien']) ?>đ
                    </div>
                    <div style="font-size: 0.9rem; color: var(--text-muted);"><?= $pkg['thoi_han_thang'] ?> tháng sử dụng</div>
                    <ul>
                        <li>Full quyền truy cập 24/7</li>
                        <li>Tủ đồ cá nhân & Sauna</li>
                        <?php if($pkg['loai_goi'] === 'pt_1_1' || strpos(strtolower($pkg['ten_goi']), 'pt') !== false): ?>
                            <li style="color: #fff; font-weight: bold;">Kèm theo HLV cá nhân</li>
                        <?php endif; ?>
                    </ul>
                    <a href="<?= SITE_URL ?>/register" class="<?= $isPopular ? 'btn-neon' : 'btn-outline' ?>" style="width: 100%; text-align: center;">Đăng Ký Ngay</a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; color:var(--text-muted); grid-column: 1/-1;">Đang cập nhật danh sách gói tập.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="section" style="background: var(--bg-card); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
        <h2 class="sec-title text-center">Câu Chuyện <span>Thành Công</span></h2>
        <div class="grid-3" style="margin-top: 3rem;">
            <div class="card" style="background: #000;">
                <div style="color: var(--primary); font-size: 2rem; margin-bottom: 10px;">"</div>
                <p style="font-style: italic; color: #ccc; margin-bottom: 20px;">"Môi trường tập ở đây siêu đã, máy móc xịn mà lại không bị quá tải. HLV hướng dẫn nhiệt tình giúp mình giảm 5kg trong 2 tháng."</p>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: #fff; overflow: hidden;"><img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200"></div>
                    <div>
                        <div style="font-weight: bold;">Minh Anh</div>
                        <div style="font-size: 0.8rem; color: var(--primary);">Hội viên VIP</div>
                    </div>
                </div>
            </div>
            <div class="card" style="background: #000;">
                <div style="color: var(--primary); font-size: 2rem; margin-bottom: 10px;">"</div>
                <p style="font-style: italic; color: #ccc; margin-bottom: 20px;">"Công cụ check-in bằng QR quá tiện lợi, mình thỉnh thoảng quên thẻ cứng ở nhà nhưng vẫn quét điện thoại vào tập bình thường."</p>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: #fff; overflow: hidden;"><img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=200"></div>
                    <div>
                        <div style="font-weight: bold;">Tuấn Khang</div>
                        <div style="font-size: 0.8rem; color: var(--primary);">Hội viên Basic</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="tin-tuc" class="section">
        <h2 class="sec-title">Tin Tức <span>& Thông Báo</span></h2>
        <p class="sec-desc">Cập nhật những hoạt động mới nhất từ hệ thống Monkey Gym.</p>
        
        <div class="grid-3">
            <?php if(!empty($announcements_list)): ?>
                <?php foreach(array_slice($announcements_list, 0, 3) as $ann): ?>
                <div class="card" style="display: flex; gap: 20px; align-items: center; background: #000;">
                    <div style="background: rgba(132,204,22,0.1); border: 1px solid var(--primary); color: var(--primary); padding: 15px; border-radius: 12px; text-align: center; min-width: 80px;">
                        <div style="font-size: 2rem; font-weight: 900; font-family: 'Outfit'; line-height: 1;"><?= date('d', strtotime($ann['created_at'])) ?></div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; font-weight: bold;">T<?= date('m', strtotime($ann['created_at'])) ?></div>
                    </div>
                    <div>
                        <h3 style="font-size: 1.2rem; margin-bottom: 5px; text-transform: uppercase;"><?= htmlspecialchars($ann['tieu_de']) ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.95rem;"><?= htmlspecialchars(mb_substr(strip_tags($ann['noi_dung']), 0, 100)) ?>...</p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; color:var(--text-muted); grid-column: 1/-1;">Hiện chưa có thông báo mới.</p>
            <?php endif; ?>
        </div>
    </section>

    <footer style="text-align: center; padding: 4rem 5% 2rem; border-top: 1px solid var(--border);">
        <div class="nav-logo" style="justify-content: center; margin-bottom: 1rem;">🐵 MONKEY<span>GYM</span></div>
        <p style="color: var(--text-muted); font-size: 0.9rem;">© 2026 Monkey Gym Fitness Center. Vượt qua giới hạn, kiến tạo bản thân.</p>
    </footer>

    <script>
        document.getElementById('formTapThu').addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            const btn = document.getElementById('btnSubmitTrial');
            const msgBox = document.getElementById('trialMessage');
            const hoTen = document.getElementById('trialName').value;
            const sdt = document.getElementById('trialPhone').value;
            
            btn.innerHTML = 'Đang xử lý...';
            btn.disabled = true;
            
            const formData = new FormData();
            formData.append('ho_ten', hoTen);
            formData.append('sdt', sdt);
            
            fetch('<?= SITE_URL ?>/api/dang-ky-tap-thu', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                msgBox.style.display = 'block';
                if(data.success) {
                    msgBox.style.color = 'var(--primary)';
                    msgBox.innerHTML = data.message;
                    document.getElementById('formTapThu').reset(); 
                } else {
                    msgBox.style.color = '#ef4444'; 
                    msgBox.innerHTML = 'Lỗi: ' + data.message;
                }
            })
            .catch(error => {
                msgBox.style.display = 'block';
                msgBox.style.color = '#ef4444';
                msgBox.innerHTML = 'Đã xảy ra lỗi kết nối mạng.';
            })
            .finally(() => {
                btn.innerHTML = 'Nhận Vé Ngay';
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
