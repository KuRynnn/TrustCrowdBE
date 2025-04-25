<?php

// app/Services/BugValidation/BugValidationService.php
namespace App\Services\BugValidation;

use App\Repositories\BugValidation\BugValidationRepository;
use App\Exceptions\BugValidationNotFoundException;
use App\Events\BugValidationCreated;
use Carbon\Carbon;

class BugValidationService
{
    protected $bugValidationRepository;

    public function __construct(BugValidationRepository $bugValidationRepository)
    {
        $this->bugValidationRepository = $bugValidationRepository;
    }

    public function getAllValidations()
    {
        return $this->bugValidationRepository->all();
    }

    public function getValidationsByQA($qaId)
    {
        return $this->bugValidationRepository->findByQA($qaId);
    }

    public function getPendingValidations()
    {
        return $this->bugValidationRepository->findPending();
    }

    public function createValidation(array $data)
    {
        if (!isset($data['validated_at'])) {
            $data['validated_at'] = Carbon::now();
        }

        $validation = $this->bugValidationRepository->create($data);
        event(new BugValidationCreated($validation));

        return $validation;
    }

    public function getValidationById($id)
    {
        $validation = $this->bugValidationRepository->findById($id);

        if (!$validation) {
            throw new BugValidationNotFoundException('Bug validation not found');
        }

        return $validation;
    }

    public function updateValidationById($id, array $data)
    {
        $validation = $this->getValidationById($id);

        if (isset($data['validation_status']) && $data['validation_status'] !== $validation->validation_status) {
            $data['validated_at'] = Carbon::now();
        }

        return $this->bugValidationRepository->updateById($id, $data);
    }

    public function deleteValidationById($id)
    {
        $validation = $this->getValidationById($id);
        return $this->bugValidationRepository->deleteById($id);
    }

    public function getValidationStatistics($qaId = null)
    {
        $query = $this->bugValidationRepository->getQuery();

        if ($qaId) {
            $query->where('qa_id', $qaId);
        }

        return [
            'total_validations' => $query->count(),
            'by_status' => $query->groupBy('validation_status')
                ->selectRaw('validation_status, count(*) as count')
                ->pluck('count', 'validation_status'),
            'average_validation_time' => $query
                ->whereNotNull('validated_at')
                ->avg(\DB::raw('TIMESTAMPDIFF(MINUTE, created_at, validated_at)')),
            'validations_today' => $query
                ->whereDate('created_at', Carbon::today())
                ->count()
        ];
    }

    public function getQAPerformanceMetrics($qaId)
    {
        $validations = $this->getValidationsByQA($qaId);

        $totalValidations = $validations->count();
        $validBugs = $validations->where('validation_status', 'Valid')->count();
        $invalidBugs = $validations->where('validation_status', 'Invalid')->count();
        $needsMoreInfo = $validations->where('validation_status', 'Needs More Info')->count();

        return [
            'total_validations' => $totalValidations,
            'validation_distribution' => [
                'valid' => $validBugs,
                'invalid' => $invalidBugs,
                'needs_more_info' => $needsMoreInfo
            ],
            'validation_rate' => $totalValidations > 0
                ? ($validBugs / $totalValidations) * 100
                : 0,
            'average_response_time' => $validations
                ->whereNotNull('validated_at')
                ->avg(function ($validation) {
                    return $validation->created_at->diffInMinutes($validation->validated_at);
                })
        ];
    }
}