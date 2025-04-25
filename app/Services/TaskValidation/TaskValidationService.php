<?php

namespace App\Services\TaskValidation;

use App\Repositories\TaskValidation\TaskValidationRepository;
use App\Services\UATTask\UATTaskService;
use App\Exceptions\TaskValidationNotFoundException;
use Carbon\Carbon;

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

    public function createTaskValidation(array $data)
    {
        // Get UAT Task data by task_id
        $task = $this->uatTaskService->getTaskById($data['task_id']);

        // Validate that QA can only review tasks from their own test cases
        if ($task->testCase->qa_id !== $data['qa_id']) {
            throw new \Exception('Unauthorized: You do not own this test case.');
        }

        // Check if the task has bug reports
        $bugReports = $task->bugReports;

        if ($bugReports->count() > 0) {
            // Check if all bug reports have been validated
            $unvalidatedBugs = $bugReports->filter(function ($bugReport) {
                return !$bugReport->validation;
            });

            if ($unvalidatedBugs->count() > 0) {
                throw new \Exception('All bug reports must be validated before validating the task.');
            }

            // Check validation status consistency
            if ($data['validation_status'] === 'Pass Verified') {
                $invalidBugs = $bugReports->filter(function ($bugReport) {
                    return $bugReport->validation && $bugReport->validation->validation_status === 'Valid';
                });

                if ($invalidBugs->count() > 0) {
                    throw new \Exception('Cannot mark task as Pass Verified when it has valid bug reports.');
                }
            }
        } else {
            // For tasks with no bug reports (marked as Passed by crowdworker)
            // QA can still verify it passed or reject it
            if ($task->status !== 'Completed') {
                throw new \Exception('Task must be completed by crowdworker before validation.');
            }
        }

        // Set validation time
        $data['validated_at'] = now();

        // Create task validation
        return $this->taskValidationRepository->create($data);
    }


    public function getValidationByTask($taskId)
    {
        $validation = $this->taskValidationRepository->findByTask($taskId);
        if (!$validation) {
            throw new TaskValidationNotFoundException('Task validation not found');
        }
        return $validation;
    }
}
