<?php

// app/Http/Controllers/Api/AuthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Crowdworker;
use App\Models\QASpecialist;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 */
class AuthController extends Controller
{
    use ApiResponse;
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        switch ($data['user_type']) {
            case 'client':
                $user = Client::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'company' => $data['company']
                ]);
                break;

            case 'crowdworker':
                $user = Crowdworker::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'skills' => $data['skills']
                ]);
                break;

            case 'qa_specialist':
                $user = QASpecialist::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'expertise' => $data['expertise']
                ]);
                break;

            default:
                return $this->errorResponse('Invalid user type', 422);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'User registered successfully', 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        Log::info('Login attempt', ['email' => $data['email']]);

        // Find user in each model and determine role
        $role = null;
        $user = null;

        if ($client = Client::where('email', $data['email'])->first()) {
            $user = $client;
            $role = 'client';
        } elseif ($crowdworker = Crowdworker::where('email', $data['email'])->first()) {
            $user = $crowdworker;
            $role = 'crowdworker';
        } elseif ($qaSpecialist = QASpecialist::where('email', $data['email'])->first()) {
            $user = $qaSpecialist;
            $role = 'qa_specialist';
        }

        if (!$user) {
            Log::error('User not found', ['email' => $data['email']]);
            return $this->errorResponse('User not found', 401);
        }

        // Debug password check
        Log::debug('Password check', [
            'plain_starts_with' => substr($data['password'], 0, 3) . '***',
            'hash_starts_with' => substr($user->password, 0, 10) . '...',
            'user_type' => get_class($user),
            'user_id' => $user->getKey()
        ]);

        if (!Hash::check($data['password'], $user->password)) {
            Log::error('Password incorrect', ['email' => $data['email']]);
            return $this->errorResponse('Password incorrect', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        Log::info('Login successful', [
            'user_id' => $user->getKey(),
            'token_id' => explode('|', $token)[0],
            'role' => $role
        ]);

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
            'role' => $role
        ], 'Logged in successfully');
    }

    public function logout(Request $request)
    {
        try {
            Log::info('Logout attempt started');

            // Get authenticated user (should be available now)
            $user = $request->user();

            if ($user) {
                Log::info('User authenticated via standard auth', ['id' => $user->getKey(), 'type' => get_class($user)]);
            } else {
                Log::info('No authenticated user found');
            }

            // Get token from bearer token
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                $parts = explode('|', $bearerToken, 2);
                $tokenId = $parts[0] ?? null;

                if ($tokenId) {
                    DB::table('personal_access_tokens')
                        ->where('id', $tokenId)
                        ->delete();

                    Log::info('Token deleted', ['token_id' => $tokenId]);
                }
            }

            return $this->successResponse(null, 'Logged out successfully');
        } catch (\Exception $e) {
            Log::error('Exception during logout', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('An error occurred', 500);
        }
    }
}
