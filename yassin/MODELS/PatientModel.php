<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Patient Model - Handles patient-related database operations
 */
class PatientModel extends BaseModel {
    
    /**
     * Get patient details by ID
     */
    public function getPatient(int $patientId): array {
        if ($patientId <= 0) {
            $this->respond(false, 'patient_id required', [], 422);
        }

        $row = [
            'patient_id' => $patientId,
            'patient_name' => null,
            'insurance' => null,
            'consultation_date' => null,
            'catchment_area' => null,
            'province' => null,
            'district' => null,
            'sector' => null,
            'cell' => null,
            'village' => null,
        ];

        // Prefer latest dental consultation timestamp
        $stmt1 = $this->db->prepare("SELECT created_at FROM dental_consultation WHERE patient_id=:id ORDER BY created_at DESC LIMIT 1");
        $stmt1->execute([':id' => $patientId]);
        $r1 = $stmt1->fetch(PDO::FETCH_ASSOC);
        if ($r1 && !empty($r1['created_at'])) {
            $row['consultation_date'] = $r1['created_at'];
        }

        // Catchment area exists in ANC module consultations; reuse as a reasonable source
        $stmt2 = $this->db->prepare("SELECT catchment_area, practitioner_name, visit_date FROM anc_consultation WHERE patient_id=:id ORDER BY visit_date DESC, created_at DESC LIMIT 1");
        $stmt2->execute([':id' => $patientId]);
        $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($r2) {
            $row['catchment_area'] = $r2['catchment_area'] ?? null;
            // If no dental date yet, fall back to ANC visit date
            if ($row['consultation_date'] === null && !empty($r2['visit_date'])) {
                $row['consultation_date'] = $r2['visit_date'];
            }
        }

        return $row;
    }
}
