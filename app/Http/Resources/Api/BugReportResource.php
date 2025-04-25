<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="BugReportResource",
 *     @OA\Property(property="bug_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="task_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="worker_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="bug_description", type="string", example="Button not working"),
 *     @OA\Property(property="steps_to_reproduce", type="string", example="1. Click login\n2. Enter credentials\n3. Click submit"),
 *     @OA\Property(property="severity", type="string", enum={"Low", "Medium", "High", "Critical"}, example="High"),
 *     @OA\Property(property="screenshot_url", type="string", nullable=true, example="https://example.com/screenshot.jpg"),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime"),
 *     @OA\Property(property="validation_status", type="string", example="Pending"),
 *     @OA\Property(
 *         property="uat_task",
 *         type="object",
 *         ref="#/components/schemas/UATTaskResource",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="crowdworker",
 *         type="object",
 *         ref="#/components/schemas/CrowdworkerResource",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="validation",
 *         type="object",
 *         ref="#/components/schemas/BugValidationResource",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="application",
 *         type="object",
 *         ref="#/components/schemas/ApplicationResource",
 *         nullable=true
 *     )
 * )
 */
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
            'screenshot_url' => $this->screenshot_url,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'uat_task' => new UATTaskResource($this->whenLoaded('uatTask')),
            'crowdworker' => new CrowdworkerResource($this->whenLoaded('crowdworker')),
            'validation' => new BugValidationResource($this->whenLoaded('validation')),
            'validation_status' => $this->whenLoaded('validation', function () {
                return $this->validation ? $this->validation->validation_status : 'Pending';
            }),
            'application' => $this->whenLoaded('uatTask', function () {
                return new ApplicationResource($this->uatTask->application);
            })
        ];
    }
}