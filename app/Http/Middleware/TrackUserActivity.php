<?php

namespace App\Http\Middleware;

use App\Services\UserActivityRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function __construct(private readonly UserActivityRecorder $activityRecorder)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() < 400 && $request->user()) {
            $this->activityRecorder->recordRequest($request->user(), $request);
        }

        return $response;
    }
}
