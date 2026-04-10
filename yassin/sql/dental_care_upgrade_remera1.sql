-- Upgrade existing dental tables in `remera1` to the updated Dental Care standard
-- Safe-ish approach: use information_schema checks + dynamic SQL so it can be re-run.
-- Target: MariaDB 10.4+

START TRANSACTION;

-- Helper: execute dynamic SQL only when condition is true.
-- Pattern:
--   SET @sql = IF(<cond>, '<sql>', 'SELECT 1');
--   PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─────────────────────────────────────────────────────────────
-- dental_consultation: align column names + add new workflow fields
-- ─────────────────────────────────────────────────────────────

-- hpi -> history_present_illness
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_consultation'
      AND column_name = 'hpi') > 0,
  'ALTER TABLE dental_consultation CHANGE COLUMN hpi history_present_illness TEXT DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ensure immune_status size
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_consultation'
      AND column_name = 'immune_status'
      AND character_maximum_length < 120) > 0,
  'ALTER TABLE dental_consultation MODIFY COLUMN immune_status VARCHAR(120) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- allergies: enforce NOT NULL DEFAULT 0
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_consultation'
      AND column_name = 'allergies'
      AND (is_nullable = 'YES' OR column_default IS NULL)) > 0,
  'ALTER TABLE dental_consultation MODIFY COLUMN allergies TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- add updated_at if missing
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_consultation'
      AND column_name = 'updated_at') = 0,
  'ALTER TABLE dental_consultation ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add missing clinical encounter fields used by the Dental Care standard
-- (keep them nullable to avoid breaking existing rows)
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE() AND table_name = 'dental_consultation' AND column_name = 'height_cm') = 0,
  'ALTER TABLE dental_consultation
     ADD COLUMN height_cm DECIMAL(6,2) DEFAULT NULL,
     ADD COLUMN weight_kg DECIMAL(6,2) DEFAULT NULL,
     ADD COLUMN bmi DECIMAL(6,2) DEFAULT NULL,
     ADD COLUMN bmi_status VARCHAR(60) DEFAULT NULL,
     ADD COLUMN systolic_bp INT(11) DEFAULT NULL,
     ADD COLUMN diastolic_bp INT(11) DEFAULT NULL,
     ADD COLUMN bp_status VARCHAR(60) DEFAULT NULL,
     ADD COLUMN pulse_bpm INT(11) DEFAULT NULL,
     ADD COLUMN respiratory_rate INT(11) DEFAULT NULL,
     ADD COLUMN temperature_c DECIMAL(4,2) DEFAULT NULL,
     ADD COLUMN oxygen_saturation DECIMAL(5,2) DEFAULT NULL,
     ADD COLUMN pain_score INT(11) DEFAULT NULL,
     ADD COLUMN presumptive_diagnosis TEXT DEFAULT NULL,
     ADD COLUMN tb_screening VARCHAR(120) DEFAULT NULL,
     ADD COLUMN hiv_status VARCHAR(120) DEFAULT NULL,
     ADD COLUMN sti_screening VARCHAR(120) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─────────────────────────────────────────────────────────────
-- dental_investigations: date_performed -> performed_date
-- ─────────────────────────────────────────────────────────────
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_investigations'
      AND column_name = 'date_performed') > 0,
  'ALTER TABLE dental_investigations CHANGE COLUMN date_performed performed_date DATE DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─────────────────────────────────────────────────────────────
-- dental_follow_ups: follow_up_date -> followup_date, conclusion -> conclusion_examination
-- ─────────────────────────────────────────────────────────────
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_follow_ups'
      AND column_name = 'follow_up_date') > 0,
  'ALTER TABLE dental_follow_ups CHANGE COLUMN follow_up_date followup_date DATE DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_follow_ups'
      AND column_name = 'conclusion') > 0,
  'ALTER TABLE dental_follow_ups CHANGE COLUMN conclusion conclusion_examination TEXT DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─────────────────────────────────────────────────────────────
-- dental_assessment_invest_discharge: align vitals + screening + discharge status names
-- ─────────────────────────────────────────────────────────────

-- sao2_percent -> oxygen_saturation_sao2
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_assessment_invest_discharge'
      AND column_name = 'sao2_percent') > 0,
  'ALTER TABLE dental_assessment_invest_discharge CHANGE COLUMN sao2_percent oxygen_saturation_sao2 DECIMAL(5,2) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- discharge_status -> discharging_status
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_assessment_invest_discharge'
      AND column_name = 'discharge_status') > 0,
  'ALTER TABLE dental_assessment_invest_discharge CHANGE COLUMN discharge_status discharging_status VARCHAR(255) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- widen decimals + temperature precision + pain_score type
SET @sql := 'ALTER TABLE dental_assessment_invest_discharge
  MODIFY COLUMN height_cm DECIMAL(6,2) DEFAULT NULL,
  MODIFY COLUMN weight_kg DECIMAL(6,2) DEFAULT NULL,
  MODIFY COLUMN bmi DECIMAL(6,2) DEFAULT NULL,
  MODIFY COLUMN temperature_c DECIMAL(4,2) DEFAULT NULL,
  MODIFY COLUMN pain_score INT(11) DEFAULT NULL';
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- screening enums (only if columns exist)
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_assessment_invest_discharge'
      AND column_name = 'tb_screening') > 0,
  "ALTER TABLE dental_assessment_invest_discharge MODIFY COLUMN tb_screening ENUM('No symptoms','Presumptive TB','On TB treatment','History of TB','Unknown','Not done') DEFAULT NULL",
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_assessment_invest_discharge'
      AND column_name = 'hiv_status') > 0,
  "ALTER TABLE dental_assessment_invest_discharge MODIFY COLUMN hiv_status ENUM('Negative','Positive','Unknown','Not done') DEFAULT NULL",
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'dental_assessment_invest_discharge'
      AND column_name = 'sti_screening') > 0,
  "ALTER TABLE dental_assessment_invest_discharge MODIFY COLUMN sti_screening ENUM('Negative','Positive','Unknown','Not done','Not applicable') DEFAULT NULL",
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

COMMIT;

