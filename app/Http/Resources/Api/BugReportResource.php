<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class BugReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'bug_id' => $this->bug_id,
            'task_id' => $this->task_id,
            'worker_id' => $this->worker_id,
            'bug_description' => $this->bug_description,
            'steps_to_reproduce' => $this->steps_to_reproduce,
            'severity' => $this->severity,
            // Remove single screenshot_url field and add evidence collection
            'evidence' => $this->whenLoaded('evidence', function () {
                return TestEvidenceResource::collection($this->evidence);
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            // Add revision information
            'is_revision' => $this->is_revision,
            'revision_number' => $this->revision_number,
            'original_bug_id' => $this->when($this->is_revision, $this->original_bug_id),
            // Relations
            'uat_task' => new UATTaskResource($this->whenLoaded('uatTask')),
            'crowdworker' => new CrowdworkerResource($this->whenLoaded('crowdworker')),
            'validation' => new BugValidationResource($this->validation),
            'validation_status' => $this->validation ? $this->validation->validation_status : 'Pending',
            'application' => $this->whenLoaded('uatTask', function () {
                return new ApplicationResource($this->uatTask->application);
            }),
            // Only include original bug report if this is a revision
            'original_bug_report' => $this->when(
                $this->is_revision && $this->relationLoaded('originalBugReport'),
                new BugReportResource($this->originalBugReport)
            ),
            // Only include revisions if explicitly requested
            'revisions' => $this->when(
                $request->has('include_revisions') && $this->relationLoaded('revisions'),
                BugReportResource::collection($this->revisions)
            )
        ];
    }
}