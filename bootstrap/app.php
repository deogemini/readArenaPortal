<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'reader' => \App\Http\Middleware\EnsureReader::class,
            'author' => \App\Http\Middleware\EnsureAuthor::class,
            'track.user.activity' => \App\Http\Middleware\TrackUserActivity::class,
            'set.locale' => \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
        $middleware->appendToGroup('api', \App\Http\Middleware\SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['smtp_password', 'firebase_service_account_json', 'client_secret']);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $message = 'This upload exceeds the server request limit. Book PDFs may be up to '.(int) ceil(config('uploads.book_pdf_max_kb', 102400) / 1024).'MB. If your file is smaller, the PHP or web server upload limit needs to be raised.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 413);
            }

            return response()->view('errors.413', ['message' => $message], 413);
        });
    })->create();
