<?php
declare(strict_types=1);

require_once __DIR__ . '/DentalCrudModel.php';

/**
 * Diagnosis Model - Handles diagnosis-related database operations
 */
class DiagnosisModel extends DentalCrudModel {
    
    public function __construct(PDO $db) {
        parent::__construct($db, 'dental_diagnoses');
    }

    /**
     * Create a new diagnosis
     */
    public function create(array $data): int {
        $consultationId = (int)($data['consultation_id'] ?? 0);
        $patientId = (int)($data['patient_id'] ?? 0);
        $name = trim((string)($data['diagnosis_name'] ?? ''));

        if ($consultationId <= 0) {
            $this->respond(false, 'Missing or invalid consultation_id', [], 422);
        }
        if ($patientId <= 0) {
            $this->respond(false, 'Missing or invalid patient_id', [], 422);
        }
        if ($name === '') {
            $this->respond(false, 'Missing diagnosis_name', [], 422);
        }

        $stmt = $this->db->prepare("
            INSERT INTO dental_diagnoses
              (consultation_id, patient_id, diagnosis_name, diagnosis_code, diagnosis_date, clinical_notes)
            VALUES
              (:cid, :pid, :name, :code, :date, :notes)
        ");
        $stmt->execute([
            ':cid' => $consultationId,
            ':pid' => $patientId,
            ':name' => $name,
            ':code' => $this->toNullableString($data['diagnosis_code'] ?? null),
            ':date' => $this->toNullableString($data['diagnosis_date'] ?? null),
            ':notes' => $this->toNullableString($data['clinical_notes'] ?? null),
        ]);

        return (int)$this->db->lastInsertId();
    }
}
