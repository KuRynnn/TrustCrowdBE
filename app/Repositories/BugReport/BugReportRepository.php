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
        return BugReport::where('task_id', $taskId)
            ->with(['evidence', 'validation']) // Ensure evidence is loaded!
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

    public function findAllRevisions($originalBugId)
    {
        return $this->model->where('original_bug_id', $originalBugId)
            ->where('is_revision', true)
            ->orderBy('revision_number')
            ->get();
    }

    public function findOriginalBugs()
    {
        return $this->model->where('is_revision', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findRevisedBugs()
    {
        return $this->model->where('is_revision', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
