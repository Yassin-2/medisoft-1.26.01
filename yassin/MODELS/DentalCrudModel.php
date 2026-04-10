<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Generic CRUD Model for dental entities (investigations, diagnoses, treatments, consumables, follow-ups)
 */
class DentalCrudModel extends BaseModel {
    private string $table;

    public function __construct(PDO $db, string $table) {
        parent::__construct($db);
        $this->table = $table;
    }

    /**
     * Get all records by consultation ID
     */
    public function getList(int $consultationId, string $orderBy = 'created_at DESC'): array {
        if ($consultationId <= 0) {
            $this->respond(false, 'consultation_id required', [], 422);
        }
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE consultation_id=:cid ORDER BY {$orderBy}");
        $stmt->execute([':cid' => $consultationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete record by ID
     */
    public function delete(int $id): void {
        if ($id <= 0) {
            $this->respond(false, 'id required', [], 422);
        }
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $this->respond(true, 'Deleted');
    }
}
