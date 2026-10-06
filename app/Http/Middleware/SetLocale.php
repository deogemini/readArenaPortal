<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $explicit = $request->header('X-Locale') ?? $request->query('locale');
        $sessionLocale = $request->hasSession() ? $request->session()->get('locale') : null;
        $userLocale = $request->user()?->locale;
        $accepted = strtolower(str_replace('-', '_', trim(explode(',', (string) $request->header('Accept-Language'))[0] ?? '')));
        $accepted = substr($accepted, 0, 2);

        $locale = collect([$explicit, $userLocale, $sessionLocale, $accepted, config('app.locale')])
            ->first(fn ($value) => in_array($value, ['en', 'sw'], true));

        $locale ??= 'en';
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        $response = $next($request);

        if ($request->is('api/*') && app()->getLocale() === 'sw' && $response instanceof JsonResponse) {
            $payload = $response->getData(true);
            if (is_array($payload) && isset($payload['message']) && is_string($payload['message'])) {
                $payload['message'] = Lang::get($payload['message'], [], 'sw');
                $response->setData($payload);
            }
        }

        return $response;
    }
}
