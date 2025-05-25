<?php

namespace App\Http\Requests\Api\BugValidation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBugValidationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'qa_id' => 'sometimes|required|exists:qa_specialists,qa_id|uuid',
            'validation_status' => 'sometimes|required|in:Valid,Invalid,Needs More Info',
            'comments' => 'sometimes|required|string',
            'validated_at' => 'nullable|date'
        ];
    }

    public function messages()
    {
        return [
            'qa_id.exists' => 'Selected QA specialist does not exist',
            'validation_status.in' => 'Invalid validation status',
            'comments.required' => 'Validation comments are required'
        ];
    }
}