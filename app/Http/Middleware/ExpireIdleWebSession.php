<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExpireIdleWebSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if (! $user) {
            return $next($request);
        }

        $lastActivity = $request->session()->get('_auth_last_activity');
        $timeoutSeconds = max(1, (int) config('session.lifetime')) * 60;

        if (! is_numeric($lastActivity) || now()->timestamp - (int) $lastActivity >= $timeoutSeconds) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flash('status', __('Your session expired. Please sign in again.'));

            return $next($request);
        }

        $request->session()->put('_auth_last_activity', now()->timestamp);

        return $next($request);
    }
}
