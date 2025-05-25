<?php

// app/Repositories/Application/ApplicationRepository.php
namespace App\Repositories\Application;

use App\Models\Application;
use App\Repositories\BaseRepository;

class ApplicationRepository extends BaseRepository
{
    public function __construct(Application $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return $this->model
            ->with(['client', 'testCases.qaSpecialist'])  // Added .qaSpecialist
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById($id)
    {
        return $this->model
            ->with(['client', 'testCases.qaSpecialist', 'uatTasks'])  // Added .qaSpecialist
            ->where('app_id', $id)
            ->first();
    }

    public function findByClient($clientId)
    {
        return $this->model
            ->with(['testCases.qaSpecialist'])  // Added .qaSpecialist
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByPlatform($platform)
    {
        return $this->model
            ->with(['client', 'testCases.qaSpecialist'])  // Added .qaSpecialist
            ->where('platform', $platform)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByStatus($status)
    {
        return $this->model
            ->with(['client', 'testCases.qaSpecialist'])  // Added .qaSpecialist
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
        $application = $this->findById($id);
        return $application ? tap($application)->update($data) : null;
    }

    public function deleteById($id)
    {
        $application = $this->findById($id);
        return $application ? $application->delete() : null;
    }

    public function getQuery()
    {
        return $this->model->query();
    }

    public function getAvailableForCrowdworker($crowdworkerId)
    {
        return $this->model
            ->whereHas('testCases') // Must have test cases
            ->whereDoesntHave('uatTasks', function ($query) use ($crowdworkerId) {
                $query->where('worker_id', $crowdworkerId); // Worker hasn't picked this app yet
            })
            ->withCount([
                'uatTasks as unique_workers_count' => function ($query) {
                    // Count UNIQUE workers, not total tasks
                    $query->select(\DB::raw('COUNT(DISTINCT worker_id)'));
                }
            ])
            ->whereRaw('(SELECT COUNT(DISTINCT worker_id) FROM uat_tasks WHERE app_id = applications.app_id) < applications.max_testers')
            ->with(['client', 'testCases.qaSpecialist']) // Load relationships
            ->where('status', 'Ready for Testing') // Only show apps ready for testing
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                // Add max_testers to the response
                $application->current_workers = $application->unique_workers_count ?? 0;
                return $application;
            });
    }
}