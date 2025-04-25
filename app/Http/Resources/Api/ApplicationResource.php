<?php

// app/Http/Resources/Api/ApplicationResource.php
namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ApplicationResource",
 *     title="Application Resource",
 *     description="Application resource representation",
 *     @OA\Property(
 *         property="app_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000",
 *         description="UUID of the application"
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
 *         example="Application description",
 *         description="Description of the application"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="pending",
 *         description="Status of the application"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="datetime",
 *         example="2025-03-12 10:00:00",
 *         description="Creation timestamp"
 *     )
 * )
 */
class ApplicationResource extends JsonResource
{
    public function toArray($request)
    {
        // Base application data
        $data = [
            'app_id' => (string) $this->app_id,
            'app_name' => $this->app_name,
            'app_url' => $this->app_url,
            'platform' => $this->platform,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];

        // Add client if loaded
        if ($this->relationLoaded('client')) {
            $data['client'] = new ClientResource($this->client);
        }

        // Add test cases if loaded
        if ($this->relationLoaded('testCases')) {
            $data['test_cases'] = $this->testCases->map(function ($testCase) {
                // Get the basic test case data
                $testCaseData = [
                    'test_id' => $testCase->test_id,
                    'app_id' => $testCase->app_id,
                    'qa_id' => $testCase->qa_id,
                    'test_title' => $testCase->test_title,
                    'test_steps' => $testCase->test_steps,
                    'expected_result' => $testCase->expected_result,
                    'priority' => $testCase->priority,
                ];

                // Add QA specialist if loaded
                if ($testCase->relationLoaded('qaSpecialist') && $testCase->qaSpecialist) {
                    $testCaseData['qa_specialist'] = [
                        'qa_id' => $testCase->qaSpecialist->qa_id,
                        'name' => $testCase->qaSpecialist->name,
                        'email' => $testCase->qaSpecialist->email,
                    ];
                }

                return $testCaseData;
            });
        }

        return $data;
    }
}