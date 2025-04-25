<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SafeSanctumMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('SafeSanctum middleware running');

        // Skip processing for requests that don't need authentication
        if ($this->shouldSkipAuthentication($request)) {
            Log::info('Skipping authentication for path', ['path' => $request->path()]);
            return $next($request);
        }

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }

        Log::info('Token received', ['token' => substr($token, 0, 10) . '...']);

        // Find token in database using the hashed value
        $tokenRecord = DB::table('personal_access_tokens')
            ->where('token', hash('sha256', $token))
            ->first();

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

    /**
     * Determine if the request should skip authentication
     */
    protected function shouldSkipAuthentication(Request $request)
    {
        // Skip for login, register, and other public routes
        $publicPaths = [
            'api/auth/login',
            'api/auth/register',
            'api/clients', // POST only
            'api/crowdworkers', // POST only
            'api/qa-specialists', // POST only
            'api/documentation',
            'api/oauth2-callback',
            'docs/*',
        ];

        foreach ($publicPaths as $path) {
            if ($request->is($path)) {
                // For the client registration endpoints, only skip for POST
                if (
                    in_array($path, ['api/clients', 'api/crowdworkers', 'api/qa-specialists']) &&
                    $request->method() !== 'POST'
                ) {
                    continue;
                }
                return true;
            }
        }

        return false;
    }
}
