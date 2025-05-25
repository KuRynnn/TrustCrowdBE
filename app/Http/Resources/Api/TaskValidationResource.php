<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskValidationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'validation_id' => $this->validation_id,
            'task_id' => $this->task_id,
            'qa_id' => $this->qa_id,
            'validation_status' => $this->validation_status,
            'comments' => $this->comments,
            'validated_at' => $this->validated_at ? $this->validated_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            // Include related task evidence when the task is loaded
            'task_evidence' => $this->whenLoaded('uatTask', function () {
                return $this->uatTask->evidence ?
                    TestEvidenceResource::collection($this->uatTask->evidence) :
                    null;
            }),
            'evidence_count' => $this->whenLoaded('uatTask', function () {
                return $this->uatTask->evidence ? $this->uatTask->evidence->count() : 0;
            }),
            // Include QA specialist when loaded
            'qa_specialist' => new QASpecialistResource($this->whenLoaded('qaSpecialist')),
        ];
    }
}