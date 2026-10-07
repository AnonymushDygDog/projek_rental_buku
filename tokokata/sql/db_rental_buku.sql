CREATE DATABASE IF NOT EXISTS db_rental_buku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_rental_buku;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nomor_telepon VARCHAR(25) NOT NULL,
    alamat TEXT NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    tahun_terbit INT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    harga_sewa_per_hari DECIMAL(10,2) NOT NULL DEFAULT 0,
    foto_buku VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    buku_id INT NOT NULL,
    durasi INT NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL DEFAULT 0,
    foto_identitas VARCHAR(255) NOT NULL,
    bukti_bayar VARCHAR(255) NOT NULL,
    status ENUM('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam',
    tanggal_pinjam DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tanggal_kembali DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Akun admin awal:
-- email: admin@warmbook.local
-- password: admin123  (segera ganti setelah login)
INSERT INTO users (nama_lengkap, email, password, nomor_telepon, alamat, role) VALUES
('Administrator', 'admin@warmbook.local', '$2y$12$Avtj3XQBXjmMnmol12AGe.Kq7OkBboEffQwS/oigYe.1Pvf1seB2O', '081234567890', 'Kantor WarmBook', 'admin');

INSERT INTO buku (judul, penulis, tahun_terbit, stok, harga_sewa_per_hari, foto_buku) VALUES
('Laskar Pelangi', 'Andrea Hirata', 2005, 5, 5000, NULL),
('Bumi Manusia', 'Pramoedya Ananta Toer', 1980, 3, 7000, NULL),
('Sapiens', 'Yuval Noah Harari', 2011, 4, 10000, NULL);

-- Jika sebelumnya sudah ada data lama dengan role member:
-- UPDATE users SET role = 'user' WHERE role = 'member';
