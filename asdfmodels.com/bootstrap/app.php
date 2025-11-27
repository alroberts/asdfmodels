<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'turnstile' => \App\Http\Middleware\VerifyTurnstile::class,
            'profile.complete' => \App\Http\Middleware\EnsureProfileComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Check if dev mode is enabled (skip for console commands to avoid DB issues during migrations)
        if (!app()->runningInConsole()) {
            try {
                $devMode = \App\Models\Setting::getValue('dev_mode', false);
                if ($devMode) {
                    // Show detailed errors in dev mode
                    $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
                        if ($request->is('admin/*')) {
                            // For admin routes, show detailed error
                            return response()->view('errors.dev-mode', [
                                'exception' => $e,
                                'message' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                                'trace' => $e->getTraceAsString(),
                            ], 500);
                        }
                    });
                }
            } catch (\Exception $e) {
                // If settings table doesn't exist, ignore
            }
        }
    })->create();
