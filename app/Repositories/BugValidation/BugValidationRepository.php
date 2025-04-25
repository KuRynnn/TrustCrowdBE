<?php

// app/Repositories/BugValidation/BugValidationRepository.php
namespace App\Repositories\BugValidation;

use App\Models\BugValidation;
use App\Repositories\BaseRepository;

class BugValidationRepository extends BaseRepository
{
    public function __construct(BugValidation $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return $this->model
            ->with(['bugReport', 'qaSpecialist'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById($id)
    {
        return $this->model
            ->with(['bugReport', 'qaSpecialist'])
            ->where('validation_id', $id)
            ->first();
    }

    public function findByQA($qaId)
    {
        return $this->model
            ->with(['bugReport'])
            ->where('qa_id', $qaId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findPending()
    {
        return $this->model
            ->with(['bugReport', 'qaSpecialist'])
            ->whereNull('validated_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByStatus($status)
    {
        return $this->model
            ->with(['bugReport', 'qaSpecialist'])
            ->where('validation_status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function updateById($id, array $data)
    {
        $validation = $this->findById($id);
        return $validation ? tap($validation)->update($data) : null;
    }

    public function deleteById($id)
    {
        $validation = $this->findById($id);
        return $validation ? $validation->delete() : null;
    }

    public function getQuery()
    {
        return $this->model->query();
    }
}