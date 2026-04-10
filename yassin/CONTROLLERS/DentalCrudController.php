<?php
declare(strict_types=1);

/**
 * Generic CRUD Controller for dental entities
 */
class DentalCrudController {
    private object $model;
    private string $entityName;

    public function __construct(object $model, string $entityName) {
        $this->model = $model;
        $this->entityName = $entityName;
    }

    /**
     * Handle list action
     */
    public function list(array $input): void {
        $consultationId = (int)($input['consultation_id'] ?? 0);
        $data = $this->model->getList($consultationId);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => '', 'data' => $data]);
        exit;
    }

    /**
     * Handle create action
     */
    public function create(array $input): void {
        $id = $this->model->create($input);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => "{$this->entityName} created", 'data' => ['id' => $id]]);
        exit;
    }

    /**
     * Handle delete action
     */
    public function delete(array $input): void {
        $id = (int)($input['id'] ?? 0);
        $this->model->delete($id);
    }
}
