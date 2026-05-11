-- ================================================================
-- MONKEY GYM - Sample Products Data
-- Chay file nay de them san pham mau vao database
-- ================================================================

USE monkey_gym;

-- Insert san pham mau
INSERT INTO SAN_PHAM (ten_sp, gia_tien, mo_ta, ton_kho, hinh_anh) VALUES

-- Whey Protein
('Whey Protein Gold Standard', 450000, 'Whey Protein nhap khau chat luong cao, tang co con duong va phat trien co, 2kg', 15, '/uploads/products/whey-gold.jpg'),

('Whey Protein Iso 100', 520000, 'Whey Protein tach chon 100%, phu hop voi die tat va tang co, 2kg', 12, '/uploads/products/whey-iso100.jpg'),

('Whey Protein Elite XT', 380000, 'Whey Protein cao cap voi vi mat dong va da huong, phu hop vo nhan vien gym, 2kg', 18, '/uploads/products/whey-elite.jpg'),

-- BCAA & Amino Acids
('BCAA Power Pump', 250000, 'BCAA 2:1:1, giup tim duong van dong va phuc hoi nhanh sau tap, 500g', 20, '/uploads/products/bcaa-pump.jpg'),

('L-Glutamine Premium', 310000, 'L-Glutamine tach lot 100%, tang lung tho cot tao va phuc hoi co, 500g', 14, '/uploads/products/l-glutamine.jpg'),

-- Pre-Workout
('Pre-Workout Attack', 380000, 'Pre-Workout voi caffeine cao, tang nang luong va nam nap sau tap, 500g', 16, '/uploads/products/preworkout-attack.jpg'),

('C4 Energy', 420000, 'Pre-Workout tieu chuan the gioi, tang suc manh va dung luong, 500g', 13, '/uploads/products/c4-energy.jpg'),

-- Vitamins & Minerals
('Vitamin D3 + K2', 180000, 'Vitamin D3 va K2 ho tro xương con va mien dich, 120 vien', 25, '/uploads/products/vitamin-d3.jpg'),

('Multivitamin Daily', 220000, 'Vitamin tong hop day du, cac khoang chat can thiet hang ngay, 150 vien', 22, '/uploads/products/multivitamin.jpg'),

('Omega 3 Fish Oil', 260000, 'Omega 3 tu dau HLV, ho tro tim mach va big khop, 100 vien', 18, '/uploads/products/omega3.jpg'),

-- Carbs & Gainers
('Carbs Powder Elite', 290000, 'Carb dung luong cao, tang kang luong va phuc hoi toi uu, 2kg', 10, '/uploads/products/carbs-elite.jpg'),

('Mass Gainer Pro', 520000, 'Mass Gainer voi 1200 Kcal tren serving, phu hop tang khối le, 3kg', 8, '/uploads/products/mass-gainer.jpg'),

-- Creatine
('Creatine Monohydrate', 200000, 'Creatine tinh khiet 100%, tang suc manh va co nang, 500g', 24, '/uploads/products/creatine-mono.jpg'),

('Creatine HCl Advanced', 340000, 'Creatine HCl ap dung nhanh hon, ho tro toi đa cho hien suat, 200g', 16, '/uploads/products/creatine-hcl.jpg'),

-- Fat Burners
('Fat Burner Thermo', 380000, 'Fat burner voi caffeine, tang toc do tieu hao cal, 120 vien', 19, '/uploads/products/fatburner-thermo.jpg'),

('CLA 1000', 310000, 'CLA giup giam mo than va tao co sac lon, 180 vien', 17, '/uploads/products/cla-1000.jpg'),

-- Recovery
('BCAA Recovery Blend', 280000, 'BCAA va Electrolyte phuc hoi toi uu sau tap, 1kg', 14, '/uploads/products/bcaa-recovery.jpg'),

('Collagen Type II', 420000, 'Collagen ho tro khop va soi toc cung khoe, 300g', 11, '/uploads/products/collagen-2.jpg'),

-- Accessories
('Shaker Bottle 700ml', 85000, 'Chai shake high quality, cam dung an toan, khong boc, 1 chiếc', 50, '/uploads/products/shaker-bottle.jpg'),

('Protein Scoop Measuring', 35000, 'Muỗng do protein chinh xac, tro dung chotien luyen tập, 1 chiếc', 100, '/uploads/products/scoop.jpg'),

('Gym Towel Pro', 120000, 'Khăn tập ab sơ từ cotton, hap nuoc tot, hinh dang gym, 1 chiếc', 40, '/uploads/products/gym-towel.jpg'),

('Water Bottle Stainless Steel 1L', 180000, 'Chai nước iu lạnh, giữ nước lạnh 24h, chất liệu thép, 1 chiếc', 35, '/uploads/products/water-bottle.jpg'),

('Resistance Band Set', 250000, 'Tập day dan khóa rang từ nhẹ đến nặng, combo 5 cái', 22, '/uploads/products/resistance-band.jpg'),

('Dumbbell Adjustable 20kg', 1200000, 'Tạ đơn điều chỉnh từ 2.5-20kg, chuyên gia gym, 1 cặp', 6, '/uploads/products/dumbbell-20kg.jpg');

-- Verify data
SELECT COUNT(*) as total_products FROM SAN_PHAM;
SELECT * FROM SAN_PHAM ORDER BY ma_sp DESC;
