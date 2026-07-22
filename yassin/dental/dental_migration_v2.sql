-- =============================================================================
-- Dental Care Module – Database Migration v2
-- Run this script on an EXISTING database to bring dental_consultation
-- in sync with the PHP models (ConsultationModel.php).
-- All ALTER statements use IF NOT EXISTS so they are safe to re-run.
-- =============================================================================

-- Vital Signs columns missing from dental_consultation
ALTER TABLE `dental_consultation`
  ADD COLUMN IF NOT EXISTS `height_cm`              DECIMAL(6,2)   DEFAULT NULL AFTER `family_med_history`,
  ADD COLUMN IF NOT EXISTS `weight_kg`              DECIMAL(6,2)   DEFAULT NULL AFTER `height_cm`,
  ADD COLUMN IF NOT EXISTS `bmi`                    DECIMAL(6,2)   DEFAULT NULL AFTER `weight_kg`,
  ADD COLUMN IF NOT EXISTS `bmi_status`             VARCHAR(50)    DEFAULT NULL AFTER `bmi`,
  ADD COLUMN IF NOT EXISTS `systolic_bp`            INT(11)        DEFAULT NULL AFTER `bmi_status`,
  ADD COLUMN IF NOT EXISTS `diastolic_bp`           INT(11)        DEFAULT NULL AFTER `systolic_bp`,
  ADD COLUMN IF NOT EXISTS `bp_status`              VARCHAR(50)    DEFAULT NULL AFTER `diastolic_bp`,
  ADD COLUMN IF NOT EXISTS `pulse_bpm`              INT(11)        DEFAULT NULL AFTER `bp_status`,
  ADD COLUMN IF NOT EXISTS `respiratory_rate`       INT(11)        DEFAULT NULL AFTER `pulse_bpm`,
  ADD COLUMN IF NOT EXISTS `temperature_c`          DECIMAL(4,2)   DEFAULT NULL AFTER `respiratory_rate`,
  ADD COLUMN IF NOT EXISTS `oxygen_saturation`      DECIMAL(5,2)   DEFAULT NULL AFTER `temperature_c`,
  ADD COLUMN IF NOT EXISTS `pain_score`             INT(11)        DEFAULT NULL AFTER `oxygen_saturation`;

-- Clinical Assessment columns
ALTER TABLE `dental_consultation`
  ADD COLUMN IF NOT EXISTS `presumptive_diagnosis`  TEXT           DEFAULT NULL AFTER `pain_score`,
  ADD COLUMN IF NOT EXISTS `tb_screening`           ENUM('No symptoms','Presumptive TB','On TB treatment','History of TB','Unknown','Not done') DEFAULT NULL AFTER `presumptive_diagnosis`,
  ADD COLUMN IF NOT EXISTS `hiv_status`             ENUM('Negative','Positive','Unknown','Not done') DEFAULT NULL AFTER `tb_screening`,
  ADD COLUMN IF NOT EXISTS `sti_screening`          ENUM('Negative','Positive','Unknown','Not done','Not applicable') DEFAULT NULL AFTER `hiv_status`;

-- Performance index on patient_id
ALTER TABLE `dental_consultation`
  ADD INDEX IF NOT EXISTS `idx_dental_consultation_patient_id` (`patient_id`);

-- Performance indexes on consultation_id for child tables
ALTER TABLE `dental_assessment_invest_discharge`
  ADD INDEX IF NOT EXISTS `idx_dad_consultation_id` (`consultation_id`);

ALTER TABLE `dental_investigations`
  ADD INDEX IF NOT EXISTS `idx_inv_consultation_id` (`consultation_id`);

ALTER TABLE `dental_diagnoses`
  ADD INDEX IF NOT EXISTS `idx_dx_consultation_id` (`consultation_id`);

ALTER TABLE `dental_treatments`
  ADD INDEX IF NOT EXISTS `idx_tx_consultation_id` (`consultation_id`);

ALTER TABLE `dental_consumables`
  ADD INDEX IF NOT EXISTS `idx_co_consultation_id` (`consultation_id`);

ALTER TABLE `dental_follow_ups`
  ADD INDEX IF NOT EXISTS `idx_fu_consultation_id` (`consultation_id`);

SELECT 'Migration v2 complete.' AS status;
