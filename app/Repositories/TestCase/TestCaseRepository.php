<?php

// app/Repositories/TestCase/TestCaseRepository.php
namespace App\Repositories\TestCase;

use App\Models\TestCase;
use App\Repositories\BaseRepository;

class TestCaseRepository extends BaseRepository
{
    public function __construct(TestCase $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return $this->model
            ->with(['application', 'qaSpecialist', 'uatTasks'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById($id)
    {
        return $this->model
            ->with(['application', 'qaSpecialist', 'uatTasks'])
            ->where('test_id', $id)
            ->first();
    }

    public function findByApplication($appId)
    {
        return $this->model
            ->with(['qaSpecialist', 'uatTasks'])
            ->where('app_id', $appId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByQASpecialist($qaId)
    {
        return $this->model
            ->with(['application', 'uatTasks'])
            ->where('qa_id', $qaId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByPriority($priority)
    {
        return $this->model
            ->with(['application', 'qaSpecialist', 'uatTasks'])
            ->where('priority', $priority)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function updateById($id, array $data)
    {
        $testCase = $this->findById($id);
        return $testCase ? tap($testCase)->update($data) : null;
    }

    public function deleteById($id)
    {
        $testCase = $this->findById($id);
        return $testCase ? $testCase->delete() : null;
    }
}
