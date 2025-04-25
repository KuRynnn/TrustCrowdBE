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

        return [
            'total_test_cases' => $application->testCases()->count(),
            'completed_tasks' => $application->uatTasks()
                ->where('status', 'Completed')
                ->count(),
            'total_bugs' => $application->uatTasks()
                ->withCount('bugReports')
                ->get()
                ->sum('bug_reports_count'),
            'valid_bugs' => $application->uatTasks()
                ->whereHas('bugReports.validation', function ($query) {
                    $query->where('validation_status', 'Valid');
                })
                ->count()
        ];
    }

    public function getAvailableApplicationsForCrowdworker($crowdworkerId)
    {
        return $this->applicationRepository->getAvailableForCrowdworker($crowdworkerId);
    }
}