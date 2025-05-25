<?php

namespace App\Http\Requests\Api\BugValidation;

use Illuminate\Foundation\Http\FormRequest;

class CreateBugValidationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'bug_id' => 'required|exists:bug_reports,bug_id|uuid|unique:bug_validations,bug_id',
            'qa_id' => 'required|exists:qa_specialists,qa_id|uuid',
            'validation_status' => 'required|in:Valid,Invalid,Needs More Info',
            'comments' => 'required|string',
            'validated_at' => 'nullable|date'
        ];
    }

    public function messages()
    {
        return [
            'bug_id.required' => 'Bug report ID is required',
            'bug_id.exists' => 'Selected bug report does not exist',
            'bug_id.unique' => 'This bug report has already been validated',
            'bug_id.uuid' => 'Invalid UUID format for bug_id',
            'qa_id.required' => 'QA Specialist ID is required',
            'qa_id.exists' => 'Selected QA specialist does not exist',
            'qa_id.uuid' => 'Invalid UUID format for qa_id',
            'validation_status.required' => 'Validation status is required',
            'validation_status.in' => 'Invalid validation status',
            'comments.required' => 'Validation comments are required'
        ];
    }
}
