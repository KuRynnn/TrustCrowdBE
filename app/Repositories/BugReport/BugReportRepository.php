<?php

// app/Repositories/BugReport/BugReportRepository.php
namespace App\Repositories\BugReport;

use App\Models\BugReport;
use App\Repositories\BaseRepository;

class BugReportRepository extends BaseRepository
{
    public function __construct(BugReport $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return $this->model
            ->with(['uatTask', 'crowdworker', 'validation'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById($id)
    {
        return $this->model
            ->with(['uatTask', 'crowdworker', 'validation'])
            ->where('bug_id', $id)
            ->first();
    }

    public function findByTask($taskId)
    {
        return $this->model
            ->with(['crowdworker', 'validation'])
            ->where('task_id', $taskId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByWorker($workerId)
    {
        return $this->model
            ->with(['uatTask', 'validation'])
            ->where('worker_id', $workerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findBySeverity($severity)
    {
        return $this->model
            ->with(['uatTask', 'crowdworker', 'validation'])
            ->where('severity', $severity)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findPendingValidation()
    {
        return $this->model
            ->with(['uatTask', 'crowdworker'])
            ->whereDoesntHave('validation')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function updateById($id, array $data)
    {
        $bugReport = $this->findById($id);
        return $bugReport ? tap($bugReport)->update($data) : null;
    }

    public function deleteById($id)
    {
        $bugReport = $this->findById($id);
        return $bugReport ? $bugReport->delete() : null;
    }

    public function getQuery()
    {
        return $this->model->query();
    }
}
