<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ClientResource",
 *     @OA\Property(property="client_id", type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="company", type="string", example="Acme Inc"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-12 10:00:00"),
 *     @OA\Property(
 *         property="applications",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ApplicationResource"),
 *         nullable=true
 *     )
 * )
 */
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