<?php
declare(strict_types=1);

require_once __DIR__ . '/DentalCrudModel.php';

/**
 * Consumable Model - Handles consumable-related database operations
 */
class ConsumableModel extends DentalCrudModel {
    
    public function __construct(PDO $db) {
        parent::__construct($db, 'dental_consumables');
    }

    /**
     * Create a new consumable
     */
    public function create(array $data): int {
        $consultationId = (int)($data['consultation_id'] ?? 0);
        $patientId = (int)($data['patient_id'] ?? 0);
        $name = trim((string)($data['consumable_name'] ?? ''));

        if ($consultationId <= 0) {
            $this->respond(false, 'Missing or invalid consultation_id', [], 422);
        }
        if ($patientId <= 0) {
            $this->respond(false, 'Missing or invalid patient_id', [], 422);
        }
        if ($name === '') {
            $this->respond(false, 'Missing consumable_name', [], 422);
        }

        $stmt = $this->db->prepare("
            INSERT INTO dental_consumables (consultation_id, patient_id, consumable_name, quantity, date_issued)
            VALUES (:cid, :pid, :name, :qty, :date)
        ");
        $stmt->execute([
            ':cid' => $consultationId,
            ':pid' => $patientId,
            ':name' => $name,
            ':qty' => $this->toNullableInt($data['quantity'] ?? 1) ?? 1,
            ':date' => $this->toNullableString($data['date_issued'] ?? null),
        ]);

        return (int)$this->db->lastInsertId();
    }
}
