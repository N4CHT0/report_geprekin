<?php

// use Throwable;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckPermission;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => CheckPermission::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if (
                $e instanceof AuthenticationException ||
                $e instanceof TokenMismatchException ||
                ($e instanceof AuthorizationException && ! auth()->check())
            ) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Session expired, silakan login kembali.',
                    ], 401);
                }

                return redirect('/login')
                    ->with('error', 'Session login sudah habis, silakan login kembali.');
            }

            return null;
        });
    })

    ->create();