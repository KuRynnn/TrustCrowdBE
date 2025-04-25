<?php

namespace App\Http\Requests\Api\QASpecialist;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateQASpecialistRequest",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Jane Smith Updated",
 *         description="Updated full name of the QA specialist",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="jane.updated@example.com",
 *         description="Updated email address of the QA specialist",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="expertise",
 *         type="string",
 *         example="Web Testing, Security Testing, Performance Testing",
 *         description="Updated areas of expertise"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="newpassword123",
 *         description="New password for the account (optional)",
 *         minLength=8
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         example="newpassword123",
 *         description="New password confirmation"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"active", "inactive"},
 *         example="active",
 *         description="Updated status of the QA specialist"
 *     )
 * )
 */
class UpdateQASpecialistRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:qa_specialists,email,' . $this->route('id') . ',qa_id',
            'expertise' => 'sometimes|required|string',
            'password' => 'sometimes|required|min:8|confirmed',
            'status' => 'sometimes|required|in:active,inactive'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'expertise.required' => 'Expertise field is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'status.in' => 'Invalid status value'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'expertise' => 'areas of expertise',
            'password' => 'password',
            'status' => 'account status'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->expertise) {
            $this->merge([
                'expertise' => is_array($this->expertise) ? implode(', ', $this->expertise) : $this->expertise
            ]);
        }
    }
}