<?php
declare(strict_types=1);

require_once __DIR__ . '/DentalCrudModel.php';

/**
 * Investigation Model - Handles investigation-related database operations
 */
class InvestigationModel extends DentalCrudModel {
    
    public function __construct(PDO $db) {
        parent::__construct($db, 'dental_investigations');
    }

    /**
     * Create a new investigation
     */
    public function create(array $data): int {
        $consultationId = (int)($data['consultation_id'] ?? 0);
        $patientId = (int)($data['patient_id'] ?? 0);
        $exam = trim((string)($data['exam_name'] ?? ''));

        if ($consultationId <= 0) {
            $this->respond(false, 'Missing or invalid consultation_id', [], 422);
        }
        if ($patientId <= 0) {
            $this->respond(false, 'Missing or invalid patient_id', [], 422);
        }
        if ($exam === '') {
            $this->respond(false, 'Missing exam_name', [], 422);
        }

        $stmt = $this->db->prepare("
            INSERT INTO dental_investigations (consultation_id, patient_id, exam_name, results, performed_date)
            VALUES (:cid, :pid, :exam, :results, :date)
        ");
        $stmt->execute([
            ':cid' => $consultationId,
            ':pid' => $patientId,
            ':exam' => $exam,
            ':results' => $this->toNullableString($data['results'] ?? null),
            ':date' => $this->toNullableString($data['performed_date'] ?? null),
        ]);

        return (int)$this->db->lastInsertId();
    }
}
