<?php
declare(strict_types=1);

require_once __DIR__ . '/../MODELS/FollowupModel.php';

/**
 * Follow-up Controller - Handles follow-up-related HTTP requests
 */
class FollowupController {
    private FollowupModel $model;

    public function __construct(PDO $db) {
        $this->model = new FollowupModel($db);
    }

    /**
     * Handle followup_list action
     */
    public function list(array $input): void {
        $consultationId = (int)($input['consultation_id'] ?? 0);
        $data = $this->model->getList($consultationId);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => '', 'data' => $data]);
        exit;
    }

    /**
     * Handle followup_create action
     */
    public function create(array $input): void {
        $id = $this->model->create($input);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Follow-up created', 'data' => ['id' => $id]]);
        exit;
    }

    /**
     * Handle followup_delete action
     */
    public function delete(array $input): void {
        $id = (int)($input['id'] ?? 0);
        $this->model->delete($id);
    }
}
