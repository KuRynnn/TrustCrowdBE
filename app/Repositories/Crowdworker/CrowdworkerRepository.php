<?php

// app/Repositories/Crowdworker/CrowdworkerRepository.php
namespace App\Repositories\Crowdworker;

use App\Models\Crowdworker;
use App\Repositories\BaseRepository;

class CrowdworkerRepository extends BaseRepository
{
    public function __construct(Crowdworker $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return $this->model
            ->with(['uatTasks', 'bugReports'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById($id)
    {
        return $this->model
            ->with(['uatTasks', 'bugReports'])
            ->where('worker_id', $id)
            ->first();
    }

    public function findBySkill($skill)
    {
        return $this->model
            ->where('skills', 'like', "%{$skill}%")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findAvailable()
    {
        return $this->model
            ->whereDoesntHave('uatTasks', function ($query) {
                $query->where('status', 'In Progress');
            })
            ->orWhereHas('uatTasks', function ($query) {
                $query->where('status', 'Completed');
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByEmail($email)
    {
        return $this->model
            ->where('email', $email)
            ->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function updateById($id, array $data)
    {
        $crowdworker = $this->findById($id);
        return $crowdworker ? tap($crowdworker)->update($data) : null;
    }

    public function deleteById($id)
    {
        $crowdworker = $this->findById($id);
        return $crowdworker ? $crowdworker->delete() : null;
    }
}