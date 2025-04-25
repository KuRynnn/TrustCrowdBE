<?php

namespace App\Repositories\TaskValidation;

use App\Models\TaskValidation;
use App\Repositories\BaseRepository;

class TaskValidationRepository extends BaseRepository
{
    public function __construct(TaskValidation $model)
    {
        parent::__construct($model);
    }

    public function findByTask($taskId)
    {
        return $this->model->where('task_id', $taskId)->first();
    }
}
