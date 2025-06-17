-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 17, 2025 at 10:19 AM
-- Server version: 8.0.35
-- PHP Version: 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stdm_app_api_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `telemetry_messages`
--

CREATE TABLE `telemetry_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `device_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nonce_telemetry` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp_telemetry` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `telemetry_messages`
--

INSERT INTO `telemetry_messages` (`id`, `device_id`, `payload`, `nonce_telemetry`, `timestamp_telemetry`, `status`, `processed_at`, `created_at`, `updated_at`) VALUES
(1, 'DyAflnJ3ap70XTZa', '{\"temperature\":25.5,\"humidity\":60,\"status\":\"active\"}', 'b4711792c4957b4f66f27e757368cc52', 1750155252, 'pending', NULL, '2025-06-17 05:16:14', '2025-06-17 05:16:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `telemetry_messages`
--
ALTER TABLE `telemetry_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `telemetry_messages_device_id_index` (`device_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `telemetry_messages`
--
ALTER TABLE `telemetry_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
