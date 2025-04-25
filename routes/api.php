<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\UATTaskController;
use App\Http\Controllers\Api\BugReportController;
use App\Http\Controllers\Api\BugValidationController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CrowdworkerController;
use App\Http\Controllers\Api\QASpecialistController;
use App\Http\Controllers\Api\TestCaseController;
use App\Http\Controllers\Api\TaskValidationController;

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
});

// Public registration endpoints
Route::post('/clients', [ClientController::class, 'store']);
Route::post('/crowdworkers', [CrowdworkerController::class, 'store']);
Route::post('/qa-specialists', [QASpecialistController::class, 'store']);



// Client Routes
Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    // Route::post('/', [ClientController::class, 'store']);
    Route::get('/{id}', [ClientController::class, 'show']);
    Route::put('/{id}', [ClientController::class, 'update']);
    Route::delete('/{id}', [ClientController::class, 'destroy']);
});

// Crowdworker Routes
Route::prefix('crowdworkers')->group(function () {
    Route::get('/', [CrowdworkerController::class, 'index']);
    // Route::post('/', [CrowdworkerController::class, 'store']);
    Route::get('/{id}', [CrowdworkerController::class, 'show']);
    Route::put('/{id}', [CrowdworkerController::class, 'update']);
    Route::delete('/{id}', [CrowdworkerController::class, 'destroy']);
});

// QA Specialist Routes
Route::prefix('qa-specialists')->group(function () {
    Route::get('/', [QASpecialistController::class, 'index']);
    // Route::post('/', [QASpecialistController::class, 'store']);
    Route::get('/{id}', [QASpecialistController::class, 'show']);
    Route::put('/{id}', [QASpecialistController::class, 'update']);
    Route::delete('/{id}', [QASpecialistController::class, 'destroy']);
});

// Application Routes
Route::prefix('applications')->group(function () {
    Route::get('/', [ApplicationController::class, 'index']);
    Route::post('/', [ApplicationController::class, 'store']);

    Route::get('/client/{clientId}', [ApplicationController::class, 'getByClient']);
    Route::get('/platform/{platform}', [ApplicationController::class, 'getByPlatform']);
    Route::get('/available-for-crowdworker', [ApplicationController::class, 'getAvailableForCrowdworker']);

    Route::get('/{id}', [ApplicationController::class, 'show']);
    Route::put('/{id}', [ApplicationController::class, 'update']);
    Route::delete('/{id}', [ApplicationController::class, 'destroy']);
    Route::get('/{id}/statistics', [ApplicationController::class, 'getStatistics']);
    Route::get('/{id}/progress', [ApplicationController::class, 'getProgress']);
    Route::patch('/{id}/status', [ApplicationController::class, 'updateStatus']);
    Route::post('/{id}/pick', [ApplicationController::class, 'pickApplication']);
    Route::get('/{id}/final-report', [ApplicationController::class, 'getFinalReport']);
});

// UAT Task Routes
Route::prefix('uat-tasks')->group(function () {
    Route::get('/application/{appId}', [UATTaskController::class, 'getByApplication']);
    Route::get('/worker/{workerId}', [UATTaskController::class, 'getByWorker']);
    Route::get('/status/{status}', [UATTaskController::class, 'getByStatus']);

    Route::get('/', [UATTaskController::class, 'index']);
    Route::post('/', [UATTaskController::class, 'store']);

    Route::get('/{id}', [UATTaskController::class, 'show']);
    Route::put('/{id}', [UATTaskController::class, 'update']);
    Route::delete('/{id}', [UATTaskController::class, 'destroy']);
    Route::put('/{id}/start', [UATTaskController::class, 'startTask']);
    Route::put('/{id}/complete', [UATTaskController::class, 'completeTask']);
    Route::get('/{id}/progress', [UATTaskController::class, 'getTaskProgress']);
    Route::get('/{id}/bug-reports', [UATTaskController::class, 'getBugReports']);
});

// Test Case Routes
Route::prefix('test-cases')->group(function () {
    Route::get('/', [TestCaseController::class, 'index']);
    Route::post('/', [TestCaseController::class, 'store']);
    Route::get('/{id}', [TestCaseController::class, 'show']);
    Route::put('/{id}', [TestCaseController::class, 'update']);
    Route::delete('/{id}', [TestCaseController::class, 'destroy']);
});

// Bug Report Routes
Route::prefix('bug-reports')->group(function () {
    Route::get('/', [BugReportController::class, 'index']);
    Route::post('/', [BugReportController::class, 'store']);
    Route::get('/{id}', [BugReportController::class, 'show']);
    Route::put('/{id}', [BugReportController::class, 'update']);
    Route::delete('/{id}', [BugReportController::class, 'destroy']);

    Route::get('/task/{taskId}', [BugReportController::class, 'getByTask']);
    Route::get('/worker/{workerId}', [BugReportController::class, 'getByWorker']);
    Route::get('/severity/{severity}', [BugReportController::class, 'getBySeverity']);
    Route::get('/{id}/validation', [BugReportController::class, 'getValidation']);
    Route::post('/{id}/screenshot', [BugReportController::class, 'uploadScreenshot']);
    Route::get('/statistics', [BugReportController::class, 'getStatistics']);
});

// Bug Validation Routes
Route::prefix('bug-validations')->group(function () {
    Route::get('/', [BugValidationController::class, 'index']);
    Route::post('/', [BugValidationController::class, 'store']);
    Route::get('/{id}', [BugValidationController::class, 'show']);
    Route::put('/{id}', [BugValidationController::class, 'update']);
    Route::delete('/{id}', [BugValidationController::class, 'destroy']);

    Route::get('/pending', [BugValidationController::class, 'getPendingValidations']);
    Route::get('/qa/{qaId}', [BugValidationController::class, 'getByQA']);
    Route::get('/status/{status}', [BugValidationController::class, 'getByStatus']);
    Route::patch('/{id}/status', [BugValidationController::class, 'updateStatus']);
    Route::get('/statistics', [BugValidationController::class, 'getStatistics']);
    Route::get('/qa-performance/{qaId}', [BugValidationController::class, 'getQAPerformance']);
});

// Task Validation Routes
Route::prefix('task-validations')->group(function () {
    Route::post('/', [TaskValidationController::class, 'store']);
    Route::get('/{taskId}', [TaskValidationController::class, 'show']);
    Route::get('/check-readiness/{taskId}', [TaskValidationController::class, 'checkTaskReadiness']);
});
