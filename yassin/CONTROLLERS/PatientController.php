<?php
declare(strict_types=1);

require_once __DIR__ . '/../MODELS/PatientModel.php';

/**
 * Patient Controller - Handles patient-related HTTP requests
 */
class PatientController {
    private PatientModel $model;

    public function __construct(PDO $db) {
        $this->model = new PatientModel($db);
    }

    /**
     * Handle patient_get action
     */
    public function get(array $input): void {
        $patientId = (int)($input['patient_id'] ?? 0);
        $data = $this->model->getPatient($patientId);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => '', 'data' => $data]);
        exit;
    }
}
