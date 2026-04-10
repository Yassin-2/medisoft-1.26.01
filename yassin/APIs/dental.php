<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/Database.php';

// Load all Controllers
require_once __DIR__ . '/../CONTROLLERS/PatientController.php';
require_once __DIR__ . '/../CONTROLLERS/ConsultationController.php';
require_once __DIR__ . '/../CONTROLLERS/DentalCrudController.php';
require_once __DIR__ . '/../CONTROLLERS/FollowupController.php';
require_once __DIR__ . '/../CONTROLLERS/DischargeController.php';

// Load all Models
require_once __DIR__ . '/../MODELS/InvestigationModel.php';
require_once __DIR__ . '/../MODELS/DiagnosisModel.php';
require_once __DIR__ . '/../MODELS/TreatmentModel.php';
require_once __DIR__ . '/../MODELS/ConsumableModel.php';

$db = (new Database())->getConnection();
if (!$db) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'data' => []]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = $_POST;

$action = (string)($input['action'] ?? ($_GET['action'] ?? ''));

try {
    switch ($action) {
        // 1) Patient Details (read-only)
        case 'patient_get':
            $controller = new PatientController($db);
            $controller->get($input);
            break;

        // 2) Consultation (structured clinical documentation)
        case 'consultation_create':
            $controller = new ConsultationController($db);
            $controller->create($input);
            break;
        case 'consultation_get':
            $controller = new ConsultationController($db);
            $controller->get($input);
            break;
        case 'consultation_getById':
            $controller = new ConsultationController($db);
            $controller->getById($input);
            break;
        case 'consultation_update':
            $controller = new ConsultationController($db);
            $controller->update($input);
            break;

        // 3) Investigations
        case 'investigation_list':
            $controller = new DentalCrudController(new InvestigationModel($db), 'Investigation');
            $controller->list($input);
            break;
        case 'investigation_create':
            $controller = new DentalCrudController(new InvestigationModel($db), 'Investigation');
            $controller->create($input);
            break;
        case 'investigation_delete':
            $controller = new DentalCrudController(new InvestigationModel($db), 'Investigation');
            $controller->delete($input);
            break;

        // 4) Final Diagnosis
        case 'diagnosis_list':
            $controller = new DentalCrudController(new DiagnosisModel($db), 'Diagnosis');
            $controller->list($input);
            break;
        case 'diagnosis_create':
            $controller = new DentalCrudController(new DiagnosisModel($db), 'Diagnosis');
            $controller->create($input);
            break;
        case 'diagnosis_delete':
            $controller = new DentalCrudController(new DiagnosisModel($db), 'Diagnosis');
            $controller->delete($input);
            break;

        // 5) Treatment
        case 'treatment_list':
            $controller = new DentalCrudController(new TreatmentModel($db), 'Treatment');
            $controller->list($input);
            break;
        case 'treatment_create':
            $controller = new DentalCrudController(new TreatmentModel($db), 'Treatment');
            $controller->create($input);
            break;
        case 'treatment_delete':
            $controller = new DentalCrudController(new TreatmentModel($db), 'Treatment');
            $controller->delete($input);
            break;

        // 6) Consumables
        case 'consumable_list':
            $controller = new DentalCrudController(new ConsumableModel($db), 'Consumable');
            $controller->list($input);
            break;
        case 'consumable_create':
            $controller = new DentalCrudController(new ConsumableModel($db), 'Consumable');
            $controller->create($input);
            break;
        case 'consumable_delete':
            $controller = new DentalCrudController(new ConsumableModel($db), 'Consumable');
            $controller->delete($input);
            break;

        // 7) Discharge (stored in dental_assessment_invest_discharge)
        case 'discharge_get':
            $controller = new DischargeController($db);
            $controller->get($input);
            break;
        case 'discharge_save':
            $controller = new DischargeController($db);
            $controller->save($input);
            break;

        // 8) General Follow-up
        case 'followup_list':
            $controller = new FollowupController($db);
            $controller->list($input);
            break;
        case 'followup_create':
            $controller = new FollowupController($db);
            $controller->create($input);
            break;
        case 'followup_delete':
            $controller = new FollowupController($db);
            $controller->delete($input);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unknown action', 'data' => []]);
            break;
    }
} catch (Throwable $e) {
    error_log('Dental API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'data' => ['error' => $e->getMessage()]]);
    exit;
}


