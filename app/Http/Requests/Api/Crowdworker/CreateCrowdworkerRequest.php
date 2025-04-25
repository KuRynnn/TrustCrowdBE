<?php

namespace App\Http\Requests\Api\Crowdworker;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateCrowdworkerRequest",
 *     required={"name", "email", "skills", "password", "password_confirmation"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="John Doe",
 *         description="Full name of the crowdworker",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="john@example.com",
 *         description="Email address of the crowdworker",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="skills",
 *         type="string",
 *         example="Web Testing, Mobile Testing, API Testing",
 *         description="Comma-separated list of testing skills"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="password123",
 *         description="Password for the account",
 *         minLength=8
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         example="password123",
 *         description="Password confirmation"
 *     )
 * )
 */
class CreateCrowdworkerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:crowdworkers,email',
            'skills' => 'required|string',
            'password' => 'required|min:8|confirmed'
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
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match'
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
            'password' => 'password'
        ];
    }
}