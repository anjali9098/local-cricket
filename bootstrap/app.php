<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\IsSuperAdmin::class,
            'localadmin' => \App\Http\Middleware\IsLocalAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle CSRF Token Mismatch & Session Expiration (TokenMismatchException)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page.',
                    'csrf_token' => csrf_token(),
                ], 419);
            }
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('error', 'Your session expired due to inactivity. Please try submitting again.');
        });

        // Handle 419 HTTP Exceptions (Laravel's prepared TokenMismatchException)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419 || str_contains($e->getMessage(), 'CSRF')) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Your session expired. Please refresh the page.',
                        'csrf_token' => csrf_token(),
                    ], 419);
                }
                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation', '_token'))
                    ->with('error', 'Your session expired due to inactivity. Please try submitting again.');
            }
            return null; // Let Laravel render standard 404, 403, etc. error views
        });

        // Handle genuine uncaught 500 server errors
        $exceptions->render(function (\Throwable $e, $request) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return null; // Let Laravel render normal HTTP status page
            }
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => $e->getMessage(),
                ], 500);
            }
            return response()->view('errors.500', ['exception' => $e], 500);
        });
    })->create();

