<?php

// app/Services/UATTask/UATTaskService.php
namespace App\Services\UATTask;

use App\Repositories\UATTask\UATTaskRepository;
use App\Exceptions\UATTaskNotFoundException;
use App\Exceptions\InvalidTaskStatusTransitionException;
use App\Events\UATTaskCreated;
use App\Events\TaskRevisionRequested;
use App\Models\UATTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UATTaskService
{
    protected $uatTaskRepository;

    public function __construct(UATTaskRepository $uatTaskRepository)
    {
        $this->uatTaskRepository = $uatTaskRepository;
    }

    public function getAllTasks()
    {
        return $this->uatTaskRepository->all();
    }

    public function getTasksByApplication($appId)
    {
        return $this->uatTaskRepository->findByApplication($appId);
    }

    public function getTasksByCrowdworker($workerId)
    {
        return $this->uatTaskRepository->findByCrowdworker($workerId);
    }

    public function getTasksByStatus($status)
    {
        return $this->uatTaskRepository->findByStatus($status);
    }

    public function createTask(array $data)
    {
        $data['status'] = $data['status'] ?? UATTask::STATUS_ASSIGNED;
        $task = $this->uatTaskRepository->create($data);
        event(new UATTaskCreated($task));
        return $task;
    }

    public function getTaskById($id)
    {
        $task = $this->uatTaskRepository->findById($id);

        if (!$task) {
            throw new UATTaskNotFoundException('UAT task not found');
        }

        return $task;
    }

    public function updateTaskById($id, array $data)
    {
        $task = $this->getTaskById($id);
        return $this->uatTaskRepository->updateById($id, $data);
    }

    public function deleteTaskById($id)
    {
        $task = $this->getTaskById($id);
        return $this->uatTaskRepository->deleteById($id);
    }

    public function startTaskById($id)
    {
        $task = $this->getTaskById($id);

        // Also allow starting tasks from Revision Required status
        if (!in_array($task->status, [UATTask::STATUS_ASSIGNED, UATTask::STATUS_REVISION_REQUIRED])) {
            throw new InvalidTaskStatusTransitionException('Task can only be started when in Assigned or Revision Required status');
        }

        $updateData = [
            'status' => UATTask::STATUS_IN_PROGRESS,
            'started_at' => Carbon::now()
        ];

        // If this is a revision being started
        if ($task->status === UATTask::STATUS_REVISION_REQUIRED) {
            $updateData['revision_status'] = UATTask::REVISION_IN_PROGRESS;
        }

        return $this->uatTaskRepository->updateById($id, $updateData);
    }

    public function completeTaskById($id)
    {
        $task = $this->getTaskById($id);

        if ($task->status !== UATTask::STATUS_IN_PROGRESS) {
            throw new InvalidTaskStatusTransitionException('Task can only be completed when in In Progress status');
        }

        $updateData = [
            'status' => UATTask::STATUS_COMPLETED,
            'completed_at' => Carbon::now()
        ];

        // If this is a revision being completed
        if ($task->revision_status === UATTask::REVISION_IN_PROGRESS) {
            $updateData['revision_status'] = UATTask::REVISION_COMPLETED;
        }

        return $this->uatTaskRepository->updateById($id, $updateData);
    }

    public function getTaskProgress($id)
    {
        $task = $this->getTaskById($id);

        return [
            'status' => $task->status,
            'revision_status' => $task->revision_status,
            'revision_count' => $task->revision_count,
            'duration' => $task->started_at ? $task->started_at->diffInMinutes(
                $task->completed_at ?? Carbon::now()
            ) : 0,
            'bug_reports_count' => $task->bugReports()->count(),
            'original_bug_reports_count' => $task->originalBugReports()->count(),
            'revised_bug_reports_count' => $task->revisedBugReports()->count(),
            'valid_bugs_count' => $task->bugReports()
                ->whereHas('validation', function ($query) {
                    $query->where('validation_status', 'Valid');
                })
                ->count()
        ];
    }

    public function requestRevision($taskId, $qaId, $comments)
    {
        DB::beginTransaction();

        try {
            $task = $this->getTaskById($taskId);

            if ($task->status !== UATTask::STATUS_COMPLETED) {
                throw new InvalidTaskStatusTransitionException('Task can only be sent for revision when in Completed status');
            }

            // Update the task
            $task = $this->uatTaskRepository->updateById($taskId, [
                'status' => UATTask::STATUS_REVISION_REQUIRED,
                'revision_status' => UATTask::REVISION_REQUESTED,
                'revision_count' => $task->revision_count + 1,
                'revision_comments' => $comments,
                'last_revised_at' => Carbon::now()
            ]);

            // Trigger event for notification
            event(new TaskRevisionRequested($task, $qaId));

            DB::commit();
            return $task;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getTaskRevisionHistory($taskId)
    {
        $task = $this->getTaskById($taskId);

        // Get all validations for this task
        $validations = $task->taskValidation;

        // Get all bug reports with their revisions
        $bugReports = $task->originalBugReports()
            ->with('revisions')
            ->get();

        return [
            'task' => $task,
            'validations' => $validations,
            'bug_reports' => $bugReports
        ];
    }
}