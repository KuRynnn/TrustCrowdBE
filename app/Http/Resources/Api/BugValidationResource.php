<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;


class BugValidationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'validation_id' => $this->validation_id,
            'bug_id' => $this->bug_id,
            'validation_status' => $this->validation_status,
            'comments' => $this->comments,
            'validated_at' => $this->validated_at ? $this->validated_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'bug_report' => new BugReportResource($this->whenLoaded('bugReport')),
            'qa_specialist' => new QASpecialistResource($this->whenLoaded('qaSpecialist')),
            'validation_time' => $this->when($this->validated_at, function () {
                return $this->created_at->diffInMinutes($this->validated_at);
            }),
            'application_details' => $this->whenLoaded('bugReport', function () {
                return [
                    'app_name' => $this->bugReport->uatTask->application->app_name,
                    'app_id' => $this->bugReport->uatTask->application->app_id
                ];
            })
        ];
    }
}