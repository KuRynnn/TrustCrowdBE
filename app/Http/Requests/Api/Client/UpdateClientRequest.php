<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * @OA\Schema(
 *     schema="UpdateClientRequest",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="John Doe Updated",
 *         description="Updated name of the client",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="john.updated@example.com",
 *         description="Updated email address of the client",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="NewSecret123!",
 *         description="New password (optional, min 8 chars, must contain numbers and letters)",
 *         minLength=8,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         example="NewSecret123!",
 *         description="New password confirmation",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="company",
 *         type="string",
 *         example="Acme Corp Updated",
 *         description="Updated company name of the client",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"active", "inactive"},
 *         example="active",
 *         description="Client status"
 *     )
 * )
 */
class UpdateClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                'unique:clients,email,' . $this->route('id') . ',client_id'
            ],
            'password' => [
                'sometimes',
                'nullable',
                'string',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->mixedCase(),
                'confirmed'
            ],
            'company' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive']
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
            'password.min' => 'Password must be at least 8 characters',
            'password.letters' => 'Password must contain at least one letter',
            'password.numbers' => 'Password must contain at least one number',
            'password.mixed' => 'Password must contain both uppercase and lowercase letters',
            'password.confirmed' => 'Password confirmation does not match',
            'company.required' => 'Company name is required',
            'company.max' => 'Company name cannot exceed 255 characters',
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status value'
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'password' => 'password',
            'company' => 'company name',
            'status' => 'account status'
        ];
    }
}