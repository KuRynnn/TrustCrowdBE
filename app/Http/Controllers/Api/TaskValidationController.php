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

            // Get evidence count
            $evidenceCount = $task->evidence()->count();

            // A task is ready if either:
            // 1. It has bugs and all bugs are validated
            // 2. It has no bugs but has evidence
            $isReady = ($totalBugReports > 0 && $unvalidatedCount === 0) ||
                ($totalBugReports === 0 && $evidenceCount > 0);

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
                'evidence_count' => $evidenceCount,
                'bug_validations' => $bugValidations
            ], $isReady ? 'Task is ready for validation' : 'Task is not ready for validation');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function validateTask(CreateTaskValidationRequest $request)
    {
        try {
            $validation = $this->taskValidationService->validateTask(
                $request->task_id,
                $request->qa_id,
                $request->validation_status,
                $request->comments
            );

            return $this->successResponse(
                new TaskValidationResource($validation),
                'Task validated successfully'
            );
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Task validation failed: ' . $e->getMessage());

            // Return friendly error message
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function getByTaskId($taskId)
    {
        $validation = $this->taskValidationService->getValidationByTaskId($taskId);

        if (!$validation) {
            return $this->errorResponse('No validation found for this task', 404);
        }

        return $this->successResponse(
            new TaskValidationResource($validation),
            'Task validation retrieved successfully'
        );
    }
}
