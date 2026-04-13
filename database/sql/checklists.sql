-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 31, 2025 at 10:23 AM
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
-- Table structure for table `checklists`
--

CREATE TABLE `checklists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sop_code` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('Draft','Active','Inactive') NOT NULL DEFAULT 'Draft',
  `description` text DEFAULT NULL,
  `type` enum('single','multiple') NOT NULL DEFAULT 'single',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checklists`
--

INSERT INTO `checklists` (`id`, `name`, `sop_code`, `category_id`, `department_id`, `status`, `description`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Checklist Inspeksi Truck', 'SOP-OPR-001', 4, 9, 'Active', 'Langkah pembukaan operasional harian', 'single', '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(2, 'Checklist K3 Truck', 'SOP-K3-010', 4, 9, 'Active', 'Pemeriksaan keselamatan area gudang', 'multiple', '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(3, 'Checklist Distribusi', 'SOP-OPR-002', 5, 21, 'Draft', 'Langkah penutupan operasional harian', 'single', '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(5, 'PRA OPERASI SPV BPP', 'SOP.HK.FSA.4.6.2022', 4, 3, 'Active', 'PRA OPERASI SPV BPP', 'single', '2025-09-27 20:34:38', '2025-10-30 17:47:50'),
(8, 'PRA OPERASI SPV SMD', 'SOP.HK.FSA.4.1 2022', 4, 3, 'Active', 'PRA OPERASI SPV SMD', 'single', '2025-09-27 20:34:38', '2025-10-30 17:48:14'),
(9, 'CHECKLIST HARIAN SPV SMD', 'SOP.HK.FSA.1.10. 2022', 4, 3, 'Active', 'CHECKLIST HARIAN SPV SMD', 'single', '2025-09-27 20:34:38', '2025-10-30 18:24:33'),
(10, 'PRA OPERASI MEKANIK BPP', 'SOP-AUTO-73e151', 9, 19, 'Active', 'PRA OPERASI MEKANIK BPP', 'single', '2025-09-27 20:34:38', '2025-10-30 17:51:21'),
(11, 'PRA OPERASI MEKANIK SMD', 'SOP.HK.FSA.4.6.2022', 7, 19, 'Active', 'PRA OPERASI MEKANIK SMD', 'single', '2025-09-27 20:34:38', '2025-10-30 17:54:44'),
(16, 'KONTROLING CCP HENSKRISTAL SMD', 'SOP.HK.FSA.1.3.2022', 4, 10, 'Active', 'KONTROLING CCP HENSKRISTAL SMD', 'single', '2025-09-27 20:34:38', '2025-10-30 17:53:40'),
(17, 'GENERAL CLEANING SAMARINDA', 'SOP-AUTO-b2e371', 10, 10, 'Active', 'GENERAL CLEANING SAMARINDA', 'single', '2025-09-27 20:34:38', '2025-10-30 18:23:38'),
(18, 'Checklist CS Office SMD', 'SOP.HK.FSA.6.2.2022', 10, 10, 'Active', 'Checklist CS Office SMD', 'single', '2025-09-27 20:34:38', '2025-10-30 18:13:02'),
(19, 'Checklist CS Laundry SMD', 'SOP-AUTO-e237a9', 10, 10, 'Active', 'Checklist CS Laundry SMD', 'single', '2025-09-27 20:34:38', '2025-10-30 18:15:12'),
(20, 'Checklist CS Area Dalam SMD', 'SOP.HK.FSA.6.2.2022', 10, 10, 'Active', 'Checklist CS Area Dalam SMD', 'single', '2025-09-27 20:34:38', '2025-10-30 18:25:10'),
(21, 'Checklist CS Area Luar SMD', 'SOP.HK.FSA.6.2.2022', 10, 10, 'Active', 'Checklist CS Area Luar SMD', 'single', '2025-09-27 20:34:38', '2025-10-30 18:25:29'),
(22, 'VERIFIKASI THERMOMETER SMD', 'SOP-AUTO-705ea4', 4, 19, 'Active', 'VERIFIKASI THERMOMETER SMD', 'single', '2025-09-27 20:34:38', '2025-10-30 18:27:17'),
(23, 'CHECKLIST HARIAN SPV', 'SOP.HK.FSA.1.0/2021', 1, 1, 'Active', 'Std (Standart), Pa (Pagi), Ma (Malam), MS(mesin)', 'single', '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(26, 'CHECKLIST HARIAN (MEKANIK) CATATAN DRIVER', 'SOP.HK.FSA.4.1.2022', 9, 9, 'Active', 'CHECKLIST HARIAN (MEKANIK) CATATAN DRIVER', 'single', '2025-09-28 20:23:32', '2025-10-30 18:28:34'),
(27, 'CHECKLIST PERSONAL HYGENE BALIKPAPAN', 'SOP.HK.FSA.4.1.2022', 11, 19, 'Active', 'CHECKLIST PERSONAL HYGIENE BALIKPAPAN', 'multiple', '2025-09-28 20:30:43', '2025-10-30 18:29:36'),
(31, 'PERSONAL HYGIENE SAMARINDA', 'SOP.HK.FSA.4.1.2022', 11, 19, 'Active', 'PERSONAL HYGIENE SAMARINDA', 'multiple', '2025-09-28 21:14:50', '2025-10-30 18:30:27'),
(32, 'PERSONEL HYGIENE CHECKLIST', 'SOP-AUTO-79b4ca', 11, 10, 'Active', 'PERSONEL HYGIENE CHECKLIST', 'multiple', '2025-09-28 22:21:10', '2025-10-30 04:58:29'),
(33, 'CHECKLIST HARIAN (MEKANIK) CATATAN SPV', 'SOP-AUTO-dbSOP.HK.FSA.4.1.20220718', 9, 19, 'Active', 'CHECKLIST HARIAN (MEKANIK) CATATAN SPV', 'single', '2025-09-28 22:39:15', '2025-10-30 18:33:17'),
(34, 'CHECKLIST HARIAN SPV BPP', 'SOP.HK.FSA.1.10.2022', 1, 10, 'Active', 'CHECKLIST HARIAN SPV BPP', 'single', '2025-09-28 22:52:35', '2025-10-30 18:34:00'),
(35, 'CHECKLIST HARIAN (MEKANIK) CATATAN MEKANIK', 'SOP.HK.FSA.4.1.2022', 9, 12, 'Active', 'CHECKLIST HARIAN (MEKANIK) CATATAN MEKANIK', 'single', '2025-09-28 23:17:28', '2025-10-30 18:34:52'),
(36, 'Cek Kendaraan', 'CK', 9, 10, 'Active', NULL, 'single', '2025-10-29 07:24:42', '2025-10-29 07:24:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `checklists`
--
ALTER TABLE `checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklists_category_id_foreign` (`category_id`),
  ADD KEY `checklists_department_id_foreign` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `checklists`
--
ALTER TABLE `checklists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checklists`
--
ALTER TABLE `checklists`
  ADD CONSTRAINT `checklists_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `checklist_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `checklists_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
