<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Client\ClientService;
use App\Http\Resources\Api\ClientResource;
use App\Http\Requests\Api\Client\CreateClientRequest;
use App\Http\Requests\Api\Client\UpdateClientRequest;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="Clients",
 *     description="API Endpoints for Client management"
 * )
 */
class ClientController extends Controller
{
    use ApiResponse;

    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @OA\Get(
     *     path="/clients",
     *     tags={"Clients"},
     *     summary="Get list of clients",
     *     description="Returns a list of all clients",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Clients retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="client_id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                     @OA\Property(property="company", type="string", example="Acme Inc"),
     *                     @OA\Property(property="created_at", type="string", format="datetime"),
     *                     @OA\Property(property="updated_at", type="string", format="datetime")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index()
    {
        $clients = $this->clientService->getAllClients();
        return $this->successResponse(
            ClientResource::collection($clients),
            'Clients retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/clients",
     *     tags={"Clients"},
     *     summary="Create new client",
     *     description="Creates a new client",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation", "company"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="John Doe",
     *                 description="Name of the client"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="john@example.com",
     *                 description="Email address of the client"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="password123",
     *                 description="Password for the account",
     *                 minLength=8
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 example="password123",
     *                 description="Password confirmation"
     *             ),
     *             @OA\Property(
     *                 property="company",
     *                 type="string",
     *                 example="Acme Inc",
     *                 description="Company name of the client"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="client_id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="company", type="string", example="Acme Inc"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(
     *                     property="applications",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="app_id", type="string", format="uuid"),
     *                         @OA\Property(property="app_name", type="string", example="Test App"),
     *                         @OA\Property(property="app_url", type="string", example="https://testapp.com")
     *                     ),
     *                     nullable=true
     *                 )
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
    public function store(CreateClientRequest $request)
    {
        $client = $this->clientService->createClient($request->validated());
        return $this->successResponse(
            new ClientResource($client),
            'Client created successfully',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/clients/{id}",
     *     tags={"Clients"},
     *     summary="Get specific client",
     *     description="Returns details of a specific client",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="client_id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="company", type="string", example="Acme Inc"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(
     *                     property="applications",
     *                     type="array",
     *                     nullable=true,
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="app_id", type="string", format="uuid"),
     *                         @OA\Property(property="app_name", type="string", example="Test Application"),
     *                         @OA\Property(property="app_url", type="string", example="https://testapp.com"),
     *                         @OA\Property(property="platform", type="string", example="web"),
     *                         @OA\Property(property="description", type="string", example="Test application description"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Client not found"),
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
        $client = $this->clientService->getClientById($id);
        return $this->successResponse(
            new ClientResource($client),
            'Client retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/clients/{id}",
     *     tags={"Clients"},
     *     summary="Update client",
     *     description="Updates an existing client",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="John Doe Updated",
     *                 description="Updated name of the client"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="john.updated@example.com",
     *                 description="Updated email address of the client"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="newpassword123",
     *                 description="New password (optional)",
     *                 minLength=8
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 example="newpassword123",
     *                 description="New password confirmation"
     *             ),
     *             @OA\Property(
     *                 property="company",
     *                 type="string",
     *                 example="Acme Corp Updated",
     *                 description="Updated company name of the client"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="client_id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *                 @OA\Property(property="company", type="string", example="Acme Corp Updated"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
     *                 @OA\Property(
     *                     property="applications",
     *                     type="array",
     *                     nullable=true,
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="app_id", type="string", format="uuid"),
     *                         @OA\Property(property="app_name", type="string", example="Test Application"),
     *                         @OA\Property(property="app_url", type="string", example="https://testapp.com"),
     *                         @OA\Property(property="platform", type="string", example="web"),
     *                         @OA\Property(property="status", type="string", example="active")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Client not found"),
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
    public function update(UpdateClientRequest $request, $id)
    {
        $client = $this->clientService->updateClientById($id, $request->validated());
        return $this->successResponse(
            new ClientResource($client),
            'Client updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/clients/{id}",
     *     tags={"Clients"},
     *     summary="Delete client",
     *     description="Deletes a specific client",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Client not found"),
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
        $this->clientService->deleteClientById($id);
        return $this->successResponse(
            null,
            'Client deleted successfully'
        );
    }
}