-- ============================================================
-- Cara menambahkan akun ADMIN baru (pilih salah satu metode)
-- Jalankan di phpMyAdmin > tab SQL, atau mysql CLI
-- ============================================================

-- METODE 1 (paling mudah): langsung insert dengan password "admin123"
-- Ganti email & nama sesuai kebutuhan Anda.
INSERT INTO users (nama_lengkap, email, password, nomor_telepon, alamat, role)
VALUES ('Admin Toko Kata', 'admin@tokokata.com', '$2y$10$soPjuuMuWdylJKGlQEZCzOzAa7KiejwmcldxGo2tpz4awCwmXy3be', '081234567890', 'Kantor Pusat', 'admin');

-- Setelah itu login di index.php dengan:
--   Email    : admin@tokokata.com
--   Password : admin123
-- (Segera ganti password setelah login pertama.)

-- ------------------------------------------------------------
-- METODE 2: ubah akun USER yang sudah ada menjadi ADMIN.
-- Ganti 'emailuser@contoh.com' dengan email akun yang sudah terdaftar.
-- UPDATE users SET role = 'admin' WHERE email = 'emailuser@contoh.com';

-- ------------------------------------------------------------
-- METODE 3: ganti password admin yang lupa (password jadi "admin123")
-- UPDATE users SET password = '$2y$10$soPjuuMuWdylJKGlQEZCzOzAa7KiejwmcldxGo2tpz4awCwmXy3be' WHERE email = 'admin@tokokata.com';
