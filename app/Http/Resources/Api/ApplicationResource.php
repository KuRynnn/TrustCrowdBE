<?php
// Update app\Http\Resources\Api\ApplicationResource.php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'max_testers' => (int) $this->max_testers,
            'created_at' => $this->created_at->toDateTimeString(),
        ];

        // Add worker count if available (from withCount)
        if (isset($this->unique_workers_count)) {
            $data['current_workers'] = (int) $this->unique_workers_count;
            $data['max_workers'] = 10; // Or get from config
        }

        // Add test case count if loaded
        if ($this->relationLoaded('testCases')) {
            $data['test_cases_count'] = $this->testCases->count();
        }

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
                    'given_context' => $testCase->given_context,
                    'when_action' => $testCase->when_action,
                    'then_result' => $testCase->then_result,
                    'priority' => $testCase->priority,
                    'created_at' => $testCase->created_at->toDateTimeString(),
                    'updated_at' => $testCase->updated_at->toDateTimeString(),
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