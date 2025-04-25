<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TestCase\TestCaseService;
use App\Http\Resources\Api\TestCaseResource;
use App\Http\Requests\Api\TestCase\CreateTestCaseRequest;
use App\Http\Requests\Api\TestCase\UpdateTestCaseRequest;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="Test Cases",
 *     description="API Endpoints for Test Case management"
 * )
 */
class TestCaseController extends Controller
{
    use ApiResponse;

    protected $testCaseService;

    public function __construct(TestCaseService $testCaseService)
    {
        $this->testCaseService = $testCaseService;
    }

    /**
     * @OA\Get(
     *     path="/test-cases",
     *     tags={"Test Cases"},
     *     summary="Get list of test cases",
     *     description="Returns a paginated list of all test cases",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by test title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="app_id",
     *         in="query",
     *         description="Filter by application ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="qa_id",
     *         in="query",
     *         description="Filter by QA specialist ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Low", "Medium", "High"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Test cases retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="test_id", type="string", format="uuid"),
     *                         @OA\Property(property="app_id", type="string", format="uuid"),
     *                         @OA\Property(property="qa_id", type="string", format="uuid"),
     *                         @OA\Property(property="test_title", type="string", example="User Login Functionality"),
     *                         @OA\Property(property="test_steps", type="string", example="1. Navigate to login page\n2. Enter valid credentials\n3. Click login button"),
     *                         @OA\Property(property="expected_result", type="string", example="User should be successfully logged in and redirected to dashboard"),
     *                         @OA\Property(property="priority", type="string", enum={"Low", "Medium", "High"}, example="High"),
     *                         @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                         @OA\Property(
     *                             property="application",
     *                             type="object",
     *                             @OA\Property(property="app_id", type="string", format="uuid"),
     *                             @OA\Property(property="app_name", type="string", example="E-commerce Platform"),
     *                             @OA\Property(property="app_url", type="string", example="https://ecommerce.example.com"),
     *                             nullable=true
     *                         ),
     *                         @OA\Property(
     *                             property="qa_specialist",
     *                             type="object",
     *                             @OA\Property(property="qa_id", type="string", format="uuid"),
     *                             @OA\Property(property="name", type="string", example="Jane Smith"),
     *                             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *                             nullable=true
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $testCases = $this->testCaseService->getAllTestCases();
        return $this->successResponse(
            TestCaseResource::collection($testCases),
            'Test cases retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/test-cases",
     *     tags={"Test Cases"},
     *     summary="Create new test case",
     *     description="Creates a new test case",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"app_id", "qa_id", "test_title", "test_steps", "expected_result", "priority"},
     *             @OA\Property(property="app_id", type="string", format="uuid"),
     *             @OA\Property(property="qa_id", type="string", format="uuid"),
     *             @OA\Property(property="test_title", type="string", example="User Login Functionality"),
     *             @OA\Property(property="test_steps", type="string", example="1. Navigate to login page\n2. Enter valid credentials\n3. Click login button"),
     *             @OA\Property(property="expected_result", type="string", example="User should be successfully logged in and redirected to dashboard"),
     *             @OA\Property(property="priority", type="string", enum={"Low", "Medium", "High"}, example="High")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Test case created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Test case created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="test_id", type="string", format="uuid"),
     *                 @OA\Property(property="app_id", type="string", format="uuid"),
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(property="test_title", type="string", example="User Login Functionality"),
     *                 @OA\Property(property="test_steps", type="string", example="1. Navigate to login page\n2. Enter valid credentials\n3. Click login button"),
     *                 @OA\Property(property="expected_result", type="string", example="User should be successfully logged in and redirected to dashboard"),
     *                 @OA\Property(property="priority", type="string", enum={"Low", "Medium", "High"}, example="High"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="app_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The application id field is required")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(CreateTestCaseRequest $request)
    {
        $testCase = $this->testCaseService->createTestCase($request->validated());
        return $this->successResponse(
            new TestCaseResource($testCase),
            'Test case created successfully',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/test-cases/{id}",
     *     tags={"Test Cases"},
     *     summary="Get specific test case",
     *     description="Returns details of a specific test case",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Test Case ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Test case retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="test_id", type="string", format="uuid"),
     *                 @OA\Property(property="app_id", type="string", format="uuid"),
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(property="test_title", type="string", example="User Login Functionality"),
     *                 @OA\Property(property="test_steps", type="string", example="1. Navigate to login page\n2. Enter valid credentials\n3. Click login button"),
     *                 @OA\Property(property="expected_result", type="string", example="User should be successfully logged in and redirected to dashboard"),
     *                 @OA\Property(property="priority", type="string", enum={"Low", "Medium", "High"}, example="High"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(
     *                     property="application",
     *                     type="object",
     *                     @OA\Property(property="app_id", type="string", format="uuid"),
     *                     @OA\Property(property="app_name", type="string", example="E-commerce Platform"),
     *                     @OA\Property(property="app_url", type="string", example="https://ecommerce.example.com"),
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="qa_specialist",
     *                     type="object",
     *                     @OA\Property(property="qa_id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", example="Jane Smith"),
     *                     @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *                     nullable=true
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Test case not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Test case not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $testCase = $this->testCaseService->getTestCaseById($id);
        return $this->successResponse(
            new TestCaseResource($testCase),
            'Test case retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/test-cases/{id}",
     *     tags={"Test Cases"},
     *     summary="Update test case",
     *     description="Updates an existing test case",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Test Case ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="app_id", type="string", format="uuid"),
     *             @OA\Property(property="qa_id", type="string", format="uuid"),
     *             @OA\Property(property="test_title", type="string", example="Updated User Login Functionality"),
     *             @OA\Property(property="test_steps", type="string", example="1. Navigate to login page\n2. Enter valid credentials\n3. Click login button\n4. Verify dashboard elements"),
     *             @OA\Property(property="expected_result", type="string", example="User should be successfully logged in and redirected to dashboard with all elements visible"),
     *             @OA\Property(property="priority", type="string", enum={"Low", "Medium", "High"}, example="High")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Test case updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Test case updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="test_id", type="string", format="uuid"),
     *                 @OA\Property(property="app_id", type="string", format="uuid"),
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(property="test_title", type="string", example="Updated User Login Functionality"),
     *                 @OA\Property(property="test_steps", type="string", example="1. Navigate to login page\n2. Enter valid credentials\n3. Click login button\n4. Verify dashboard elements"),
     *                 @OA\Property(property="expected_result", type="string", example="User should be successfully logged in and redirected to dashboard with all elements visible"),
     *                 @OA\Property(property="priority", type="string", enum={"Low", "Medium", "High"}, example="High"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Test case not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Test case not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="priority",
     *                     type="array",
     *                     @OA\Items(type="string", example="The priority must be one of: Low, Medium, High")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/test-cases/{id}",
     *     tags={"Test Cases"},
     *     summary="Delete test case",
     *     description="Deletes a test case",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Test Case ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Test case deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Test case deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Test case not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Test case not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
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