<?php

namespace App\Http\Requests\Api\Crowdworker;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateCrowdworkerRequest",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="John Doe Updated",
 *         description="Updated full name of the crowdworker",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="john.updated@example.com",
 *         description="Updated email address of the crowdworker",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="skills",
 *         type="string",
 *         example="Web Testing, Mobile Testing, API Testing, Performance Testing",
 *         description="Updated comma-separated list of testing skills"
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
 *         description="Updated status of the crowdworker"
 *     )
 * )
 */
class UpdateCrowdworkerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:crowdworkers,email,' . $this->route('id') . ',worker_id',
            'skills' => 'sometimes|required|string',
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
            'skills.required' => 'Skills are required',
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
            'skills' => 'testing skills',
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
        if ($this->skills) {
            $this->merge([
                'skills' => is_array($this->skills) ? implode(', ', $this->skills) : $this->skills
            ]);
        }
    }
}