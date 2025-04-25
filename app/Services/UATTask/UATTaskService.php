<?php

// app/Services/UATTask/UATTaskService.php
namespace App\Services\UATTask;

use App\Repositories\UATTask\UATTaskRepository;
use App\Exceptions\UATTaskNotFoundException;
use App\Exceptions\InvalidTaskStatusTransitionException;
use App\Events\UATTaskCreated;
use Carbon\Carbon;

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
        $data['status'] = $data['status'] ?? 'Assigned';
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

        if ($task->status !== 'Assigned') {
            throw new InvalidTaskStatusTransitionException('Task can only be started when in Assigned status');
        }

        return $this->uatTaskRepository->updateById($id, [
            'status' => 'In Progress',
            'started_at' => Carbon::now()
        ]);
    }

    public function completeTaskById($id)
    {
        $task = $this->getTaskById($id);

        if ($task->status !== 'In Progress') {
            throw new InvalidTaskStatusTransitionException('Task can only be completed when in In Progress status');
        }

        return $this->uatTaskRepository->updateById($id, [
            'status' => 'Completed',
            'completed_at' => Carbon::now()
        ]);
    }

    public function getTaskProgress($id)
    {
        $task = $this->getTaskById($id);

        return [
            'status' => $task->status,
            'duration' => $task->started_at ? $task->started_at->diffInMinutes(
                $task->completed_at ?? Carbon::now()
            ) : 0,
            'bug_reports_count' => $task->bugReports()->count(),
            'valid_bugs_count' => $task->bugReports()
                ->whereHas('validation', function ($query) {
                    $query->where('validation_status', 'Valid');
                })
                ->count()
        ];
    }
}