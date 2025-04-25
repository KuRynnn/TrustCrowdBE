<?php

namespace App\Http\Controllers\Api;

use App\Models\UATTask;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Application\ApplicationService;
use App\Services\UATTask\UATTaskService;
use App\Services\TestCase\TestCaseService;
use App\Http\Resources\Api\ApplicationResource;
use App\Http\Resources\Api\UATTaskResource;
use App\Http\Requests\Api\Application\CreateApplicationRequest;
use App\Http\Requests\Api\Application\UpdateApplicationRequest;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="Applications",
 *     description="API Endpoints for Application management"
 * )
 */
class ApplicationController extends Controller
{
    use ApiResponse;

    protected $applicationService;
    protected $uatTaskService;
    protected $testCaseService;

    public function __construct(
        ApplicationService $applicationService,
        UATTaskService $uatTaskService,
        TestCaseService $testCaseService
    ) {
        $this->applicationService = $applicationService;
        $this->uatTaskService = $uatTaskService;
        $this->testCaseService = $testCaseService;
    }

    /**
     * @OA\Get(
     *     path="/applications",
     *     tags={"Applications"},
     *     summary="Get list of applications",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Applications retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="app_id", type="string", format="uuid"),
     *                     @OA\Property(property="app_name", type="string", example="Test App"),
     *                     @OA\Property(property="app_url", type="string", example="https://test.com"),
     *                     @OA\Property(property="platform", type="string", example="web"),
     *                     @OA\Property(property="description", type="string", example="Description"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="datetime")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $applications = $this->applicationService->getAllApplications();
        return $this->successResponse(
            ApplicationResource::collection($applications),
            'Applications retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/applications",
     *     tags={"Applications"},
     *     summary="Create new application",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id", "app_name", "app_url", "platform", "description", "status"},
     *             @OA\Property(property="client_id", type="string", format="uuid"),
     *             @OA\Property(property="app_name", type="string", example="Test App"),
     *             @OA\Property(property="app_url", type="string", example="https://test.com"),
     *             @OA\Property(property="platform", type="string", example="web"),
     *             @OA\Property(property="description", type="string", example="Description"),
     *             @OA\Property(property="status", type="string", enum={"pending", "active", "completed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Application created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="app_id", type="string", format="uuid"),
     *                 @OA\Property(property="app_name", type="string", example="Test App"),
     *                 @OA\Property(property="app_url", type="string", example="https://test.com"),
     *                 @OA\Property(property="platform", type="string", example="web"),
     *                 @OA\Property(property="description", type="string", example="Description"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="created_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(CreateApplicationRequest $request)
    {
        try {
            $data = $request->validated();

            // Use client_id from request directly
            if (!isset($data['client_id'])) {
                \Illuminate\Support\Facades\Log::error('No client_id provided in request');
                return $this->errorResponse('Client ID is required', 422);
            }

            // Default status ke 'pending' jika belum dikirim
            $data['status'] = $data['status'] ?? 'pending';

            $application = $this->applicationService->createApplication($data);

            return $this->successResponse(
                new ApplicationResource($application),
                'Application created successfully',
                201
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating application: ' . $e->getMessage());
            return $this->errorResponse('Failed to create application: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/applications/{id}",
     *     tags={"Applications"},
     *     summary="Get specific application",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="app_id", type="string", format="uuid"),
     *                 @OA\Property(property="app_name", type="string", example="Test App"),
     *                 @OA\Property(property="app_url", type="string", example="https://test.com"),
     *                 @OA\Property(property="platform", type="string", example="web"),
     *                 @OA\Property(property="description", type="string", example="Description"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="created_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show($id)
    {
        $application = $this->applicationService->getApplicationById($id);
        return $this->successResponse(
            new ApplicationResource($application),
            'Application retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/applications/{id}",
     *     tags={"Applications"},
     *     summary="Update application",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="client_id", type="string", format="uuid"),
     *             @OA\Property(property="app_name", type="string", example="Updated App"),
     *             @OA\Property(property="app_url", type="string", example="https://updated.com"),
     *             @OA\Property(property="platform", type="string", example="android"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="status", type="string", enum={"pending", "active", "completed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="app_id", type="string", format="uuid"),
     *                 @OA\Property(property="app_name", type="string", example="Updated App"),
     *                 @OA\Property(property="app_url", type="string", example="https://updated.com"),
     *                 @OA\Property(property="platform", type="string", example="android"),
     *                 @OA\Property(property="description", type="string", example="Updated description"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="created_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(UpdateApplicationRequest $request, $id)
    {
        $application = $this->applicationService->updateApplicationById($id, $request->validated());
        return $this->successResponse(
            new ApplicationResource($application),
            'Application updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/applications/{id}",
     *     tags={"Applications"},
     *     summary="Delete application",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->applicationService->deleteApplicationById($id);
        return $this->successResponse(
            null,
            'Application deleted successfully'
        );
    }

    public function getAvailableForCrowdworker(Request $request)
    {
        // Get crowdworker ID from request
        $crowdworkerId = $request->input('worker_id');
        if (!$crowdworkerId) {
            return $this->errorResponse('Worker ID is required', 422);
        }

        // Limit aktif task (e.g. 2)
        $activeTasks = UATTask::where('worker_id', $crowdworkerId)
            ->whereIn('status', ['Assigned', 'In Progress'])
            ->count();

        if ($activeTasks >= 2) {
            return $this->successResponse([], 'You have reached the maximum active UAT tasks (2)');
        }

        $applications = $this->applicationService->getAvailableApplicationsForCrowdworker($crowdworkerId);

        return $this->successResponse(
            ApplicationResource::collection($applications),
            'Applications available for testing'
        );
    }

    /**
     * @OA\Post(
     *     path="/applications/{id}/pick",
     *     tags={"Applications"},
     *     summary="Crowdworker picks an application for testing",
     *     description="Assigns all test cases of the application to the crowdworker as UAT Tasks",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Application ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application picked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application picked successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="application", ref="#/components/schemas/Application"),
     *                 @OA\Property(
     *                     property="uat_tasks",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/UATTask")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User is not a crowdworker or has reached maximum active tasks",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Application is not ready for testing or has no test cases",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function pickApplication($id, Request $request)
    {
        // Get crowdworker ID from request
        $crowdworkerId = $request->input('worker_id');
        if (!$crowdworkerId) {
            return $this->errorResponse('Worker ID is required', 422);
        }

        // Check if crowdworker has reached maximum active tasks
        $activeTasks = UATTask::where('worker_id', $crowdworkerId)
            ->whereIn('status', ['Assigned', 'In Progress'])
            ->count();

        if ($activeTasks >= 2) {
            return $this->errorResponse('You have reached the maximum active UAT tasks (2)', 422);
        }

        // Get the application
        try {
            $application = $this->applicationService->getApplicationById($id);
        } catch (\Exception $e) {
            return $this->errorResponse('Application not found', 404);
        }

        // Check if application is ready for testing
        if ($application->status !== 'Ready for Testing') {
            return $this->errorResponse('Application is not ready for testing', 422);
        }

        // Get all test cases for the application
        $testCases = $application->testCases;

        if ($testCases->isEmpty()) {
            return $this->errorResponse('Application has no test cases', 422);
        }

        // Create UAT Tasks for each test case
        $uatTasks = [];
        foreach ($testCases as $testCase) {
            $taskData = [
                'app_id' => $application->app_id,
                'test_id' => $testCase->test_id,
                'worker_id' => $crowdworkerId,
                'status' => 'Assigned'
            ];

            $uatTasks[] = $this->uatTaskService->createTask($taskData);
        }

        return $this->successResponse([
            'application' => new ApplicationResource($application),
            'uat_tasks' => UATTaskResource::collection($uatTasks)
        ], 'Application picked successfully. ' . count($uatTasks) . ' UAT tasks created.');
    }

    /**
     * @OA\Get(
     *     path="/applications/{id}/final-report",
     *     tags={"Applications"},
     *     summary="Get final report for client",
     *     description="Generates a comprehensive final report for the client with validated results",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Application ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Final report generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Final report generated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="application", ref="#/components/schemas/Application"),
     *                 @OA\Property(property="total_tasks", type="integer", example=10),
     *                 @OA\Property(property="completed_tasks", type="integer", example=8),
     *                 @OA\Property(property="total_bugs", type="integer", example=5),
     *                 @OA\Property(property="valid_bugs", type="integer", example=3),
     *                 @OA\Property(
     *                     property="bugs_by_severity",
     *                     type="object",
     *                     @OA\Property(property="Critical", type="integer", example=1),
     *                     @OA\Property(property="High", type="integer", example=1),
     *                     @OA\Property(property="Medium", type="integer", example=1),
     *                     @OA\Property(property="Low", type="integer", example=0)
     *                 ),
     *                 @OA\Property(
     *                     property="validated_bugs",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="bug_id", type="string", format="uuid"),
     *                         @OA\Property(property="bug_description", type="string", example="Login button not working"),
     *                         @OA\Property(property="severity", type="string", example="High"),
     *                         @OA\Property(property="validation_status", type="string", example="Valid"),
     *                         @OA\Property(property="validator_notes", type="string", example="Confirmed issue, needs immediate fix")
     *                     )
     *                 ),
     *                 @OA\Property(property="qa_notes", type="string", example="Overall good quality, but some critical issues need to be addressed"),
     *                 @OA\Property(property="test_completion_percentage", type="number", format="float", example=80),
     *                 @OA\Property(property="generated_at", type="string", format="datetime", example="2025-04-10 20:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User is not the client who owns this application",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function getFinalReport($id, Request $request)
    {
        // Get client ID from request
        $clientId = $request->input('client_id');

        // Get the application
        try {
            $application = $this->applicationService->getApplicationById($id);

            // Check if user is the client who owns this application
            if ($clientId && $application->client_id !== $clientId) {
                return $this->errorResponse('Forbidden - You do not own this application', 403);
            }

            // Get application progress data
            $progress = $this->applicationService->getApplicationProgress($id);

            // Get all validated bug reports
            $validatedBugs = [];
            $bugsBySeverity = [
                'Critical' => 0,
                'High' => 0,
                'Medium' => 0,
                'Low' => 0
            ];

            // Get all UAT tasks for this application
            $uatTasks = $application->uatTasks;

            // Collect all bug reports from these tasks
            foreach ($uatTasks as $task) {
                foreach ($task->bugReports as $bugReport) {
                    // Only include validated bugs
                    if ($bugReport->validation && $bugReport->validation->validation_status === 'Valid') {
                        $validatedBugs[] = [
                            'bug_id' => $bugReport->bug_id,
                            'bug_description' => $bugReport->bug_description,
                            'severity' => $bugReport->severity,
                            'steps_to_reproduce' => $bugReport->steps_to_reproduce,
                            'validation_status' => $bugReport->validation->validation_status,
                            'validator_notes' => $bugReport->validation->comments
                        ];

                        // Count bugs by severity
                        if (isset($bugsBySeverity[$bugReport->severity])) {
                            $bugsBySeverity[$bugReport->severity]++;
                        }
                    }
                }
            }

            // Calculate test completion percentage
            $testCompletionPercentage = 0;
            if ($progress['total_test_cases'] > 0) {
                $testCompletionPercentage = ($progress['completed_tasks'] / $progress['total_test_cases']) * 100;
            }

            // Compile QA notes from task validations
            $qaNotesArray = [];
            foreach ($uatTasks as $task) {
                if ($task->taskValidation && !empty($task->taskValidation->comments)) {
                    $qaNotesArray[] = $task->taskValidation->comments;
                }
            }
            $qaNotes = !empty($qaNotesArray) ? implode("\n", $qaNotesArray) : "No specific notes from QA.";

            // Prepare the final report
            $finalReport = [
                'application' => new ApplicationResource($application),
                'total_tasks' => $progress['total_test_cases'],
                'completed_tasks' => $progress['completed_tasks'],
                'total_bugs' => $progress['total_bugs'],
                'valid_bugs' => $progress['valid_bugs'],
                'bugs_by_severity' => $bugsBySeverity,
                'validated_bugs' => $validatedBugs,
                'qa_notes' => $qaNotes,
                'test_completion_percentage' => round($testCompletionPercentage, 2),
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];

            return $this->successResponse(
                $finalReport,
                'Final report generated successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Application not found or error generating report: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Get applications by client ID
     * 
     * @param string $clientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByClient($clientId)
    {
        try {
            $applications = $this->applicationService->getApplicationsByClient($clientId);
            return $this->successResponse(
                ApplicationResource::collection($applications),
                'Applications retrieved successfully'
            );
        } catch (\Exception $e) {
            // Log the specific error
            \Illuminate\Support\Facades\Log::error('Error in getByClient: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve applications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get application statistics
     * 
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics($id)
    {
        try {
            $application = $this->applicationService->getApplicationById($id);

            // Get all UAT tasks for this application
            $uatTasks = $application->uatTasks;

            // Initialize statistics
            $statistics = [
                'total_tasks' => $uatTasks->count(),
                'completed_tasks' => $uatTasks->where('status', 'Completed')->count(),
                'in_progress_tasks' => $uatTasks->whereIn('status', ['Assigned', 'In Progress'])->count(),
                'pending_validation' => $uatTasks->where('status', 'Pending Validation')->count(),
                'total_bugs' => 0,
                'bugs_by_severity' => [
                    'Critical' => 0,
                    'High' => 0,
                    'Medium' => 0,
                    'Low' => 0
                ],
                'bugs_by_status' => [
                    'Valid' => 0,
                    'Invalid' => 0,
                    'Pending' => 0
                ]
            ];

            // Count bugs and categorize them
            foreach ($uatTasks as $task) {
                $bugReports = $task->bugReports;
                $statistics['total_bugs'] += $bugReports->count();

                foreach ($bugReports as $bug) {
                    // Count by severity
                    if (isset($statistics['bugs_by_severity'][$bug->severity])) {
                        $statistics['bugs_by_severity'][$bug->severity]++;
                    }

                    // Count by validation status
                    if ($bug->validation) {
                        $validationStatus = $bug->validation->validation_status;
                        if ($validationStatus === 'Valid') {
                            $statistics['bugs_by_status']['Valid']++;
                        } elseif ($validationStatus === 'Invalid') {
                            $statistics['bugs_by_status']['Invalid']++;
                        } else {
                            $statistics['bugs_by_status']['Pending']++;
                        }
                    } else {
                        $statistics['bugs_by_status']['Pending']++;
                    }
                }
            }

            return $this->successResponse(
                $statistics,
                'Application statistics retrieved successfully'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error retrieving application statistics: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve application statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get application progress
     * 
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgress($id)
    {
        try {
            // Use the existing getApplicationProgress method from your service
            $applicationProgress = $this->applicationService->getApplicationProgress($id);
            $application = $this->applicationService->getApplicationById($id);

            // Calculate progress percentage
            $progressPercentage = 0;
            if ($applicationProgress['total_test_cases'] > 0) {
                $progressPercentage = ($applicationProgress['completed_tasks'] / $applicationProgress['total_test_cases']) * 100;
            }

            // Prepare timeline data
            $timeline = [
                'created_at' => $application->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $application->updated_at->format('Y-m-d H:i:s')
            ];

            // Add estimated completion if available
            // This is a placeholder - you may want to implement a more sophisticated calculation
            if ($progressPercentage > 0 && $progressPercentage < 100) {
                $elapsedDays = $application->created_at->diffInDays(now());
                if ($elapsedDays > 0 && $progressPercentage > 0) {
                    $totalEstimatedDays = ($elapsedDays / $progressPercentage) * 100;
                    $remainingDays = $totalEstimatedDays - $elapsedDays;
                    $estimatedCompletion = now()->addDays(ceil($remainingDays))->format('Y-m-d');
                    $timeline['estimated_completion'] = $estimatedCompletion;
                }
            } elseif ($progressPercentage >= 100) {
                $timeline['estimated_completion'] = 'Completed';
            } else {
                $timeline['estimated_completion'] = 'Not started';
            }

            // Build complete progress data
            $progress = array_merge($applicationProgress, [
                'progress_percentage' => round($progressPercentage, 2),
                'timeline' => $timeline
            ]);

            return $this->successResponse(
                $progress,
                'Application progress retrieved successfully'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error retrieving application progress: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve application progress: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update application status
     * 
     * @param string $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus($id, Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:pending,active,Ready for Testing,completed,on-hold'
            ]);

            $application = $this->applicationService->getApplicationById($id);
            $this->applicationService->updateApplicationById($id, ['status' => $request->status]);

            return $this->successResponse(
                ['status' => $request->status],
                'Application status updated successfully'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating application status: ' . $e->getMessage());
            return $this->errorResponse('Failed to update application status: ' . $e->getMessage(), 500);
        }
    }
}
