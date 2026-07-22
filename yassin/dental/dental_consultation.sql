-- phpMyAdmin SQL Dump – updated to v2
-- Host: localhost
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
--
-- NOTE: This is the COMPLETE table definition after dental_migration_v2.sql
-- has been applied.  Use this for FRESH installs only.
-- For existing databases, run dental_migration_v2.sql instead.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `remera1`

-- --------------------------------------------------------
-- Table structure for table `dental_consultation`
-- --------------------------------------------------------

CREATE TABLE `dental_consultation` (
  `id`                          int(11)        NOT NULL,
  `patient_id`                  int(11)        NOT NULL,

  -- 2.1 Present Illness
  `chief_complaints`            text           DEFAULT NULL,
  `history_present_illness`     text           DEFAULT NULL,

  -- 2.2 Past Medical History
  `immune_status`               varchar(120)   DEFAULT NULL,
  `gyneco_obstetrical_history`  text           DEFAULT NULL,
  `lifestyle`                   text           DEFAULT NULL,
  `psychosocial_status`         text           DEFAULT NULL,
  `allergies`                   tinyint(1)     NOT NULL DEFAULT 0,
  `allergies_details`           text           DEFAULT NULL,

  -- 2.3 Family History
  `family_health_status`        text           DEFAULT NULL,
  `family_med_history`          text           DEFAULT NULL,

  -- 2.4 Physical Examination – Anthropometric & Vital Signs
  `height_cm`                   decimal(6,2)   DEFAULT NULL,
  `weight_kg`                   decimal(6,2)   DEFAULT NULL,
  `bmi`                         decimal(6,2)   DEFAULT NULL,
  `bmi_status`                  varchar(50)    DEFAULT NULL,
  `systolic_bp`                 int(11)        DEFAULT NULL,
  `diastolic_bp`                int(11)        DEFAULT NULL,
  `bp_status`                   varchar(50)    DEFAULT NULL,
  `pulse_bpm`                   int(11)        DEFAULT NULL,
  `respiratory_rate`            int(11)        DEFAULT NULL,
  `temperature_c`               decimal(4,2)   DEFAULT NULL,
  `oxygen_saturation`           decimal(5,2)   DEFAULT NULL,
  `pain_score`                  int(11)        DEFAULT NULL,

  -- 2.4 Clinical Assessments
  `presumptive_diagnosis`       text           DEFAULT NULL,
  `tb_screening`                enum('No symptoms','Presumptive TB','On TB treatment','History of TB','Unknown','Not done') DEFAULT NULL,
  `hiv_status`                  enum('Negative','Positive','Unknown','Not done') DEFAULT NULL,
  `sti_screening`               enum('Negative','Positive','Unknown','Not done','Not applicable') DEFAULT NULL,

  `created_at`                  timestamp      NULL DEFAULT current_timestamp(),
  `updated_at`                  timestamp      NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Indexes
ALTER TABLE `dental_consultation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dental_consultation_patient_id` (`patient_id`);

-- AUTO_INCREMENT
ALTER TABLE `dental_consultation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
