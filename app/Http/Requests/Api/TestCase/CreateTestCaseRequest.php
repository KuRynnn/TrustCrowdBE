<?php

namespace App\Http\Requests\Api\TestCase;

use Illuminate\Foundation\Http\FormRequest;

class CreateTestCaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'app_id' => 'required|uuid|exists:applications,app_id',
            'qa_id' => 'required|uuid|exists:qa_specialists,qa_id',
            'test_title' => 'required|string|max:255',
            'given_context' => 'required|string',
            'when_action' => 'required|string',
            'then_result' => 'required|string',
            'priority' => 'required|string|in:Low,Medium,High'
        ];
    }

    public function messages()
    {
        return [
            'app_id.required' => 'Application ID is required',
            'app_id.exists' => 'Selected application does not exist',
            'qa_id.required' => 'QA Specialist ID is required',
            'qa_id.exists' => 'Selected QA Specialist does not exist',
            'test_title.required' => 'Test title is required',
            'test_title.max' => 'Test title cannot exceed 255 characters',
            'given_context.required' => 'Given context is required',
            'when_action.required' => 'When action is required',
            'then_result.required' => 'Then result is required',
            'priority.required' => 'Priority is required',
            'priority.in' => 'Priority must be Low, Medium, or High'
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