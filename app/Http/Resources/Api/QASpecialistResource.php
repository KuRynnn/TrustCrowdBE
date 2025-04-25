<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="QASpecialistResource",
 *     @OA\Property(property="qa_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="name", type="string", example="Jane Smith"),
 *     @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
 *     @OA\Property(property="expertise", type="string", example="Web Application Testing, Security Testing"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
 *     @OA\Property(
 *         property="test_cases",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="test_case_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *             @OA\Property(property="title", type="string", example="Login Functionality Test"),
 *             @OA\Property(property="status", type="string", example="Approved")
 *         ),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="bug_validations",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="validation_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *             @OA\Property(property="bug_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *             @OA\Property(property="status", type="string", example="Verified"),
 *             @OA\Property(property="comments", type="string", example="Bug verified and confirmed")
 *         ),
 *         nullable=true
 *     )
 * )
 */
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