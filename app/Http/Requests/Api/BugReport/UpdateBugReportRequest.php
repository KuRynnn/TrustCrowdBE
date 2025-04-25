<?php

namespace App\Http\Requests\Api\BugReport;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateBugReportRequest",
 *     @OA\Property(
 *         property="task_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="ID of the UAT task"
 *     ),
 *     @OA\Property(
 *         property="worker_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="ID of the crowdworker"
 *     ),
 *     @OA\Property(
 *         property="bug_description",
 *         type="string",
 *         example="Updated: Login button not responding after recent deployment",
 *         description="Updated description of the bug"
 *     ),
 *     @OA\Property(
 *         property="steps_to_reproduce",
 *         type="string",
 *         example="1. Clear cache\n2. Navigate to login page\n3. Enter credentials\n4. Click login button",
 *         description="Updated step-by-step instructions to reproduce the bug"
 *     ),
 *     @OA\Property(
 *         property="severity",
 *         type="string",
 *         enum={"Low", "Medium", "High", "Critical"},
 *         example="Critical",
 *         description="Updated severity level of the bug"
 *     ),
 *     @OA\Property(
 *         property="screenshot_url",
 *         type="string",
 *         example="https://example.com/updated-screenshot.jpg",
 *         description="Updated URL of the bug screenshot",
 *         nullable=true
 *     )
 * )
 */
class UpdateBugReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'task_id' => 'sometimes|required|exists:uat_tasks,task_id|uuid',
            'worker_id' => 'sometimes|required|exists:crowdworkers,worker_id',
            'bug_description' => 'sometimes|required|string',
            'steps_to_reproduce' => 'sometimes|required|string',
            'severity' => 'sometimes|required|in:Low,Medium,High,Critical',
            'screenshot_url' => 'nullable|string|url'
        ];
    }

    public function messages()
    {
        return [
            'task_id.exists' => 'Selected UAT task does not exist',
            'worker_id.exists' => 'Selected crowdworker does not exist',
            'bug_description.required' => 'Bug description is required',
            'steps_to_reproduce.required' => 'Steps to reproduce are required',
            'severity.in' => 'Invalid severity level',
            'screenshot_url.url' => 'Invalid screenshot URL format'
        ];
    }
}