<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UATTaskResource",
 *     @OA\Property(property="task_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="app_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="test_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="worker_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="status", type="string", enum={"Assigned", "In Progress", "Completed"}, example="In Progress"),
 *     @OA\Property(property="started_at", type="string", format="datetime", example="2025-03-12 10:00:00", nullable=true),
 *     @OA\Property(property="completed_at", type="string", format="datetime", example="2025-03-12 11:00:00", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 11:00:00"),
 *     @OA\Property(property="duration", type="integer", example=60, nullable=true),
 *     @OA\Property(property="bug_reports_count", type="integer", example=2, nullable=true),
 *     @OA\Property(
 *         property="application",
 *         type="object",
 *         @OA\Property(property="app_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *         @OA\Property(property="app_name", type="string", example="E-commerce Platform"),
 *         @OA\Property(property="app_url", type="string", example="https://example.com"),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="test_case",
 *         type="object",
 *         @OA\Property(property="test_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *         @OA\Property(property="test_title", type="string", example="User Login Test"),
 *         @OA\Property(property="test_steps", type="string", example="1. Navigate to login page\n2. Enter credentials"),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="crowdworker",
 *         type="object",
 *         @OA\Property(property="worker_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="bug_reports",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="bug_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *             @OA\Property(property="title", type="string", example="Login Button Not Responsive"),
 *             @OA\Property(property="severity", type="string", example="High")
 *         ),
 *         nullable=true
 *     )
 * )
 */
class UATTaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'task_id' => $this->task_id,
            'app_id' => $this->app_id,
            'test_id' => $this->test_id,
            'worker_id' => $this->worker_id,
            'status' => $this->status,
            'started_at' => $this->started_at ? $this->started_at->toDateTimeString() : null,
            'completed_at' => $this->completed_at ? $this->completed_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'application' => new ApplicationResource($this->whenLoaded('application')),
            'test_case' => new TestCaseResource($this->whenLoaded('testCase')),
            'crowdworker' => new CrowdworkerResource($this->whenLoaded('crowdworker')),
            'bug_reports' => BugReportResource::collection($this->whenLoaded('bugReports')),
            'bug_reports_count' => $this->whenLoaded('bugReports', function () {
                return $this->bugReports->count();
            }),
            'duration' => $this->when($this->started_at && $this->completed_at, function () {
                return $this->started_at->diffInMinutes($this->completed_at);
            })
        ];
    }
}