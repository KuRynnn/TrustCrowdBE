<?php

namespace App\Http\Requests\Api\UATTask;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateUATTaskRequest",
 *     @OA\Property(
 *         property="app_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="ID of the application"
 *     ),
 *     @OA\Property(
 *         property="test_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="ID of the test case"
 *     ),
 *     @OA\Property(
 *         property="worker_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="ID of the crowdworker"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"Assigned", "In Progress", "Completed"},
 *         example="In Progress",
 *         description="Updated status of the UAT task"
 *     ),
 *     @OA\Property(
 *         property="started_at",
 *         type="string",
 *         format="datetime",
 *         example="2025-03-12 10:00:00",
 *         nullable=true,
 *         description="Updated start time of the task"
 *     ),
 *     @OA\Property(
 *         property="completed_at",
 *         type="string",
 *         format="datetime",
 *         example="2025-03-12 11:00:00",
 *         nullable=true,
 *         description="Updated completion time of the task"
 *     )
 * )
 */
class UpdateUATTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'app_id' => 'sometimes|required|exists:applications,app_id|uuid',
            'test_id' => 'sometimes|required|exists:test_cases,test_id|uuid',
            'worker_id' => 'sometimes|required|exists:crowdworkers,worker_id|uuid',
            'status' => 'sometimes|required|in:Assigned,In Progress,Completed',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after:started_at'
        ];
    }

    public function messages()
    {
        return [
            'app_id.exists' => 'Selected application does not exist',
            'test_id.exists' => 'Selected test case does not exist',
            'worker_id.exists' => 'Selected crowdworker does not exist',
            'status.in' => 'Invalid status value',
            'completed_at.after' => 'Completion date must be after start date'
        ];
    }

    public function attributes()
    {
        return [
            'app_id' => 'application',
            'test_id' => 'test case',
            'worker_id' => 'crowdworker',
            'status' => 'task status',
            'started_at' => 'start date',
            'completed_at' => 'completion date'
        ];
    }
}