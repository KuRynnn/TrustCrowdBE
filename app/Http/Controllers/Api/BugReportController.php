<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BugReport\BugReportService;
use App\Http\Resources\Api\BugReportResource;
use App\Http\Requests\Api\BugReport\CreateBugReportRequest;
use App\Http\Requests\Api\BugReport\UpdateBugReportRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Bug Reports",
 *     description="API Endpoints for Bug Report management"
 * )
 */
class BugReportController extends Controller
{
    use ApiResponse;

    protected $bugReportService;

    public function __construct(BugReportService $bugReportService)
    {
        $this->bugReportService = $bugReportService;
    }

    /**
     * @OA\Get(
     *     path="/bug-reports",
     *     tags={"Bug Reports"},
     *     summary="Get list of bug reports",
     *     description="Returns a list of all bug reports",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug reports retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="bug_id", type="string", format="uuid"),
     *                     @OA\Property(property="task_id", type="string", format="uuid"),
     *                     @OA\Property(property="worker_id", type="string", format="uuid"),
     *                     @OA\Property(property="bug_description", type="string", example="Button not working"),
     *                     @OA\Property(property="steps_to_reproduce", type="string", example="1. Click login\n2. Enter credentials\n3. Click submit"),
     *                     @OA\Property(
     *                         property="severity",
     *                         type="string",
     *                         enum={"Low", "Medium", "High", "Critical"},
     *                         example="High"
     *                     ),
     *                     @OA\Property(property="screenshot_url", type="string", nullable=true, example="https://example.com/screenshot.jpg"),
     *                     @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                     @OA\Property(property="validation_status", type="string", example="Pending"),
     *                     @OA\Property(
     *                         property="uat_task",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="task_id", type="string", format="uuid"),
     *                         @OA\Property(property="title", type="string", example="Test Login Feature"),
     *                         @OA\Property(property="description", type="string", example="Test the login functionality")
     *                     ),
     *                     @OA\Property(
     *                         property="crowdworker",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="worker_id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $bugReports = $this->bugReportService->getAllBugReports();
        return $this->successResponse(
            BugReportResource::collection($bugReports),
            'Bug reports retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/bug-reports",
     *     tags={"Bug Reports"},
     *     summary="Create new bug report",
     *     description="Creates a new bug report",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"task_id", "worker_id", "bug_description", "steps_to_reproduce", "severity"},
     *             @OA\Property(property="task_id", type="string", format="uuid"),
     *             @OA\Property(property="worker_id", type="string", format="uuid"),
     *             @OA\Property(property="bug_description", type="string", example="Login button not working"),
     *             @OA\Property(property="steps_to_reproduce", type="string", example="1. Go to login page\n2. Click login"),
     *             @OA\Property(
     *                 property="severity",
     *                 type="string",
     *                 enum={"Low", "Medium", "High", "Critical"},
     *                 example="High"
     *             ),
     *             @OA\Property(property="screenshot_url", type="string", nullable=true, example="https://example.com/screenshot.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bug report created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug report created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="bug_id", type="string", format="uuid"),
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="worker_id", type="string", format="uuid"),
     *                 @OA\Property(property="bug_description", type="string", example="Button not working"),
     *                 @OA\Property(property="steps_to_reproduce", type="string", example="1. Click login\n2. Enter credentials\n3. Click submit"),
     *                 @OA\Property(
     *                     property="severity",
     *                     type="string",
     *                     enum={"Low", "Medium", "High", "Critical"},
     *                     example="High"
     *                 ),
     *                 @OA\Property(property="screenshot_url", type="string", nullable=true, example="https://example.com/screenshot.jpg"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="validation_status", type="string", example="Pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="task_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The task id field is required")
     *                 ),
     *                 @OA\Property(
     *                     property="severity",
     *                     type="array",
     *                     @OA\Items(type="string", example="The severity field must be one of: Low, Medium, High, Critical")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function store(CreateBugReportRequest $request)
    {
        $bugReport = $this->bugReportService->createBugReport($request->validated());
        return $this->successResponse(
            new BugReportResource($bugReport),
            'Bug report created successfully',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/bug-reports/{id}",
     *     tags={"Bug Reports"},
     *     summary="Get specific bug report",
     *     description="Returns details of a specific bug report",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bug report ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug report retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="bug_id", type="string", format="uuid"),
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="worker_id", type="string", format="uuid"),
     *                 @OA\Property(property="bug_description", type="string", example="Button not working"),
     *                 @OA\Property(property="steps_to_reproduce", type="string", example="1. Click login\n2. Enter credentials\n3. Click submit"),
     *                 @OA\Property(
     *                     property="severity",
     *                     type="string",
     *                     enum={"Low", "Medium", "High", "Critical"},
     *                     example="High"
     *                 ),
     *                 @OA\Property(property="screenshot_url", type="string", nullable=true, example="https://example.com/screenshot.jpg"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="validation_status", type="string", example="Pending"),
     *                 @OA\Property(
     *                     property="uat_task",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="task_id", type="string", format="uuid"),
     *                     @OA\Property(property="title", type="string", example="Test Login Feature"),
     *                     @OA\Property(property="description", type="string", example="Test the login functionality")
     *                 ),
     *                 @OA\Property(
     *                     property="crowdworker",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="worker_id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com")
     *                 ),
     *                 @OA\Property(
     *                     property="validation",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="validation_id", type="string", format="uuid"),
     *                     @OA\Property(property="validation_status", type="string", example="Pending"),
     *                     @OA\Property(property="validator_notes", type="string", example="Under review")
     *                 ),
     *                 @OA\Property(
     *                     property="application",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="app_id", type="string", format="uuid"),
     *                     @OA\Property(property="app_name", type="string", example="Test Application"),
     *                     @OA\Property(property="platform", type="string", example="web")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bug report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bug report not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $bugReport = $this->bugReportService->getBugReportById($id);
        return $this->successResponse(
            new BugReportResource($bugReport),
            'Bug report retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/bug-reports/{id}",
     *     tags={"Bug Reports"},
     *     summary="Update bug report",
     *     description="Updates an existing bug report",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bug report ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="task_id", type="string", format="uuid"),
     *             @OA\Property(property="worker_id", type="string", format="uuid"),
     *             @OA\Property(property="bug_description", type="string", example="Updated: Button not working after recent changes"),
     *             @OA\Property(property="steps_to_reproduce", type="string", example="1. Clear cache\n2. Click login\n3. Enter credentials"),
     *             @OA\Property(
     *                 property="severity",
     *                 type="string",
     *                 enum={"Low", "Medium", "High", "Critical"},
     *                 example="Critical"
     *             ),
     *             @OA\Property(property="screenshot_url", type="string", nullable=true, example="https://example.com/updated-screenshot.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bug report updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug report updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="bug_id", type="string", format="uuid"),
     *                 @OA\Property(property="task_id", type="string", format="uuid"),
     *                 @OA\Property(property="worker_id", type="string", format="uuid"),
     *                 @OA\Property(property="bug_description", type="string", example="Updated: Button not working"),
     *                 @OA\Property(property="steps_to_reproduce", type="string", example="1. Clear cache\n2. Click login"),
     *                 @OA\Property(
     *                     property="severity",
     *                     type="string",
     *                     enum={"Low", "Medium", "High", "Critical"},
     *                     example="Critical"
     *                 ),
     *                 @OA\Property(property="screenshot_url", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="validation_status", type="string", example="Pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bug report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bug report not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="severity",
     *                     type="array",
     *                     @OA\Items(type="string", example="The severity field must be one of: Low, Medium, High, Critical")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateBugReportRequest $request, $id)
    {
        $bugReport = $this->bugReportService->updateBugReportById($id, $request->validated());
        return $this->successResponse(
            new BugReportResource($bugReport),
            'Bug report updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/bug-reports/{id}",
     *     tags={"Bug Reports"},
     *     summary="Delete bug report",
     *     description="Deletes a specific bug report",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bug report ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bug report deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug report deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bug report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bug report not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->bugReportService->deleteBugReportById($id);
        return $this->successResponse(
            null,
            'Bug report deleted successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/bug-reports/by-severity/{severity}",
     *     tags={"Bug Reports"},
     *     summary="Get bug reports by severity",
     *     description="Returns a list of bug reports filtered by severity level",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="severity",
     *         in="path",
     *         required=true,
     *         description="Severity level to filter by",
     *         @OA\Schema(
     *             type="string",
     *             enum={"Low", "Medium", "High", "Critical"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug reports retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="bug_id", type="string", format="uuid"),
     *                     @OA\Property(property="task_id", type="string", format="uuid"),
     *                     @OA\Property(property="worker_id", type="string", format="uuid"),
     *                     @OA\Property(property="bug_description", type="string", example="Button not working"),
     *                     @OA\Property(property="steps_to_reproduce", type="string", example="1. Click login\n2. Enter credentials"),
     *                     @OA\Property(
     *                         property="severity",
     *                         type="string",
     *                         enum={"Low", "Medium", "High", "Critical"},
     *                         example="High"
     *                     ),
     *                     @OA\Property(property="screenshot_url", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                     @OA\Property(property="validation_status", type="string", example="Pending")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid severity level",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid severity level"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function getBySeverity($severity)
    {
        $bugReports = $this->bugReportService->getBugReportsBySeverity($severity);
        return $this->successResponse(
            BugReportResource::collection($bugReports),
            'Bug reports retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/bug-reports/{id}/screenshot",
     *     tags={"Bug Reports"},
     *     summary="Upload a screenshot for a bug report",
     *     description="Uploads a screenshot for a specific bug report",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bug report ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="screenshot",
     *                     type="string",
     *                     format="binary",
     *                     description="The screenshot file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Screenshot uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Screenshot uploaded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/BugReportResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bug report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bug report not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="screenshot",
     *                     type="array",
     *                     @OA\Items(type="string", example="The screenshot must be an image")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function uploadScreenshot(Request $request, $id)
    {
        try {
            \Log::info("Screenshot upload started for bug ID: $id");

            // Check if request has a file
            if (!$request->hasFile('screenshot')) {
                \Log::warning("No screenshot file found in request for bug ID: $id");
                return $this->errorResponse('No screenshot file was uploaded', 422);
            }

            \Log::info("Request contains file for bug ID: $id");

            // Validate the request
            try {
                $validated = $request->validate([
                    'screenshot' => 'required|image|mimes:jpeg,png,jpg|max:10240', // Max 10MB
                ]);
                \Log::info("Validation passed");
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error("Validation failed", [
                    'errors' => $e->errors()
                ]);
                throw $e;
            }

            // Get the bug report
            try {
                $bugReport = $this->bugReportService->getBugReportById($id);
                \Log::info("Found bug report", [
                    'bug_id' => $bugReport->bug_id
                ]);
            } catch (\Exception $e) {
                \Log::error("Failed to find bug report", [
                    'bug_id' => $id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

            // Handle file upload
            $file = $request->file('screenshot');
            \Log::info("File details", [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'path' => $file->getRealPath()
            ]);

            // Check if the file is valid
            if (!$file->isValid()) {
                \Log::error("File upload failed", [
                    'error' => $file->getError(),
                    'error_message' => $file->getErrorMessage()
                ]);
                return $this->errorResponse('File upload failed: ' . $file->getErrorMessage(), 422);
            }

            // Check storage directory
            $storageDir = storage_path('app/public/bug-screenshots');
            \Log::info("Storage directory: $storageDir");

            if (!file_exists($storageDir)) {
                \Log::info("Creating directory: $storageDir");
                mkdir($storageDir, 0775, true);
            }

            if (!is_writable($storageDir)) {
                \Log::error("Directory not writable: $storageDir");
                return $this->errorResponse('Server storage error: Directory not writable', 500);
            }

            // Generate a unique filename
            $filename = 'bug_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            \Log::info("Generated filename: $filename");

            // First try a direct move for debugging
            try {
                $manualPath = $storageDir . '/' . $filename;
                \Log::info("Trying direct file move to: $manualPath");
                if (copy($file->getRealPath(), $manualPath)) {
                    \Log::info("Direct file copy succeeded");
                    $path = 'bug-screenshots/' . $filename;
                } else {
                    \Log::warning("Direct file copy failed, trying Laravel's storage");

                    // Try Laravel's storage
                    $path = $file->storeAs('bug-screenshots', $filename, 'public');
                    \Log::info("Laravel store result path: $path");

                    if (!$path) {
                        \Log::error("Laravel file storage failed");
                        return $this->errorResponse('Failed to store screenshot', 500);
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Exception during file storage: " . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                return $this->errorResponse('Failed to store screenshot: ' . $e->getMessage(), 500);
            }

            \Log::info("File stored at path: $path");

            // Generate the public URL for the file
            $url = asset('storage/' . $path);
            \Log::info("Generated URL: $url");

            // Update the bug report with the screenshot URL
            try {
                $updatedBugReport = $this->bugReportService->updateBugReportById($id, [
                    'screenshot_url' => $url
                ]);
                \Log::info("Bug report updated with screenshot URL");
            } catch (\Exception $e) {
                \Log::error("Failed to update bug report with screenshot URL", [
                    'bug_id' => $id,
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

            \Log::info("Screenshot upload completed successfully");
            return $this->successResponse(
                new BugReportResource($updatedBugReport),
                'Screenshot uploaded successfully'
            );
        } catch (\Exception $e) {
            \Log::error("Unhandled exception in uploadScreenshot: " . $e->getMessage(), [
                'bug_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error uploading screenshot: ' . $e->getMessage(),
                500
            );
        }
    }
}