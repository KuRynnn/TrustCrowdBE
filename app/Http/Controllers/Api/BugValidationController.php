<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BugValidation\BugValidationService;
use App\Http\Resources\Api\BugValidationResource;
use App\Http\Requests\Api\BugValidation\CreateBugValidationRequest;
use App\Http\Requests\Api\BugValidation\UpdateBugValidationRequest;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="Bug Validations",
 *     description="API Endpoints for Bug Validation management"
 * )
 */
class BugValidationController extends Controller
{
    use ApiResponse;

    protected $bugValidationService;

    public function __construct(BugValidationService $bugValidationService)
    {
        $this->bugValidationService = $bugValidationService;
    }

    /**
     * @OA\Get(
     *     path="/bug-validations",
     *     tags={"Bug Validations"},
     *     summary="Get list of bug validations",
     *     description="Returns a list of all bug validations",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug validations retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="validation_id", type="string", format="uuid"),
     *                     @OA\Property(property="bug_id", type="string", format="uuid"),
     *                     @OA\Property(property="qa_id", type="string", format="uuid"),
     *                     @OA\Property(
     *                         property="validation_status",
     *                         type="string",
     *                         enum={"Valid", "Invalid", "Needs More Info"},
     *                         example="Valid"
     *                     ),
     *                     @OA\Property(property="comments", type="string", example="Bug verified and reproducible"),
     *                     @OA\Property(property="validated_at", type="string", format="datetime", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="datetime"),
     *                     @OA\Property(property="updated_at", type="string", format="datetime"),
     *                     @OA\Property(property="validation_time", type="integer", example=60, nullable=true),
     *                     @OA\Property(
     *                         property="application_details",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="app_name", type="string", example="Test Application"),
     *                         @OA\Property(property="app_id", type="string", format="uuid")
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
        $validations = $this->bugValidationService->getAllValidations();
        return $this->successResponse(
            BugValidationResource::collection($validations),
            'Bug validations retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/bug-validations",
     *     tags={"Bug Validations"},
     *     summary="Create new bug validation",
     *     description="Creates a new bug validation",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bug_id", "qa_id", "validation_status", "comments"},
     *             @OA\Property(property="bug_id", type="string", format="uuid"),
     *             @OA\Property(property="qa_id", type="string", format="uuid"),
     *             @OA\Property(
     *                 property="validation_status",
     *                 type="string",
     *                 enum={"Valid", "Invalid", "Needs More Info"},
     *                 example="Valid"
     *             ),
     *             @OA\Property(property="comments", type="string", example="Bug verified and reproducible"),
     *             @OA\Property(
     *                 property="validated_at",
     *                 type="string",
     *                 format="datetime",
     *                 nullable=true,
     *                 example="2025-03-12 10:00:00"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bug validation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug validation created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="validation_id", type="string", format="uuid"),
     *                 @OA\Property(property="bug_id", type="string", format="uuid"),
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(
     *                     property="validation_status",
     *                     type="string",
     *                     enum={"Valid", "Invalid", "Needs More Info"},
     *                     example="Valid"
     *                 ),
     *                 @OA\Property(property="comments", type="string", example="Bug verified and reproducible"),
     *                 @OA\Property(property="validated_at", type="string", format="datetime"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
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
     *                     property="bug_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The bug id field is required")
     *                 ),
     *                 @OA\Property(
     *                     property="validation_status",
     *                     type="array",
     *                     @OA\Items(type="string", example="The validation status must be one of: Valid, Invalid, Needs More Info")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(CreateBugValidationRequest $request)
    {
        $validation = $this->bugValidationService->createValidation($request->validated());
        return $this->successResponse(
            new BugValidationResource($validation),
            'Bug validation created successfully',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/bug-validations/{id}",
     *     tags={"Bug Validations"},
     *     summary="Get specific bug validation",
     *     description="Returns details of a specific bug validation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bug validation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug validation retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="validation_id", type="string", format="uuid"),
     *                 @OA\Property(property="bug_id", type="string", format="uuid"),
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(
     *                     property="validation_status",
     *                     type="string",
     *                     enum={"Valid", "Invalid", "Needs More Info"},
     *                     example="Valid"
     *                 ),
     *                 @OA\Property(property="comments", type="string", example="Bug verified and reproducible"),
     *                 @OA\Property(property="validated_at", type="string", format="datetime"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime"),
     *                 @OA\Property(property="validation_time", type="integer", example=60),
     *                 @OA\Property(
     *                     property="bug_report",
     *                     type="object",
     *                     @OA\Property(property="bug_id", type="string", format="uuid"),
     *                     @OA\Property(property="description", type="string", example="Login button not working")
     *                 ),
     *                 @OA\Property(
     *                     property="qa_specialist",
     *                     type="object",
     *                     @OA\Property(property="qa_id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", example="John Doe")
     *                 ),
     *                 @OA\Property(
     *                     property="application_details",
     *                     type="object",
     *                     @OA\Property(property="app_name", type="string", example="Test Application"),
     *                     @OA\Property(property="app_id", type="string", format="uuid")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bug validation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bug validation not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $validation = $this->bugValidationService->getValidationById($id);
        return $this->successResponse(
            new BugValidationResource($validation),
            'Bug validation retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/bug-validations/{id}",
     *     tags={"Bug Validations"},
     *     summary="Update bug validation",
     *     description="Updates an existing bug validation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bug validation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="qa_id", type="string", format="uuid"),
     *             @OA\Property(
     *                 property="validation_status",
     *                 type="string",
     *                 enum={"Valid", "Invalid", "Needs More Info"},
     *                 example="Valid"
     *             ),
     *             @OA\Property(property="comments", type="string", example="Updated: Bug verified and reproducible"),
     *             @OA\Property(
     *                 property="validated_at",
     *                 type="string",
     *                 format="datetime",
     *                 nullable=true,
     *                 example="2025-03-12 10:00:00"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bug validation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug validation updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="validation_id", type="string", format="uuid"),
     *                 @OA\Property(property="bug_id", type="string", format="uuid"),
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(
     *                     property="validation_status",
     *                     type="string",
     *                     enum={"Valid", "Invalid", "Needs More Info"},
     *                     example="Valid"
     *                 ),
     *                 @OA\Property(property="comments", type="string", example="Updated: Bug verified and reproducible"),
     *                 @OA\Property(property="validated_at", type="string", format="datetime"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bug validation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bug validation not found"),
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
     *                     property="validation_status",
     *                     type="array",
     *                     @OA\Items(type="string", example="The validation status must be one of: Valid, Invalid, Needs More Info")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateBugValidationRequest $request, $id)
    {
        $validation = $this->bugValidationService->updateValidationById($id, $request->validated());
        return $this->successResponse(
            new BugValidationResource($validation),
            'Bug validation updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/bug-validations/{id}",
     *     tags={"Bug Validations"},
     *     summary="Delete bug validation",
     *     description="Deletes a specific bug validation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bug validation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bug validation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bug validation deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bug validation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bug validation not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->bugValidationService->deleteValidationById($id);
        return $this->successResponse(
            null,
            'Bug validation deleted successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/bug-validations/pending",
     *     tags={"Bug Validations"},
     *     summary="Get list of pending bug validations",
     *     description="Returns a list of bug validations with pending status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Pending bug validations retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="validation_id", type="string", format="uuid"),
     *                     @OA\Property(property="bug_id", type="string", format="uuid"),
     *                     @OA\Property(property="qa_id", type="string", format="uuid"),
     *                     @OA\Property(
     *                         property="validation_status",
     *                         type="string",
     *                         enum={"Valid", "Invalid", "Needs More Info"},
     *                         example="Needs More Info"
     *                     ),
     *                     @OA\Property(property="comments", type="string", example="Awaiting additional information"),
     *                     @OA\Property(property="validated_at", type="string", format="datetime", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="datetime"),
     *                     @OA\Property(property="updated_at", type="string", format="datetime"),
     *                     @OA\Property(
     *                         property="bug_report",
     *                         type="object",
     *                         @OA\Property(property="bug_id", type="string", format="uuid"),
     *                         @OA\Property(property="description", type="string", example="Login issue")
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
    public function getPendingValidations()
    {
        $validations = $this->bugValidationService->getPendingValidations();
        return $this->successResponse(
            BugValidationResource::collection($validations),
            'Pending bug validations retrieved successfully'
        );
    }
}