<?php
declare(strict_types=1);

require_once __DIR__ . '/../MODELS/ConsultationModel.php';
require_once __DIR__ . '/../MODELS/AssessmentDischargeModel.php';

/**
 * Consultation Controller - Handles consultation-related HTTP requests
 */
class ConsultationController {
    private ConsultationModel $model;
    private AssessmentDischargeModel $assessmentModel;

    public function __construct(PDO $db) {
        $this->model = new ConsultationModel($db);
        $this->assessmentModel = new AssessmentDischargeModel($db);
    }

    /**
     * Handle consultation_create action
     */
    public function create(array $input): void {
        $patientId = (int)($input['patient_id'] ?? 0);
        if ($patientId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Missing or invalid patient_id', 'data' => []]);
            exit;
        }

        $height = $this->toNullableFloat($input['height_cm'] ?? null);
        $weight = $this->toNullableFloat($input['weight_kg'] ?? null);
        $bmi = $this->calcBmi($height, $weight);
        $bmiSt = $this->bmiStatus($bmi);
        $sys = $this->toNullableInt($input['systolic_bp'] ?? null);
        $dia = $this->toNullableInt($input['diastolic_bp'] ?? null);
        $bpSt = $this->bpStatus($sys, $dia);

        $consultationId = $this->model->create($input);

        // Also upsert assessment/discharge record for the consultation
        $this->assessmentModel->upsert(
            $patientId,
            $consultationId,
            $input,
            $height,
            $weight,
            $bmi,
            $bmiSt,
            $sys,
            $dia,
            $bpSt
        );

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Consultation created', 'data' => ['consultation_id' => $consultationId]]);
        exit;
    }

    /**
     * Handle consultation_get action
     */
    public function get(array $input): void {
        $patientId = (int)($input['patient_id'] ?? 0);
        $data = $this->model->getByPatient($patientId);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => '', 'data' => $data]);
        exit;
    }

    /**
     * Handle consultation_getById action
     */
    public function getById(array $input): void {
        $consultationId = (int)($input['consultation_id'] ?? 0);
        $data = $this->model->getById($consultationId);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => '', 'data' => $data]);
        exit;
    }

    /**
     * Handle consultation_update action
     */
    public function update(array $input): void {
        $consultationId = (int)($input['consultation_id'] ?? 0);
        if ($consultationId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Missing or invalid consultation_id', 'data' => []]);
            exit;
        }

        // Fetch patient_id for linking
        $patientId = $this->model->getPatientId($consultationId);

        $height = $this->toNullableFloat($input['height_cm'] ?? null);
        $weight = $this->toNullableFloat($input['weight_kg'] ?? null);
        $bmi = $this->calcBmi($height, $weight);
        $bmiSt = $this->bmiStatus($bmi);
        $sys = $this->toNullableInt($input['systolic_bp'] ?? null);
        $dia = $this->toNullableInt($input['diastolic_bp'] ?? null);
        $bpSt = $this->bpStatus($sys, $dia);

        $this->model->update($input);

        // Update assessment/discharge record
        $this->assessmentModel->upsert(
            $patientId,
            $consultationId,
            $input,
            $height,
            $weight,
            $bmi,
            $bmiSt,
            $sys,
            $dia,
            $bpSt
        );

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Consultation updated', 'data' => ['consultation_id' => $consultationId]]);
        exit;
    }

    // Helper methods
    private function toNullableFloat(mixed $v): ?float {
        if ($v === null || $v === '') return null;
        if (!is_numeric($v)) return null;
        return (float)$v;
    }

    private function toNullableInt(mixed $v): ?int {
        if ($v === null || $v === '') return null;
        if (!is_numeric($v)) return null;
        return (int)$v;
    }

    private function calcBmi(?float $heightCm, ?float $weightKg): ?float {
        if (!$heightCm || !$weightKg) return null;
        if ($heightCm <= 0 || $weightKg <= 0) return null;
        $hm = $heightCm / 100.0;
        return round($weightKg / ($hm * $hm), 2);
    }

    private function bmiStatus(?float $bmi): ?string {
        if ($bmi === null) return null;
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }

    private function bpStatus(?int $sys, ?int $dia): ?string {
        if ($sys === null || $dia === null) return null;
        if ($sys < 120 && $dia < 80) return 'Normal';
        if ($sys < 130 && $dia < 80) return 'Elevated';
        if ($sys < 140 || $dia < 90) return 'High Stage 1';
        return 'High Stage 2';
    }
}
