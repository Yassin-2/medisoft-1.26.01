<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Assessment Discharge Model - Handles assessment and discharge data
 */
class AssessmentDischargeModel extends BaseModel {
    
    /**
     * Upsert (insert or update) assessment/discharge record
     */
    public function upsert(
        int $patientId,
        int $consultationId,
        array $data,
        ?float $height = null,
        ?float $weight = null,
        ?float $bmi = null,
        ?string $bmiSt = null,
        ?int $sys = null,
        ?int $dia = null,
        ?string $bpSt = null
    ): void {
        $stmt = $this->db->prepare("SELECT id FROM dental_assessment_invest_discharge WHERE consultation_id=:cid LIMIT 1");
        $stmt->execute([':cid' => $consultationId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        $payload = [
            ':patient_id' => $patientId,
            ':consultation_id' => $consultationId,
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
            ':oxygen_saturation_sao2' => $this->toNullableFloat($data['oxygen_saturation_sao2'] ?? ($data['oxygen_saturation'] ?? null)),
            ':pain_score' => $this->toNullableInt($data['pain_score'] ?? null),
            ':presumptive_diagnosis' => $this->toNullableString($data['presumptive_diagnosis'] ?? null),
            ':tb_screening' => $this->toNullableString($data['tb_screening'] ?? null),
            ':hiv_status' => $this->toNullableString($data['hiv_status'] ?? null),
            ':sti_screening' => $this->toNullableString($data['sti_screening'] ?? null),
            ':discharging_status' => $this->toNullableString($data['discharging_status'] ?? null),
            ':discharge_date' => $this->toNullableString($data['discharge_date'] ?? null),
            ':discharging_service' => $this->toNullableString($data['discharging_service'] ?? null),
            ':clinical_summary' => $this->toNullableString($data['clinical_summary'] ?? null),
        ];

        if (!$existing) {
            $stmt2 = $this->db->prepare("
                INSERT INTO dental_assessment_invest_discharge
                (patient_id, consultation_id,
                 height_cm, weight_kg, bmi, bmi_status,
                 systolic_bp, diastolic_bp, bp_status,
                 pulse_bpm, respiratory_rate, temperature_c, oxygen_saturation_sao2, pain_score,
                 presumptive_diagnosis, tb_screening, hiv_status, sti_screening,
                 discharging_status, discharge_date, discharging_service, clinical_summary)
                VALUES
                (:patient_id, :consultation_id,
                 :height_cm, :weight_kg, :bmi, :bmi_status,
                 :systolic_bp, :diastolic_bp, :bp_status,
                 :pulse_bpm, :respiratory_rate, :temperature_c, :oxygen_saturation_sao2, :pain_score,
                 :presumptive_diagnosis, :tb_screening, :hiv_status, :sti_screening,
                 :discharging_status, :discharge_date, :discharging_service, :clinical_summary)
            ");
            $stmt2->execute($payload);
            return;
        }

        $payload[':id'] = (int)$existing['id'];
        $stmt3 = $this->db->prepare("
            UPDATE dental_assessment_invest_discharge SET
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
              oxygen_saturation_sao2=:oxygen_saturation_sao2,
              pain_score=:pain_score,
              presumptive_diagnosis=:presumptive_diagnosis,
              tb_screening=:tb_screening,
              hiv_status=:hiv_status,
              sti_screening=:sti_screening,
              discharging_status=:discharging_status,
              discharge_date=:discharge_date,
              discharging_service=:discharging_service,
              clinical_summary=:clinical_summary
            WHERE id=:id
        ");
        $stmt3->execute($payload);
    }

    /**
     * Get discharge information by consultation ID
     */
    public function getDischarge(int $consultationId): array {
        if ($consultationId <= 0) {
            $this->respond(false, 'consultation_id required', [], 422);
        }
        $stmt = $this->db->prepare("
            SELECT
              discharging_status,
              discharge_date,
              discharging_service,
              clinical_summary
            FROM dental_assessment_invest_discharge
            WHERE consultation_id=:cid
            LIMIT 1
        ");
        $stmt->execute([':cid' => $consultationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }
}
