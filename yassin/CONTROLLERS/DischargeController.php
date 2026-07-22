<?php
declare(strict_types=1);

require_once __DIR__ . '/../MODELS/ConsultationModel.php';
require_once __DIR__ . '/../MODELS/AssessmentDischargeModel.php';

/**
 * Discharge Controller - Handles discharge-related HTTP requests
 */
class DischargeController {
    private ConsultationModel $consultationModel;
    private AssessmentDischargeModel $model;

    public function __construct(PDO $db) {
        $this->consultationModel = new ConsultationModel($db);
        $this->model = new AssessmentDischargeModel($db);
    }

    /**
     * Handle discharge_get action
     */
    public function get(array $input): void {
        $consultationId = (int)($input['consultation_id'] ?? 0);
        $data = $this->model->getDischarge($consultationId);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => '', 'data' => $data]);
        exit;
    }

    /**
     * Handle discharge_save action
     * Uses saveDischargeOnly() to avoid overwriting vital signs stored
     * by the consultation save.
     */
    public function save(array $input): void {
        $consultationId = (int)($input['consultation_id'] ?? 0);
        if ($consultationId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Missing or invalid consultation_id', 'data' => []]);
            exit;
        }

        // Need patient_id to insert a new row if the assessment record doesn't exist yet
        $patientId = $this->consultationModel->getPatientId($consultationId);

        // Save ONLY discharge columns – vitals are left untouched
        $this->model->saveDischargeOnly($patientId, $consultationId, $input);

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Discharge saved', 'data' => ['consultation_id' => $consultationId]]);
        exit;
    }
}

