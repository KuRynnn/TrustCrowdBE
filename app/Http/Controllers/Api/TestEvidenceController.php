<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TestEvidence\TestEvidenceService;
use App\Services\UATTask\UATTaskService;
use App\Http\Resources\Api\TestEvidenceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;


class TestEvidenceController extends Controller
{
    use ApiResponse;
    protected $testEvidenceService;
    protected $uatTaskService;
    public function __construct(
        TestEvidenceService $testEvidenceService,
        UATTaskService $uatTaskService
    ) {
        $this->testEvidenceService = $testEvidenceService;
        $this->uatTaskService = $uatTaskService;
    }
    /**
     * Upload evidence for a bug report
     */
    public function uploadForBug(Request $request, $bugId)
    {
        $validator = Validator::make($request->all(), [
            'step_number' => 'required|integer|min:1',
            'step_description' => 'required|string',
            'screenshot' => 'required|image|max:5120', // Max 5MB
            'notes' => 'nullable|string',
            'context' => 'nullable|string|in:given,when,then' // Add context validation
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        try {
            $evidence = $this->testEvidenceService->uploadEvidenceForBug(
                $bugId,
                $request->input('step_number'),
                $request->input('step_description'),
                $request->file('screenshot'),
                $request->input('notes'),
                $request->input('context', 'then') // Default to 'then' context
            );
            return response()->json([
                'message' => 'Evidence uploaded successfully',
                'data' => new TestEvidenceResource($evidence)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload evidence',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload evidence for a task
     */
    public function uploadForTask(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'step_number' => 'required|integer|min:1',
            'step_description' => 'required|string',
            'screenshot' => 'required|image|max:5120',
            'context' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            // Get the task to ensure it exists
            $task = $this->uatTaskService->getTaskById($taskId);

            // Get the step description from the request
            $stepDescription = $request->input('step_description');

            // Check if test case exists before trying to access its properties
            $testCase = $task->testCase ?? null;
            $fullDescription = $stepDescription; // Default to just the provided description

            // Only try to add context if test case exists
            if ($testCase) {
                $context = $request->input('context', 'when'); // Default to 'when'

                // Safely access test case properties with defaults
                $givenContext = $testCase->given_context ?? '';
                $whenAction = $testCase->when_action ?? '';
                $thenResult = $testCase->then_result ?? '';

                // Prepare context-specific description
                if ($context === 'given' && !empty($givenContext)) {
                    $fullDescription = '[GIVEN] ' . $givenContext . ' - ' . $stepDescription;
                } elseif ($context === 'when' && !empty($whenAction)) {
                    $fullDescription = '[WHEN] ' . $whenAction . ' - ' . $stepDescription;
                } elseif ($context === 'then' && !empty($thenResult)) {
                    $fullDescription = '[THEN] ' . $thenResult . ' - ' . $stepDescription;
                }
            }

            // Make sure the screenshot file is present
            if (!$request->hasFile('screenshot')) {
                return $this->errorResponse('Screenshot file is missing', 422);
            }

            // Upload the evidence
            $evidence = $this->testEvidenceService->uploadEvidenceForTask(
                $taskId,
                $request->input('step_number'),
                $fullDescription,
                $request->file('screenshot'),
                $request->input('notes'),
                $request->input('context')
            );

            return $this->successResponse(
                new TestEvidenceResource($evidence),
                'Evidence uploaded successfully'
            );
        } catch (\Exception $e) {
            // Log the detailed error for debugging
            \Log::error('Evidence upload failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return $this->errorResponse('Failed to upload evidence: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all evidence for a bug report
     */
    public function getForBug($bugId)
    {
        try {
            $evidence = $this->testEvidenceService->getEvidenceForBug($bugId);

            return response()->json([
                'data' => TestEvidenceResource::collection($evidence)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get evidence',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all evidence for a task
     */
    public function getForTask($taskId)
    {
        try {
            $evidence = $this->testEvidenceService->getEvidenceForTask($taskId);

            return response()->json([
                'data' => TestEvidenceResource::collection($evidence)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get evidence',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete evidence
     */
    public function delete($evidenceId)
    {
        try {
            $this->testEvidenceService->deleteEvidence($evidenceId);

            return response()->json([
                'message' => 'Evidence deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete evidence',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update evidence
     */
    public function update(Request $request, $evidenceId)
    {
        $validator = Validator::make($request->all(), [
            'step_description' => 'required|string',
            'notes' => 'nullable|string',
            'step_number' => 'sometimes|integer|min:1',
            'context' => 'sometimes|string|in:given,when,then'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            // Update only the fields that are provided
            $updateData = [];

            if ($request->has('step_description')) {
                $updateData['step_description'] = $request->input('step_description');
            }

            if ($request->has('notes')) {
                $updateData['notes'] = $request->input('notes');
            }

            if ($request->has('step_number')) {
                $updateData['step_number'] = $request->input('step_number');
            }

            if ($request->has('context')) {
                $updateData['context'] = $request->input('context');
            }

            $evidence = $this->testEvidenceService->updateEvidence($evidenceId, $updateData);

            return $this->successResponse(
                new TestEvidenceResource($evidence),
                'Evidence updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update evidence: ' . $e->getMessage(), 500);
        }
    }
}