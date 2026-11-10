<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LeaderboardService;
use App\Services\RewardService;
use App\Services\UserActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if (is_string($email = $request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['nullable', 'string', 'in:reader,author'],
            'device_name' => ['nullable', 'string', 'max:80'],
            'locale' => ['nullable', 'string', Rule::in(['en', 'sw'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'reader',
            'locale' => $validated['locale'] ?? (in_array($request->header('X-Locale'), ['en', 'sw'], true) ? $request->header('X-Locale') : 'en'),
            'email_verified_at' => now(),
        ]);

        app(UserActivityRecorder::class)->record($user, 'registered', 'android_app');
        app(RewardService::class)->awardAccountCreated($user);

        return $this->tokenResponse($user, 'Registration successful', $validated['device_name'] ?? 'android-app', 201);
    }

    public function login(Request $request)
    {
        if (is_string($email = $request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        app(UserActivityRecorder::class)->record($user, 'signed_in', 'android_app');
        app(RewardService::class)->awardDailyLogin($user);

        return $this->tokenResponse($user, 'Login successful', $validated['device_name'] ?? 'android-app');
    }

    public function google(Request $request)
    {
        $validated = $request->validate([
            'id_token' => ['nullable', 'required_without:access_token', 'string', 'max:8192'],
            'access_token' => ['nullable', 'required_without:id_token', 'string', 'max:8192'],
            'role' => ['nullable', 'string', 'in:reader,author'],
            'device_name' => ['nullable', 'string', 'max:80'],
            'locale' => ['nullable', 'string', Rule::in(['en', 'sw'])],
        ]);

        $googleToken = $validated['id_token'] ?? $validated['access_token'];
        $isIdToken = isset($validated['id_token']) || $this->isJwt($googleToken);

        try {
            if ($isIdToken) {
                $audience = $this->googleIdTokenAudience($googleToken);
                $allowedClientIds = config('services.google.client_ids', []);

                if (! $audience || ! in_array($audience, $allowedClientIds, true)) {
                    return response()->json(['message' => 'Unable to authenticate with Google'], 401);
                }

                // Google ID tokens are issued to a particular OAuth client. Build the
                // verifier for that configured audience so Android and web clients can
                // both sign in while Socialite still verifies the token signature.
                $googleProvider = Socialite::buildProvider(
                    GoogleProvider::class,
                    array_merge(config('services.google'), ['client_id' => $audience])
                );
                $googleUser = $googleProvider->userFromToken($googleToken);
            } else {
                $googleUser = Socialite::driver('google')->userFromToken($googleToken);
            }
        } catch (\Throwable) {
            return response()->json(['message' => 'Unable to authenticate with Google'], 401);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return response()->json(['message' => 'Google account did not provide an email address'], 422);
        }

        $email = Str::lower($email);
        $user = User::where('email', $email)->first();
        $isNewUser = ! $user;

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'role' => $validated['role'] ?? 'reader',
                'locale' => $validated['locale'] ?? (in_array($request->header('X-Locale'), ['en', 'sw'], true) ? $request->header('X-Locale') : 'en'),
            ]);
        }

        app(UserActivityRecorder::class)->record($user, $isNewUser ? 'registered' : 'signed_in', 'android_app');
        if ($isNewUser) {
            app(RewardService::class)->awardAccountCreated($user);
        } else {
            app(RewardService::class)->awardDailyLogin($user);
        }

        return $this->tokenResponse($user, 'Login successful', $validated['device_name'] ?? 'android-app');
    }

    private function isJwt(string $token): bool
    {
        return strlen($token) > 100 && substr_count($token, '.') === 2;
    }

    private function googleIdTokenAudience(string $token): ?string
    {
        if (! $this->isJwt($token)) {
            return null;
        }

        $parts = explode('.', $token);
        $payloadPart = strtr($parts[1], '-_', '+/');
        $payloadPart .= str_repeat('=', (4 - strlen($payloadPart) % 4) % 4);
        $payload = json_decode(base64_decode($payloadPart, true) ?: '', true);

        return is_array($payload) && is_string($payload['aud'] ?? null)
            ? $payload['aud']
            : null;
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
            'message' => Lang::get($message, [], $user->locale ?: 'en'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'locale' => $user->locale ?: 'en',
                'points' => app(LeaderboardService::class)->pointsForUser($user->id),
            ],
            'token' => $user->createToken($deviceName)->plainTextToken,
            'token_type' => 'Bearer',
        ], $status);
    }
}
