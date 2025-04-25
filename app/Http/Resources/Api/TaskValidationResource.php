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
            'notes' => $this->notes,
            'validated_at' => $this->validated_at ? $this->validated_at->toDateTimeString() : null,
        ];
    }
}
