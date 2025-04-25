<?php

// app/Http/Requests/Api/Auth/RegisterRequest.php
namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'user_type' => 'required|in:client,crowdworker,qa_specialist',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email|unique:crowdworkers,email|unique:qa_specialists,email',
            'password' => 'required|string|min:8|confirmed'
        ];

        switch ($this->user_type) {
            case 'client':
                $rules['company'] = 'required|string|max:255';
                break;

            case 'crowdworker':
                $rules['skills'] = 'required|string|max:255';
                break;

            case 'qa_specialist':
                $rules['expertise'] = 'required|string|max:255';
                break;
        }

        return $rules;
    }
}