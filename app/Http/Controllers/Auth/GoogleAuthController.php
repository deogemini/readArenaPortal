<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $exception) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign in failed. Please try again.',
            ]);
        }

        $email = $googleUser->getEmail();

        if (!$email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google account did not provide an email address.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $name = $googleUser->getName() ?: Str::before($email, '@');

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'role' => 'reader',
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $defaultRoute = $user->isAdmin()
            ? route('admin.dashboard', absolute: false)
            : ($user->isAuthor()
                ? route('author.dashboard', absolute: false)
                : route('reader.dashboard', absolute: false));

        return redirect()->intended($defaultRoute);
    }
}
