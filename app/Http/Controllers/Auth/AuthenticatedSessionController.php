<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\UserActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('locale', $request->user()->locale ?: 'en');
        app()->setLocale($request->user()->locale ?: 'en');
        app(UserActivityRecorder::class)->record($request->user(), 'signed_in', 'web_portal');

        $defaultRoute = auth()->user()->isAdmin()
            ? route('admin.dashboard', absolute: false)
            : (auth()->user()->isAuthor()
                ? route('author.dashboard', absolute: false)
                : route('reader.dashboard', absolute: false));

        return redirect()->intended($defaultRoute);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if ($user = $request->user()) {
            app(UserActivityRecorder::class)->record($user, 'signed_out', 'web_portal', false);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
