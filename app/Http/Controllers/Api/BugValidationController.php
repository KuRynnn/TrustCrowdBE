<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BugValidation\BugValidationService;
use App\Http\Resources\Api\BugValidationResource;
use App\Http\Requests\Api\BugValidation\CreateBugValidationRequest;
use App\Http\Requests\Api\BugValidation\UpdateBugValidationRequest;
use App\Traits\ApiResponse;

class BugValidationController extends Controller
{
    use ApiResponse;

    protected $bugValidationService;

    public function __construct(BugValidationService $bugValidationService)
    {
        $this->bugValidationService = $bugValidationService;
    }

    public function index()
    {
        $validations = $this->bugValidationService->getAllValidations();
        return $this->successResponse(
            BugValidationResource::collection($validations),
            'Bug validations retrieved successfully'
        );
    }

    public function store(CreateBugValidationRequest $request)
    {
        $validation = $this->bugValidationService->createValidation($request->validated());
        return $this->successResponse(
            new BugValidationResource($validation),
            'Bug validation created successfully',
            201
        );
    }

    public function show($id)
    {
        $validation = $this->bugValidationService->getValidationById($id);
        return $this->successResponse(
            new BugValidationResource($validation),
            'Bug validation retrieved successfully'
        );
    }

    public function update(UpdateBugValidationRequest $request, $id)
    {
        $validation = $this->bugValidationService->updateValidationById($id, $request->validated());
        return $this->successResponse(
            new BugValidationResource($validation),
            'Bug validation updated successfully'
        );
    }

    public function destroy($id)
    {
        $this->bugValidationService->deleteValidationById($id);
        return $this->successResponse(
            null,
            'Bug validation deleted successfully'
        );
    }

    public function getPendingValidations()
    {
        $validations = $this->bugValidationService->getPendingValidations();
        return $this->successResponse(
            BugValidationResource::collection($validations),
            'Pending bug validations retrieved successfully'
        );
    }
}