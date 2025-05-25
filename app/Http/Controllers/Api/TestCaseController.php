<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TestCase\TestCaseService;
use App\Http\Resources\Api\TestCaseResource;
use App\Http\Requests\Api\TestCase\CreateTestCaseRequest;
use App\Http\Requests\Api\TestCase\UpdateTestCaseRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request; // Add this import

class TestCaseController extends Controller
{
    use ApiResponse;

    protected $testCaseService;

    public function __construct(TestCaseService $testCaseService)
    {
        $this->testCaseService = $testCaseService;
    }

    public function index()
    {
        $testCases = $this->testCaseService->getAllTestCases();
        return $this->successResponse(
            TestCaseResource::collection($testCases),
            'Test cases retrieved successfully'
        );
    }

    public function store(CreateTestCaseRequest $request)
    {
        // No need for manual validation since we're using the Form Request class
        $testCase = $this->testCaseService->createTestCase($request->validated());

        return $this->successResponse(
            new TestCaseResource($testCase),
            'Test case created successfully',
            201
        );
    }

    public function show($id)
    {
        $testCase = $this->testCaseService->getTestCaseById($id);
        return $this->successResponse(
            new TestCaseResource($testCase),
            'Test case retrieved successfully'
        );
    }

    public function update(UpdateTestCaseRequest $request, $id)
    {
        try {
            // Get qa_id from the request instead of auth
            $qaId = $request->input('qa_id');

            if (!$qaId) {
                return $this->errorResponse('QA ID is required', 422);
            }

            $testCase = $this->testCaseService->updateTestCaseById($id, $request->validated(), $qaId);

            return $this->successResponse(
                new TestCaseResource($testCase),
                'Test case updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            // Get qa_id from the request instead of auth
            $qaId = $request->input('qa_id');

            if (!$qaId) {
                return $this->errorResponse('QA ID is required', 422);
            }

            $this->testCaseService->deleteTestCaseById($id, $qaId);

            return $this->successResponse(
                null,
                'Test case deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}