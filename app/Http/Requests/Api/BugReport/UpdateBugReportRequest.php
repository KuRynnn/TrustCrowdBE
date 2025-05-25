<?php

namespace App\Http\Requests\Api\BugReport;

use Illuminate\Foundation\Http\FormRequest;

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