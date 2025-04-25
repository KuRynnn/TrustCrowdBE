<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskValidation\TaskValidationService;
use App\Services\UATTask\UATTaskService;
use App\Http\Resources\Api\TaskValidationResource;
use App\Http\Resources\Api\BugValidationResource;
use App\Http\Requests\Api\TaskValidation\CreateTaskValidationRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Task Validations",
 *     description="API Endpoints for Task Validation management"
 * )
 */
class TaskValidationController extends Controller
{
    use ApiResponse;

    protected $taskValidationService;
    protected $uatTaskService;

    public function __construct(
        TaskValidationService $taskValidationService,
        UATTaskService $uatTaskService
    ) {
        $this->taskValidationService = $taskValidationService;
        $this->uatTaskService = $uatTaskService;
    }

    /**
     * @OA\Post(
     *     path="/task-validations",
     *     tags={"Task Validations"},
     *     summary="Create new task validation",
     *     description="Creates a new task validation by QA Specialist",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"task_id", "qa_id", "validation_status"},
     *             @OA\Property(property="task_id", type="string", format="uuid"),
     *             @OA\Property(property="qa_id", type="string", format="uuid"),
     *             @OA\Property(
     *                 property="validation_status",
     *                 type="string",
     *                 enum={"Pass Verified", "Rejected", "Need Revision"},
     *                 example="Pass Verified"
     *             ),
     *             @OA\Property(property="comments", type="string", example="Task completed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task validation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Task validation created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="validation_id", type="string", format="uuid"),
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(
     *                     property="validation_status",
     *                     type="string",
     *                     enum={"Pass Verified", "Rejected", "Need Revision"},
     *                     example="Pass Verified"
     *                 ),
     *                 @OA\Property(property="comments", type="string", example="Task completed successfully"),
     *                 @OA\Property(property="validated_at", type="string", format="datetime"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or bug reports not validated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="All bug reports must be validated before validating the task"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function store(CreateTaskValidationRequest $request)
    {
        try {
            $taskValidation = $this->taskValidationService->createTaskValidation($request->validated());

            return $this->successResponse(
                new TaskValidationResource($taskValidation),
                'Task validation created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/task-validations/{taskId}",
     *     tags={"Task Validations"},
     *     summary="Get task validation",
     *     description="Returns details of a specific task validation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="UAT Task ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Task validation retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="validation_id", type="string", format="uuid"),
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(
     *                     property="validation_status",
     *                     type="string",
     *                     enum={"Pass Verified", "Rejected", "Need Revision"},
     *                     example="Pass Verified"
     *                 ),
     *                 @OA\Property(property="comments", type="string", example="Task completed successfully"),
     *                 @OA\Property(property="validated_at", type="string", format="datetime"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task validation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Task validation not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function show($taskId)
    {
        try {
            $validation = $this->taskValidationService->getValidationByTask($taskId);
            return $this->successResponse(
                new TaskValidationResource($validation),
                'Task validation retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/task-validations/check-readiness/{taskId}",
     *     tags={"Task Validations"},
     *     summary="Check if task is ready for validation",
     *     description="Checks if all bug reports for a task have been validated",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="UAT Task ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Task is ready for validation"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="is_ready", type="boolean", example=true),
     *                 @OA\Property(property="total_bug_reports", type="integer", example=3),
     *                 @OA\Property(property="validated_bug_reports", type="integer", example=3),
     *                 @OA\Property(property="unvalidated_bug_reports", type="integer", example=0),
     *                 @OA\Property(
     *                     property="bug_validations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="bug_id", type="string", format="uuid"),
     *                         @OA\Property(property="validation_status", type="string", example="Valid"),
     *                         @OA\Property(property="comments", type="string", example="Bug confirmed")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Task not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function checkTaskReadiness($taskId)
    {
        try {
            $task = $this->uatTaskService->getTaskById($taskId);

            // Get all bug reports for this task
            $bugReports = $task->bugReports;
            $totalBugReports = $bugReports->count();

            // Check if all bug reports have been validated
            $validatedBugReports = $bugReports->filter(function ($bugReport) {
                return $bugReport->validation !== null;
            });

            $validatedCount = $validatedBugReports->count();
            $unvalidatedCount = $totalBugReports - $validatedCount;
            $isReady = ($unvalidatedCount === 0) && ($task->status === 'Completed');

            // Get validation details for all bug reports
            $bugValidations = $validatedBugReports->map(function ($bugReport) {
                return [
                    'bug_id' => $bugReport->bug_id,
                    'validation_status' => $bugReport->validation->validation_status,
                    'comments' => $bugReport->validation->comments
                ];
            });

            return $this->successResponse([
                'is_ready' => $isReady,
                'total_bug_reports' => $totalBugReports,
                'validated_bug_reports' => $validatedCount,
                'unvalidated_bug_reports' => $unvalidatedCount,
                'task_status' => $task->status,
                'bug_validations' => $bugValidations
            ], $isReady ? 'Task is ready for validation' : 'Task is not ready for validation');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
