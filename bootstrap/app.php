<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // Pengecualian keamanan CSRF untuk Webhook Telegram dan Logout
        $middleware->validateCsrfTokens(except: [
            '/telegram/webhook',
            '/logout',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Tangani sesi kedaluwarsa (419 Token Mismatch) agar tidak memunculkan layar hitam error
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('info', 'Sesi Anda telah berakhir atau berpindah akun. Silakan login kembali.');
        });
    })->create();