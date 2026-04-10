<?php
declare(strict_types=1);

require_once __DIR__ . '/DentalCrudModel.php';

/**
 * Treatment Model - Handles treatment-related database operations
 */
class TreatmentModel extends DentalCrudModel {
    
    public function __construct(PDO $db) {
        parent::__construct($db, 'dental_treatments');
    }

    /**
     * Create a new treatment
     */
    public function create(array $data): int {
        $consultationId = (int)($data['consultation_id'] ?? 0);
        $patientId = (int)($data['patient_id'] ?? 0);
        $med = trim((string)($data['medication_name'] ?? ''));

        if ($consultationId <= 0) {
            $this->respond(false, 'Missing or invalid consultation_id', [], 422);
        }
        if ($patientId <= 0) {
            $this->respond(false, 'Missing or invalid patient_id', [], 422);
        }
        if ($med === '') {
            $this->respond(false, 'Missing medication_name', [], 422);
        }

        $stmt = $this->db->prepare("
            INSERT INTO dental_treatments
              (consultation_id, patient_id, medication_name, dosage, route, frequency, duration)
            VALUES
              (:cid, :pid, :med, :dosage, :route, :frequency, :duration)
        ");
        $stmt->execute([
            ':cid' => $consultationId,
            ':pid' => $patientId,
            ':med' => $med,
            ':dosage' => $this->toNullableString($data['dosage'] ?? null),
            ':route' => $this->toNullableString($data['route'] ?? null),
            ':frequency' => $this->toNullableString($data['frequency'] ?? null),
            ':duration' => $this->toNullableString($data['duration'] ?? null),
        ]);

        return (int)$this->db->lastInsertId();
    }
}
