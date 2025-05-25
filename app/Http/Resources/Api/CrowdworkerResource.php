<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;


class CrowdworkerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'worker_id' => $this->worker_id,
            'name' => $this->name,
            'email' => $this->email,
            'skills' => $this->skills,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'uat_tasks' => UATTaskResource::collection($this->whenLoaded('uatTasks')),
            'bug_reports' => BugReportResource::collection($this->whenLoaded('bugReports')),
            'completed_tasks_count' => $this->whenLoaded('uatTasks', function () {
                return $this->uatTasks->where('status', 'Completed')->count();
            }),
            'total_bug_reports' => $this->whenLoaded('bugReports', function () {
                return $this->bugReports->count();
            })
        ];
    }
}