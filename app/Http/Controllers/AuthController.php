<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user and return user with token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => isset($validated['role']) ? UserRole::tryFrom($validated['role'])->value : UserRole::USER->value,
        ]);

        // Send notification (broadcast + database)
        $user->notify(new NewUserRegistered(
            $user->getAttribute('name'),
            $user->getAttribute('email'),
            $user->getAttribute('phone')
        ));

        $token = $user->createToken('api-token', [$user->getRoleValue()])->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user and return user with token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'identifier' => 'required', // can be email or phone
            'password' => 'required',
        ]);

        $user = User::findByEmailOrPhone($credentials['identifier']);

        if (! $user || ! Hash::check($credentials['password'], $user->getAuthPassword())) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $abilities = [$user->getRoleValue()];
        $token = $user->createToken('api-token', $abilities)->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout user (revoke current token).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
