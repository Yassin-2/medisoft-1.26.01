-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 10, 2026 at 09:34 AM
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
-- Database: `remera1`
--

-- --------------------------------------------------------

--
-- Table structure for table `dental_consultation`
--

CREATE TABLE `dental_consultation` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `chief_complaints` text DEFAULT NULL,
  `history_present_illness` text DEFAULT NULL,
  `immune_status` varchar(120) DEFAULT NULL,
  `gyneco_obstetrical_history` text DEFAULT NULL,
  `lifestyle` text DEFAULT NULL,
  `psychosocial_status` text DEFAULT NULL,
  `allergies` tinyint(1) NOT NULL DEFAULT 0,
  `allergies_details` text DEFAULT NULL,
  `family_health_status` text DEFAULT NULL,
  `family_med_history` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dental_consultation`
--
ALTER TABLE `dental_consultation`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dental_consultation`
--
ALTER TABLE `dental_consultation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
