-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 10, 2026 at 09:35 AM
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
-- Table structure for table `dental_assessment_invest_discharge`
--

CREATE TABLE `dental_assessment_invest_discharge` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `height_cm` decimal(6,2) DEFAULT NULL,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `bmi` decimal(6,2) DEFAULT NULL,
  `bmi_status` varchar(50) DEFAULT NULL,
  `systolic_bp` int(11) DEFAULT NULL,
  `diastolic_bp` int(11) DEFAULT NULL,
  `bp_status` varchar(50) DEFAULT NULL,
  `pulse_bpm` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `temperature_c` decimal(4,2) DEFAULT NULL,
  `oxygen_saturation_sao2` decimal(5,2) DEFAULT NULL,
  `pain_score` int(11) DEFAULT NULL,
  `presumptive_diagnosis` text DEFAULT NULL,
  `tb_screening` enum('No symptoms','Presumptive TB','On TB treatment','History of TB','Unknown','Not done') DEFAULT NULL,
  `hiv_status` enum('Negative','Positive','Unknown','Not done') DEFAULT NULL,
  `sti_screening` enum('Negative','Positive','Unknown','Not done','Not applicable') DEFAULT NULL,
  `discharging_status` varchar(255) DEFAULT NULL,
  `discharge_date` date DEFAULT NULL,
  `discharging_service` varchar(255) DEFAULT NULL,
  `clinical_summary` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dental_assessment_invest_discharge`
--
ALTER TABLE `dental_assessment_invest_discharge`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dental_assessment_invest_discharge`
--
ALTER TABLE `dental_assessment_invest_discharge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
