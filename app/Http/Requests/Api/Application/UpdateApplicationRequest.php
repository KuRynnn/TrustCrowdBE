<?php
// app/Http/Requests/Api/Application/UpdateApplicationRequest.php
namespace App\Http\Requests\Api\Application;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Application;

class UpdateApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Get the application being updated
        $application = Application::find($this->route('id'));
        $currentWorkers = 0;

        if ($application) {
            // Count unique workers currently testing this application
            $currentWorkers = $application->uatTasks()
                ->distinct('worker_id')
                ->count('worker_id');
        }

        return [
            'client_id' => 'sometimes|required|exists:clients,client_id|uuid',
            'app_name' => 'sometimes|required|string|max:255',
            'app_url' => 'sometimes|required|url',
            'platform' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:pending,active,completed',
            'max_testers' => [
                'sometimes',
                'required',
                'integer',
                'min:' . max(1, $currentWorkers), // Can't be less than current workers
                'max:100'
            ]
        ];
    }

    public function messages()
    {
        // Get current worker count for dynamic error message
        $application = Application::find($this->route('id'));
        $currentWorkers = 0;

        if ($application) {
            $currentWorkers = $application->uatTasks()
                ->distinct('worker_id')
                ->count('worker_id');
        }

        return [
            'client_id.exists' => 'Selected client does not exist',
            'client_id.uuid' => 'Invalid UUID format for client_id',
            'app_url.url' => 'Invalid application URL format',
            'status.in' => 'Invalid status value',
            'max_testers.integer' => 'Maximum testers must be a number',
            'max_testers.min' => $currentWorkers > 1
                ? "Cannot set maximum testers below {$currentWorkers} as there are already {$currentWorkers} testers working on this application"
                : 'At least 1 tester must be allowed',
            'max_testers.max' => 'Maximum 100 testers allowed'
        ];
    }
}