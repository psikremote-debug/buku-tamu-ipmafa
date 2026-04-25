-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 05, 2026 at 12:22 PM
-- Server version: 8.0.30
-- PHP Version: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_bukutamu_digitalv2`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `id_admin` int NOT NULL,
  `nama_lengkap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','superadmin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`id_admin`, `nama_lengkap`, `username`, `password_hash`, `email`, `role`, `last_login`, `created_at`) VALUES
(1, 'Super Admin', 'admin', '$2y$10$uwpP6A2OUWp.eXA29dlU/OhlQPkJ6gar1a1U/soEU28H10taPsLyW', '', 'superadmin', '2026-03-05 18:56:21', '2026-01-08 08:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `tb_keperluan`
--

CREATE TABLE `tb_keperluan` (
  `id_keperluan` int NOT NULL,
  `nama_keperluan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_keperluan`
--

INSERT INTO `tb_keperluan` (`id_keperluan`, `nama_keperluan`, `created_at`) VALUES
(1, 'Permohonan Data & Informasi', '2026-03-05 12:18:21'),
(2, 'Layanan Perlindungan & Pengaduan Kasus terhadap perempuan', '2026-03-05 12:18:21'),
(3, 'Layanan Perlindungan & Pengaduan Kasus terhadap anak', '2026-03-05 12:18:21'),
(4, 'Layanan Pendampingan hukum & Psikologi', '2026-03-05 12:18:21'),
(5, 'Layanan Permohonan rujukan ke rumah aman', '2026-03-05 12:18:21'),
(6, 'Permohonan fasilitasi kegiatan perempuan & organisasi perempuan', '2026-03-05 12:18:21'),
(7, 'Permohonan fasilitasi kegiatan anak', '2026-03-05 12:18:21'),
(8, 'Konsultasi & Layanan Alat Obat Kontrasepsi', '2026-03-05 12:18:21'),
(9, 'Koordinasi tim pendamping keluarga', '2026-03-05 12:18:21'),
(10, 'Kunjungan terkait kampung KB', '2026-03-05 12:18:21'),
(11, 'Billing Catin (Bimbingan & Konseling Calon Pengantin)', '2026-03-05 12:18:21'),
(12, 'Audiensi, Studi Banding atau permohonan wawancara', '2026-03-05 12:18:21');

-- --------------------------------------------------------

--
-- Table structure for table `tb_kepuasan`
--

CREATE TABLE `tb_kepuasan` (
  `id_kepuasan` int NOT NULL,
  `id_tamu_fk` int DEFAULT NULL,
  `nama_responden` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_survei` date NOT NULL,
  `waktu_survei` time NOT NULL,
  `nilai_pelayanan` tinyint NOT NULL,
  `nilai_fasilitas` tinyint NOT NULL,
  `nilai_keramahan` tinyint NOT NULL,
  `nilai_kecepatan` tinyint NOT NULL,
  `saran_masukan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_kepuasan`
--

INSERT INTO `tb_kepuasan` (`id_kepuasan`, `id_tamu_fk`, `nama_responden`, `tanggal_survei`, `waktu_survei`, `nilai_pelayanan`, `nilai_fasilitas`, `nilai_keramahan`, `nilai_kecepatan`, `saran_masukan`, `created_at`) VALUES
(2, NULL, 'teset', '2026-03-05', '19:21:31', 5, 5, 5, 5, 'oke', '2026-03-05 12:21:31');

-- --------------------------------------------------------

--
-- Table structure for table `tb_profile`
--

CREATE TABLE `tb_profile` (
  `id_profile` int NOT NULL,
  `nama_perusahaan` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `telepon` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default-logo.png',
  `foto2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default-image.png',
  `info_umum` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_profile`
--

INSERT INTO `tb_profile` (`id_profile`, `nama_perusahaan`, `alamat`, `telepon`, `email`, `website`, `foto`, `foto2`, `info_umum`) VALUES
(1, 'PT MH Studios Lampung', 'Jalan merdeka timur, No 11, Kecamatan Bumi Merdeka Kabupaten Indonesia tengah Profinsi Lingar Pura', '0852667892957', 'mhstudios@example.com', 'https://mhstudios.my.id', 'logo_695fab172fd7b.jpg', 'illust_695faa6320ee2.png', 'MH Sudios adalah dinas pemerintah yang mengelola teknologi informasi, internet, dan penyebaran informasi kepada masyarakat. Tujuannya untuk mendukung layanan publik yang lebih mudah dan cepat.');

-- --------------------------------------------------------

--
-- Table structure for table `tb_tamu`
--

CREATE TABLE `tb_tamu` (
  `id_tamu` int NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `waktu_masuk` time NOT NULL,
  `nama_tamu` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `asal_instansi` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telepon` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bertemu_dengan` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keperluan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan_tambahan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `foto_tamu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanda_tangan` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status_keluar` enum('Masuk','Keluar') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Masuk',
  `waktu_keluar` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_tamu`
--

INSERT INTO `tb_tamu` (`id_tamu`, `tanggal_kunjungan`, `waktu_masuk`, `nama_tamu`, `asal_instansi`, `jabatan`, `no_telepon`, `bertemu_dengan`, `keperluan`, `catatan_tambahan`, `foto_tamu`, `tanda_tangan`, `status_keluar`, `waktu_keluar`, `created_at`) VALUES
(25, '2026-03-05', '19:19:12', 'test', 'test', 'test', '08888888888888', 'Bidang Keluarga Sejahtera', 'Permohonan fasilitasi kegiatan anak', '', 'tamu_20260305_191912_bebe8aa0.jpg', NULL, 'Keluar', '19:20:00', '2026-03-05 12:19:12');

-- --------------------------------------------------------

--
-- Table structure for table `tb_tujuan`
--

CREATE TABLE `tb_tujuan` (
  `id_tujuan` int NOT NULL,
  `nama_tujuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_tujuan`
--

INSERT INTO `tb_tujuan` (`id_tujuan`, `nama_tujuan`, `keterangan`, `created_at`) VALUES
(1, 'Kepala Dinas', NULL, '2026-03-05 12:18:21'),
(2, 'Sekretariat', NULL, '2026-03-05 12:18:21'),
(3, 'Bidang PPPA', NULL, '2026-03-05 12:18:21'),
(4, 'Bidang Keluarga Sejahtera', NULL, '2026-03-05 12:18:21'),
(5, 'Bidang Pengendalian Penduduk', NULL, '2026-03-05 12:18:21'),
(6, 'Bidang Keluarga Berencana', NULL, '2026-03-05 12:18:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tb_keperluan`
--
ALTER TABLE `tb_keperluan`
  ADD PRIMARY KEY (`id_keperluan`);

--
-- Indexes for table `tb_kepuasan`
--
ALTER TABLE `tb_kepuasan`
  ADD PRIMARY KEY (`id_kepuasan`),
  ADD KEY `idx_kepuasan_tamu` (`id_tamu_fk`);

--
-- Indexes for table `tb_profile`
--
ALTER TABLE `tb_profile`
  ADD PRIMARY KEY (`id_profile`);

--
-- Indexes for table `tb_tamu`
--
ALTER TABLE `tb_tamu`
  ADD PRIMARY KEY (`id_tamu`),
  ADD KEY `idx_tamu_tanggal` (`tanggal_kunjungan`);

--
-- Indexes for table `tb_tujuan`
--
ALTER TABLE `tb_tujuan`
  ADD PRIMARY KEY (`id_tujuan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_keperluan`
--
ALTER TABLE `tb_keperluan`
  MODIFY `id_keperluan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tb_kepuasan`
--
ALTER TABLE `tb_kepuasan`
  MODIFY `id_kepuasan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_profile`
--
ALTER TABLE `tb_profile`
  MODIFY `id_profile` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_tamu`
--
ALTER TABLE `tb_tamu`
  MODIFY `id_tamu` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tb_tujuan`
--
ALTER TABLE `tb_tujuan`
  MODIFY `id_tujuan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_kepuasan`
--
ALTER TABLE `tb_kepuasan`
  ADD CONSTRAINT `fk_kepuasan_tamu` FOREIGN KEY (`id_tamu_fk`) REFERENCES `tb_tamu` (`id_tamu`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
