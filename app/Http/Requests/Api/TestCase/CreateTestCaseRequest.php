<?php

namespace App\Http\Requests\Api\TestCase;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateTestCaseRequest",
 *     required={"app_id", "qa_id", "test_title", "test_steps", "expected_result", "priority"},
 *     @OA\Property(
 *         property="app_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="ID of the application"
 *     ),
 *     @OA\Property(
 *         property="qa_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="ID of the QA specialist"
 *     ),
 *     @OA\Property(
 *         property="test_title",
 *         type="string",
 *         example="User Login Functionality",
 *         description="Title of the test case",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="test_steps",
 *         type="string",
 *         example="1. Navigate to login page\n2. Enter valid credentials\n3. Click login button",
 *         description="Detailed steps to execute the test"
 *     ),
 *     @OA\Property(
 *         property="expected_result",
 *         type="string",
 *         example="User should be successfully logged in and redirected to dashboard",
 *         description="Expected outcome of the test"
 *     ),
 *     @OA\Property(
 *         property="priority",
 *         type="string",
 *         enum={"Low", "Medium", "High"},
 *         example="High",
 *         description="Priority level of the test case"
 *     )
 * )
 */
class CreateTestCaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'app_id' => 'required|exists:applications,app_id|uuid',
            'qa_id' => 'required|exists:qa_specialists,qa_id|uuid',
            'test_title' => 'required|string|max:255',
            'test_steps' => 'required|string',
            'expected_result' => 'required|string',
            'priority' => 'required|in:Low,Medium,High'
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
            'test_steps.required' => 'Test steps are required',
            'expected_result.required' => 'Expected result is required',
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
            'test_steps' => 'test steps',
            'expected_result' => 'expected result',
            'priority' => 'priority level'
        ];
    }
}