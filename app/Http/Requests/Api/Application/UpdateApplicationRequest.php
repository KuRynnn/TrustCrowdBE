<?php

// app/Http/Requests/Api/Application/UpdateApplicationRequest.php
namespace App\Http\Requests\Api\Application;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateApplicationRequest",
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
 *         example="Updated Application",
 *         description="Name of the application"
 *     ),
 *     @OA\Property(
 *         property="app_url",
 *         type="string",
 *         example="https://updatedapp.com",
 *         description="URL of the application"
 *     ),
 *     @OA\Property(
 *         property="platform",
 *         type="string",
 *         example="android",
 *         description="Platform of the application"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="Updated description",
 *         description="Description of the application"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "active", "completed"},
 *         example="active",
 *         description="Status of the application"
 *     )
 * )
 */
class UpdateApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'client_id' => 'sometimes|required|exists:clients,client_id|uuid',
            'app_name' => 'sometimes|required|string|max:255',
            'app_url' => 'sometimes|required|url',
            'platform' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:pending,active,completed'
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