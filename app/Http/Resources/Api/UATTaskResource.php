<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class UATTaskResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'task_id' => $this->task_id,
            'app_id' => $this->app_id,
            'test_id' => $this->test_id,
            'worker_id' => $this->worker_id,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Always include application data when loaded
            'application' => $this->whenLoaded('application', function () {
                return new ApplicationResource($this->application);
            }),

            // Always include test case data when loaded
            'test_case' => $this->whenLoaded('testCase', function () {
                return new TestCaseResource($this->testCase);
            }),

            // Always include crowdworker data when loaded
            'crowdworker' => $this->whenLoaded('crowdworker', function () {
                return new CrowdworkerResource($this->crowdworker);
            }),
        ];

        // Add timing information conditionally
        if ($this->started_at || $this->completed_at) {
            $data['timing'] = [
                'started_at' => $this->started_at ? $this->started_at->toDateTimeString() : null,
                'completed_at' => $this->completed_at ? $this->completed_at->toDateTimeString() : null,
                'duration' => $this->when($this->started_at, function () {
                    return $this->started_at->diffInMinutes($this->completed_at ?? now());
                }),
            ];
        }

        // Add revision information only if there are revisions
        if ($this->revision_count > 0) {
            $data['revision'] = [
                'count' => $this->revision_count,
                'status' => $this->revision_status,
                'comments' => $this->revision_comments,
                'last_revised_at' => $this->last_revised_at ? $this->last_revised_at->toDateTimeString() : null,
            ];
        }

        // Include additional relationships only when explicitly requested
        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));

            if (in_array('task_validation', $includes)) {
                $data['task_validation'] = new TaskValidationResource($this->whenLoaded('taskValidation'));
            }

            // Include bug reports conditionally
            if (in_array('bug_reports', $includes)) {
                $data['bug_reports_count'] = $this->whenLoaded('bugReports', function () {
                    return $this->bugReports->count();
                });

                $data['bug_reports'] = $this->whenLoaded('bugReports', function () {
                    return $this->bugReports->map(function ($bug) {
                        $bug->loadMissing(['evidence', 'validation']);
                        return new BugReportResource($bug);
                    });
                });
            }

            // Include evidence conditionally
            if (in_array('evidence', $includes)) {
                $data['evidence'] = $this->whenLoaded('evidence', function () {
                    return TestEvidenceResource::collection($this->evidence);
                });

                $data['evidence_count'] = $this->whenLoaded('evidence', function () {
                    return $this->evidence->count();
                });
            }
        } else {
            // Always include bug reports when loaded (even without explicit include)
            if ($this->relationLoaded('bugReports')) {
                $data['bug_reports_count'] = $this->bugReports->count();
                $data['bug_reports'] = $this->bugReports->map(function ($bug) {
                    // Make sure related models are loaded
                    $bug->loadMissing(['evidence', 'validation']);
                    return new BugReportResource($bug);
                });
            }

            // Always include evidence when loaded
            if ($this->relationLoaded('evidence')) {
                $data['evidence'] = TestEvidenceResource::collection($this->evidence);
                $data['evidence_count'] = $this->evidence->count();
            }

            // Always include task validation when loaded
            if ($this->relationLoaded('taskValidation')) {
                $data['task_validation'] = new TaskValidationResource($this->taskValidation);
            }
        }

        return $data;
    }
}