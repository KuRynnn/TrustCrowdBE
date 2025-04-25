<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QASpecialist\QASpecialistService;
use App\Http\Resources\Api\QASpecialistResource;
use App\Http\Requests\Api\QASpecialist\CreateQASpecialistRequest;
use App\Http\Requests\Api\QASpecialist\UpdateQASpecialistRequest;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="QA Specialists",
 *     description="API Endpoints for QA Specialist management"
 * )
 */
class QASpecialistController extends Controller
{
    use ApiResponse;

    protected $qaSpecialistService;

    public function __construct(QASpecialistService $qaSpecialistService)
    {
        $this->qaSpecialistService = $qaSpecialistService;
    }

    /**
     * @OA\Get(
     *     path="/qa-specialists",
     *     tags={"QA Specialists"},
     *     summary="Get list of QA specialists",
     *     description="Returns a paginated list of all QA specialists",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="QA Specialists retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="qa_id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string", example="Jane Smith"),
     *                         @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *                         @OA\Property(property="expertise", type="string", example="Web Testing, Security Testing"),
     *                         @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                         @OA\Property(
     *                             property="test_cases",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="test_case_id", type="string", format="uuid"),
     *                                 @OA\Property(property="title", type="string", example="Login Functionality Test"),
     *                                 @OA\Property(property="status", type="string", example="Approved")
     *                             ),
     *                             nullable=true
     *                         ),
     *                         @OA\Property(
     *                             property="bug_validations",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="validation_id", type="string", format="uuid"),
     *                                 @OA\Property(property="bug_id", type="string", format="uuid"),
     *                                 @OA\Property(property="status", type="string", example="Verified"),
     *                                 @OA\Property(property="comments", type="string", example="Bug verified and confirmed")
     *                             ),
     *                             nullable=true
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50)
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
        $specialists = $this->qaSpecialistService->getAllSpecialists();
        return $this->successResponse(
            QASpecialistResource::collection($specialists),
            'QA Specialists retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/qa-specialists",
     *     tags={"QA Specialists"},
     *     summary="Create new QA specialist",
     *     description="Creates a new QA specialist account",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "expertise", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Jane Smith"),
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *             @OA\Property(property="expertise", type="string", example="Web Testing, Security Testing"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="QA Specialist created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="QA Specialist created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="Jane Smith"),
     *                 @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *                 @OA\Property(property="expertise", type="string", example="Web Testing, Security Testing"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00")
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
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="The email has already been taken")
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
    public function store(CreateQASpecialistRequest $request)
    {
        $specialist = $this->qaSpecialistService->createSpecialist($request->validated());
        return $this->successResponse(
            new QASpecialistResource($specialist),
            'QA Specialist created successfully',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/qa-specialists/{id}",
     *     tags={"QA Specialists"},
     *     summary="Get specific QA specialist",
     *     description="Returns details of a specific QA specialist",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="QA Specialist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="QA Specialist retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="Jane Smith"),
     *                 @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *                 @OA\Property(property="expertise", type="string", example="Web Testing, Security Testing"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(
     *                     property="test_cases",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="test_case_id", type="string", format="uuid"),
     *                         @OA\Property(property="title", type="string", example="Login Functionality Test"),
     *                         @OA\Property(property="status", type="string", example="Approved")
     *                     ),
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="bug_validations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="validation_id", type="string", format="uuid"),
     *                         @OA\Property(property="bug_id", type="string", format="uuid"),
     *                         @OA\Property(property="status", type="string", example="Verified"),
     *                         @OA\Property(property="comments", type="string", example="Bug verified and confirmed")
     *                     ),
     *                     nullable=true
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QA Specialist not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="QA Specialist not found"),
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
        $specialist = $this->qaSpecialistService->getSpecialistById($id);
        return $this->successResponse(
            new QASpecialistResource($specialist),
            'QA Specialist retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/qa-specialists/{id}",
     *     tags={"QA Specialists"},
     *     summary="Update QA specialist",
     *     description="Updates an existing QA specialist's information",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="QA Specialist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Smith Updated"),
     *             @OA\Property(property="email", type="string", format="email", example="jane.updated@example.com"),
     *             @OA\Property(property="expertise", type="string", example="Web Testing, Security Testing, API Testing"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QA Specialist updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="QA Specialist updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="qa_id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="Jane Smith Updated"),
     *                 @OA\Property(property="email", type="string", format="email", example="jane.updated@example.com"),
     *                 @OA\Property(property="expertise", type="string", example="Web Testing, Security Testing, API Testing"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QA Specialist not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="QA Specialist not found"),
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
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="The email has already been taken")
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
    public function update(UpdateQASpecialistRequest $request, $id)
    {
        $specialist = $this->qaSpecialistService->updateSpecialistById($id, $request->validated());
        return $this->successResponse(
            new QASpecialistResource($specialist),
            'QA Specialist updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/qa-specialists/{id}",
     *     tags={"QA Specialists"},
     *     summary="Delete QA specialist",
     *     description="Deletes a QA specialist account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="QA Specialist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QA Specialist deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="QA Specialist deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QA Specialist not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="QA Specialist not found"),
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
        $this->qaSpecialistService->deleteSpecialistById($id);
        return $this->successResponse(
            null,
            'QA Specialist deleted successfully'
        );
    }
}