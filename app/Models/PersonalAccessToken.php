<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Illuminate\Support\Facades\Log;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected static function booted()
    {
        static::retrieved(function ($token) {
            Log::info('Token retrieved', ['id' => $token->id, 'name' => $token->name]);
        });

        static::deleted(function ($token) {
            Log::info('Token deleted', ['id' => $token->id]);
        });
    }
}