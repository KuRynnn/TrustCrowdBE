<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Crowdworker\CrowdworkerService;
use App\Http\Resources\Api\CrowdworkerResource;
use App\Http\Requests\Api\Crowdworker\CreateCrowdworkerRequest;
use App\Http\Requests\Api\Crowdworker\UpdateCrowdworkerRequest;
use App\Traits\ApiResponse;

class CrowdworkerController extends Controller
{
    use ApiResponse;

    protected $crowdworkerService;

    public function __construct(CrowdworkerService $crowdworkerService)
    {
        $this->crowdworkerService = $crowdworkerService;
    }

    public function index()
    {
        $crowdworkers = $this->crowdworkerService->getAllCrowdworkers();
        return $this->successResponse(
            CrowdworkerResource::collection($crowdworkers),
            'Crowdworkers retrieved successfully'
        );
    }

    public function store(CreateCrowdworkerRequest $request)
    {
        $crowdworker = $this->crowdworkerService->createCrowdworker($request->validated());
        return $this->successResponse(
            new CrowdworkerResource($crowdworker),
            'Crowdworker created successfully',
            201
        );
    }

    public function show($id)
    {
        $crowdworker = $this->crowdworkerService->getCrowdworkerById($id);
        return $this->successResponse(
            new CrowdworkerResource($crowdworker),
            'Crowdworker retrieved successfully'
        );
    }

    public function update(UpdateCrowdworkerRequest $request, $id)
    {
        $crowdworker = $this->crowdworkerService->updateCrowdworkerById($id, $request->validated());
        return $this->successResponse(
            new CrowdworkerResource($crowdworker),
            'Crowdworker updated successfully'
        );
    }

    public function destroy($id)
    {
        $this->crowdworkerService->deleteCrowdworkerById($id);
        return $this->successResponse(
            null,
            'Crowdworker deleted successfully'
        );
    }
}