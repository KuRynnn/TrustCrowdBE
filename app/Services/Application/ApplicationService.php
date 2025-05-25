<?php

// app/Services/Application/ApplicationService.php
namespace App\Services\Application;

use App\Repositories\Application\ApplicationRepository;
use App\Exceptions\ApplicationNotFoundException;
use App\Events\ApplicationStatusChanged;

class ApplicationService
{
    protected $applicationRepository;

    public function __construct(ApplicationRepository $applicationRepository)
    {
        $this->applicationRepository = $applicationRepository;
    }

    public function getAllApplications()
    {
        return $this->applicationRepository->all();
    }

    public function getApplicationsByClient($clientId)
    {
        return $this->applicationRepository->findByClient($clientId);
    }

    public function getApplicationsByPlatform($platform)
    {
        return $this->applicationRepository->findByPlatform($platform);
    }

    public function createApplication(array $data)
    {
        return $this->applicationRepository->create($data);
    }

    public function getApplicationById($id)
    {
        $application = $this->applicationRepository->findById($id);

        if (!$application) {
            throw new ApplicationNotFoundException('Application not found');
        }

        return $application;
    }

    public function updateApplicationById($id, array $data)
    {
        $application = $this->getApplicationById($id);

        if (isset($data['status']) && $data['status'] !== $application->status) {
            event(new ApplicationStatusChanged($application, $data['status']));
        }

        return $this->applicationRepository->updateById($id, $data);
    }

    public function deleteApplicationById($id)
    {
        $application = $this->getApplicationById($id);
        return $this->applicationRepository->deleteById($id);
    }

    public function getApplicationStatistics($clientId = null)
    {
        $query = $this->applicationRepository->getQuery();

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return [
            'total_applications' => $query->count(),
            'by_status' => $query->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status'),
            'by_platform' => $query->groupBy('platform')
                ->selectRaw('platform, count(*) as count')
                ->pluck('count', 'platform'),
            'test_coverage' => $query->withCount('testCases')
                ->get()
                ->average('test_cases_count')
        ];
    }

    public function getApplicationProgress($id)
    {
        $application = $this->getApplicationById($id);

        // Get total test cases
        $totalTestCases = $application->testCases()->count();

        // Count completed tasks (both 'Completed' and 'Verified' statuses)
        $completedTasks = $application->uatTasks()
            ->whereIn('status', ['Completed', 'Verified'])
            ->count();

        // Count tasks in progress
        $inProgressTasks = $application->uatTasks()
            ->where('status', 'In Progress')
            ->count();

        // Count tasks not started
        $notStartedTasks = $application->uatTasks()
            ->where('status', 'Assigned')
            ->count();

        // Count all bugs
        $totalBugs = $application->uatTasks()
            ->withCount('bugReports')
            ->get()
            ->sum('bug_reports_count');

        // Count valid bugs
        $validBugs = $application->uatTasks()
            ->whereHas('bugReports.validation', function ($query) {
                $query->where('validation_status', 'Valid');
            })
            ->count();

        // Count invalid bugs
        $invalidBugs = $application->uatTasks()
            ->whereHas('bugReports.validation', function ($query) {
                $query->where('validation_status', 'Invalid');
            })
            ->count();

        // Calculate percentage
        $percentage = 0;
        if ($totalTestCases > 0) {
            $percentage = round(($completedTasks / $totalTestCases) * 100, 2);
        }

        return [
            'total_test_cases' => $totalTestCases,
            'completed_tasks' => $completedTasks,
            'completed_test_cases' => $completedTasks, // For backward compatibility
            'in_progress_test_cases' => $inProgressTasks,
            'not_started_test_cases' => $notStartedTasks,
            'total_bugs' => $totalBugs,
            'valid_bugs' => $validBugs,
            'invalid_bugs' => $invalidBugs,
            'percentage' => $percentage
        ];
    }

    /**
     * Get application progress for a specific worker
     * 
     * @param string $id Application ID
     * @param string $workerId Worker ID
     * @return array Progress data filtered by worker
     */
    public function getApplicationProgressForWorker($id, $workerId)
    {
        $application = $this->getApplicationById($id);

        // Get total test cases assigned to this worker
        $workerTasks = $application->uatTasks()
            ->where('worker_id', $workerId)
            ->get();

        $totalAssignedTestCases = $workerTasks->count();

        if ($totalAssignedTestCases === 0) {
            // Worker has no tasks for this application
            return [
                'total_test_cases' => 0,
                'completed_test_cases' => 0,
                'in_progress_test_cases' => 0,
                'not_started_test_cases' => 0,
                'total_bugs' => 0,
                'valid_bugs' => 0,
                'invalid_bugs' => 0,
                'percentage' => 0
            ];
        }

        // Count completed tasks for this worker
        $completedTasks = $workerTasks->filter(function ($task) {
            return in_array($task->status, ['Completed', 'Verified']);
        })->count();

        // Count tasks in progress for this worker
        $inProgressTasks = $workerTasks->filter(function ($task) {
            return $task->status === 'In Progress';
        })->count();

        // Count tasks not started for this worker
        $notStartedTasks = $workerTasks->filter(function ($task) {
            return $task->status === 'Assigned';
        })->count();

        // Count bugs reported by this worker
        $totalBugs = $workerTasks->sum(function ($task) {
            return $task->bugReports->count();
        });

        // Count valid bugs reported by this worker
        $validBugs = $workerTasks->sum(function ($task) {
            return $task->bugReports->filter(function ($bug) {
                return $bug->validation && $bug->validation->validation_status === 'Valid';
            })->count();
        });

        // Count invalid bugs reported by this worker
        $invalidBugs = $workerTasks->sum(function ($task) {
            return $task->bugReports->filter(function ($bug) {
                return $bug->validation && $bug->validation->validation_status === 'Invalid';
            })->count();
        });

        // Calculate percentage of completion for this worker
        $percentage = round(($completedTasks / $totalAssignedTestCases) * 100, 2);

        return [
            'total_test_cases' => $totalAssignedTestCases,
            'completed_tasks' => $completedTasks,
            'completed_test_cases' => $completedTasks,
            'in_progress_test_cases' => $inProgressTasks,
            'not_started_test_cases' => $notStartedTasks,
            'total_bugs' => $totalBugs,
            'valid_bugs' => $validBugs,
            'invalid_bugs' => $invalidBugs,
            'percentage' => $percentage
        ];
    }

    public function getAvailableApplicationsForCrowdworker($crowdworkerId)
    {
        return $this->applicationRepository->getAvailableForCrowdworker($crowdworkerId);
    }
}