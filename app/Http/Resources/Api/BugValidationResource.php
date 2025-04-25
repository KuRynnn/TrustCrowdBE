<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="BugValidationResource",
 *     @OA\Property(property="validation_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="bug_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="qa_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(
 *         property="validation_status",
 *         type="string",
 *         enum={"Valid", "Invalid", "Needs More Info"},
 *         example="Valid"
 *     ),
 *     @OA\Property(property="comments", type="string", example="Bug verified and reproducible"),
 *     @OA\Property(
 *         property="validated_at",
 *         type="string",
 *         format="datetime",
 *         nullable=true,
 *         example="2025-03-12 10:00:00"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="datetime",
 *         example="2025-03-12 09:00:00"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="datetime",
 *         example="2025-03-12 10:00:00"
 *     ),
 *     @OA\Property(
 *         property="validation_time",
 *         type="integer",
 *         example=60,
 *         description="Time taken to validate in minutes",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="bug_report",
 *         ref="#/components/schemas/BugReportResource",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="qa_specialist",
 *         ref="#/components/schemas/QASpecialistResource",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="application_details",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="app_name", type="string", example="Test Application"),
 *         @OA\Property(property="app_id", type="integer", example=1)
 *     )
 * )
 */
class BugValidationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'validation_id' => $this->validation_id,
            'bug_id' => $this->bug_id,
            'qa_id' => $this->qa_id,
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