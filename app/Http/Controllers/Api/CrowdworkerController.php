<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Crowdworker\CrowdworkerService;
use App\Http\Resources\Api\CrowdworkerResource;
use App\Http\Requests\Api\Crowdworker\CreateCrowdworkerRequest;
use App\Http\Requests\Api\Crowdworker\UpdateCrowdworkerRequest;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="Crowdworkers",
 *     description="API Endpoints for Crowdworker management"
 * )
 */
class CrowdworkerController extends Controller
{
    use ApiResponse;

    protected $crowdworkerService;

    public function __construct(CrowdworkerService $crowdworkerService)
    {
        $this->crowdworkerService = $crowdworkerService;
    }

    /**
     * @OA\Get(
     *     path="/crowdworkers",
     *     tags={"Crowdworkers"},
     *     summary="Get list of crowdworkers",
     *     description="Returns a list of all crowdworkers",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Crowdworkers retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="worker_id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                     @OA\Property(property="skills", type="string", example="Web Testing, Mobile Testing"),
     *                     @OA\Property(property="created_at", type="string", format="datetime"),
     *                     @OA\Property(property="updated_at", type="string", format="datetime"),
     *                     @OA\Property(property="completed_tasks_count", type="integer", example=5),
     *                     @OA\Property(property="total_bug_reports", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index()
    {
        $crowdworkers = $this->crowdworkerService->getAllCrowdworkers();
        return $this->successResponse(
            CrowdworkerResource::collection($crowdworkers),
            'Crowdworkers retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/crowdworkers",
     *     tags={"Crowdworkers"},
     *     summary="Create new crowdworker",
     *     description="Creates a new crowdworker account",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "skills", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="skills", type="string", example="Web Testing, Mobile Testing"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Crowdworker created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Crowdworker created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="worker_id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="skills", type="string", example="Web Testing, Mobile Testing"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
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
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="The email has already been taken")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(CreateCrowdworkerRequest $request)
    {
        $crowdworker = $this->crowdworkerService->createCrowdworker($request->validated());
        return $this->successResponse(
            new CrowdworkerResource($crowdworker),
            'Crowdworker created successfully',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/crowdworkers/{id}",
     *     tags={"Crowdworkers"},
     *     summary="Get specific crowdworker",
     *     description="Returns details of a specific crowdworker",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Crowdworker ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Crowdworker retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="worker_id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="skills", type="string", example="Web Testing, Mobile Testing"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime"),
     *                 @OA\Property(property="completed_tasks_count", type="integer", example=5),
     *                 @OA\Property(property="total_bug_reports", type="integer", example=10),
     *                 @OA\Property(
     *                     property="uat_tasks",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="task_id", type="string", format="uuid"),
     *                         @OA\Property(property="title", type="string", example="Test Login Feature"),
     *                         @OA\Property(property="status", type="string", example="Completed")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="bug_reports",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="bug_id", type="string", format="uuid"),
     *                         @OA\Property(property="description", type="string", example="Login button not working"),
     *                         @OA\Property(property="severity", type="string", example="High")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Crowdworker not found"
     *     )
     * )
     */
    public function show($id)
    {
        $crowdworker = $this->crowdworkerService->getCrowdworkerById($id);
        return $this->successResponse(
            new CrowdworkerResource($crowdworker),
            'Crowdworker retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/crowdworkers/{id}",
     *     tags={"Crowdworkers"},
     *     summary="Update crowdworker",
     *     description="Updates an existing crowdworker",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Crowdworker ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *             @OA\Property(property="skills", type="string", example="Web Testing, Mobile Testing, API Testing"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Crowdworker updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Crowdworker updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="worker_id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *                 @OA\Property(property="skills", type="string", example="Web Testing, Mobile Testing, API Testing"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Crowdworker not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(UpdateCrowdworkerRequest $request, $id)
    {
        $crowdworker = $this->crowdworkerService->updateCrowdworkerById($id, $request->validated());
        return $this->successResponse(
            new CrowdworkerResource($crowdworker),
            'Crowdworker updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/crowdworkers/{id}",
     *     tags={"Crowdworkers"},
     *     summary="Delete crowdworker",
     *     description="Deletes a specific crowdworker",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Crowdworker ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Crowdworker deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Crowdworker deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Crowdworker not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->crowdworkerService->deleteCrowdworkerById($id);
        return $this->successResponse(
            null,
            'Crowdworker deleted successfully'
        );
    }
}