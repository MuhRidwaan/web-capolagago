<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('password.change', 'password.change.update', 'logout')) {
            return $next($request);
        }

        return redirect()
            ->route('password.change')
            ->with('error', 'Password sementara terdeteksi. Silakan ganti password terlebih dahulu.');
    }
}
