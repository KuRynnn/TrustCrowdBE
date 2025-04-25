<?php

// app/Services/QASpecialist/QASpecialistService.php
namespace App\Services\QASpecialist;

use App\Repositories\QASpecialist\QASpecialistRepository;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\QASpecialistNotFoundException;
use App\Events\QASpecialistCreated;

class QASpecialistService
{
    protected $qaSpecialistRepository;

    public function __construct(QASpecialistRepository $qaSpecialistRepository)
    {
        $this->qaSpecialistRepository = $qaSpecialistRepository;
    }

    public function getAllSpecialists()
    {
        return $this->qaSpecialistRepository->all();
    }

    public function createSpecialist(array $data)
    {
        $specialist = $this->qaSpecialistRepository->create($data);
        event(new QASpecialistCreated($specialist));

        return $specialist;
    }

    public function getSpecialistById($id)
    {
        $specialist = $this->qaSpecialistRepository->findById($id);

        if (!$specialist) {
            throw new QASpecialistNotFoundException('QA Specialist not found');
        }

        return $specialist;
    }

    public function updateSpecialistById($id, array $data)
    {
        $specialist = $this->getSpecialistById($id);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->qaSpecialistRepository->updateById($id, $data);
    }

    public function deleteSpecialistById($id)
    {
        $specialist = $this->getSpecialistById($id);
        return $this->qaSpecialistRepository->deleteById($id);
    }
}