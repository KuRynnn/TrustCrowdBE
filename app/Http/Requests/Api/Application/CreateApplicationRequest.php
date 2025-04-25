<?php

// app/Http/Requests/Api/Application/CreateApplicationRequest.php
namespace App\Http\Requests\Api\Application;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateApplicationRequest",
 *     required={"client_id", "app_name", "app_url", "platform", "description", "status"},
 *     @OA\Property(
 *         property="client_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="UUID of the client"
 *     ),
 *     @OA\Property(
 *         property="app_name",
 *         type="string",
 *         example="Test Application",
 *         description="Name of the application"
 *     ),
 *     @OA\Property(
 *         property="app_url",
 *         type="string",
 *         example="https://testapp.com",
 *         description="URL of the application"
 *     ),
 *     @OA\Property(
 *         property="platform",
 *         type="string",
 *         example="web",
 *         description="Platform of the application"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="This is a test application",
 *         description="Description of the application"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "active", "completed"},
 *         example="pending",
 *         description="Status of the application"
 *     )
 * )
 */
class CreateApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'client_id' => 'required|uuid|exists:clients,client_id',
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'platform' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable|in:pending,active,completed'
        ];
    }

    public function messages()
    {
        return [
            'client_id.exists' => 'Selected client does not exist',
            'client_id.uuid' => 'Invalid UUID format for client_id',
            'app_url.url' => 'Invalid application URL format',
            'status.in' => 'Invalid status value'
        ];
    }
}
