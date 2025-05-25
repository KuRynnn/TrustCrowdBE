<?php

namespace App\Services\TaskValidation;

use App\Repositories\TaskValidation\TaskValidationRepository;
use App\Services\UATTask\UATTaskService;
use App\Models\UATTask;
use App\Events\TaskRevisionRequested; // Keep this if it exists
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskValidationService
{
    protected $taskValidationRepository;
    protected $uatTaskService;

    public function __construct(
        TaskValidationRepository $taskValidationRepository,
        UATTaskService $uatTaskService
    ) {
        $this->taskValidationRepository = $taskValidationRepository;
        $this->uatTaskService = $uatTaskService;
    }

    public function validateTask($taskId, $qaId, $status, $comments)
    {
        DB::beginTransaction();

        try {
            // Get the task
            $task = $this->uatTaskService->getTaskById($taskId);

            // Ensure task is in a state that can be validated
            if (!in_array($task->status, [UATTask::STATUS_COMPLETED])) {
                throw new \Exception('Task can only be validated when in Completed status');
            }

            // Create validation record
            $validation = $this->taskValidationRepository->create([
                'task_id' => $taskId,
                'qa_id' => $qaId,
                'validation_status' => $status,
                'comments' => $comments,
                'validated_at' => Carbon::now()
            ]);

            // Update task based on validation status
            switch ($status) {
                case 'Pass Verified':
                    // Just update the task status directly
                    $this->uatTaskService->updateTaskById($taskId, [
                        'status' => UATTask::STATUS_VERIFIED
                    ]);
                    // No event needed for now
                    break;

                case 'Rejected':
                    // Just update the task status directly
                    $this->uatTaskService->updateTaskById($taskId, [
                        'status' => UATTask::STATUS_REJECTED
                    ]);
                    // No event needed for now
                    break;

                case 'Need Revision':
                    // Use the requestRevision method to handle the revision properly
                    $this->uatTaskService->requestRevision($taskId, $qaId, $comments);
                    break;

                default:
                    throw new \Exception('Invalid validation status');
            }

            DB::commit();
            return $validation;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Task validation error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    public function getValidationById($id)
    {
        return $this->taskValidationRepository->findById($id);
    }

    public function getValidationByTaskId($taskId)
    {
        return $this->taskValidationRepository->findByTaskId($taskId);
    }

    public function createTaskValidation(array $data)
    {
        // Get the task
        $taskId = $data['task_id'];
        $task = $this->uatTaskService->getTaskById($taskId);

        // Check if the task is completed and ready for validation
        if ($task->status !== UATTask::STATUS_COMPLETED) {
            throw new \Exception('Task must be completed before it can be validated');
        }

        // Check if all bug reports are validated
        $bugReports = $task->bugReports;

        if ($bugReports->count() > 0) {
            $unvalidatedCount = $bugReports->filter(function ($report) {
                return $report->validation === null;
            })->count();

            if ($unvalidatedCount > 0) {
                throw new \Exception("All bug reports must be validated before validating the task. ($unvalidatedCount unvalidated reports)");
            }
        }

        // Check if the task already has a validation
        $existingValidation = $this->getValidationByTaskId($taskId);
        if ($existingValidation) {
            throw new \Exception("This task has already been validated");
        }

        // Create the validation
        return $this->taskValidationRepository->create([
            'task_id' => $data['task_id'],
            'qa_id' => $data['qa_id'],
            'validation_status' => $data['validation_status'],
            'comments' => $data['comments'] ?? null,
            'validated_at' => Carbon::now()
        ]);
    }

    public function getValidationByTask($taskId)
    {
        $validation = $this->getValidationByTaskId($taskId);

        if (!$validation) {
            throw new \Exception('Task validation not found');
        }

        return $validation;
    }
}