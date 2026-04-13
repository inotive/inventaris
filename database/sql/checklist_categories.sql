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
-- Table structure for table `checklist_categories`
--

CREATE TABLE `checklist_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checklist_categories`
--

INSERT INTO `checklist_categories` (`id`, `name`, `code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Operasional Harian', 'OPR', 'Checklist operasional harian', '2025-10-29 01:39:39', '2025-10-29 01:39:39'),
(2, 'K3', 'K3', 'Keselamatan dan Kesehatan Kerja', '2025-10-29 01:39:39', '2025-10-29 01:39:39'),
(3, 'Administrasi', 'ADM', 'Checklist administrasi', '2025-10-29 01:39:39', '2025-10-29 01:39:39'),
(4, 'Peralatan Pabrik', 'PLT', 'Checklist peralatan pabrik', '2025-10-29 01:39:39', '2025-10-29 01:39:39'),
(5, 'Peralatan Kantor', 'PLK', 'Checklist peralatan kantor', '2025-10-29 01:39:39', '2025-10-29 01:39:39'),
(6, 'Perlembagaan', 'PLG', 'Checklist perlembagaan', '2025-10-29 01:39:39', '2025-10-29 01:39:39'),
(7, 'Peralatan Bengkel', 'PLB', 'Checklist peralatan bengkel', '2025-10-29 01:39:39', '2025-10-29 01:39:39'),
(8, 'Peralatan Lainnya', 'PLL', 'Checklist peralatan lainnya', '2025-10-29 01:39:39', '2025-10-29 01:39:39'),
(9, 'Kendaraan', 'VEH', 'Checklist kendaraan', '2025-10-29 01:39:39', '2025-10-29 01:39:39'),
(10, 'PERLENGKAPAN KANTOR', NULL, NULL, '2025-09-27 20:34:38', '2025-09-27 20:34:38'),
(11, 'PERSONEL HYGIENE CHECKLIST', NULL, NULL, '2025-09-27 20:34:38', '2025-09-27 20:34:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `checklist_categories`
--
ALTER TABLE `checklist_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `checklist_categories_name_unique` (`name`),
  ADD UNIQUE KEY `checklist_categories_code_unique` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `checklist_categories`
--
ALTER TABLE `checklist_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
