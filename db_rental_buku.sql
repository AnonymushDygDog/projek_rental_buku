-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2026 at 03:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_rental_buku`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `tahun_terbit` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `harga_sewa_per_hari` decimal(10,2) NOT NULL DEFAULT 0.00,
  `foto_buku` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `judul`, `penulis`, `tahun_terbit`, `stok`, `harga_sewa_per_hari`, `foto_buku`, `created_at`) VALUES
(2, 'Bumi Manusia', 'Pramoedya Ananta Toer', 1980, 3, 7000.00, NULL, '2026-09-25 01:02:29'),
(4, 'Janji', 'Tere Liye', 2021, 10, 4000.00, 'uploads/buku/20260925031134_931c12547768.png', '2026-09-25 01:11:34'),
(5, 'Pareidolia', 'Tere Liye', 2000, 100, 2.50, 'uploads/buku/20261001030034_99247923fee3.png', '2026-10-01 01:00:34');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `durasi` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL DEFAULT 0.00,
  `foto_identitas` varchar(255) NOT NULL,
  `bukti_bayar` varchar(255) NOT NULL,
  `status` enum('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam',
  `tanggal_pinjam` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_kembali` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `user_id`, `buku_id`, `durasi`, `total_harga`, `foto_identitas`, `bukti_bayar`, `status`, `tanggal_pinjam`, `tanggal_kembali`) VALUES
(1, 2, 2, 14, 98000.00, 'uploads/identitas/20260925030722_358337d4220a.jpg', 'uploads/pembayaran/20260925030722_caf2b34057c2.webp', 'dikembalikan', '2026-09-25 08:07:22', '2026-09-25 08:11:57'),
(2, 2, 4, 10, 40000.00, 'uploads/identitas/20260925031231_7275d0b15e97.png', 'uploads/pembayaran/20260925031231_7be3a6cb06db.png', 'dikembalikan', '2026-09-25 08:12:31', '2026-09-25 08:12:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nomor_telepon` varchar(25) NOT NULL,
  `alamat` text NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `nomor_telepon`, `alamat`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@gmail.com', 'admin123', '081234567890', 'Kantor WarmBook', 'admin', '2026-09-25 01:02:29'),
(2, 'sky', 'skyyywooo@gmail.com', '$2y$10$fWc7UavyprFZEfWXHIrz7OWR/VQ1lG4JRycY7iNtBTt7axTWn6WzW', '123', '123', 'user', '2026-09-25 01:06:38'),
(3, 'Soke', 'soke123@gmail.com', '$2y$10$2quOJnhX3qmN9laqfvXgA.RaqvDfeKPGQMyrzO.5aHqcKiqLkF30q', '08123123123', '123', 'user', '2026-09-25 01:27:43'),
(4, 'sky', 'supri@a.com', '$2y$10$U4EQ1GkAqOjUxBUStNPBCejYC84JiWU7KPL96JSi.r2yU2.Efmncq', '08123123123', '123', 'user', '2026-10-01 00:20:56'),
(5, 'Admin Toko Kata', 'admin@tokokata.com', '$2y$10$soPjuuMuWdylJKGlQEZCzOzAa7KiejwmcldxGo2tpz4awCwmXy3be', '081234567890', 'Kantor Pusat', 'admin', '2026-10-01 00:57:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
