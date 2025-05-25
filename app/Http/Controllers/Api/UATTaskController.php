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

class UATTaskController extends Controller
{
    use ApiResponse;

    protected $uatTaskService;

    public function __construct(UATTaskService $uatTaskService)
    {
        $this->uatTaskService = $uatTaskService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'app_id', 'worker_id']);
        $tasks = $this->uatTaskService->getAllTasks($filters);
        return $this->successResponse(
            UATTaskResource::collection($tasks),
            'UAT tasks retrieved successfully'
        );
    }

    public function store(CreateUATTaskRequest $request)
    {
        $task = $this->uatTaskService->createTask($request->validated());
        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task created successfully',
            201
        );
    }

    public function show($id)
    {
        $task = $this->uatTaskService->getTaskById($id);

        // Load bug reports with evidence manually
        $task->load([
            'bugReports.evidence',
            'bugReports.validation'
        ]);

        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task retrieved successfully'
        );
    }

    public function update(UpdateUATTaskRequest $request, $id)
    {
        $task = $this->uatTaskService->updateTaskById($id, $request->validated());
        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task updated successfully'
        );
    }

    public function destroy($id)
    {
        $this->uatTaskService->deleteTaskById($id);
        return $this->successResponse(
            null,
            'UAT task deleted successfully'
        );
    }

    public function getByApplication($appId)
    {
        $tasks = $this->uatTaskService->getTasksByApplication($appId);
        return $this->successResponse(
            UATTaskResource::collection($tasks),
            'UAT tasks retrieved successfully'
        );
    }

    public function getByWorker($workerId)
    {
        // Change this line to use the correct method name
        $tasks = $this->uatTaskService->getTasksByCrowdworker($workerId);
        return $this->successResponse(
            UATTaskResource::collection($tasks),
            'UAT tasks retrieved successfully'
        );
    }

    public function getByStatus($status)
    {
        $tasks = $this->uatTaskService->getTasksByStatus($status);
        return $this->successResponse(
            UATTaskResource::collection($tasks),
            'UAT tasks retrieved successfully'
        );
    }

    public function startTask($id)
    {
        $task = $this->uatTaskService->startTaskById($id);
        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task started successfully'
        );
    }

    public function completeTask($id)
    {
        // Check if evidence exists for this task
        $task = $this->uatTaskService->getTaskById($id);
        $evidenceCount = $task->evidence()->count();

        // If no bugs reported, ensure there's evidence
        if ($task->bugReports()->count() === 0 && $evidenceCount === 0) {
            return $this->errorResponse(
                'Task cannot be completed without evidence. Please upload screenshots of test steps.',
                422
            );
        }

        // Complete the task
        $task = $this->uatTaskService->completeTaskById($id);

        return $this->successResponse(
            new UATTaskResource($task),
            'UAT task completed successfully'
        );
    }

    public function getTaskProgress($id)
    {
        $progress = $this->uatTaskService->getTaskProgressById($id);
        return $this->successResponse(
            $progress,
            'Task progress retrieved successfully'
        );
    }

    public function getBugReports($id)
    {
        $task = $this->uatTaskService->getTaskById($id);
        $bugReports = $task->bugReports;

        return $this->successResponse(
            BugReportResource::collection($bugReports),
            'Bug reports retrieved successfully'
        );
    }

    public function getRevisionHistory($id)
    {
        $history = $this->uatTaskService->getTaskRevisionHistory($id);

        return $this->successResponse(
            [
                'task' => new UATTaskResource($history['task']),
                'validations' => TaskValidationResource::collection([$history['validations']]),
                'bug_reports' => $history['bug_reports']->map(function ($bug) {
                    return [
                        'original' => new BugReportResource($bug),
                        'revisions' => BugReportResource::collection($bug->revisions)
                    ];
                })
            ],
            'Task revision history retrieved successfully'
        );
    }

    public function startRevision($id)
    {
        // Reuse the startTask method as it now handles revision states too
        $task = $this->uatTaskService->startTaskById($id);

        return $this->successResponse(
            new UATTaskResource($task),
            'Task revision started successfully'
        );
    }
}