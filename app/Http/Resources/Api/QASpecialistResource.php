<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;


class QASpecialistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'qa_id' => $this->qa_id,
            'name' => $this->name,
            'email' => $this->email,
            'expertise' => $this->expertise,
            'created_at' => $this->created_at->toDateTimeString(),
            'test_cases' => TestCaseResource::collection($this->whenLoaded('testCases')),
            'bug_validations' => BugValidationResource::collection($this->whenLoaded('bugValidations'))
        ];
    }
}