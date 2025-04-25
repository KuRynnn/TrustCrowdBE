<?php

// app/Services/BugReport/BugReportService.php
namespace App\Services\BugReport;

use App\Repositories\BugReport\BugReportRepository;
use App\Exceptions\BugReportNotFoundException;
use App\Events\BugReportCreated;

class BugReportService
{
    protected $bugReportRepository;

    public function __construct(BugReportRepository $bugReportRepository)
    {
        $this->bugReportRepository = $bugReportRepository;
    }

    public function getAllBugReports()
    {
        return $this->bugReportRepository->all();
    }

    public function getBugReportsByTask($taskId)
    {
        return $this->bugReportRepository->findByTask($taskId);
    }

    public function getBugReportsBySeverity($severity)
    {
        return $this->bugReportRepository->findBySeverity($severity);
    }

    public function getBugReportsByWorker($workerId)
    {
        return $this->bugReportRepository->findByWorker($workerId);
    }

    public function createBugReport(array $data)
    {
        $bugReport = $this->bugReportRepository->create($data);

        // Trigger event for notification
        event(new BugReportCreated($bugReport));

        return $bugReport;
    }

    public function getBugReportById($id)
    {
        $bugReport = $this->bugReportRepository->findById($id);

        if (!$bugReport) {
            throw new BugReportNotFoundException('Bug report not found');
        }

        return $bugReport;
    }

    public function updateBugReportById($id, array $data)
    {
        $bugReport = $this->getBugReportById($id);
        return $this->bugReportRepository->updateById($id, $data);
    }

    public function deleteBugReportById($id)
    {
        $bugReport = $this->getBugReportById($id);
        return $this->bugReportRepository->deleteById($id);
    }

    public function getBugReportStatistics($taskId = null)
    {
        $query = $this->bugReportRepository->getQuery();

        if ($taskId) {
            $query->where('task_id', $taskId);
        }

        return [
            'total_bugs' => $query->count(),
            'by_severity' => $query->groupBy('severity')
                ->selectRaw('severity, count(*) as count')
                ->pluck('count', 'severity'),
            'validation_status' => $query->whereHas('validation')
                ->withCount([
                    'validation as valid_count' => function ($q) {
                        $q->where('validation_status', 'Valid');
                    }
                ])
                ->withCount([
                    'validation as invalid_count' => function ($q) {
                        $q->where('validation_status', 'Invalid');
                    }
                ])
                ->first()
        ];
    }
}