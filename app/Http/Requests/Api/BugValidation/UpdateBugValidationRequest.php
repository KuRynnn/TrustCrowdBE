<?php

namespace App\Http\Requests\Api\BugValidation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateBugValidationRequest",
 *     @OA\Property(
 *         property="qa_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="ID of the QA specialist"
 *     ),
 *     @OA\Property(
 *         property="validation_status",
 *         type="string",
 *         enum={"Valid", "Invalid", "Needs More Info"},
 *         example="Valid",
 *         description="Updated status of the validation"
 *     ),
 *     @OA\Property(
 *         property="comments",
 *         type="string",
 *         example="Updated: Bug has been verified and is reproducible",
 *         description="Updated validation comments"
 *     ),
 *     @OA\Property(
 *         property="validated_at",
 *         type="string",
 *         format="datetime",
 *         nullable=true,
 *         example="2025-03-12 10:00:00",
 *         description="Updated timestamp of validation"
 *     )
 * )
 */
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