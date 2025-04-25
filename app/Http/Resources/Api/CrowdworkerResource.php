<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CrowdworkerResource",
 *     @OA\Property(property="worker_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="skills", type="string", example="Web Testing, Mobile Testing, API Testing"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
 *     @OA\Property(property="completed_tasks_count", type="integer", example=5, nullable=true),
 *     @OA\Property(property="total_bug_reports", type="integer", example=10, nullable=true),
 *     @OA\Property(
 *         property="uat_tasks",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="task_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *             @OA\Property(property="title", type="string", example="Test Login Feature"),
 *             @OA\Property(property="status", type="string", example="Completed")
 *         ),
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
 *             @OA\Property(property="description", type="string", example="Login button not working"),
 *             @OA\Property(property="severity", type="string", example="High")
 *         ),
 *         nullable=true
 *     )
 * )
 */
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