<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * @OA\Schema(
 *     schema="CreateClientRequest",
 *     required={"name", "email", "password", "password_confirmation", "company"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="John Doe",
 *         description="Name of the client",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="john@example.com",
 *         description="Email address of the client",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="Secret123!",
 *         description="Password for the client account (min 8 chars, must contain numbers and letters)",
 *         minLength=8
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         example="Secret123!",
 *         description="Password confirmation"
 *     ),
 *     @OA\Property(
 *         property="company",
 *         type="string",
 *         example="Acme Inc",
 *         description="Company name of the client",
 *         maxLength=255
 *     )
 * )
 */
class CreateClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients,email'],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->mixedCase(),
                'confirmed'
            ],
            'company' => ['required', 'string', 'max:255']
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'email.max' => 'Email cannot exceed 255 characters',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.letters' => 'Password must contain at least one letter',
            'password.numbers' => 'Password must contain at least one number',
            'password.mixed' => 'Password must contain both uppercase and lowercase letters',
            'password.confirmed' => 'Password confirmation does not match',
            'company.required' => 'Company name is required',
            'company.max' => 'Company name cannot exceed 255 characters'
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'password' => 'password',
            'company' => 'company name'
        ];
    }
}