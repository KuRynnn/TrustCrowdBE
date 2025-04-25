<?php

// app/Services/Crowdworker/CrowdworkerService.php
namespace App\Services\Crowdworker;

use App\Repositories\Crowdworker\CrowdworkerRepository;
use App\Exceptions\CrowdworkerNotFoundException;
use App\Events\CrowdworkerCreated;
use Illuminate\Support\Facades\Hash;

class CrowdworkerService
{
    protected $crowdworkerRepository;

    public function __construct(CrowdworkerRepository $crowdworkerRepository)
    {
        $this->crowdworkerRepository = $crowdworkerRepository;
    }

    public function getAllCrowdworkers()
    {
        return $this->crowdworkerRepository->all();
    }

    public function getCrowdworkersBySkill($skill)
    {
        return $this->crowdworkerRepository->findBySkill($skill);
    }

    public function getAvailableCrowdworkers()
    {
        return $this->crowdworkerRepository->findAvailable();
    }

    public function createCrowdworker(array $data)
    {
        $crowdworker = $this->crowdworkerRepository->create($data);
        event(new CrowdworkerCreated($crowdworker));

        return $crowdworker;
    }

    public function getCrowdworkerById($id)
    {
        $crowdworker = $this->crowdworkerRepository->findById($id);

        if (!$crowdworker) {
            throw new CrowdworkerNotFoundException('Crowdworker not found');
        }

        return $crowdworker;
    }

    public function updateCrowdworkerById($id, array $data)
    {
        $crowdworker = $this->getCrowdworkerById($id);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->crowdworkerRepository->updateById($id, $data);
    }

    public function deleteCrowdworkerById($id)
    {
        $crowdworker = $this->getCrowdworkerById($id);
        return $this->crowdworkerRepository->deleteById($id);
    }

    public function getCrowdworkerPerformance($id)
    {
        $crowdworker = $this->getCrowdworkerById($id);
        return [
            'total_tasks' => $crowdworker->uatTasks()->count(),
            'completed_tasks' => $crowdworker->uatTasks()->where('status', 'Completed')->count(),
            'total_bugs_reported' => $crowdworker->bugReports()->count(),
            'valid_bugs' => $crowdworker->bugReports()
                ->whereHas('validation', function ($query) {
                    $query->where('validation_status', 'Valid');
                })
                ->count()
        ];
    }
}