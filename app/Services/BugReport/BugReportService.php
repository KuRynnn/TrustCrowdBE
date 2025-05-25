<?php

// app/Services/BugReport/BugReportService.php
namespace App\Services\BugReport;

use App\Repositories\BugReport\BugReportRepository;
use App\Exceptions\BugReportNotFoundException;
use App\Events\BugReportCreated;
use App\Events\BugReportRevised;
use Illuminate\Support\Facades\DB;

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

    public function createBugReportRevision($originalBugId, array $data)
    {
        DB::beginTransaction();

        try {
            // Get the original bug report
            $originalBug = $this->getBugReportById($originalBugId);

            // Create a new bug report as a revision
            $data['original_bug_id'] = $originalBugId;
            $data['is_revision'] = true;
            $data['revision_number'] = $originalBug->revisions()->count() + 1;
            $data['task_id'] = $originalBug->task_id;
            $data['worker_id'] = $originalBug->worker_id;

            $revisionBug = $this->bugReportRepository->create($data);

            // Trigger event for notification
            event(new BugReportRevised($revisionBug, $originalBug));

            DB::commit();
            return $revisionBug;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getBugReportHistory($bugId)
    {
        // Get the bug report
        $bugReport = $this->getBugReportById($bugId);

        // If this is a revision, get the original
        if ($bugReport->is_revision) {
            $originalBug = $bugReport->originalBugReport;
            $revisions = $originalBug->revisions()->orderBy('revision_number')->get();
        } else {
            $originalBug = $bugReport;
            $revisions = $bugReport->revisions()->orderBy('revision_number')->get();
        }

        return [
            'original' => $originalBug,
            'revisions' => $revisions,
            'validation' => $bugReport->validation
        ];
    }

    public function getLatestVersionOfBugReport($bugId)
    {
        $bugReport = $this->getBugReportById($bugId);

        // If this is an original bug report, get the latest revision if any
        if (!$bugReport->is_revision) {
            $latestRevision = $bugReport->revisions()
                ->orderBy('revision_number', 'desc')
                ->first();

            return $latestRevision ?: $bugReport;
        }

        // If this is a revision, get the original and then find the latest revision
        $originalBug = $bugReport->originalBugReport;
        $latestRevision = $originalBug->revisions()
            ->orderBy('revision_number', 'desc')
            ->first();

        return $latestRevision ?: $originalBug;
    }
}