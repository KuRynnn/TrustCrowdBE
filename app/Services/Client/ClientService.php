<?php

// app/Services/Client/ClientService.php
namespace App\Services\Client;

use App\Repositories\Client\ClientRepository;
use App\Exceptions\ClientNotFoundException;
use App\Events\ClientCreated;
use Illuminate\Support\Facades\Hash;

class ClientService
{
    protected $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function getAllClients()
    {
        return $this->clientRepository->all();
    }

    public function createClient(array $data)
    {
        $client = $this->clientRepository->create($data);
        event(new ClientCreated($client));

        return $client;
    }

    public function getClientById($id)
    {
        $client = $this->clientRepository->findById($id);

        if (!$client) {
            throw new ClientNotFoundException('Client not found');
        }

        return $client;
    }

    public function updateClientById($id, array $data)
    {
        $client = $this->getClientById($id);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->clientRepository->updateById($id, $data);
    }

    public function deleteClientById($id)
    {
        $client = $this->getClientById($id);
        return $this->clientRepository->deleteById($id);
    }
}