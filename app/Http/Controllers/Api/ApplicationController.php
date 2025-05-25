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

    public function index()
    {
        $applications = $this->applicationService->getAllApplications();

        // Add current workers count to each application
        $applications->each(function ($application) {
            $currentWorkers = $application->uatTasks()
                ->distinct('worker_id')
                ->count('worker_id');

            $application->current_workers = $currentWorkers;
        });

        return $this->successResponse(
            ApplicationResource::collection($applications),
            'Applications retrieved successfully'
        );
    }

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

    public function show($id)
    {
        $application = $this->applicationService->getApplicationById($id);

        // Add current workers count
        $currentWorkers = $application->uatTasks()
            ->distinct('worker_id')
            ->count('worker_id');

        $application->current_workers = $currentWorkers;

        return $this->successResponse(
            new ApplicationResource($application),
            'Application retrieved successfully'
        );
    }

    public function update(UpdateApplicationRequest $request, $id)
    {
        $application = $this->applicationService->updateApplicationById($id, $request->validated());
        return $this->successResponse(
            new ApplicationResource($application),
            'Application updated successfully'
        );
    }

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

        // Count active APPLICATIONS (not tasks) - using distinct app_id
        $activeApplications = UATTask::where('worker_id', $crowdworkerId)
            ->whereIn('status', ['Assigned', 'In Progress'])
            ->distinct('app_id')
            ->count('app_id');

        // Check if worker has reached the maximum active applications (2)
        if ($activeApplications >= 2) {
            return $this->successResponse([], 'You have reached the maximum active applications (2). Please complete your current applications before picking new ones.');
        }

        $applications = $this->applicationService->getAvailableApplicationsForCrowdworker($crowdworkerId);

        return $this->successResponse(
            ApplicationResource::collection($applications),
            'Applications available for testing'
        );
    }

    public function pickApplication($id, Request $request)
    {
        // Get crowdworker ID from request
        $crowdworkerId = $request->input('worker_id');

        if (!$crowdworkerId) {
            return $this->errorResponse('Worker ID is required', 422);
        }

        // Count active APPLICATIONS (not tasks) - using distinct app_id
        $activeApplications = UATTask::where('worker_id', $crowdworkerId)
            ->whereIn('status', ['Assigned', 'In Progress'])
            ->distinct('app_id')
            ->count('app_id');

        // Check if worker has reached maximum active applications
        if ($activeApplications >= 2) {
            return $this->errorResponse('You have reached the maximum active applications (2). Please complete your current applications before picking new ones.', 422);
        }

        // Check if worker already has tasks for this specific application
        $existingTasksForApp = UATTask::where('worker_id', $crowdworkerId)
            ->where('app_id', $id)
            ->exists();

        if ($existingTasksForApp) {
            return $this->errorResponse('You already have tasks for this application.', 422);
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

    public function getFinalReport($id, Request $request)
    {
        try {
            $application = $this->applicationService->getApplicationById($id);

            // Get all validated bugs
            $validatedBugs = [];
            $bugsBySeverity = [
                'Critical' => 0,
                'High' => 0,
                'Medium' => 0,
                'Low' => 0
            ];

            // Get all UAT tasks for this application
            $uatTasks = $application->uatTasks;

            // Collect all bug reports
            foreach ($uatTasks as $task) {
                foreach ($task->bugReports as $bugReport) {
                    if ($bugReport->validation && $bugReport->validation->validation_status === 'Valid') {
                        $validatedBugs[] = [
                            'bug_id' => $bugReport->bug_id,
                            'bug_description' => $bugReport->bug_description,
                            'severity' => $bugReport->severity,
                            'steps_to_reproduce' => $bugReport->steps_to_reproduce,
                        ];

                        // Count bugs by severity
                        if (isset($bugsBySeverity[$bugReport->severity])) {
                            $bugsBySeverity[$bugReport->severity]++;
                        }
                    }
                }
            }

            // Determine acceptance status based on the paper's 5 categories
            $acceptanceStatus = $this->determineAcceptanceStatus($bugsBySeverity);

            // Get progress data
            $progress = $this->applicationService->getApplicationProgress($id);

            // Prepare the final report
            $finalReport = [
                'application' => new ApplicationResource($application),
                'test_coverage' => [
                    'total_test_cases' => $progress['total_test_cases'],
                    'completed_tasks' => $progress['completed_tasks'],
                    'completion_percentage' => $progress['percentage']
                ],
                'bug_summary' => [
                    'total_bugs' => count($validatedBugs),
                    'by_severity' => $bugsBySeverity,
                    'detailed_bugs' => $validatedBugs
                ],
                'acceptance_status' => $acceptanceStatus,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];

            return $this->successResponse(
                $finalReport,
                'Final report generated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error generating report: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper method to determine acceptance status based on the paper's criteria
     */
    private function determineAcceptanceStatus($bugsBySeverity)
    {
        if ($bugsBySeverity['Critical'] > 0) {
            return [
                'status' => 'Rejected',
                'description' => 'System is rejected due to critical issues that make it unusable.',
                'recommendation' => 'Major rework is required before retesting.'
            ];
        }

        if ($bugsBySeverity['High'] > 0) {
            return [
                'status' => 'Rework',
                'description' => 'System needs to be fixed and retested before acceptance.',
                'recommendation' => 'Fix all high-severity issues and submit for retesting.'
            ];
        }

        if ($bugsBySeverity['Medium'] > 0) {
            return [
                'status' => 'Conditional Acceptance',
                'description' => 'System is accepted conditionally, pending fixes to medium-severity issues.',
                'recommendation' => 'Deploy with commitment to fix medium-severity issues within agreed timeframe.'
            ];
        }

        if ($bugsBySeverity['Low'] > 0) {
            return [
                'status' => 'Provisional Acceptance',
                'description' => 'System is accepted with minor cosmetic issues that don\'t affect functionality.',
                'recommendation' => 'Address low-severity issues in future updates.'
            ];
        }

        return [
            'status' => 'Accept',
            'description' => 'System is accepted without any changes required.',
            'recommendation' => 'Ready for production deployment.'
        ];
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

            // Add current workers count to each application
            $applications->each(function ($application) {
                $currentWorkers = $application->uatTasks()
                    ->distinct('worker_id')
                    ->count('worker_id');

                $application->current_workers = $currentWorkers;
            });

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

            // Get all test cases for this application
            $testCases = $application->testCases;

            $testCaseStats = [];
            $totalBugs = 0;
            $totalCriticalBugs = 0;
            $totalValidBugs = 0;
            $totalInvalidBugs = 0;
            $totalPendingValidation = 0;

            foreach ($testCases as $testCase) {
                // Get UAT tasks for this test case
                $uatTasks = $testCase->uatTasks;

                // Count tasks by status
                $tasksByStatus = [
                    'total' => $uatTasks->count(),
                    'assigned' => $uatTasks->where('status', 'Assigned')->count(),
                    'in_progress' => $uatTasks->where('status', 'In Progress')->count(),
                    'completed' => $uatTasks->where('status', 'Completed')->count(),
                    'revision_required' => $uatTasks->where('status', 'Revision Required')->count(),
                    'verified' => $uatTasks->where('status', 'Verified')->count(),
                    'rejected' => $uatTasks->where('status', 'Rejected')->count(),
                ];

                // Initialize bug counters
                $bugCount = 0;
                $criticalBugs = 0;
                $highBugs = 0;
                $mediumBugs = 0;
                $lowBugs = 0;
                $validBugs = 0;
                $invalidBugs = 0;
                $pendingValidation = 0;

                // Count bugs for this test case
                foreach ($uatTasks as $task) {
                    foreach ($task->bugReports as $bug) {
                        $bugCount++;

                        // Count by severity
                        switch (strtolower($bug->severity)) {
                            case 'critical':
                                $criticalBugs++;
                                break;
                            case 'high':
                                $highBugs++;
                                break;
                            case 'medium':
                                $mediumBugs++;
                                break;
                            case 'low':
                                $lowBugs++;
                                break;
                        }

                        // Count by validation status
                        if ($bug->validation) {
                            if (strtolower($bug->validation->validation_status) === 'valid') {
                                $validBugs++;
                            } else if (strtolower($bug->validation->validation_status) === 'invalid') {
                                $invalidBugs++;
                            } else {
                                $pendingValidation++;
                            }
                        } else {
                            $pendingValidation++;
                        }
                    }
                }

                // Add to totals
                $totalBugs += $bugCount;
                $totalCriticalBugs += $criticalBugs;
                $totalValidBugs += $validBugs;
                $totalInvalidBugs += $invalidBugs;
                $totalPendingValidation += $pendingValidation;

                // Unique crowdworkers for this test case
                $uniqueCrowdworkers = $uatTasks->pluck('worker_id')->unique()->count();

                $testCaseStats[] = [
                    'test_id' => $testCase->test_id,
                    'test_title' => $testCase->test_title,
                    'priority' => $testCase->priority,
                    'crowdworkers_count' => $uniqueCrowdworkers,
                    'tasks_by_status' => $tasksByStatus,
                    'total_bugs' => $bugCount,
                    'critical_bugs' => $criticalBugs,
                    'high_bugs' => $highBugs,
                    'medium_bugs' => $mediumBugs,
                    'low_bugs' => $lowBugs,
                    'valid_bugs' => $validBugs,
                    'invalid_bugs' => $invalidBugs,
                    'pending_validation' => $pendingValidation
                ];
            }

            return $this->successResponse([
                'test_case_statistics' => $testCaseStats,
                'summary' => [
                    'total_bugs' => $totalBugs,
                    'critical_bugs' => $totalCriticalBugs,
                    'valid_bugs' => $totalValidBugs,
                    'invalid_bugs' => $totalInvalidBugs,
                    'pending_validation' => $totalPendingValidation,
                    'total_test_cases' => count($testCases),
                ]
            ], 'Application statistics retrieved successfully');
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
            $application = $this->applicationService->getApplicationById($id);

            // Get all UAT tasks for this application
            $uatTasks = $application->uatTasks;

            // Count tasks by status
            $tasksByStatus = [
                'total' => $uatTasks->count(),
                'assigned' => $uatTasks->where('status', 'Assigned')->count(),
                'in_progress' => $uatTasks->where('status', 'In Progress')->count(),
                'completed' => $uatTasks->where('status', 'Completed')->count(),
                'revision_required' => $uatTasks->where('status', 'Revision Required')->count(),
                'verified' => $uatTasks->where('status', 'Verified')->count(),
                'rejected' => $uatTasks->where('status', 'Rejected')->count(),
            ];

            // Calculate percentage of completed tasks (verified + rejected)
            $completedTasks = $tasksByStatus['verified'] + $tasksByStatus['rejected'];
            $totalTasks = $tasksByStatus['total'];
            $percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

            // Count unique crowdworkers
            $uniqueCrowdworkers = $uatTasks->pluck('worker_id')->unique()->count();

            // Count test cases
            $testCasesCount = $application->testCases->count();

            // Calculate total possible tasks (crowdworkers × test cases)
            $totalPossibleTasks = $testCasesCount * $uniqueCrowdworkers;

            return $this->successResponse([
                'tasks_by_status' => $tasksByStatus,
                'total_crowdworkers' => $uniqueCrowdworkers,
                'total_test_cases' => $testCasesCount,
                'total_possible_tasks' => $totalPossibleTasks,
                'percentage' => $percentage,
                'completed_test_cases' => $completedTasks, // For backward compatibility
                'in_progress_test_cases' => $tasksByStatus['in_progress'], // For backward compatibility
                'not_started_test_cases' => $tasksByStatus['assigned'], // For backward compatibility
            ], 'Application progress retrieved successfully');
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

    /**
     * Get applications by platform
     * 
     * @param string $platform
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByPlatform($platform)
    {
        try {
            $applications = $this->applicationService->getApplicationsByPlatform($platform);
            return $this->successResponse(
                ApplicationResource::collection($applications),
                'Applications retrieved successfully'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getByPlatform: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve applications: ' . $e->getMessage(), 500);
        }
    }

    // In your ApplicationController.php or similar
    public function getApplicationProgress($appId, Request $request)
    {
        $workerId = $request->query('worker_id');

        // If worker_id is provided, get progress for just that worker
        if ($workerId) {
            $progress = $this->applicationService->getApplicationProgressForWorker($appId, $workerId);
        } else {
            // Otherwise get overall progress across all workers
            $progress = $this->applicationService->getApplicationProgress($appId);
        }

        return $this->successResponse(
            $progress,
            'Application progress retrieved successfully'
        );
    }


}