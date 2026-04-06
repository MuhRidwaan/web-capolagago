<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'                => RoleMiddleware::class,
            'permission'          => PermissionMiddleware::class,
            'role_or_permission'  => RoleOrPermissionMiddleware::class,
        ]);

        // Webhook Midtrans tidak butuh CSRF token (server-to-server)
        $middleware->validateCsrfTokens(except: [
            'payment/webhook/midtrans',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tampilkan halaman 403 yang proper saat akses ditolak (permission/role)
        $exceptions->render(function (
            \Spatie\Permission\Exceptions\UnauthorizedException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            return response()->view('errors.403', [
                'message' => 'Kamu tidak memiliki izin untuk mengakses halaman ini.',
            ], 403);
        });
    })->create();

RedirectIfAuthenticated::redirectUsing(function () {
    return route('admin.dashboard');
});
