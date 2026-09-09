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
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch. Please refresh.'], 419);
            }
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->withErrors(['email' => 'Your session expired. Please try signing in again.']);
        });

        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 500);
            }
            return response()->make(
                '<div style="background:#0f172a;color:#f8fafc;padding:30px;font-family:sans-serif;min-height:100vh;">' .
                '<h2 style="color:#ef4444;margin-top:0;">🚨 Server Error Diagnostic</h2>' .
                '<p style="color:#f59e0b;font-size:1.15rem;font-weight:bold;">' . htmlspecialchars($e->getMessage()) . '</p>' .
                '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' : Line ' . $e->getLine() . '</p>' .
                '<pre style="background:#1e293b;padding:15px;border-radius:6px;overflow:auto;font-size:0.85rem;line-height:1.5;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>' .
                '</div>',
                500
            );
        });
    })->create();

