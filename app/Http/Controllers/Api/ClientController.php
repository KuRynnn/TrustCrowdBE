<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Client\ClientService;
use App\Http\Resources\Api\ClientResource;
use App\Http\Requests\Api\Client\CreateClientRequest;
use App\Http\Requests\Api\Client\UpdateClientRequest;
use App\Traits\ApiResponse;

class ClientController extends Controller
{
    use ApiResponse;

    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index()
    {
        $clients = $this->clientService->getAllClients();
        return $this->successResponse(
            ClientResource::collection($clients),
            'Clients retrieved successfully'
        );
    }

    public function store(CreateClientRequest $request)
    {
        $client = $this->clientService->createClient($request->validated());
        return $this->successResponse(
            new ClientResource($client),
            'Client created successfully',
            201
        );
    }

    public function show($id)
    {
        $client = $this->clientService->getClientById($id);
        return $this->successResponse(
            new ClientResource($client),
            'Client retrieved successfully'
        );
    }

    public function update(UpdateClientRequest $request, $id)
    {
        $client = $this->clientService->updateClientById($id, $request->validated());
        return $this->successResponse(
            new ClientResource($client),
            'Client updated successfully'
        );
    }

    public function destroy($id)
    {
        $this->clientService->deleteClientById($id);
        return $this->successResponse(
            null,
            'Client deleted successfully'
        );
    }
}