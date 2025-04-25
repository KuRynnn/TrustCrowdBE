<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TestAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('Test Auth Middleware running');

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }

        Log::info('Token received', ['token' => substr($token, 0, 10) . '...']);

        // Parse token ID from format "id|token"
        $parts = explode('|', $token, 2);
        if (count($parts) !== 2 || !is_numeric($parts[0])) {
            return response()->json(['error' => 'Invalid token format'], 401);
        }

        $tokenId = $parts[0];

        // Find token in database
        $tokenRecord = DB::table('personal_access_tokens')->find($tokenId);

        if (!$tokenRecord) {
            return response()->json(['error' => 'Token not found'], 401);
        }

        Log::info('Token found', [
            'id' => $tokenRecord->id,
            'tokenable_type' => $tokenRecord->tokenable_type,
            'tokenable_id' => $tokenRecord->tokenable_id
        ]);

        // Find the user based on tokenable type and ID
        $userClass = $tokenRecord->tokenable_type;

        // Map user types to their primary key fields
        $keyMap = [
            'App\\Models\\Client' => 'client_id',
            'App\\Models\\Crowdworker' => 'worker_id',
            'App\\Models\\QASpecialist' => 'qa_id',
            'App\\Models\\User' => 'id',
        ];

        $keyField = $keyMap[$userClass] ?? 'id';

        try {
            // Try to find the user
            $user = $userClass::where($keyField, $tokenRecord->tokenable_id)->first();

            if (!$user) {
                Log::error('User not found', [
                    'tokenable_type' => $tokenRecord->tokenable_type,
                    'tokenable_id' => $tokenRecord->tokenable_id,
                    'key_field' => $keyField
                ]);
                return response()->json(['error' => 'User not found'], 401);
            }

            Log::info('User found', [
                'id' => $user->getKey(),
                'email' => $user->email,
                'class' => get_class($user)
            ]);

            // Determine guard
            $guardMap = [
                'App\\Models\\Client' => 'client',
                'App\\Models\\Crowdworker' => 'crowdworker',
                'App\\Models\\QASpecialist' => 'qa_specialist',
                'App\\Models\\User' => 'api',
            ];

            $guard = $guardMap[$userClass] ?? 'api';

            // Manually authenticate user
            Auth::guard($guard)->setUser($user);
            Auth::setUser($user);

            // Set safe attributes
            $request->attributes->set('safe_user_id', $tokenRecord->tokenable_id);
            $request->attributes->set('safe_user_type', $tokenRecord->tokenable_type);
            $request->attributes->set('safe_token_id', $tokenRecord->id);

            Log::info('Authentication completed', [
                'guard' => $guard,
                'is_authenticated' => Auth::guard($guard)->check(),
                'attributes' => $request->attributes->all()
            ]);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Authentication error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Authentication error: ' . $e->getMessage()], 500);
        }
    }
}