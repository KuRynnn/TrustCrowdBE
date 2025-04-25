<?php

// app/Repositories/Client/ClientRepository.php
namespace App\Repositories\Client;

use App\Models\Client;
use App\Repositories\BaseRepository;

class ClientRepository extends BaseRepository
{
    public function __construct(Client $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return $this->model
            ->with(['applications'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById($id)
    {
        return $this->model
            ->with(['applications'])
            ->where('client_id', $id)
            ->first();
    }

    public function findByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function updateById($id, array $data)
    {
        $client = $this->findById($id);
        return $client ? tap($client)->update($data) : null;
    }

    public function deleteById($id)
    {
        $client = $this->findById($id);
        return $client ? $client->delete() : null;
    }
}