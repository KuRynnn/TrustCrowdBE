<?php

// app/Services/TestCase/TestCaseService.php
namespace App\Services\TestCase;

use App\Repositories\TestCase\TestCaseRepository;
use App\Services\Application\ApplicationService;
use App\Exceptions\TestCaseNotFoundException;
use App\Events\TestCaseCreated;
use App\Models\Application;

class TestCaseService
{
    protected $testCaseRepository;
    protected $applicationService;

    public function __construct(
        TestCaseRepository $testCaseRepository,
        ApplicationService $applicationService
    ) {
        $this->testCaseRepository = $testCaseRepository;
        $this->applicationService = $applicationService;
    }

    public function getAllTestCases()
    {
        return $this->testCaseRepository->all();
    }

    public function getTestCasesByApplication($appId)
    {
        return $this->testCaseRepository->findByApplication($appId);
    }

    public function getTestCasesByQASpecialist($qaId)
    {
        return $this->testCaseRepository->findByQASpecialist($qaId);
    }

    public function createTestCase(array $data)
    {
        $testCase = $this->testCaseRepository->create($data);
        event(new TestCaseCreated($testCase));

        // Check if we should update the application status to "Ready for Testing"
        $this->checkAndUpdateApplicationStatus($data['app_id']);

        return $testCase;
    }

    /**
     * Check if all required test cases are created and update application status
     * 
     * @param string $appId
     * @return void
     */
    protected function checkAndUpdateApplicationStatus($appId)
    {
        try {
            // Get the application
            $application = $this->applicationService->getApplicationById($appId);

            // If application is already in "Ready for Testing" status, no need to update
            if ($application->status === 'Ready for Testing') {
                return;
            }

            // Get test cases count for this application
            $testCasesCount = $this->testCaseRepository->findByApplication($appId)->count();

            // If there are test cases, update the application status
            if ($testCasesCount > 0) {
                $this->applicationService->updateApplicationById($appId, [
                    'status' => 'Ready for Testing'
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't throw it to prevent disrupting the main flow
            \Log::error('Failed to update application status: ' . $e->getMessage());
        }
    }

    public function getTestCaseById($id)
    {
        $testCase = $this->testCaseRepository->findById($id);

        if (!$testCase) {
            throw new TestCaseNotFoundException('Test case not found');
        }

        return $testCase;
    }

    public function updateTestCaseById($id, array $data, $qaId)
    {
        $testCase = $this->getTestCaseById($id);

        // Only allow update if the QA owns the test case
        if ($testCase->qa_id !== $qaId) {
            throw new \Exception('Unauthorized: You do not own this test case.');
        }

        return $this->testCaseRepository->updateById($id, $data);
    }

    public function deleteTestCaseById($id, $qaId)
    {
        $testCase = $this->getTestCaseById($id);

        // Only allow delete if the QA owns the test case
        if ($testCase->qa_id !== $qaId) {
            throw new \Exception('Unauthorized: You do not own this test case.');
        }

        return $this->testCaseRepository->deleteById($id);
    }

}
