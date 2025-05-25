<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BugReport\BugReportService;
use App\Services\TestEvidence\TestEvidenceService;
use App\Http\Resources\Api\BugReportResource;
use App\Http\Resources\Api\TestEvidenceResource;
use App\Http\Resources\Api\BugValidationResource;
use App\Http\Requests\Api\BugReport\CreateBugReportRequest;
use App\Http\Requests\Api\BugReport\UpdateBugReportRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BugReportController extends Controller
{
    use ApiResponse;

    protected $bugReportService;

    public function __construct(BugReportService $bugReportService)
    {
        $this->bugReportService = $bugReportService;
    }

    public function index()
    {
        $bugReports = $this->bugReportService->getAllBugReports();
        return $this->successResponse(
            BugReportResource::collection($bugReports),
            'Bug reports retrieved successfully'
        );
    }

    public function store(CreateBugReportRequest $request)
    {
        $validated = $request->validated();

        // Create the bug report
        $bugReport = $this->bugReportService->createBugReport($validated);

        // Load the evidence relationship
        $bugReport->load('evidence');

        return $this->successResponse(
            new BugReportResource($bugReport),
            'Bug report created successfully',
            201
        );
    }

    public function show($id)
    {
        $bugReport = $this->bugReportService->getBugReportById($id);
        $bugReport->load(['evidence', 'uatTask.application', 'crowdworker', 'validation', 'originalBugReport']);

        return $this->successResponse(
            new BugReportResource($bugReport),
            'Bug report retrieved successfully'
        );
    }

    public function update(UpdateBugReportRequest $request, $id)
    {
        $bugReport = $this->bugReportService->updateBugReportById($id, $request->validated());
        return $this->successResponse(
            new BugReportResource($bugReport),
            'Bug report updated successfully'
        );
    }

    public function destroy($id)
    {
        $this->bugReportService->deleteBugReportById($id);
        return $this->successResponse(
            null,
            'Bug report deleted successfully'
        );
    }

    public function getBySeverity($severity)
    {
        $bugReports = $this->bugReportService->getBugReportsBySeverity($severity);
        return $this->successResponse(
            BugReportResource::collection($bugReports),
            'Bug reports retrieved successfully'
        );
    }

    public function uploadScreenshot(Request $request, $id)
    {
        try {
            \Log::info("Screenshot upload started for bug ID: $id");
            // Check if request has a file
            if (!$request->hasFile('screenshot')) {
                \Log::warning("No screenshot file found in request for bug ID: $id");
                return $this->errorResponse('No screenshot file was uploaded', 422);
            }
            // Validate the request
            $validated = $request->validate([
                'screenshot' => 'required|image|mimes:jpeg,png,jpg|max:10240', // Max 10MB
                'step_number' => 'required|integer|min:1',
                'step_description' => 'required|string|max:255',
                'notes' => 'nullable|string',
                'context' => 'nullable|string|in:given,when,then' // Add context validation
            ]);
            // Get the bug report to verify it exists
            $bugReport = $this->bugReportService->getBugReportById($id);
            // Use the TestEvidenceService directly
            $testEvidenceService = app(TestEvidenceService::class);
            $evidence = $testEvidenceService->uploadEvidenceForBug(
                $id,
                $request->input('step_number'),
                $request->input('step_description'),
                $request->file('screenshot'),
                $request->input('notes'),
                $request->input('context', 'then') // Default to 'then' context
            );
            return $this->successResponse(
                new TestEvidenceResource($evidence),
                'Screenshot uploaded successfully',
                201
            );
        } catch (\Exception $e) {
            \Log::error("Error uploading screenshot: " . $e->getMessage(), [
                'bug_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse(
                'Error uploading screenshot: ' . $e->getMessage(),
                500
            );
        }
    }

    public function createRevision(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'bug_description' => 'required|string',
            'steps_to_reproduce' => 'required|string',
            'severity' => 'required|string|in:Low,Medium,High,Critical',
            'screenshot_url' => 'nullable|string'
        ]);

        $bugReport = $this->bugReportService->createBugReportRevision($id, $validated);

        return $this->successResponse(
            new BugReportResource($bugReport),
            'Bug report revision created successfully',
            201
        );
    }

    public function getBugHistory($id)
    {
        $history = $this->bugReportService->getBugReportHistory($id);

        // Eager load evidence for original and revisions
        $history['original']->load(['evidence', 'uatTask.application', 'crowdworker', 'validation']);

        if ($history['revisions']) {
            $history['revisions']->load(['evidence', 'uatTask.application', 'crowdworker', 'validation']);
        }

        return $this->successResponse(
            [
                'original' => new BugReportResource($history['original']),
                'revisions' => BugReportResource::collection($history['revisions']),
                'validation' => $history['validation'] ? new BugValidationResource($history['validation']) : null
            ],
            'Bug report history retrieved successfully'
        );
    }

    public function getByTask($taskId)
    {
        $bugReports = $this->bugReportService->getBugReportsByTask($taskId);
        $bugReports->load(['evidence', 'uatTask.application', 'crowdworker', 'validation']);

        \Log::info("Bug reports for task $taskId:", [
            'count' => $bugReports->count(),
            'first_bug' => $bugReports->first() ? [
                'bug_id' => $bugReports->first()->bug_id,
                'has_evidence' => $bugReports->first()->relationLoaded('evidence'),
                'evidence_count' => $bugReports->first()->evidence->count(),
            ] : 'No bugs found'
        ]);

        return $this->successResponse(
            BugReportResource::collection($bugReports),
            'Bug reports retrieved successfully'
        );
    }

}