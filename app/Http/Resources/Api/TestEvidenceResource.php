<?php
namespace App\Http\Resources\Api;
use Illuminate\Http\Resources\Json\JsonResource;

class TestEvidenceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'evidence_id' => $this->evidence_id,
            'bug_id' => $this->bug_id,
            'task_id' => $this->task_id,
            'step_number' => $this->step_number,
            'step_description' => $this->step_description,
            'screenshot_url' => $this->screenshot_url,
            'notes' => $this->notes,
            'context' => $this->context, // Add the context field
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString()
        ];
    }
}