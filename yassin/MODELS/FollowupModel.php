<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Follow-up Model - Handles follow-up-related database operations
 */
class FollowupModel extends BaseModel {
    
    /**
     * Get all follow-ups by consultation ID
     */
    public function getList(int $consultationId): array {
        if ($consultationId <= 0) {
            $this->respond(false, 'consultation_id required', [], 422);
        }
        $stmt = $this->db->prepare("SELECT * FROM dental_follow_ups WHERE consultation_id=:cid ORDER BY followup_date ASC, created_at ASC");
        $stmt->execute([':cid' => $consultationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new follow-up
     */
    public function create(array $data): int {
        $consultationId = (int)($data['consultation_id'] ?? 0);
        $patientId = (int)($data['patient_id'] ?? 0);

        if ($consultationId <= 0) {
            $this->respond(false, 'Missing or invalid consultation_id', [], 422);
        }
        if ($patientId <= 0) {
            $this->respond(false, 'Missing or invalid patient_id', [], 422);
        }

        $stmt = $this->db->prepare("
            INSERT INTO dental_follow_ups
              (consultation_id, patient_id, followup_date, programmed_visit, conclusion_examination,
               next_appointment_date, complaint_diagnosis, treatment_comments)
            VALUES
              (:cid, :pid, :fdate, :prog, :conc, :rdv, :compl, :tx)
        ");
        $stmt->execute([
            ':cid' => $consultationId,
            ':pid' => $patientId,
            ':fdate' => $this->toNullableString($data['followup_date'] ?? null),
            ':prog' => (int)($data['programmed_visit'] ?? 0),
            ':conc' => $this->toNullableString($data['conclusion_examination'] ?? null),
            ':rdv' => $this->toNullableString($data['next_appointment_date'] ?? null),
            ':compl' => $this->toNullableString($data['complaint_diagnosis'] ?? null),
            ':tx' => $this->toNullableString($data['treatment_comments'] ?? null),
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Delete follow-up by ID
     */
    public function delete(int $id): void {
        if ($id <= 0) {
            $this->respond(false, 'id required', [], 422);
        }
        $stmt = $this->db->prepare("DELETE FROM dental_follow_ups WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $this->respond(true, 'Deleted');
    }
}
