<?php

// app/Repositories/QASpecialist/QASpecialistRepository.php
namespace App\Repositories\QASpecialist;

use App\Models\QASpecialist;
use App\Repositories\BaseRepository;

class QASpecialistRepository extends BaseRepository
{
    public function __construct(QASpecialist $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return $this->model
            ->with(['testCases', 'bugValidations'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById($id)
    {
        return $this->model
            ->with(['testCases', 'bugValidations'])
            ->where('qa_id', $id)
            ->first();
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
        $qaSpecialist = $this->findById($id);
        return $qaSpecialist ? tap($qaSpecialist)->update($data) : null;
    }

    public function deleteById($id)
    {
        $qaSpecialist = $this->findById($id);
        return $qaSpecialist ? $qaSpecialist->delete() : null;
    }
}