<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QASpecialist\QASpecialistService;
use App\Http\Resources\Api\QASpecialistResource;
use App\Http\Requests\Api\QASpecialist\CreateQASpecialistRequest;
use App\Http\Requests\Api\QASpecialist\UpdateQASpecialistRequest;
use App\Traits\ApiResponse;

class QASpecialistController extends Controller
{
    use ApiResponse;

    protected $qaSpecialistService;

    public function __construct(QASpecialistService $qaSpecialistService)
    {
        $this->qaSpecialistService = $qaSpecialistService;
    }

    public function index()
    {
        $specialists = $this->qaSpecialistService->getAllSpecialists();
        return $this->successResponse(
            QASpecialistResource::collection($specialists),
            'QA Specialists retrieved successfully'
        );
    }

    public function store(CreateQASpecialistRequest $request)
    {
        $specialist = $this->qaSpecialistService->createSpecialist($request->validated());
        return $this->successResponse(
            new QASpecialistResource($specialist),
            'QA Specialist created successfully',
            201
        );
    }

    public function show($id)
    {
        $specialist = $this->qaSpecialistService->getSpecialistById($id);
        return $this->successResponse(
            new QASpecialistResource($specialist),
            'QA Specialist retrieved successfully'
        );
    }

    public function update(UpdateQASpecialistRequest $request, $id)
    {
        $specialist = $this->qaSpecialistService->updateSpecialistById($id, $request->validated());
        return $this->successResponse(
            new QASpecialistResource($specialist),
            'QA Specialist updated successfully'
        );
    }

    public function destroy($id)
    {
        $this->qaSpecialistService->deleteSpecialistById($id);
        return $this->successResponse(
            null,
            'QA Specialist deleted successfully'
        );
    }
}