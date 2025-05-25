<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'client_id' => $this->client_id,
            'name' => $this->name,
            'email' => $this->email,
            'company' => $this->company,
            'status' => $this->status ?? 'active',
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'applications' => ApplicationResource::collection($this->whenLoaded('applications'))
        ];
    }
}