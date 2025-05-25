<?php

namespace App\Http\Requests\Api\TestCase;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestCaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'app_id' => 'sometimes|required|exists:applications,app_id|uuid',
            'qa_id' => 'sometimes|required|exists:qa_specialists,qa_id|uuid',
            'test_title' => 'sometimes|required|string|max:255',
            'given_context' => 'sometimes|required|string',
            'when_action' => 'sometimes|required|string',
            'then_result' => 'sometimes|required|string',
            'priority' => 'sometimes|required|in:Low,Medium,High'
        ];
    }

    public function messages()
    {
        return [
            'app_id.exists' => 'Selected application does not exist',
            'qa_id.exists' => 'Selected QA Specialist does not exist',
            'test_title.required' => 'Test title is required',
            'test_title.max' => 'Test title cannot exceed 255 characters',
            'given_context.required' => 'Given context is required',
            'when_action.required' => 'When action is required',
            'then_result.required' => 'Then result is required',
            'priority.in' => 'Priority must be low, medium, or high'
        ];
    }

    public function attributes()
    {
        return [
            'app_id' => 'application',
            'qa_id' => 'QA specialist',
            'test_title' => 'test title',
            'given_context' => 'given context',
            'when_action' => 'when action',
            'then_result' => 'then result',
            'priority' => 'priority level'
        ];
    }
}