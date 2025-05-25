<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TestCaseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'test_id' => $this->test_id,
            'app_id' => $this->app_id,
            'qa_id' => $this->qa_id,
            'test_title' => $this->test_title,
            'given_context' => $this->given_context,
            'when_action' => $this->when_action,
            'then_result' => $this->then_result,
            'priority' => $this->priority,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Include relationships only when requested
            'application' => $this->when(
                $request->has('include') &&
                in_array('application', explode(',', $request->input('include'))),
                function () {
                    return new ApplicationResource($this->whenLoaded('application'));
                }
            ),

            'qa_specialist' => $this->when(
                $request->has('include') &&
                in_array('qa_specialist', explode(',', $request->input('include'))),
                function () {
                    return new QASpecialistResource($this->whenLoaded('qaSpecialist'));
                }
            ),

            'uat_tasks' => $this->when(
                $request->has('include') &&
                in_array('uat_tasks', explode(',', $request->input('include'))),
                function () {
                    return UATTaskResource::collection($this->whenLoaded('uatTasks'));
                }
            ),
        ];
    }
}