-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 31, 2025 at 10:26 AM
-- Server version: 10.11.14-MariaDB-cll-lve
-- PHP Version: 8.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inot1191_henkristal`
--

-- --------------------------------------------------------

--
-- Table structure for table `question_categories`
--

CREATE TABLE `question_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_categories`
--

INSERT INTO `question_categories` (`id`, `name`, `code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Kebersihan', 'CLN', 'Kategori untuk kebersihan area', '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(2, 'Keamanan', 'SEC', 'Kategori untuk keamanan', '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(3, 'Operasional', 'OPR', 'Operasional harian', '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(4, 'Hasil Suhu Produk (Muatan)', 'HSP', NULL, '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(5, 'Area Coldroom', 'CLD', NULL, '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(6, 'Area Ruang Produksi', 'PRD', NULL, '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(7, 'Fisik Luar Kendaraan', 'fisik-luar-kendaraan', NULL, '2025-09-28 19:54:23', '2025-09-28 19:54:23'),
(8, 'Fisik Dalam Kendaraan', 'fisik-dalam-kendaraan', NULL, '2025-09-28 20:23:32', '2025-09-28 20:23:32'),
(9, 'Air Radiator', 'air-radiator', NULL, '2025-09-28 20:23:32', '2025-09-28 20:23:32'),
(10, 'Air Aki', 'air-aki', NULL, '2025-09-28 20:23:32', '2025-09-28 20:23:32'),
(11, 'AKI', 'aki', NULL, '2025-09-28 20:23:32', '2025-09-28 20:23:32'),
(12, 'Rem', 'rem', NULL, '2025-09-28 20:23:32', '2025-09-28 20:23:32'),
(13, 'BBM', 'bbm', NULL, '2025-09-28 20:23:32', '2025-09-28 20:23:32'),
(14, 'Ban Mobil', 'ban-mobil', NULL, '2025-09-28 20:23:32', '2025-09-28 20:23:32'),
(15, 'CHECKLIST HYGIENE STAFF PACKING', 'checklist-hygiene-staff-packing', NULL, '2025-09-28 20:30:43', '2025-09-28 20:30:43'),
(16, 'PERSONAL HYGIENE STAFF MUAT', 'personal-hygiene-staff-muat', NULL, '2025-09-28 20:30:44', '2025-09-28 20:30:44'),
(17, 'PERSONAL HYGIENE CLEANING SERVICE', 'personal-hygiene-cleaning-service', NULL, '2025-09-28 20:30:44', '2025-09-28 20:30:44'),
(18, 'PERSONAL HYGIENE STAFF DRIVER', 'personal-hygiene-staff-driver', NULL, '2025-09-28 20:30:44', '2025-09-28 20:30:44'),
(19, 'PERSONAL HYGIENE STAFF MANAGEMENT', 'personal-hygiene-staff-management', NULL, '2025-09-28 20:30:44', '2025-09-28 20:30:44'),
(20, 'AREA RUANG IN/OUT PRODUKSI', 'area-ruang-inout-produksi', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(21, 'AREA LOADING DOCK', 'area-loading-dock', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(22, 'AREA WATER TREATMENT', 'area-water-treatment', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(23, 'RUANG MESIN', 'ruang-mesin', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(24, 'AREA GUDANG', 'area-gudang', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(25, 'AREA LINGKUNGAN PABRIK', 'area-lingkungan-pabrik', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(26, 'AREA DAPUR UMUM', 'area-dapur-umum', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(27, 'OFFICE /KANTOR (Ruang meeting, ruang dirut, administrasi)', 'office-kantor-ruang-meeting-ruang-dirut-administrasi', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(28, 'AREA PANTRY', 'area-pantry', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(29, 'AREA LAUNDRY ROOM', 'area-laundry-room', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(30, 'AREA TOILET 1 & 2', 'area-toilet-1-2', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(31, 'Wadah sabun bersih', 'wadah-sabun-bersih', NULL, '2025-09-28 20:59:53', '2025-09-28 20:59:53'),
(32, 'PERSONAL HYGIENE STAF SUSUN', 'personal-hygiene-staf-susun', NULL, '2025-09-28 21:14:50', '2025-09-28 21:14:50'),
(33, 'PERSONAL HYGIENE STAF MUAT', 'personal-hygiene-staf-muat', NULL, '2025-09-28 21:14:50', '2025-09-28 21:14:50'),
(34, 'PERSONAL HYGIENE STAF SEALER MANUAL', 'personal-hygiene-staf-sealer-manual', NULL, '2025-09-28 21:14:50', '2025-09-28 21:14:50'),
(35, 'PERSONAL HYGIENE STAF OPERATOR', 'personal-hygiene-staf-operator', NULL, '2025-09-28 21:14:50', '2025-09-28 21:14:50'),
(36, 'PERSONAL HYGIENE STAF DRIVER', 'personal-hygiene-staf-driver', NULL, '2025-09-28 21:14:50', '2025-09-28 21:14:50'),
(37, 'PERSONAL HYGIENE STAF MANAGEMENT', 'personal-hygiene-staf-management', NULL, '2025-09-28 21:14:50', '2025-09-28 21:14:50'),
(38, 'Kesehatan', 'kesehatan', NULL, '2025-09-28 22:21:10', '2025-09-28 22:21:10'),
(39, 'Kerapian', 'kerapian', NULL, '2025-09-28 22:21:10', '2025-09-28 22:21:10'),
(40, 'Tutup Kepala', 'tutup-kepala', NULL, '2025-09-28 22:21:10', '2025-09-28 22:21:10'),
(41, 'Identitas', 'identitas', NULL, '2025-09-28 22:21:10', '2025-09-28 22:21:10'),
(42, 'Masker', 'masker', NULL, '2025-09-28 22:21:10', '2025-09-28 22:21:10'),
(43, 'Sarung Tangan', 'sarung-tangan', NULL, '2025-09-28 22:21:10', '2025-09-28 22:21:10'),
(44, 'Sepatu/Sendal Safety', 'sepatusendal-safety', NULL, '2025-09-28 22:21:10', '2025-09-28 22:21:10'),
(45, 'Cuci Tangan & Kaki', 'cuci-tangan-kaki', NULL, '2025-09-28 22:21:10', '2025-09-28 22:21:10'),
(46, 'Fisik Luar', 'fisik-luar', NULL, '2025-09-28 22:39:15', '2025-09-28 22:39:15'),
(47, 'Box Dalam', 'box-dalam', NULL, '2025-09-28 22:39:15', '2025-09-28 22:39:15'),
(48, 'Box Luar', 'box-luar', NULL, '2025-09-28 22:39:15', '2025-09-28 22:39:15'),
(49, 'Aset Armada', 'aset-armada', NULL, '2025-09-28 22:39:15', '2025-09-28 22:39:15'),
(50, 'Pengecekan', 'pengecekan', NULL, '2025-09-28 23:17:28', '2025-09-28 23:17:28'),
(51, 'Pintu Box', 'pintu-box', NULL, '2025-09-28 23:17:28', '2025-09-28 23:17:28'),
(52, 'Pintu Kendaraan', 'pintu-kendaraan', NULL, '2025-09-28 23:17:28', '2025-09-28 23:17:28'),
(53, 'Kaca Spion', 'kaca-spion', NULL, '2025-09-28 23:17:28', '2025-09-28 23:17:28'),
(54, 'Lampu Mobil (Dekat, Jauh & Sen)', 'lampu-mobil-dekat-jauh-sen', NULL, '2025-09-28 23:17:28', '2025-09-28 23:17:28'),
(55, 'Oli Mobil', 'oli-mobil', NULL, '2025-09-28 23:17:28', '2025-09-28 23:17:28'),
(56, 'KENDARAAN', 'kendaraan', NULL, '2025-09-28 23:17:28', '2025-09-28 23:17:28'),
(57, 'Area Mesin', 'area-mesin', NULL, '2025-10-29 16:13:12', '2025-10-29 16:13:12'),
(58, 'Area Ruang Ganti In & Out (Produksi)', 'area-ruang-ganti-in-out-produksi', NULL, '2025-10-29 16:13:12', '2025-10-29 16:13:12'),
(59, 'Area Smoking', 'area-smoking', NULL, '2025-10-29 16:42:28', '2025-10-29 16:42:28'),
(60, 'Kelistrikan (Panel}', 'kelistrikan-panel', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(61, 'Panel Listrik PLN', 'panel-listrik-pln', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(62, 'Panel Listrik WASA', 'panel-listrik-wasa', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(63, 'Panel Coldroom', 'panel-coldroom', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(64, 'Kabel', 'kabel', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(65, 'Suhu Panel Chiller', 'suhu-panel-chiller', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(66, 'Pompa', 'pompa', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(67, 'Air Tandon', 'air-tandon', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(68, 'Air Bersih (Dari PDAM)', 'air-bersih-dari-pdam', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(69, 'TDS Air Minum (Produksi)', 'tds-air-minum-produksi', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(70, 'Tandon 1', 'tandon-1', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(71, 'Tandon 2', 'tandon-2', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(72, 'Tandon 3', 'tandon-3', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(73, 'Tabung FIlter', 'tabung-filter', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(74, 'Oasing Evoqua', 'oasing-evoqua', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(75, 'Lampu UV', 'lampu-uv', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(76, 'Area Produksi & Cold Room', 'area-produksi-cold-room', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(77, 'Convayer', 'convayer', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(78, 'Kondensor', 'kondensor', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(79, 'Pipa Kondensor', 'pipa-kondensor', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(80, 'Kompresor & Kondensor', 'kompresor-kondensor', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(81, 'Air Curtain', 'air-curtain', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(82, 'MESIN PRODUKSI 5 TON TAHUN 2010', 'mesin-produksi-5-ton-tahun-2010', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(83, 'Panel Mesin', 'panel-mesin', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(84, 'MESIN PRODUKSI 5 TON TAHUN 2012', 'mesin-produksi-5-ton-tahun-2012', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(85, 'Air Penampungan Mesin', 'air-penampungan-mesin', NULL, '2025-10-29 23:55:49', '2025-10-29 23:55:49'),
(86, 'MESIN PRODUKSI 5 TON TAHUN 2016', 'mesin-produksi-5-ton-tahun-2016', NULL, '2025-10-30 02:01:46', '2025-10-30 02:01:46'),
(87, 'MESIN PRODUKSI 20 TON TAHUN 2021', 'mesin-produksi-20-ton-tahun-2021', NULL, '2025-10-30 02:01:46', '2025-10-30 02:01:46'),
(88, 'MESIN PRODUKSI 30 TON TAHUN 2024', 'mesin-produksi-30-ton-tahun-2024', NULL, '2025-10-30 02:01:46', '2025-10-30 02:01:46'),
(89, 'Mesin Screw', 'mesin-screw', NULL, '2025-10-30 03:48:14', '2025-10-30 03:48:14'),
(90, 'Timbangan', 'timbangan', NULL, '2025-10-30 03:48:14', '2025-10-30 03:48:14'),
(91, 'Troli', 'troli', NULL, '2025-10-30 03:48:14', '2025-10-30 03:48:14'),
(92, 'Seluncuran Produk', 'seluncuran-produk', NULL, '2025-10-30 03:48:14', '2025-10-30 03:48:14'),
(93, 'Palet', 'palet', NULL, '2025-10-30 03:48:14', '2025-10-30 03:48:14'),
(94, 'Plastik Kemasan', 'plastik-kemasan', NULL, '2025-10-30 03:48:14', '2025-10-30 03:48:14'),
(95, 'Mesin Sealer', 'mesin-sealer', NULL, '2025-10-30 03:48:14', '2025-10-30 03:48:14'),
(96, 'Termometer Digital / Manual', 'termometer-digital-manual', NULL, '2025-10-30 03:48:14', '2025-10-30 03:48:14'),
(97, 'Pra Operasi Mekanik SMD', 'pra-operasi-mekanik-smd', NULL, '2025-10-30 06:05:29', '2025-10-30 06:05:29'),
(98, 'MESIN PRODUKSI 30 TON TAHUN 2021', 'mesin-produksi-30-ton-tahun-2021', NULL, '2025-10-30 06:49:20', '2025-10-30 06:49:20'),
(99, 'MESIN PRODUKSI 30 TON TAHUN 2022', 'mesin-produksi-30-ton-tahun-2022', NULL, '2025-10-30 06:49:20', '2025-10-30 06:49:20'),
(100, 'MESIN PRODUKSI 30 TON TAHUN 2023', 'mesin-produksi-30-ton-tahun-2023', NULL, '2025-10-30 06:49:20', '2025-10-30 06:49:20'),
(101, 'KONTROLING CCP HENSKRISTAL', 'kontroling-ccp-henskristal', NULL, '2025-10-30 08:03:12', '2025-10-30 08:03:12'),
(102, 'Jenis Temuan Bahaya Kimia', 'jenis-temuan-bahaya-kimia', NULL, '2025-10-30 08:03:12', '2025-10-30 08:03:12'),
(103, 'Jenis Temuan Bahaya Biologi', 'jenis-temuan-bahaya-biologi', NULL, '2025-10-30 08:03:12', '2025-10-30 08:03:12'),
(104, 'Jenis Temuan Bahaya Fisika', 'jenis-temuan-bahaya-fisika', NULL, '2025-10-30 08:03:12', '2025-10-30 08:03:12'),
(105, 'Gudang Kemasan', 'gudang-kemasan', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(106, 'Ruang Ganti In & Out', 'ruang-ganti-in-out', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(107, 'Halaman Depan', 'halaman-depan', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(108, 'Halaman Samping', 'halaman-samping', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(109, 'Rumah Gardu', 'rumah-gardu', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(110, 'Area Bengkel', 'area-bengkel', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(111, 'Toilet Umum 1', 'toilet-umum-1', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(112, 'Toilet Umum 2', 'toilet-umum-2', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(113, 'Area Parkir Motor', 'area-parkir-motor', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(114, 'AREA GUDANG UMUM', 'area-gudang-umum', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(115, 'Parit Halaman Depan (Out)', 'parit-halaman-depan-out', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(116, 'Parit Halaman Samping (In)', 'parit-halaman-samping-in', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(117, 'Tangga In & Out (Office)', 'tangga-in-out-office', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(118, 'Ruang Tamu/Tunggu (Office)', 'ruang-tamutunggu-office', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(119, 'Tempat Wudhu & Musholla (Office)', 'tempat-wudhu-musholla-office', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(120, 'Ruang Kerja Direktur (Office)', 'ruang-kerja-direktur-office', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(121, 'Kamar Tidur Direktur (Office)', 'kamar-tidur-direktur-office', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(122, 'Kamar Mandi Direktur (Office)', 'kamar-mandi-direktur-office', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(123, 'Ruang Administrasi (Office)', 'ruang-administrasi-office', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(124, 'Ruang Manager (Office)', 'ruang-manager-office', NULL, '2025-10-30 09:14:06', '2025-10-30 09:14:06'),
(125, 'Ruang Meeting (Office)', 'ruang-meeting-office', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(126, 'Pantry & Cuci Tangan (Office)', 'pantry-cuci-tangan-office', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(127, 'Toilet Women (Office)', 'toilet-women-office', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(128, 'Toilet Men (Office)', 'toilet-men-office', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(129, 'Janitor Room (Office)', 'janitor-room-office', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(130, 'Apar 1 (Loading Dock)', 'apar-1-loading-dock', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(131, 'Apar 2 (Bengkel)', 'apar-2-bengkel', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(132, 'Apar 3 (Rumah Gardu)', 'apar-3-rumah-gardu', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(133, 'Apar 4 (Pantry Office)', 'apar-4-pantry-office', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(134, 'Apar 5 (Ruang Ganti In & Out)', 'apar-5-ruang-ganti-in-out', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(135, 'Apar 6 (Ruang Mesin)', 'apar-6-ruang-mesin', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(136, 'Apar 7 (Gudang Umum)', 'apar-7-gudang-umum', NULL, '2025-10-30 15:13:59', '2025-10-30 15:13:59'),
(137, 'Gudang Oli', 'gudang-oli', NULL, '2025-10-30 17:41:42', '2025-10-30 17:41:42'),
(138, 'Kalibrasi Thermometer', 'kalibrasi-thermometer', NULL, '2025-10-30 17:42:47', '2025-10-30 17:42:47'),
(139, 'Checklist Area', 'checklist-area', NULL, '2025-10-30 18:13:02', '2025-10-30 18:13:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `question_categories`
--
ALTER TABLE `question_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `question_categories_name_unique` (`name`),
  ADD UNIQUE KEY `question_categories_code_unique` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `question_categories`
--
ALTER TABLE `question_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
