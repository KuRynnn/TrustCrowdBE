<?php

namespace App\Http\Requests\Api\BugReport;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateBugReportRequest",
 *     required={"task_id", "worker_id", "bug_description", "steps_to_reproduce", "severity"},
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
 *         example="Login button not responding",
 *         description="Detailed description of the bug"
 *     ),
 *     @OA\Property(
 *         property="steps_to_reproduce",
 *         type="string",
 *         example="1. Navigate to login page\n2. Enter credentials\n3. Click login button",
 *         description="Step-by-step instructions to reproduce the bug"
 *     ),
 *     @OA\Property(
 *         property="severity",
 *         type="string",
 *         enum={"Low", "Medium", "High", "Critical"},
 *         example="High",
 *         description="Severity level of the bug"
 *     ),
 *     @OA\Property(
 *         property="screenshot_url",
 *         type="string",
 *         example="https://example.com/screenshot.jpg",
 *         description="URL of the bug screenshot",
 *         nullable=true
 *     )
 * )
 */
class CreateBugReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'task_id' => 'required|exists:uat_tasks,task_id|uuid',
            'worker_id' => 'required|exists:crowdworkers,worker_id',
            'bug_description' => 'required|string',
            'steps_to_reproduce' => 'required|string',
            'severity' => 'required|in:Low,Medium,High,Critical',
            'screenshot_url' => 'nullable|string|url'
        ];
    }

    public function messages()
    {
        return [
            'task_id.required' => 'UAT Task ID is required',
            'task_id.exists' => 'Selected UAT task does not exist',
            'worker_id.required' => 'Worker ID is required',
            'worker_id.exists' => 'Selected crowdworker does not exist',
            'bug_description.required' => 'Bug description is required',
            'steps_to_reproduce.required' => 'Steps to reproduce are required',
            'severity.required' => 'Severity level is required',
            'severity.in' => 'Invalid severity level',
            'screenshot_url.url' => 'Invalid screenshot URL format'
        ];
    }
}