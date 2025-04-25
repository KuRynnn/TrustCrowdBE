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

    public function getAvailableForCrowdworker($crowdworkerId, $maxCrowdworkerPerApp = 10)
    {
        return $this->model
            ->whereHas('testCases') // harus ada test case
            ->whereDoesntHave('uatTasks', function ($query) use ($crowdworkerId) {
                $query->where('worker_id', $crowdworkerId); // belum pernah ambil
            })
            ->withCount('uatTasks') // hitung jumlah crowdworker yang sedang menguji
            ->having('uat_tasks_count', '<', $maxCrowdworkerPerApp) // belum mencapai batas
            ->with(['client', 'testCases.qaSpecialist']) // Added .qaSpecialist to info tambahan
            ->orderBy('created_at', 'desc')
            ->get();
    }
}