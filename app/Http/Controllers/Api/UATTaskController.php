<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UATTask\UATTaskService;
use App\Http\Resources\Api\UATTaskResource;
use App\Http\Resources\Api\BugReportResource;
use App\Http\Requests\Api\UATTask\CreateUATTaskRequest;
use App\Http\Requests\Api\UATTask\UpdateUATTaskRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="UAT Tasks",
 *     description="API Endpoints for UAT Task management"
 * )
 */
class UATTaskController extends Controller
{
    use ApiResponse;

    protected $uatTaskService;

    public function __construct(UATTaskService $uatTaskService)
    {
        $this->uatTaskService = $uatTaskService;
    }

    /**
     * @OA\Get(
     *     path="/uat-tasks",
     *     tags={"UAT Tasks"},
     *     summary="Get list of UAT tasks",
     *     description="Returns a paginated list of all UAT tasks",
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
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Assigned", "In Progress", "Completed"})
     *     ),
     *     @OA\Parameter(
     *         name="app_id",
     *         in="query",
     *         description="Filter by application ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="worker_id",
     *         in="query",
     *         description="Filter by worker ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT tasks retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="task_id", type="string", format="uuid"),
     *                         @OA\Property(property="app_id", type="string", format="uuid"),
     *                         @OA\Property(property="test_id", type="string", format="uuid"),
     *                         @OA\Property(property="worker_id", type="string", format="uuid"),
     *                         @OA\Property(property="status", type="string", enum={"Assigned", "In Progress", "Completed"}, example="In Progress"),
     *                         @OA\Property(property="started_at", type="string", format="datetime", example="2025-03-12 10:00:00", nullable=true),
     *                         @OA\Property(property="completed_at", type="string", format="datetime", example="2025-03-12 11:00:00", nullable=true),
     *                         @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 11:00:00"),
     *                         @OA\Property(property="duration", type="integer", example=60, nullable=true),
     *                         @OA\Property(property="bug_reports_count", type="integer", example=2, nullable=true),
     *                         @OA\Property(
     *                             property="application",
     *                             type="object",
     *                             @OA\Property(property="app_id", type="string", format="uuid"),
     *                             @OA\Property(property="app_name", type="string", example="E-commerce Platform"),
     *                             nullable=true
     *                         ),
     *                         @OA\Property(
     *                             property="test_case",
     *                             type="object",
     *                             @OA\Property(property="test_id", type="string", format="uuid"),
     *                             @OA\Property(property="test_title", type="string", example="User Login Test"),
     *                             nullable=true
     *                         ),
     *                         @OA\Property(
     *                             property="crowdworker",
     *                             type="object",
     *                             @OA\Property(property="worker_id", type="string", format="uuid"),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             nullable=true
     *                         ),
     *                         @OA\Property(
     *                             property="bug_reports",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="bug_id", type="string", format="uuid"),
     *                                 @OA\Property(property="title", type="string", example="Login Button Not Responsive"),
     *                                 @OA\Property(property="severity", type="string", example="High")
     *                             ),
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
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'app_id', 'worker_id']);
        $tasks = $this->uatTaskService->getAllTasks($filters);
        return $this->successResponse(
            UATTaskResource::collection($tasks),
            'UAT tasks retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/uat-tasks",
     *     tags={"UAT Tasks"},
     *     summary="Create new UAT task",
     *     description="Creates a new UAT task assignment",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"app_id", "test_id", "worker_id", "status"},
     *             @OA\Property(property="app_id", type="string", format="uuid"),
     *             @OA\Property(property="test_id", type="string", format="uuid"),
     *             @OA\Property(property="worker_id", type="string", format="uuid"),
     *             @OA\Property(property="status", type="string", enum={"Assigned", "In Progress", "Completed"}, example="Assigned"),
     *             @OA\Property(property="started_at", type="string", format="datetime", example="2025-03-12 10:00:00", nullable=true),
     *             @OA\Property(property="completed_at", type="string", format="datetime", example="2025-03-12 11:00:00", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="UAT task created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT task created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="app_id", type="string", format="uuid"),
     *                 @OA\Property(property="test_id", type="string", format="uuid"),
     *                 @OA\Property(property="worker_id", type="string", format="uuid"),
     *                 @OA\Property(property="status", type="string", example="Assigned"),
     *                 @OA\Property(property="started_at", type="string", format="datetime", example="2025-03-12 10:00:00", nullable=true),
     *                 @OA\Property(property="completed_at", type="string", format="datetime", example="2025-03-12 11:00:00", nullable=true),
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
    public function store(CreateUATTaskRequest $request)
    {
        $task = $this->uatTaskService->createTask($request->validated());
        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task created successfully',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/uat-tasks/{id}",
     *     tags={"UAT Tasks"},
     *     summary="Get specific UAT task",
     *     description="Returns details of a specific UAT task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UAT Task ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT task retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="app_id", type="string", format="uuid"),
     *                 @OA\Property(property="test_id", type="string", format="uuid"),
     *                 @OA\Property(property="worker_id", type="string", format="uuid"),
     *                 @OA\Property(property="status", type="string", enum={"Assigned", "In Progress", "Completed"}, example="In Progress"),
     *                 @OA\Property(property="started_at", type="string", format="datetime", example="2025-03-12 10:00:00", nullable=true),
     *                 @OA\Property(property="completed_at", type="string", format="datetime", example="2025-03-12 11:00:00", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 11:00:00"),
     *                 @OA\Property(property="duration", type="integer", example=60, nullable=true),
     *                 @OA\Property(
     *                     property="application",
     *                     type="object",
     *                     @OA\Property(property="app_id", type="string", format="uuid"),
     *                     @OA\Property(property="app_name", type="string", example="E-commerce Platform"),
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="test_case",
     *                     type="object",
     *                     @OA\Property(property="test_id", type="string", format="uuid"),
     *                     @OA\Property(property="test_title", type="string", example="User Login Test"),
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="crowdworker",
     *                     type="object",
     *                     @OA\Property(property="worker_id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     nullable=true
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="UAT task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="UAT task not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $task = $this->uatTaskService->getTaskById($id);
        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/uat-tasks/{id}",
     *     tags={"UAT Tasks"},
     *     summary="Update UAT task",
     *     description="Updates an existing UAT task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UAT Task ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="app_id", type="string", format="uuid"),
     *             @OA\Property(property="test_id", type="string", format="uuid"),
     *             @OA\Property(property="worker_id", type="string", format="uuid"),
     *             @OA\Property(property="status", type="string", enum={"Assigned", "In Progress", "Completed"}, example="In Progress"),
     *             @OA\Property(property="started_at", type="string", format="datetime", example="2025-03-12 10:00:00", nullable=true),
     *             @OA\Property(property="completed_at", type="string", format="datetime", example="2025-03-12 11:00:00", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="UAT task updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT task updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="app_id", type="string", format="uuid"),
     *                 @OA\Property(property="test_id", type="string", format="uuid"),
     *                 @OA\Property(property="worker_id", type="string", format="uuid"),
     *                 @OA\Property(property="status", type="string", example="In Progress"),
     *                 @OA\Property(property="started_at", type="string", format="datetime", example="2025-03-12 10:00:00", nullable=true),
     *                 @OA\Property(property="completed_at", type="string", format="datetime", example="2025-03-12 11:00:00", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 11:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="UAT task not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(UpdateUATTaskRequest $request, $id)
    {
        $task = $this->uatTaskService->updateTaskById($id, $request->validated());
        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/uat-tasks/{id}",
     *     tags={"UAT Tasks"},
     *     summary="Delete UAT task",
     *     description="Deletes a UAT task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UAT Task ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="UAT task deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT task deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="UAT task not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->uatTaskService->deleteTaskById($id);
        return $this->successResponse(
            null,
            'UAT task deleted successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/uat-tasks/application/{appId}",
     *     tags={"UAT Tasks"},
     *     summary="Get UAT tasks by application",
     *     description="Returns UAT tasks for a specific application",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="appId",
     *         in="path",
     *         required=true,
     *         description="Application ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT tasks retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UATTaskResource")
     *             )
     *         )
     *     )
     * )
     */
    public function getByApplication($appId)
    {
        $tasks = $this->uatTaskService->getTasksByApplication($appId);
        return $this->successResponse(
            UATTaskResource::collection($tasks),
            'UAT tasks retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/uat-tasks/worker/{workerId}",
     *     tags={"UAT Tasks"},
     *     summary="Get UAT tasks by worker",
     *     description="Returns UAT tasks assigned to a specific worker",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="workerId",
     *         in="path",
     *         required=true,
     *         description="Worker ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT tasks retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UATTaskResource")
     *             )
     *         )
     *     )
     * )
     */
    // Update the getByWorker method in UATTaskController.php
    public function getByWorker($workerId)
    {
        // Change this line to use the correct method name
        $tasks = $this->uatTaskService->getTasksByCrowdworker($workerId);
        return $this->successResponse(
            UATTaskResource::collection($tasks),
            'UAT tasks retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/uat-tasks/status/{status}",
     *     tags={"UAT Tasks"},
     *     summary="Get UAT tasks by status",
     *     description="Returns UAT tasks with a specific status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         required=true,
     *         description="Task Status",
     *         @OA\Schema(type="string", enum={"Assigned", "In Progress", "Completed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT tasks retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UATTaskResource")
     *             )
     *         )
     *     )
     * )
     */
    public function getByStatus($status)
    {
        $tasks = $this->uatTaskService->getTasksByStatus($status);
        return $this->successResponse(
            UATTaskResource::collection($tasks),
            'UAT tasks retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/uat-tasks/{id}/start",
     *     tags={"UAT Tasks"},
     *     summary="Start UAT task",
     *     description="Marks a UAT task as started",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UAT Task ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="UAT task started successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT task started successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="status", type="string", example="In Progress"),
     *                 @OA\Property(property="started_at", type="string", format="datetime", example="2025-03-12 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="UAT task not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Task cannot be started"
     *     )
     * )
     */
    public function startTask($id)
    {
        $task = $this->uatTaskService->startTaskById($id);
        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task started successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/uat-tasks/{id}/complete",
     *     tags={"UAT Tasks"},
     *     summary="Complete UAT task",
     *     description="Marks a UAT task as completed",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UAT Task ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="UAT task completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="UAT task completed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="status", type="string", example="Completed"),
     *                 @OA\Property(property="completed_at", type="string", format="datetime", example="2025-03-12 11:00:00"),
     *                 @OA\Property(property="duration", type="integer", example=60)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="UAT task not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Task cannot be completed"
     *     )
     * )
     */
    public function completeTask($id)
    {
        $task = $this->uatTaskService->completeTaskById($id);
        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task completed successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/uat-tasks/{id}/progress",
     *     tags={"UAT Tasks"},
     *     summary="Get task progress",
     *     description="Returns progress information for a specific task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UAT Task ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task progress retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Task progress retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="task_status", type="string", example="In Progress"),
     *                 @OA\Property(property="percentage", type="number", format="float", example=75),
     *                 @OA\Property(property="duration_minutes", type="integer", example=45),
     *                 @OA\Property(property="started_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="bugs_found", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="UAT task not found"
     *     )
     * )
     */
    public function getTaskProgress($id)
    {
        $progress = $this->uatTaskService->getTaskProgressById($id);
        return $this->successResponse(
            $progress,
            'Task progress retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/uat-tasks/{id}/bug-reports",
     *     tags={"UAT Tasks"},
     *     summary="Get bug reports for a task",
     *     description="Returns bug reports submitted for a specific task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UAT Task ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bug reports retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug reports retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/BugReportResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="UAT task not found"
     *     )
     * )
     */
    public function getBugReports($id)
    {
        $task = $this->uatTaskService->getTaskById($id);
        $bugReports = $task->bugReports;

        return $this->successResponse(
            BugReportResource::collection($bugReports),
            'Bug reports retrieved successfully'
        );
    }
}