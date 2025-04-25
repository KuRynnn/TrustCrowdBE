<?php

// app/Repositories/UATTask/UATTaskRepository.php
namespace App\Repositories\UATTask;

use App\Models\UATTask;
use App\Repositories\BaseRepository;

class UATTaskRepository extends BaseRepository
{
    public function __construct(UATTask $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return $this->model
            ->with(['application', 'testCase', 'crowdworker', 'bugReports.validation'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById($id)
    {
        return $this->model
            ->with(['application', 'testCase', 'crowdworker', 'bugReports.validation', 'taskValidation'])
            ->where('task_id', $id)
            ->first();
    }

    public function findByApplication($appId)
    {
        return $this->model
            ->with(['testCase', 'crowdworker', 'bugReports.validation'])
            ->where('app_id', $appId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByCrowdworker($workerId)
    {
        return $this->model
            ->with(['application', 'testCase', 'bugReports.validation'])
            ->where('worker_id', $workerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByStatus($status)
    {
        return $this->model
            ->with(['application', 'testCase', 'crowdworker', 'bugReports.validation'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function updateById($id, array $data)
    {
        $uatTask = $this->findById($id);
        return $uatTask ? tap($uatTask)->update($data) : null;
    }

    public function deleteById($id)
    {
        $uatTask = $this->findById($id);
        return $uatTask ? $uatTask->delete() : null;
    }
}