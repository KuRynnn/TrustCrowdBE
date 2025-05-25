<?php

// app/Http/Requests/Api/Application/CreateApplicationRequest.php
namespace App\Http\Requests\Api\Application;

use Illuminate\Foundation\Http\FormRequest;

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
            'status' => 'nullable|in:pending,active,completed',
            'max_testers' => 'required|integer|min:1|max:100'
        ];
    }

    public function messages()
    {
        return [
            'client_id.exists' => 'Selected client does not exist',
            'client_id.uuid' => 'Invalid UUID format for client_id',
            'app_url.url' => 'Invalid application URL format',
            'status.in' => 'Invalid status value',
            'max_testers.required' => 'Maximum number of testers is required',
            'max_testers.integer' => 'Maximum testers must be a number',
            'max_testers.min' => 'At least 1 tester must be allowed',
            'max_testers.max' => 'Maximum 100 testers allowed'
        ];
    }
}