<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TestCaseResource",
 *     @OA\Property(property="test_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="app_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="qa_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="test_title", type="string", example="User Login Functionality"),
 *     @OA\Property(property="test_steps", type="string", example="1. Navigate to login page\n2. Enter valid credentials\n3. Click login button"),
 *     @OA\Property(property="expected_result", type="string", example="User should be successfully logged in and redirected to dashboard"),
 *     @OA\Property(property="priority", type="string", enum={"Low", "Medium", "High"}, example="High"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
 *     @OA\Property(
 *         property="application",
 *         type="object",
 *         @OA\Property(property="app_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *         @OA\Property(property="app_name", type="string", example="E-commerce Platform"),
 *         @OA\Property(property="app_url", type="string", example="https://ecommerce.example.com"),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="qa_specialist",
 *         type="object",
 *         @OA\Property(property="qa_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *         @OA\Property(property="name", type="string", example="Jane Smith"),
 *         @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="uat_tasks",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="task_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *             @OA\Property(property="status", type="string", example="In Progress"),
 *             @OA\Property(property="assigned_to", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *             @OA\Property(property="completed_at", type="string", format="datetime", example="2025-03-12 10:00:00", nullable=true)
 *         ),
 *         nullable=true
 *     )
 * )
 */
class TestCaseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'test_id' => $this->test_id,
            'app_id' => $this->app_id,
            'qa_id' => $this->qa_id,
            'test_title' => $this->test_title,
            'test_steps' => $this->test_steps,
            'expected_result' => $this->expected_result,
            'priority' => $this->priority,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'application' => new ApplicationResource($this->whenLoaded('application')),
            'qa_specialist' => new QASpecialistResource($this->whenLoaded('qaSpecialist')),
            'uat_tasks' => UATTaskResource::collection($this->whenLoaded('uatTasks'))
        ];
    }
}