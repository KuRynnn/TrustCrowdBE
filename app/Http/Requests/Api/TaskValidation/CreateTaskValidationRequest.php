<?php

namespace App\Http\Requests\Api\TaskValidation;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskValidationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'task_id' => 'required|exists:uat_tasks,task_id|uuid',
            'qa_id' => 'required|exists:qa_specialists,qa_id|uuid',
            'validation_status' => 'required|in:Pass Verified,Rejected,Need Revision',
            'comments' => 'nullable|string',
        ];
    }
}
