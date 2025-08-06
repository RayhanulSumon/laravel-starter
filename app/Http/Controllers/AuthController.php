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

    /**
     * Send password reset link to user's email or phone.
     */
    public function requestPasswordReset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        if ($request->filled('email')) {
            $status = \Illuminate\Support\Facades\Password::sendResetLink(
                $request->only('email')
            );
            if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
                return response()->json(['message' => 'Password reset link sent to email.']);
            }
            return response()->json(['message' => 'Unable to send reset link to email.'], 400);
        }

        if ($request->filled('phone')) {
            $user = \App\Models\User::where('phone', $request->phone)->first();
            if (! $user) {
                return response()->json(['message' => 'User with this phone not found.'], 404);
            }
            // Generate a random 6-digit code
            $code = random_int(100000, 999999);
            // Store code in cache for 10 minutes (or use DB if preferred)
            \Illuminate\Support\Facades\Cache::put('password_reset_' . $user->phone, $code, now()->addMinutes(10));
            // Send SMS (replace with your SMS provider logic)
            // Example: SmsService::send($user->phone, "Your password reset code is: $code");
            // For now, just return the code in response for testing
            return response()->json(['message' => 'Password reset code sent to phone.', 'code' => $code]);
        }

        return response()->json(['message' => 'Email or phone is required.'], 422);
    }

    /**
     * Reset user's password using token (email) or code (phone).
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'token' => 'nullable|string',
            'code' => 'nullable|string',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($request->filled('email') && $request->filled('token')) {
            $status = \Illuminate\Support\Facades\Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->save();
                }
            );
            if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
                return response()->json(['message' => 'Password has been reset.']);
            }
            return response()->json(['message' => 'Invalid token or email.'], 400);
        }

        if ($request->filled('phone') && $request->filled('code')) {
            $user = \App\Models\User::where('phone', $request->phone)->first();
            if (! $user) {
                return response()->json(['message' => 'User with this phone not found.'], 404);
            }
            $cachedCode = \Illuminate\Support\Facades\Cache::get('password_reset_' . $user->phone);
            if ($cachedCode && $cachedCode == $request->code) {
                $user->forceFill([
                    'password' => Hash::make($request->password)
                ])->save();
                // Remove code from cache
                \Illuminate\Support\Facades\Cache::forget('password_reset_' . $user->phone);
                return response()->json(['message' => 'Password has been reset.']);
            }
            return response()->json(['message' => 'Invalid or expired code.'], 400);
        }

        return response()->json(['message' => 'Required fields missing.'], 422);
    }
}
