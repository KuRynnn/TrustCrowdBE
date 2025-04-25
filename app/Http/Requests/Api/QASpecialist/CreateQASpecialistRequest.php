<?php

namespace App\Http\Requests\Api\QASpecialist;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateQASpecialistRequest",
 *     required={"name", "email", "expertise", "password", "password_confirmation"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Jane Smith",
 *         description="Full name of the QA specialist",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="jane@example.com",
 *         description="Email address of the QA specialist",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="expertise",
 *         type="string",
 *         example="Web Application Testing, Security Testing, API Testing",
 *         description="Areas of expertise in testing"
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
class CreateQASpecialistRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:qa_specialists,email',
            'expertise' => 'required|string',
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
            'expertise.required' => 'Expertise field is required',
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
            'expertise' => 'areas of expertise',
            'password' => 'password'
        ];
    }
}