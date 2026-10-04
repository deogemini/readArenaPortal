<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['nullable', 'string', 'in:reader,author'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'reader',
            'email_verified_at' => now(),
        ]);

        return $this->tokenResponse($user, 'Registration successful', $validated['device_name'] ?? 'android-app', 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);

        $user = User::where('email', Str::lower($validated['email']))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return $this->tokenResponse($user, 'Login successful', $validated['device_name'] ?? 'android-app');
    }

    public function google(Request $request)
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:reader,author'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $googleUser = Socialite::driver('google')->userFromToken($validated['access_token']);
        } catch (\Throwable) {
            return response()->json(['message' => 'Unable to authenticate with Google'], 401);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return response()->json(['message' => 'Google account did not provide an email address'], 422);
        }

        $email = Str::lower($email);
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'role' => $validated['role'] ?? 'reader',
            ]);
        }

        return $this->tokenResponse($user, 'Login successful', $validated['device_name'] ?? 'android-app');
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink(['email' => Str::lower($validated['email'])]);

        // Use the same response for known and unknown emails to avoid account discovery.
        return response()->json([
            'message' => 'If an account exists for this email, a password reset link has been sent.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            [
                'email' => Str::lower($validated['email']),
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $validated['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 422);
        }

        return response()->json(['message' => 'Password reset successfully. Please sign in again.']);
    }

    private function tokenResponse(User $user, string $message, string $deviceName, int $status = 200)
    {
        return response()->json([
            'message' => $message,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
            ],
            'token' => $user->createToken($deviceName)->plainTextToken,
            'token_type' => 'Bearer',
        ], $status);
    }
}
