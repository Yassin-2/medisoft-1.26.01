<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Consultation Model - Handles consultation-related database operations
 */
class ConsultationModel extends BaseModel {
    
    /**
     * Create a new consultation
     */
    public function create(array $data): int {
        $patientId = (int)($data['patient_id'] ?? 0);
        if ($patientId <= 0) {
            $this->respond(false, 'Missing or invalid patient_id', [], 422);
        }

        $height = $this->toNullableFloat($data['height_cm'] ?? null);
        $weight = $this->toNullableFloat($data['weight_kg'] ?? null);
        $bmi = $this->calcBmi($height, $weight);
        $bmiSt = $this->bmiStatus($bmi);
        $sys = $this->toNullableInt($data['systolic_bp'] ?? null);
        $dia = $this->toNullableInt($data['diastolic_bp'] ?? null);
        $bpSt = $this->bpStatus($sys, $dia);

        $stmt = $this->db->prepare("
            INSERT INTO dental_consultation
            (patient_id, chief_complaints, history_present_illness,
             immune_status, gyneco_obstetrical_history, lifestyle, psychosocial_status,
             allergies, allergies_details, family_health_status, family_med_history,
             height_cm, weight_kg, bmi, bmi_status,
             systolic_bp, diastolic_bp, bp_status,
             pulse_bpm, respiratory_rate, temperature_c, oxygen_saturation, pain_score,
             presumptive_diagnosis, tb_screening, hiv_status, sti_screening)
            VALUES
            (:patient_id, :chief_complaints, :history_present_illness,
             :immune_status, :gyneco_obstetrical_history, :lifestyle, :psychosocial_status,
             :allergies, :allergies_details, :family_health_status, :family_med_history,
             :height_cm, :weight_kg, :bmi, :bmi_status,
             :systolic_bp, :diastolic_bp, :bp_status,
             :pulse_bpm, :respiratory_rate, :temperature_c, :oxygen_saturation, :pain_score,
             :presumptive_diagnosis, :tb_screening, :hiv_status, :sti_screening)
        ");

        $stmt->execute([
            ':patient_id' => $patientId,
            ':chief_complaints' => $this->toNullableString($data['chief_complaints'] ?? null),
            ':history_present_illness' => $this->toNullableString($data['history_present_illness'] ?? null),
            ':immune_status' => $this->toNullableString($data['immune_status'] ?? null),
            ':gyneco_obstetrical_history' => $this->toNullableString($data['gyneco_obstetrical_history'] ?? null),
            ':lifestyle' => $this->toNullableString($data['lifestyle'] ?? null),
            ':psychosocial_status' => $this->toNullableString($data['psychosocial_status'] ?? null),
            ':allergies' => (int)($data['allergies'] ?? 0),
            ':allergies_details' => $this->toNullableString($data['allergies_details'] ?? null),
            ':family_health_status' => $this->toNullableString($data['family_health_status'] ?? null),
            ':family_med_history' => $this->toNullableString($data['family_med_history'] ?? null),
            ':height_cm' => $height,
            ':weight_kg' => $weight,
            ':bmi' => $bmi,
            ':bmi_status' => $bmiSt,
            ':systolic_bp' => $sys,
            ':diastolic_bp' => $dia,
            ':bp_status' => $bpSt,
            ':pulse_bpm' => $this->toNullableInt($data['pulse_bpm'] ?? null),
            ':respiratory_rate' => $this->toNullableInt($data['respiratory_rate'] ?? null),
            ':temperature_c' => $this->toNullableFloat($data['temperature_c'] ?? null),
            ':oxygen_saturation' => $this->toNullableFloat($data['oxygen_saturation'] ?? null),
            ':pain_score' => $this->toNullableInt($data['pain_score'] ?? null),
            ':presumptive_diagnosis' => $this->toNullableString($data['presumptive_diagnosis'] ?? null),
            ':tb_screening' => $this->toNullableString($data['tb_screening'] ?? null),
            ':hiv_status' => $this->toNullableString($data['hiv_status'] ?? null),
            ':sti_screening' => $this->toNullableString($data['sti_screening'] ?? null),
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get all consultations for a patient
     */
    public function getByPatient(int $patientId): array {
        if ($patientId <= 0) {
            $this->respond(false, 'patient_id required', [], 422);
        }
        $stmt = $this->db->prepare("SELECT * FROM dental_consultation WHERE patient_id=:pid ORDER BY created_at DESC");
        $stmt->execute([':pid' => $patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get consultation by ID with assessment/discharge data
     */
    public function getById(int $consultationId): array {
        if ($consultationId <= 0) {
            $this->respond(false, 'consultation_id required', [], 422);
        }

        $stmt = $this->db->prepare("SELECT * FROM dental_consultation WHERE id=:id LIMIT 1");
        $stmt->execute([':id' => $consultationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $this->respond(false, 'Not found', [], 404);
        }

        $stmt2 = $this->db->prepare("SELECT * FROM dental_assessment_invest_discharge WHERE consultation_id=:cid LIMIT 1");
        $stmt2->execute([':cid' => $consultationId]);
        $assess = $stmt2->fetch(PDO::FETCH_ASSOC) ?: null;

        return ['consultation' => $row, 'assessment_discharge' => $assess];
    }

    /**
     * Update an existing consultation
     */
    public function update(array $data): int {
        $consultationId = (int)($data['consultation_id'] ?? 0);
        if ($consultationId <= 0) {
            $this->respond(false, 'Missing or invalid consultation_id', [], 422);
        }

        // Fetch patient_id for linking
        $stmt0 = $this->db->prepare("SELECT patient_id FROM dental_consultation WHERE id=:id");
        $stmt0->execute([':id' => $consultationId]);
        $r0 = $stmt0->fetch(PDO::FETCH_ASSOC);
        if (!$r0) {
            $this->respond(false, 'Consultation not found', [], 404);
        }
        $patientId = (int)$r0['patient_id'];

        $height = $this->toNullableFloat($data['height_cm'] ?? null);
        $weight = $this->toNullableFloat($data['weight_kg'] ?? null);
        $bmi = $this->calcBmi($height, $weight);
        $bmiSt = $this->bmiStatus($bmi);
        $sys = $this->toNullableInt($data['systolic_bp'] ?? null);
        $dia = $this->toNullableInt($data['diastolic_bp'] ?? null);
        $bpSt = $this->bpStatus($sys, $dia);

        $stmt = $this->db->prepare("
            UPDATE dental_consultation SET
              chief_complaints=:chief_complaints,
              history_present_illness=:history_present_illness,
              immune_status=:immune_status,
              gyneco_obstetrical_history=:gyneco_obstetrical_history,
              lifestyle=:lifestyle,
              psychosocial_status=:psychosocial_status,
              allergies=:allergies,
              allergies_details=:allergies_details,
              family_health_status=:family_health_status,
              family_med_history=:family_med_history,
              height_cm=:height_cm,
              weight_kg=:weight_kg,
              bmi=:bmi,
              bmi_status=:bmi_status,
              systolic_bp=:systolic_bp,
              diastolic_bp=:diastolic_bp,
              bp_status=:bp_status,
              pulse_bpm=:pulse_bpm,
              respiratory_rate=:respiratory_rate,
              temperature_c=:temperature_c,
              oxygen_saturation=:oxygen_saturation,
              pain_score=:pain_score,
              presumptive_diagnosis=:presumptive_diagnosis,
              tb_screening=:tb_screening,
              hiv_status=:hiv_status,
              sti_screening=:sti_screening
            WHERE id=:id
        ");

        $stmt->execute([
            ':chief_complaints' => $this->toNullableString($data['chief_complaints'] ?? null),
            ':history_present_illness' => $this->toNullableString($data['history_present_illness'] ?? null),
            ':immune_status' => $this->toNullableString($data['immune_status'] ?? null),
            ':gyneco_obstetrical_history' => $this->toNullableString($data['gyneco_obstetrical_history'] ?? null),
            ':lifestyle' => $this->toNullableString($data['lifestyle'] ?? null),
            ':psychosocial_status' => $this->toNullableString($data['psychosocial_status'] ?? null),
            ':allergies' => (int)($data['allergies'] ?? 0),
            ':allergies_details' => $this->toNullableString($data['allergies_details'] ?? null),
            ':family_health_status' => $this->toNullableString($data['family_health_status'] ?? null),
            ':family_med_history' => $this->toNullableString($data['family_med_history'] ?? null),
            ':height_cm' => $height,
            ':weight_kg' => $weight,
            ':bmi' => $bmi,
            ':bmi_status' => $bmiSt,
            ':systolic_bp' => $sys,
            ':diastolic_bp' => $dia,
            ':bp_status' => $bpSt,
            ':pulse_bpm' => $this->toNullableInt($data['pulse_bpm'] ?? null),
            ':respiratory_rate' => $this->toNullableInt($data['respiratory_rate'] ?? null),
            ':temperature_c' => $this->toNullableFloat($data['temperature_c'] ?? null),
            ':oxygen_saturation' => $this->toNullableFloat($data['oxygen_saturation'] ?? null),
            ':pain_score' => $this->toNullableInt($data['pain_score'] ?? null),
            ':presumptive_diagnosis' => $this->toNullableString($data['presumptive_diagnosis'] ?? null),
            ':tb_screening' => $this->toNullableString($data['tb_screening'] ?? null),
            ':hiv_status' => $this->toNullableString($data['hiv_status'] ?? null),
            ':sti_screening' => $this->toNullableString($data['sti_screening'] ?? null),
            ':id' => $consultationId,
        ]);

        return $consultationId;
    }

    /**
     * Get patient ID from consultation
     */
    public function getPatientId(int $consultationId): int {
        $stmt = $this->db->prepare("SELECT patient_id FROM dental_consultation WHERE id=:id");
        $stmt->execute([':id' => $consultationId]);
        $r0 = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r0) {
            $this->respond(false, 'Consultation not found', [], 404);
        }
        return (int)$r0['patient_id'];
    }
}
